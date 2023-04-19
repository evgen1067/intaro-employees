<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
class Employee
{
    public const GENDER_TYPES = [
        1 => 'мужской',
        2 => 'женский',
        3 => 'не указано',
    ];

    public const STATUS_TYPES = [
        1 => 'работает',
        2 => 'декрет',
        3 => 'уволен',
        4 => 'не указано',
    ];

    public const REASON_TYPES = [
        1 => 'не пройден испытательный срок',
        2 => 'проблемы с дисциплиной',
        3 => 'не справлялся с поставленными задачами',
        4 => 'сокращение',
        5 => 'предложение о работе с высокой заработной платой',
        6 => 'потерял ценность',
        7 => 'не видит для себя профессионального развития',
        8 => 'хочет сменить должность/направление',
        9 => 'выгорание',
        10 => 'релокация',
    ];

    public const CATEGORY_TYPE = [
        1 => 'добровольная',
        2 => 'принудительная',
        3 => 'нежелательная',
    ];

    public const REVERSE_STATUS_TYPES = [
        'работает' => 1,
        'декрет' => 2,
        'уволен' => 3,
    ];

    public const REVERSE_REASON_TYPES = [
        'не пройден испытательный срок' => 1,
        'проблемы с дисциплиной' => 2,
        'не справлялся с поставленными задачами' => 3,
        'сокращение' => 4,
        'предложение о работе с высокой заработной платой' => 5,
        'потерял ценность' => 6,
        'не видит для себя профессионального развития' => 7,
        'хочет сменить должность/направление' => 8,
        'выгорание' => 9,
        'релокация' => 10,
    ];

    public const REVERSE_CATEGORY_TYPE = [
        'добровольная' => 1,
        'принудительная' => 2,
        'нежелательная' => 3,
    ];

    public const REVERSE_GENDER_TYPES = [
        'мужской' => 1,
        'женский' => 2,
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 500)]
    private ?string $name = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $gender = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfBirth = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfEmployment = null;

    #[ORM\Column(length: 500)]
    private ?string $position = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dateOfDismissal = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $reasonOfDismissal = null;

    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $categoryOfDismissal = null;

    #[ORM\ManyToMany(targetEntity: Department::class, inversedBy: 'employees')]
    private Collection $departments;

    #[ORM\ManyToOne(inversedBy: 'employees')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Company $company = null;

    #[ORM\Column(length: 500)]
    private ?string $competence = null;

    #[ORM\Column(length: 500)]
    private ?string $grade = null;

    #[ORM\Column]
    private ?int $bitrixId = null;

    #[ORM\Column]
    private ?int $evoId = null;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getGender(): ?int
    {
        return $this->gender;
    }

    public function setGender(int $gender): self
    {
        $this->gender = $gender;

        return $this;
    }

    public function getDateOfBirth(): ?\DateTimeInterface
    {
        return $this->dateOfBirth;
    }

    public function setDateOfBirth(?\DateTimeInterface $dateOfBirth): self
    {
        $this->dateOfBirth = $dateOfBirth;

        return $this;
    }

    public function getDateOfEmployment(): ?\DateTimeInterface
    {
        return $this->dateOfEmployment;
    }

    public function setDateOfEmployment(?\DateTimeInterface $dateOfEmployment): self
    {
        $this->dateOfEmployment = $dateOfEmployment;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): self
    {
        $this->position = $position;

        return $this;
    }

    public function getStatus(): ?int
    {
        return $this->status;
    }

    public function setStatus(int $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getDateOfDismissal(): ?\DateTimeInterface
    {
        return $this->dateOfDismissal;
    }

    public function setDateOfDismissal(?\DateTimeInterface $dateOfDismissal): self
    {
        $this->dateOfDismissal = $dateOfDismissal;

        return $this;
    }

    public function getReasonOfDismissal(): ?int
    {
        return $this->reasonOfDismissal;
    }

    public function setReasonOfDismissal(?int $reasonOfDismissal): self
    {
        $this->reasonOfDismissal = $reasonOfDismissal;

        return $this;
    }

    public function getCategoryOfDismissal(): ?int
    {
        return $this->categoryOfDismissal;
    }

    public function setCategoryOfDismissal(?int $categoryOfDismissal): self
    {
        $this->categoryOfDismissal = $categoryOfDismissal;

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
    }

    public function addDepartment(Department $department): self
    {
        if (!$this->departments->contains($department)) {
            $this->departments->add($department);
        }

        return $this;
    }

    public function removeDepartment(Department $department): self
    {
        $this->departments->removeElement($department);

        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getCompetence(): ?string
    {
        return $this->competence;
    }

    public function setCompetence(string $competence): self
    {
        $this->competence = $competence;

        return $this;
    }

    public function getGrade(): ?string
    {
        return $this->grade;
    }

    public function setGrade(string $grade): self
    {
        $this->grade = $grade;

        return $this;
    }

    public static function getWorkExperience(?\DateTimeImmutable $dateStart, ?\DateTimeImmutable $dateEnd = null): float|null
    {
        if (null == $dateStart) {
            return null;
        }
        if (null === $dateEnd) {
            $dateEnd = new \DateTimeImmutable();
        }
        $interval = $dateStart->diff($dateEnd);

        return round(fdiv($interval->days, 365.0), 2);
    }

    public static function mapSqlArray(array $sqlArray, array|null $filterWork, array|null $sort): array
    {
        $mappedSqlArray = [];
        foreach ($sqlArray as $key => $sqlRow) {
            $mappedSqlRow = [];
            $mappedSqlRow['id'] = $sqlRow['id'];
            $mappedSqlRow['name'] = $sqlRow['name'];
            $mappedSqlRow['gender'] = self::GENDER_TYPES[$sqlRow['gender']];
            $mappedSqlRow['date_of_birth'] = null !== $sqlRow['date_of_birth'] ? new \DateTimeImmutable($sqlRow['date_of_birth']) : null;
            $mappedSqlRow['date_of_employment'] = null !== $sqlRow['date_of_employment'] ? new \DateTimeImmutable($sqlRow['date_of_employment']) : null;
            $mappedSqlRow['company'] = $sqlRow['company'];
            $mappedSqlRow['departments'] = $sqlRow['departments'];
            $mappedSqlRow['position'] = $sqlRow['position'];
            $mappedSqlRow['competence'] = $sqlRow['competence'];
            $mappedSqlRow['grade'] = $sqlRow['grade'];
            $mappedSqlRow['status'] = self::STATUS_TYPES[$sqlRow['status']];
            $mappedSqlRow['date_of_dismissal'] = 3 === $sqlRow['status'] ? (
                null !== $sqlRow['date_of_dismissal'] ? new \DateTimeImmutable($sqlRow['date_of_dismissal']) : null
            ) : null;
            $mappedSqlRow['reason_of_dismissal'] = 3 === $sqlRow['status'] ? (
                null !== $sqlRow['reason_of_dismissal'] ? self::REASON_TYPES[$sqlRow['reason_of_dismissal']] : null
            ) : null;
            $mappedSqlRow['category_of_dismissal'] = 3 === $sqlRow['status'] ? (
                null !== $sqlRow['category_of_dismissal'] ? self::CATEGORY_TYPE[$sqlRow['category_of_dismissal']] : null
            ) : null;
            $mappedSqlRow['workExperience'] = self::getWorkExperience(
                $mappedSqlRow['date_of_employment'] ?: null,
                ($mappedSqlRow['date_of_dismissal'] ?: null)
            ) ?? 0;

            $mappedSqlRow['date_of_birth'] = $mappedSqlRow['date_of_birth']?->format('d.m.Y');
            $mappedSqlRow['date_of_employment'] = $mappedSqlRow['date_of_employment']?->format('d.m.Y');
            $mappedSqlRow['date_of_dismissal'] = $mappedSqlRow['date_of_dismissal']?->format('d.m.Y');

            if (null !== $filterWork) {
                if (isset($filterWork['type']) && 'number_equal' === $filterWork['type']) {
                    $value = $filterWork['value'];
                    if ($mappedSqlRow['workExperience'] == $value) {
                        $mappedSqlArray[] = $mappedSqlRow;
                    }
                } elseif (isset($filterWork['type']) && 'number_not_equal' === $filterWork['type']) {
                    $value = $filterWork['value'];
                    if ($mappedSqlRow['workExperience'] != $value) {
                        $mappedSqlArray[] = $mappedSqlRow;
                    }
                } elseif (isset($filterWork['type']) && 'number_inequality' === $filterWork['type']) {
                    $valueFrom = $filterWork['valueFrom'] ?? '';
                    $valueTo = $filterWork['valueTo'] ?? '';
                    if (isset($filterWork['isStrict'])) {
                        if ('' !== $valueFrom && '' === $valueTo) {
                            if ($mappedSqlRow['workExperience'] > $valueFrom && true === $filterWork['isStrict']) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            } elseif ($mappedSqlRow['workExperience'] >= $valueFrom && false === $filterWork['isStrict']) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            }
                        } elseif ('' !== $valueTo && '' === $valueFrom) {
                            if ($mappedSqlRow['workExperience'] < $valueTo && true === $filterWork['isStrict']) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            } elseif ($mappedSqlRow['workExperience'] <= $valueTo && false === $filterWork['isStrict']) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            }
                        } elseif ('' !== $valueTo && '' !== $valueFrom) {
                            if (
                                $mappedSqlRow['workExperience'] < $valueTo &&
                                $mappedSqlRow['workExperience'] > $valueFrom &&
                                true === $filterWork['isStrict']
                            ) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            } elseif (
                                $mappedSqlRow['workExperience'] <= $valueTo &&
                                $mappedSqlRow['workExperience'] >= $valueFrom &&
                                false === $filterWork['isStrict']
                            ) {
                                $mappedSqlArray[] = $mappedSqlRow;
                            }
                        }
                    }
                }
            } else {
                $mappedSqlArray[] = $mappedSqlRow;
            }
        }
        if ($sort && 'workExperience' === $sort['key']) {
            if ('desc' === $sort['value']) {
                usort($mappedSqlArray, static function ($a, $b) {
                    if ($a['workExperience'] === $b['workExperience']) {
                        return 0;
                    }

                    return ($a['workExperience'] > $b['workExperience']) ? 1 : -1;
                });
            } else {
                usort($mappedSqlArray, static function ($a, $b) {
                    if ($a['workExperience'] === $b['workExperience']) {
                        return 0;
                    }

                    return ($a['workExperience'] < $b['workExperience']) ? 1 : -1;
                });
            }
        }

        return $mappedSqlArray;
    }

    public function getBitrixId(): ?int
    {
        return $this->bitrixId;
    }

    public function setBitrixId(int $bitrixId): self
    {
        $this->bitrixId = $bitrixId;

        return $this;
    }

    public function getEvoId(): ?int
    {
        return $this->evoId;
    }

    public function setEvoId(int $evoId): self
    {
        $this->evoId = $evoId;

        return $this;
    }

    public static function compareEmployees(Employee $old, Employee $new): bool
    {
        $checkStandardField =
            $old->getName() === $new->getName() &&
            $old->getGender() && $new->getGender() &&
            $old->getDateOfBirth() && $new->getDateOfBirth() &&
            $old->getDateOfEmployment() && $new->getDateOfEmployment() &&
            $old->getPosition() && $new->getPosition() &&
            $old->getStatus() && $new->getStatus() &&
            $old->getDateOfDismissal() && $new->getDateOfDismissal() &&
            $old->getReasonOfDismissal() && $new->getReasonOfDismissal() &&
            $old->getCategoryOfDismissal() && $new->getCategoryOfDismissal() &&
            $old->getCompetence() && $new->getCompetence() &&
            $old->getGrade() && $new->getGrade();
        if ($checkStandardField) {
            // departments
            $oldDepartments = $old->getDepartments()->toArray();
            $newDepartments = $new->getDepartments()->toArray();
            if (count($oldDepartments) !== count($newDepartments)) {
                return false;
            }
            $oldIds = array_column($oldDepartments, 'bitrixId');
            $newIds = array_column($newDepartments, 'bitrixId');
            foreach ($oldIds as $oldId) {
                if (!in_array($oldId, $newIds)) {
                    return false;
                }
            }
            $oldCompany = $old->getCompany();
            $newCompany = $new->getCompany();
            if ($oldCompany->getName() !== $newCompany->getName()) {
                return false;
            }
            return true;
        }
        return false;
    }

    public function updateEmployee(Employee $new): self
    {
        $this
            ->setName($new->getName())
            ->setGender($new->getGender())
            ->setDateOfBirth($new->getDateOfBirth())
            ->setDateOfEmployment($new->getDateOfEmployment())
            ->setPosition($new->getPosition())
            ->setStatus($new->getStatus())
            ->setDateOfDismissal($new->getDateOfDismissal())
            ->setReasonOfDismissal($new->getReasonOfDismissal())
            ->setCategoryOfDismissal($new->getCategoryOfDismissal())
            ->setCompetence($new->getCompetence())
            ->setGrade($new->getGrade());

        return $this;
    }
}
