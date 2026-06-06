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
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        $selector = ProductSelector::tryFrom($query->selector());

        if (null === $selector) {
            throw ProductNotFoundException::forSelector($query->selector());
        }

        $product = $vendingMachine->productInventory()->find($selector);

        if (null === $product) {
            throw ProductNotFoundException::forSelector($selector->value);
        }

        if (0 === $product->stockQuantity()->value()) {
            throw ProductOutOfStockException::forSelector($selector);
        }

        $insertedCents = $vendingMachine->insertedMoney()->totalCents();
        $priceCents = $product->price()->cents();

        if ($insertedCents < $priceCents) {
            throw InsufficientMoneyException::forSelector($selector, $priceCents, $insertedCents);
        }

        $this->changeCalculator->calculate($vendingMachine->availableChange(), $insertedCents - $priceCents);

        return $product;
    }

    public function handles(): string
    {
        return FindPurchasableProductQuery::class;
    }
}
