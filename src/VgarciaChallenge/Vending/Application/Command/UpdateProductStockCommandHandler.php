<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class UpdateProductStockCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
    ) {
    }

    public function __invoke(UpdateProductStockCommand $command): UpdateProductStockResult
    {
        $vendingMachine = $this->configuredVendingMachine();
        $selector = $this->productSelectorFrom($command);

        $product = $vendingMachine->changeProductStock($selector, $command->quantity());

        $this->vendingMachineRepository->save($vendingMachine);

        return new UpdateProductStockResult(
            $product->selector(),
            $command->quantity(),
            $product->stockQuantity(),
            $product->maxStockQuantity(),
        );
    }

    public function handles(): string
    {
        return UpdateProductStockCommand::class;
    }

    private function configuredVendingMachine(): VendingMachine
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        return $vendingMachine;
    }

    private function productSelectorFrom(UpdateProductStockCommand $command): ProductSelector
    {
        $selector = ProductSelector::tryFrom($command->selector());

        if (null === $selector) {
            throw ProductNotFoundException::forSelector($command->selector());
        }

        return $selector;
    }
}
