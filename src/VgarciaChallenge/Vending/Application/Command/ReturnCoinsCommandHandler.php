<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class ReturnCoinsCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
    ) {
    }

    public function __invoke(ReturnCoinsCommand $command): Money
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        $returnedMoney = $vendingMachine->returnInsertedMoney();

        $this->vendingMachineRepository->save($vendingMachine);

        return $returnedMoney;
    }

    public function handles(): string
    {
        return ReturnCoinsCommand::class;
    }
}
