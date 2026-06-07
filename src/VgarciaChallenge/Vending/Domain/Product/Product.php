<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\Exception\InvalidProductStockAdjustmentException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockLimitExceededException;
use App\VgarciaChallenge\Vending\Domain\Product\Exception\ProductStockNotEnoughException;

use function abs;

class Product
{
    private function __construct(
        private ProductId $productId,
        private ProductName $name,
        private ProductSelector $selector,
        private ProductPrice $price,
        private ProductStockQuantity $stockQuantity,
        private ProductMaxStockQuantity $maxStockQuantity,
    ) {
        $this->ensureMaxStockQuantityIsNotExceeded($this->stockQuantity->value());
    }

    public static function create(
        ProductId $productId,
        ProductName $name,
        ProductSelector $selector,
        ProductPrice $price,
        ProductStockQuantity $stockQuantity,
        ?ProductMaxStockQuantity $maxStockQuantity = null,
    ): self {
        return new self(
            $productId,
            $name,
            $selector,
            $price,
            $stockQuantity,
            $maxStockQuantity ?? $selector->defaultMaxStockQuantity(),
        );
    }

    public static function reconstitute(
        ProductId $productId,
        ProductName $name,
        ProductSelector $selector,
        ProductPrice $price,
        ProductStockQuantity $stockQuantity,
        ?ProductMaxStockQuantity $maxStockQuantity = null,
    ): self {
        return new self(
            $productId,
            $name,
            $selector,
            $price,
            $stockQuantity,
            $maxStockQuantity ?? $selector->defaultMaxStockQuantity(),
        );
    }

    /** @param array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int,maxStockQuantity?:int} $payload */
    public static function fromPrimitives(array $payload): self
    {
        $selector = ProductSelector::from($payload['selector']);

        return self::reconstitute(
            new ProductId($payload['productId']),
            new ProductName($payload['name']),
            $selector,
            ProductPrice::fromCents($payload['priceCents']),
            new ProductStockQuantity($payload['stockQuantity']),
            new ProductMaxStockQuantity($payload['maxStockQuantity'] ?? $selector->defaultMaxStockQuantity()->value()),
        );
    }

    public function productId(): ProductId
    {
        return $this->productId;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function selector(): ProductSelector
    {
        return $this->selector;
    }

    public function price(): ProductPrice
    {
        return $this->price;
    }

    public function stockQuantity(): ProductStockQuantity
    {
        return $this->stockQuantity;
    }

    public function maxStockQuantity(): ProductMaxStockQuantity
    {
        return $this->maxStockQuantity;
    }

    public function decrementStock(): void
    {
        $this->changeStockBy(-1);
    }

    public function changeStockBy(int $quantity): void
    {
        $newStockQuantity = $this->stockQuantity->value() + $quantity;

        $this->ensureStockAdjustmentIsNotZero($quantity);
        $this->ensureStockQuantityIsAvailable($quantity, $newStockQuantity);
        $this->ensureMaxStockQuantityIsNotExceeded($newStockQuantity);

        $this->stockQuantity = new ProductStockQuantity($newStockQuantity);
    }

    /** @return array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int,maxStockQuantity:int} */
    public function toPrimitives(): array
    {
        return [
            'productId' => $this->productId->value(),
            'name' => $this->name->value(),
            'selector' => $this->selector->value,
            'priceCents' => $this->price->cents(),
            'stockQuantity' => $this->stockQuantity->value(),
            'maxStockQuantity' => $this->maxStockQuantity->value(),
        ];
    }

    private function ensureStockAdjustmentIsNotZero(int $quantity): void
    {
        if (0 === $quantity) {
            throw InvalidProductStockAdjustmentException::forSelector($this->selector);
        }
    }

    private function ensureStockQuantityIsAvailable(int $quantity, int $newStockQuantity): void
    {
        if ($newStockQuantity < 0) {
            throw ProductStockNotEnoughException::forSelector(
                $this->selector,
                abs($quantity),
                $this->stockQuantity->value(),
            );
        }
    }

    private function ensureMaxStockQuantityIsNotExceeded(int $newStockQuantity): void
    {
        if ($newStockQuantity > $this->maxStockQuantity->value()) {
            throw ProductStockLimitExceededException::forSelector(
                $this->selector,
                $newStockQuantity,
                $this->maxStockQuantity->value(),
            );
        }
    }
}
