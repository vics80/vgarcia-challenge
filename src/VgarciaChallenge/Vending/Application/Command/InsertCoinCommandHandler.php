<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class InsertCoinCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
    ) {
    }

    public function __invoke(InsertCoinCommand $command): void
    {
        $vendingMachine = $this->configuredVendingMachine();

        $vendingMachine->insertCoin(Coin::fromDecimalString($command->coin()));

        $this->vendingMachineRepository->save($vendingMachine);
    }

    public function handles(): string
    {
        return InsertCoinCommand::class;
    }

    private function configuredVendingMachine(): VendingMachine
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        return $vendingMachine;
    }
}
