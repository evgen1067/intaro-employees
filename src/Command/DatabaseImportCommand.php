<?php

namespace App\Command;

use App\Entity\Company;
use App\Entity\Department;
use App\Entity\Employee;
use App\Exception\ApiException;
use App\Repository\CompanyRepository;
use App\Repository\DepartmentRepository;
use App\Repository\EmployeeRepository;
use App\Services\BitrixService;
use App\Services\EvolutionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'database:import')]
class DatabaseImportCommand extends Command
{
    public function __construct(
        private EvolutionService $evolutionService,
        private BitrixService $bitrixService,
        private DepartmentRepository $departmentRepository,
        private EmployeeRepository $employeeRepository,
        private CompanyRepository $companyRepository,
        string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $token = $this->evolutionService->auth();
            $departmentsBitrix = $this->bitrixService->getDepartments();
            foreach ($departmentsBitrix as $id => $departmentBitrix) {
                $dbResult = $this->departmentRepository->findOneBy(['bitrixId' => $id]);
                if (!empty($dbResult) && $dbResult->getName() === trim($departmentBitrix)) {
                    $departmentsBitrix[$id] = $dbResult;
                    continue;
                }
                $departmentsBitrix[$id] = new Department((int) $id, trim($departmentBitrix));
                $this->departmentRepository->save($departmentsBitrix[$id], true);
            }
            $output->writeln('Данные по отделам успешно загружены из Битрикс24');

            $dbResult = $this->departmentRepository->findOneBy(['bitrixId' => 0]);
            if (empty($dbResult) || $dbResult->getName() !== 'Не указано') {
                $emptyDepartment = new Department(0, 'Не указано');
                $this->departmentRepository->save($emptyDepartment, true);
            } else {
                $emptyDepartment = $dbResult;
            }

            $usersBitrix = $this->bitrixService->getUsers();
            $output->writeln('Данные по сотрудникам успешно загружены из Битрикс24');
            $output->writeln('Сотрудников получено из Битрикс24: '.count($usersBitrix));

            $usersEvolution = $this->evolutionService->getUsers($token);
            $output->writeln('Данные по сотрудникам успешно загружены из Evolution');
            $output->writeln('Сотрудников получено из Evolution: '.count($usersEvolution));

            $count = 0;
            foreach ($usersEvolution as $key => $userEvo) {
                $nameParts = explode(' ', $userEvo['title']);
                $usersEvolution[$key]['MAP_NAME'] = $nameParts[0].' '.$nameParts[1];
                $index = array_search($usersEvolution[$key]['MAP_NAME'], array_column($usersBitrix, 'MAP_NAME'));
                if (false !== $index) {
                    ++$count;
                    if (strlen($usersBitrix[$index]['name']) < strlen($userEvo['title'])) {
                        $usersBitrix[$index]['name'] = $userEvo['title'];
                    }

                    $companyKey = (
                        isset($userEvo['company']) && strlen($userEvo['company']) > 0
                    ) ? $userEvo['company'] : 'Не указано';

                    if (!isset($companies[$companyKey])) {
                        $dbResult = $this->companyRepository->findOneBy(['name' => $companyKey]);
                        if (empty($dbResult)) {
                            $companies[$companyKey] = new Company($companyKey);
                            $this->companyRepository->save($companies[$companyKey], true);
                        } else {
                            $companies[$companyKey] = $dbResult;
                        }
                    }

                    $usersBitrix[$index]['company'] = $companies[$companyKey];
                    $usersBitrix[$index]['competence'] = strlen($userEvo['competence']) > 0 ? $userEvo['competence'] : 'Не указано';
                    $usersBitrix[$index]['grade'] = strlen($userEvo['grade']) > 0 ? $userEvo['grade'] : 'не указано';

                    $bitrixId = (int) $usersBitrix[$index]['id'];
                    $evoId = (int) $userEvo['id'];

                    $employee = new Employee();
                    $employee
                        ->setName($usersBitrix[$index]['name'])
                        ->setGender($usersBitrix[$index]['gender'])
                        ->setDateOfBirth($usersBitrix[$index]['dateOfBirth'])
                        ->setDateOfEmployment($usersBitrix[$index]['dateOfEmployment'])
                        ->setPosition($usersBitrix[$index]['position'])
                        ->setStatus($usersBitrix[$index]['status'])
                        ->setCompany($usersBitrix[$index]['company'])
                        ->setCompetence($usersBitrix[$index]['competence'])
                        ->setGrade($usersBitrix[$index]['grade'])
                        ->setBitrixId($bitrixId)
                        ->setEvoId($evoId);
                    if (3 === $employee->getStatus()) {
                        $employee->setDateOfDismissal($usersBitrix[$index]['dateOfDismissal']);
                    }
                    foreach ($usersBitrix[$index]['departments'] as $userDepartment) {
                        $employee->addDepartment($departmentsBitrix[$userDepartment]);
                    }
                    // проверки на пустоту отделов и компаний
                    if (0 === count($usersBitrix[$index]['departments']) || !isset($usersBitrix[$index]['departments'])) {
                        $employee->addDepartment($emptyDepartment);
                    }

                    $dbResult = $this->employeeRepository->findOneBy(['evoId' => $evoId, 'bitrixId' => $bitrixId]);
                    if (empty($dbResult)) {
                        $this->employeeRepository->save($employee, true);
                    } else {
                        $flag = Employee::compareEmployees($dbResult, $employee);
                        if ($flag) {
                            continue;
                        }
                        $dbResult->updateEmployee($employee);
                        $this->employeeRepository->save($dbResult, true);
                    }
                }
            }
            $output->writeln('В БД загружены данные по '.$count.' сотрудникам');

            return Command::SUCCESS;
        } catch (ApiException|\JsonException|\Exception $e) {
            $output->writeln($e->getMessage());

            return Command::FAILURE;
        }
    }
}
