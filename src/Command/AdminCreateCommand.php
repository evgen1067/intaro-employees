<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsCommand(name: 'admin:create')]
class AdminCreateCommand extends Command
{
    public function __construct(
        private UserPasswordHasherInterface $hasher,
        private UserRepository $repo,
        string $name = null
    ) {
        parent::__construct($name);
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $user = new User();
        $user
            ->setName($_ENV['ADMIN_NAME'])
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setEmail($_ENV['ADMIN_EMAIL']);
        $password = $this->hasher->hashPassword($user, $_ENV['ADMIN_PASSWORD']);
        $user->setPassword($password);
        $this->repo->save($user, true);
        return Command::SUCCESS;
    }
}