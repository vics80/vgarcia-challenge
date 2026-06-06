<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class DecrementProductStockCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
    ) {
    }

    public function __invoke(DecrementProductStockCommand $command): void
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        $selector = ProductSelector::tryFrom($command->selector());

        if (null === $selector) {
            throw ProductNotFoundException::forSelector($command->selector());
        }

        $vendingMachine->decrementProductStock($selector);

        $this->vendingMachineRepository->save($vendingMachine);
    }

    public function handles(): string
    {
        return DecrementProductStockCommand::class;
    }
}
