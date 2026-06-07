<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function sprintf;

#[AsCommand(
    name: 'vending:admin:coins',
    description: 'Add or remove coins from the vending machine change inventory.',
)]
final class AdminCoinInventoryConsoleCommand extends AbstractSignedQuantityConsoleCommand
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        $this
            ->addArgument(
                'coin',
                InputArgument::REQUIRED,
                'Coin: 0.05, 0.10, 0.25 or 1.00',
            )
            ->addArgument(
                'quantity',
                InputArgument::OPTIONAL,
                'Positive quantity to add or negative quantity to remove',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $coin = (string) $input->getArgument('coin');
        $quantity = $this->quantityFrom($input, 'coin');

        /** @var UpdateCoinInventoryResult $result */
        $result = $this->commandBus->dispatch(new UpdateCoinInventoryCommand($coin, $quantity));

        (new SymfonyStyle($input, $output))->success(sprintf(
            'Updated coin inventory for %s by %+d. Current quantity: %d/%d.',
            $result->coin()->decimalString(),
            $result->quantity(),
            $result->inventoryQuantity()->value(),
            $result->maxInventoryQuantity()->value(),
        ));

        return Command::SUCCESS;
    }
}
