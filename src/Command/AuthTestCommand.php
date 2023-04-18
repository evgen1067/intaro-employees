<?php

namespace App\Command;

use App\Exception\ApiException;
use App\Services\EvolutionService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'auth:test')]
class AuthTestCommand extends Command
{
    public function __construct(
        private EvolutionService $evolutionService,
        string $name = null
    ) {
        parent::__construct($name);
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        try {
            $token = $this->evolutionService->auth();
            $output->writeln($token);
        } catch (ApiException|\JsonException $e) {
            $output->writeln($e->getMessage());
        }

        return 0;
    }
}
