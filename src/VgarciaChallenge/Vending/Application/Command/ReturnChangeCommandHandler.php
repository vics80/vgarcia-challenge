<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class ReturnChangeCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
        private ChangeCalculator $changeCalculator,
    ) {
    }

    public function __invoke(ReturnChangeCommand $command): Money
    {
        $vendingMachine = $this->configuredVendingMachine();

        $returnedChange = $this->changeCalculator->calculate(
            $vendingMachine->availableChange(),
            $vendingMachine->insertedMoney()->totalCents() - $command->productPriceCents(),
        );

        $vendingMachine->returnChangeAndClearInsertedMoney($returnedChange);

        $this->vendingMachineRepository->save($vendingMachine);

        return $returnedChange;
    }

    public function handles(): string
    {
        return ReturnChangeCommand::class;
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
