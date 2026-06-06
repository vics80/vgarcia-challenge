<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\InsertCoinCommand;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vending:insert-coin',
    description: 'Insert a coin into the vending machine.',
)]
final class InsertCoinConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'coin',
            InputArgument::REQUIRED,
            'Coin value: 0.05, 0.10, 0.25 or 1.00',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coin = (string) $input->getArgument('coin');

        $this->commandBus->dispatch(new InsertCoinCommand($coin));

        (new SymfonyStyle($input, $output))->success(sprintf('Inserted coin %s.', $coin));

        return Command::SUCCESS;
    }
}
