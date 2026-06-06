<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\ReturnCoinsCommand;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'vending:return-coins',
    description: 'Return the inserted coins from the vending machine.',
)]
final class ReturnCoinsConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var Money $returnedMoney */
        $returnedMoney = $this->commandBus->dispatch(new ReturnCoinsCommand());

        (new SymfonyStyle($input, $output))->success(sprintf(
            'Returned coins: %s.',
            $this->formatReturnedCoins($returnedMoney),
        ));

        return Command::SUCCESS;
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
