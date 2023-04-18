<?php

namespace App\Entity;

use App\Repository\HiringPlanRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HiringPlanRepository::class)]
class HiringPlan
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;
    #[ORM\Column(length: 512)]
    private ?string $position = null;

    #[ORM\Column]
    private ?int $expectedCount = null;

    #[ORM\Column(length: 512)]
    private ?string $director = null;

    #[ORM\Column]
    private ?int $offersCount = null;

    #[ORM\Column]
    private ?int $employeesCount = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $comment = null;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'hiringPlans')]
    private Collection $manager;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $status = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $urgency = null;

    public const STATUS_TYPES = [
        1 => 'Найм закрыт',
        2 => 'В процессе найма',
    ];

    public const URGENCY_TYPES = [
        1 => 'низкая',
        2 => 'средняя',
        3 => 'высокая',
        4 => 'приоритетная',
        5 => 'холд',
        6 => 'до лучшего',
    ];

    public function __construct()
    {
        $this->manager = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    public function getExpectedCount(): ?int
    {
        return $this->expectedCount;
    }

    public function setExpectedCount(int $expectedCount): self
    {
        $this->expectedCount = $expectedCount;

        return $this;
    }

    public function getDirector(): ?string
    {
        return $this->director;
    }

    public function setDirector(string $director): self
    {
        $this->director = $director;

        return $this;
    }

    public function getOffersCount(): ?int
    {
        return $this->offersCount;
    }

    public function setOffersCount(int $offersCount): self
    {
        $this->offersCount = $offersCount;

        return $this;
    }

    public function getEmployeesCount(): ?int
    {
        return $this->employeesCount;
    }

    public function setEmployeesCount(int $employeesCount): self
    {
        $this->employeesCount = $employeesCount;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getManager(): Collection
    {
        return $this->manager;
    }

    public function addManager(User $manager): self
    {
        if (!$this->manager->contains($manager)) {
            $this->manager->add($manager);
        }

        return $this;
    }

    public function removeManager(User $manager): self
    {
        $this->manager->removeElement($manager);

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

    public function getUrgency(): ?int
    {
        return $this->urgency;
    }

    public function setUrgency(int $urgency): self
    {
        $this->urgency = $urgency;

        return $this;
    }

    public static function mapSqlArray(array $sqlArray): array
    {
        foreach ($sqlArray as $sqlKey => $sqlRow) {
            $sqlArray[$sqlKey]['status'] = self::STATUS_TYPES[$sqlRow['status']];
            $sqlArray[$sqlKey]['urgency'] = self::URGENCY_TYPES[$sqlRow['urgency']];
        }
        return $sqlArray;
    }

    public function fromJson(array $json): self
    {
        $status = $json['status'];
        $position = $json['position'];
        $expectedCount = $json['expected_count'];
        $urgency = $json['urgency'];
        $director = $json['director'];
        $offersCount = $json['offers_count'];
        $employeesCount = $json['employees_count'];
        $comment = $json['comment'];

        $this
            ->setStatus($status)
            ->setPosition($position)
            ->setExpectedCount($expectedCount)
            ->setUrgency($urgency)
            ->setDirector($director)
            ->setOffersCount($offersCount)
            ->setEmployeesCount($employeesCount)
            ->setComment($comment);

        return $this;
    }
}
