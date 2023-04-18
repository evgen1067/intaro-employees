<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`manager_user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_SUPER_ADMIN = 'ROLE_SUPER_ADMIN';

    public const ROLE_TOP_MANAGER = 'ROLE_TOP_MANAGER';

    public const ROLE_HR_MANAGER = 'ROLE_HR_MANAGER';

    public const ROLE_DEPARTMENT_MANAGER = 'ROLE_DEPARTMENT_MANAGER';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 500)]
    private ?string $name = null;

    #[ORM\ManyToMany(targetEntity: Department::class, inversedBy: 'users')]
    private Collection $departments;

    #[ORM\ManyToMany(targetEntity: Company::class, inversedBy: 'users')]
    private Collection $companies;

    #[ORM\ManyToMany(targetEntity: HiringPlan::class, mappedBy: 'manager')]
    private Collection $hiringPlans;

    public function __construct()
    {
        $this->departments = new ArrayCollection();
        $this->companies = new ArrayCollection();
        $this->hiringPlans = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
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

    /**
     * @return Collection<int, Company>
     */
    public function getCompanies(): Collection
    {
        return $this->companies;
    }

    public function addCompany(Company $company): self
    {
        if (!$this->companies->contains($company)) {
            $this->companies->add($company);
        }

        return $this;
    }

    public function removeCompany(Company $company): self
    {
        $this->companies->removeElement($company);

        return $this;
    }

    /**
     * @return Collection<int, HiringPlan>
     */
    public function getHiringPlans(): Collection
    {
        return $this->hiringPlans;
    }

    public function addHiringPlan(HiringPlan $hiringPlan): self
    {
        if (!$this->hiringPlans->contains($hiringPlan)) {
            $this->hiringPlans->add($hiringPlan);
            $hiringPlan->addManager($this);
        }

        return $this;
    }

    public function removeHiringPlan(HiringPlan $hiringPlan): self
    {
        if ($this->hiringPlans->removeElement($hiringPlan)) {
            $hiringPlan->removeManager($this);
        }

        return $this;
    }
}
