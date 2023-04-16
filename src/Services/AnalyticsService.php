<?php

namespace App\Services;

use App\Entity\Employee;
use App\Repository\EmployeeRepository;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;

class AnalyticsService
{
    private \DateTimeImmutable $valueTo;

    private \DateTimeImmutable $valueFrom;

    private array $departments;

    private array $companies;

    private EntityManagerInterface $em;

    public function __construct(
        \DateTimeImmutable     $valueFrom,
        \DateTimeImmutable     $valueTo,
        EntityManagerInterface $em
    )
    {
        $this->valueFrom = $valueFrom;
        $this->valueTo = $valueTo;
        $this->departments = [];
        $this->companies = [];
        $this->em = $em;
    }

    private function addRoleWhere(string $sql, array $params): array
    {
        if (count($this->companies) > 0) {
            $result = '';
            foreach ($this->companies as $companyId) {
                $result .= $companyId . ',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            $sql .= " and c.id in (" . $result . ")";
        }
        if (count($this->departments) > 0) {
            $result = '';
            foreach ($this->departments as $departmentId) {
                $result .= $departmentId . ',';
            }
            $result = mb_substr($result, 0, strlen($result) - 1);
            $sql .= " and d.id in (" . $result . ")";
        }
        return [$sql, $params];
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function getDismissal(array $roleFilters): array
    {
        $this->companies = $roleFilters['companies'];
        $this->departments = $roleFilters['departments'];
        $employees = $this->getEmployeesForDismissal($this->valueFrom, $this->valueTo, $roleFilters);
        // общее число увольнений по отделу
        $total = count($employees);
        // средний стаж работы по отделу
        $avgWorkExp = $this->getAverageWorkExperience($employees);
        // увольнения по стажу работы
        $workExpChart = $this->getDismissedByWorkExperience($employees);
        [
            $departmentChart, // данные по увольнениям в зависимости от отдела
            $positionChart, // данные по увольнениям в зависимости от должности
            $competenceChart, // данные по увольнениям в зависимости от компетенции
            $gradeChart, // данные по увольнениям в зависимости от грейда
            $genderChart, // данные по увольнениям в зависимости от пола
        ] = $this->getDismissedByFields($employees);
        // число сотрудников
        $countEmployees = $this->getTotalNumberOfEmployees($this->valueTo);
        // число принятых сотрудников
        $numberOfAcceptedEmployees = $this->getTotalNumberOfAccepted();
        // прогноз числа увольнений
        $predictions = $this->getDismissedPredictions($employees);

        return [
            'totalDismissed' => $total,
            'totalCount' => $countEmployees,
            'totalAccepted' => $numberOfAcceptedEmployees,
            'predictions' => $predictions,
            'avgWorkExp' => round($avgWorkExp, 3),
            'workExpChart' => $workExpChart,
            'genderChart' => $genderChart,
            'departmentChart' => $departmentChart,
            'positionChart' => $positionChart,
            'competenceChart' => $competenceChart,
            'gradeChart' => $gradeChart,
        ];
    }

    /**
     * @throws Exception
     */
    private function getEmployeesForDismissal(\DateTimeImmutable $valueFrom, \DateTimeImmutable $valueTo): array
    {
        $conn = $this->em->getConnection();
        $sql = "
        select
                e.id as id,
                e.name as name,
                e.gender as gender,
                e.date_of_birth as date_of_birth,
                e.date_of_employment as date_of_employment,
                coalesce(c.name, 'не указано') as company,
                coalesce(d.name, 'не указано') as department,
                e.position as position,
                e.status as status,
                e.competence as competence,
                e.grade as grade,
                e.date_of_dismissal as date_of_dismissal,
                e.reason_of_dismissal as reason_of_dismissal,
                e.category_of_dismissal as category_of_dismissal
             from employee e
             inner join company c
                        on c.id = e.company_id
             inner join employee_department ed
                        on e.id = ed.employee_id
             inner join department d
                        on d.id = ed.department_id
             where e.status = 3
                and e.date_of_dismissal >= :startDate
                and e.date_of_dismissal <= :endDate";
        $params = [
            'startDate' => $valueFrom->format('Y-m-d'),
            'endDate' => $valueTo->format('Y-m-d'),
        ];
        [$sql, $params] = $this->addRoleWhere($sql, $params);

        $sql .= " group by e.id, d.id, c.id";
        return $conn->prepare($sql)->executeQuery($params)->fetchAllAssociative();
    }

    /**
     * @throws \Exception
     */
    private function getAverageWorkExperience(array $employees): float
    {
        if (count($employees) === 0) return 0;
        $avgWorkExp = 0;
        foreach ($employees as $key => $item) {
            $workExp = Employee::getWorkExperience(
                new \DateTimeImmutable($item['date_of_employment']),
                new \DateTimeImmutable($item['date_of_dismissal']),
            );
            $avgWorkExp += (null !== $workExp ? $workExp : 0);
        }

        return fdiv($avgWorkExp, count($employees));
    }

    /**
     * @throws \Exception
     */
    private function getDismissedByWorkExperience(array $employees): array
    {
        $dismissedByWorkExp = [
            ['key' => 'Менее 3х месяцев', 'value' => 0],
            ['key' => 'До 1 года работы', 'value' => 0],
            ['key' => 'До 3х лет работы', 'value' => 0],
            ['key' => 'Свыше 3х лет работы', 'value' => 0],
        ];

        foreach ($employees as $key => $item) {
            $workExp = Employee::getWorkExperience(
                new \DateTimeImmutable($item['date_of_employment']),
                new \DateTimeImmutable($item['date_of_dismissal']),
            );

            if ($workExp < 0.25) {
                ++$dismissedByWorkExp[0]['value'];
            } elseif ($workExp < 1) {
                ++$dismissedByWorkExp[1]['value'];
            } elseif ($workExp < 3) {
                ++$dismissedByWorkExp[2]['value'];
            } else {
                ++$dismissedByWorkExp[3]['value'];
            }
        }

        return $dismissedByWorkExp;
    }

    private function getDismissedByFields(array $employees): array
    {
        $fields = ['department', 'position', 'competence', 'grade', 'gender'];
        $arrEnv = [];
        foreach ($fields as $query) {
            $_employees = $this->arrayFieldToKey($employees, $query);
            $data = [];

            foreach ($_employees as $key => $item) {
                if ($query === 'gender') {
                    $key = Employee::GENDER_TYPES[$key];
                }
                $data[] = [
                    'key' => $key,
                    'value' => $item,
                ];
            }
            $arrEnv[] = $data;
        }

        return $arrEnv;
    }

    /**
     * @throws Exception
     */
    public function getDismissedByYears(): array
    {
        $sql = "
        with years as (
            select
                distinct
                extract(year from e.date_of_dismissal) as year
            from employee e
            where e.status = 3
            order by year
        )
        select
            y.year as year,
            count(e.id) as count
        from years y
            inner join employee e
                on (extract(year from e.date_of_dismissal) = y.year and e.status = 3)
        group by y.year
        order by y.year
        ";
        $conn = $this->em->getConnection();
        return $conn->prepare($sql)->executeQuery([])->fetchAllAssociative();
    }

    /**
     * @throws Exception
     */
    private function getDismissedPredictions(array $employees): array
    {
        $sqlResult = $this->getDismissedByYears();

        $xArray = [];
        $yDismissedArray = [];
        $result = [];

        foreach ($sqlResult as $row) {
            $year = (int) $row['year'];
            $count = (int) $row['count'];
            $result[$year] = ['key' => $year, 'value' => $count];
            $xArray[] = $year;
            $yDismissedArray[] = $count;
        }
        $key = $xArray[count($xArray) - 1] + 1;
        $predictions = $this->makePredict(
            $key,
            $xArray,
            $yDismissedArray,
        );
        $result[$key] = $predictions;

        return $result;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function getTurnover(array $roleFilters): array
    {
        $this->companies = $roleFilters['companies'];
        $this->departments = $roleFilters['departments'];
        // число сотрудников
        $countEmployees = $this->getTotalNumberOfEmployees($this->valueTo);
        // список уволенных сотрудников
        $employees = $this->getEmployeesForDismissal($this->valueFrom, $this->valueTo);
        // число уволенных сотрудников
        $numberOfDismissedEmployees = count($employees);
        // число принятых сотрудников
        $numberOfAcceptedEmployees = $this->getTotalNumberOfAccepted();
        // среднесписочная численность
        $numberOfAverageEmployees = $this->getNumberOfAverageEmployees(
            $this->valueTo,
            $this->valueFrom,
            $employees,
            $countEmployees
        );
        // коэффициент текучести
        $turnoverRate = $this->getTurnoverRate(
            $numberOfDismissedEmployees,
            $numberOfAverageEmployees,
        );
        // коэффициент текучести  по стажу работы
        $workExperienceChart = $this->getTurnoverByWorkExp(
            $employees,
            $numberOfAverageEmployees
        );
        [
            $departmentChart, // данные по текучести в зависимости от отдела
            $positionChart, // данные по текучести в зависимости от должности
            $genderChart, // данные по текучести в зависимости от гендера
            $competenceChart, // данные по текучести в зависимости от компетенции
            $gradeChart, // данные по текучести в зависимости от грейда
        ] = $this->getTurnoverByFields(
            $employees,
            $numberOfAverageEmployees
        );
        // прогноз текучести
        $predictions = $this->getTurnoverPredictions($employees);

        return [
            'totalNumber' => $countEmployees,
            'acceptedNumber' => $numberOfAcceptedEmployees,
            'dismissedNumber' => $numberOfDismissedEmployees,
            'averageNumber' => $numberOfAverageEmployees,
            'turnoverRate' => $turnoverRate,
            'workExperienceChart' => $workExperienceChart,
            'departmentChart' => $departmentChart,
            'positionChart' => $positionChart,
            'genderChart' => $genderChart,
            'competenceChart' => $competenceChart,
            'gradeChart' => $gradeChart,
            'predictionsChart' => $predictions,
        ];
    }

    /**
     * @throws Exception
     */
    private function getTotalNumberOfEmployees(\DateTimeImmutable $valueTo): int
    {
        $conn = $this->em->getConnection();
        $sql = "
        select
            count(distinct e.id) as count
        from employee e
        inner join employee_department ed on e.id = ed.employee_id
        inner join department d on d.id = ed.department_id
        inner join company c on e.company_id = c.id
        where (e.status != 3 or (e.status = 3 and e.date_of_dismissal > :end))
        ";
        $params = [
            'end' => $valueTo->format('Y-m-d'),
        ];
        [$sql, $params] = $this->addRoleWhere($sql, $params);
        return $conn->prepare($sql)->executeQuery($params)->fetchAllAssociative()[0]['count'];
    }

    /**
     * @throws Exception
     */
    private function getTotalNumberOfAccepted(): int
    {
        $conn = $this->em->getConnection();
        $sql = "
        select
            count(distinct e.id) as count
        from employee e 
        inner join employee_department ed on e.id = ed.employee_id
        inner join department d on d.id = ed.department_id
        inner join company c on e.company_id = c.id
        where e.date_of_employment >= :start
            and e.date_of_employment <= :end
        ";
        $params = [
            'start' => $this->valueFrom->format('Y-m-d'),
            'end' => $this->valueTo->format('Y-m-d'),
        ];
        [$sql, $params] = $this->addRoleWhere($sql, $params);
        return $conn->prepare($sql)->executeQuery($params)->fetchAllAssociative()[0]['count'];
    }

    /**
     * @throws \Exception
     */
    private function getNumberOfAverageEmployees(
        \DateTimeImmutable $valueTo,
        \DateTimeImmutable $valueFrom,
        array              $employees,
        int                $constNumberOfEmployees
    ): float
    {
        $periodEndDate = $valueTo;

        $numberOfAverageEmployees = 0;

        $numberOfDays = $valueTo->diff($valueFrom)->days + 1;

        while ($periodEndDate >= $valueFrom) {
            $currentIncrement = $constNumberOfEmployees;
            // пробег по уволенным сотрудникам
            foreach ($employees as $item) {
                if (new \DateTimeImmutable($item['date_of_dismissal']) > $periodEndDate) {
                    ++$currentIncrement;
                }
            }
            $numberOfAverageEmployees += $currentIncrement;
            $periodEndDate = $periodEndDate->modify('-1 day');
        }

        return round(fdiv($numberOfAverageEmployees, $numberOfDays), 3);
    }

    private function getTurnoverRate(float $num, float $numberOfAverageEmployees): float
    {
        return $num > 0 ? round(fdiv($num, $numberOfAverageEmployees) * 100, 3) : 0;
    }

    private function getTurnoverByFields(
        array $employees,
        float $numberOfAverageEmployees,
    ): array
    {
        $fields = ['department', 'position', 'gender', 'competence', 'grade'];
        $arrEnv = [];
        foreach ($fields as $query) {
            $_employees = $this->arrayFieldToKey($employees, $query);

            $data = [];

            foreach ($_employees as $key => $item) {
                if ('gender' === $query) {
                    $data[] = [
                        'key' => Employee::GENDER_TYPES[$key],
                        'value' => $item > 0 ? round(fdiv($item, $numberOfAverageEmployees) * 100, 3) : 0,
                    ];
                    continue;
                }
                $data[] = [
                    'key' => $key   ,
                    'value' => $item > 0 ? round(fdiv($item, $numberOfAverageEmployees) * 100, 3) : 0,
                ];
            }
            $arrEnv[] = $data;
        }

        return $arrEnv;
    }

    /**
     * @throws \Exception
     */
    private function getTurnoverByWorkExp(array $employees, float $numberOfAverageEmployees): array
    {
        $workExperienceChart = $this->getDismissedByWorkExperience($employees);
        for ($i = 0, $iMax = count($workExperienceChart); $i < $iMax; ++$i) {
            $workExperienceChart[$i]['value'] =
                $workExperienceChart[$i]['value'] > 0 ? round(fdiv($workExperienceChart[$i]['value'], $numberOfAverageEmployees) * 100, 3) : 0;
        }

        return $workExperienceChart;
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function getTurnoverPredictions(array $employees): array
    {
        $sqlResult = $this->getDismissedByYears();

        $xArray = [];
        $yTurnoverArray = [];
        $result = [];

        foreach ($sqlResult as $row) {
            $year = (int) $row['year'];
            $count = (int) $row['count'];

            $valueFrom = \DateTimeImmutable::createFromFormat('Y-m-d', $year.'-01-01');
            $valueTo = \DateTimeImmutable::createFromFormat('Y-m-d', $year.'-12-31');

            $countEmployees = $this->getTotalNumberOfEmployees($valueTo);
            $employees = $this->getEmployeesForDismissal($valueFrom, $valueTo);
            $numberOfAverageEmployees = $this->getNumberOfAverageEmployees(
                $valueTo,
                $valueFrom,
                $employees,
                $countEmployees
            );
            // коэффициент текучести
            $turnoverRate = $this->getTurnoverRate(
                $count,
                $numberOfAverageEmployees,
            );

            $result[$year] = ['key' => $year, 'value' => $turnoverRate];
            $xArray[] = $year;
            $yTurnoverArray[] = $turnoverRate;
        }
        $key = $xArray[count($xArray) - 1] + 1;
        $predictions = $this->makePredict(
            $key,
            $xArray,
            $yTurnoverArray,
        );
        $result[$key] = $predictions;

        return $result;
    }

    private function arrayFieldToKey(array $array, string $key): array
    {
        $arrayOut = [];
        foreach ($array as $obj) {
            if (!isset($arrayOut[$obj[$key]])) {
                $arrayOut[$obj[$key]] = 0;
            }
            ++$arrayOut[$obj[$key]];
        }

        return $arrayOut;
    }

    private function makePredict(int $key, array $xArr, array $yArr): array
    {
        $linRepgressor = new LinearRegressionService($xArr, $yArr);

        return [
            'key' => $key,
            'value' => $linRepgressor->predict($key),
        ];
    }
}