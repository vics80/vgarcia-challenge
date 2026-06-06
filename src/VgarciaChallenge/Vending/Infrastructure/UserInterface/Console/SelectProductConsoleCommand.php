<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductCommand;
use App\VgarciaChallenge\Vending\Application\Command\SelectProductResult;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vending:select-product',
    description: 'Select a product from the vending machine.',
)]
final class SelectProductConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument(
            'selector',
            InputArgument::REQUIRED,
            'Product selector: WATER, JUICE or SODA',
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selector = (string) $input->getArgument('selector');

        /** @var SelectProductResult $result */
        $result = $this->commandBus->dispatch(new SelectProductCommand($selector));
        $product = $result->product();

        (new SymfonyStyle($input, $output))->success([
            sprintf(
                'Dispensed product: %s (%s).',
                $product->name()->value(),
                $product->selector()->value,
            ),
            $this->formatReturnedChange($result->returnedChange()),
        ]);

        return Command::SUCCESS;
    }

    private function formatReturnedChange(Money $money): string
    {
        if ($money->isEmpty()) {
            return 'No change returned.';
        }

        return sprintf(
            'Returned change: %s (%s total).',
            $this->formatReturnedCoins($money),
            $money->decimalString(),
        );
    }

    private function formatReturnedCoins(Money $money): string
    {
        $coins = [];

        foreach ($money->toPrimitives() as $coin) {
            $decimalString = Coin::from($coin['coinCents'])->decimalString();

            for ($quantity = 0; $quantity < $coin['quantity']; ++$quantity) {
                $coins[] = $decimalString;
            }
        }

        return implode(', ', $coins);
    }
}
