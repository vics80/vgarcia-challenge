<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Query;

use App\VgarciaChallenge\Shared\Application\Query\QueryHandler;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\InsufficientMoneyException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductNotFoundException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductOutOfStockException;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\Product\ProductSelector;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class FindPurchasableProductQueryHandler implements QueryHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
        private ChangeCalculator $changeCalculator,
    ) {
    }

    public function __invoke(FindPurchasableProductQuery $query): Product
    {
        $vendingMachine = $this->configuredVendingMachine();
        $selector = $this->productSelectorFrom($query);
        $product = $this->productForSelector($vendingMachine, $selector);

        $this->ensureProductHasStock($product, $selector);
        $this->ensureInsertedMoneyIsEnough($vendingMachine, $product, $selector);
        $this->ensureChangeCanBeReturned($vendingMachine, $product);

        return $product;
    }

    public function handles(): string
    {
        return FindPurchasableProductQuery::class;
    }

    private function configuredVendingMachine(): VendingMachine
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        return $vendingMachine;
    }

    private function productSelectorFrom(FindPurchasableProductQuery $query): ProductSelector
    {
        $selector = ProductSelector::tryFrom($query->selector());

        if (null === $selector) {
            throw ProductNotFoundException::forSelector($query->selector());
        }

        return $selector;
    }

    private function productForSelector(VendingMachine $vendingMachine, ProductSelector $selector): Product
    {
        $product = $vendingMachine->productInventory()->find($selector);

        if (null === $product) {
            throw ProductNotFoundException::forSelector($selector->value);
        }

        return $product;
    }

    private function ensureProductHasStock(Product $product, ProductSelector $selector): void
    {
        if (0 === $product->stockQuantity()->value()) {
            throw ProductOutOfStockException::forSelector($selector);
        }
    }

    private function ensureInsertedMoneyIsEnough(
        VendingMachine $vendingMachine,
        Product $product,
        ProductSelector $selector,
    ): void {
        $insertedCents = $vendingMachine->insertedMoney()->totalCents();
        $priceCents = $product->price()->cents();

        if ($insertedCents < $priceCents) {
            throw InsufficientMoneyException::forSelector($selector, $priceCents, $insertedCents);
        }
    }

    private function ensureChangeCanBeReturned(VendingMachine $vendingMachine, Product $product): void
    {
        $this->changeCalculator->calculate(
            $vendingMachine->availableChange(),
            $vendingMachine->insertedMoney()->totalCents() - $product->price()->cents(),
        );
    }
}
