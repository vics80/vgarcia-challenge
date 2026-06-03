<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

class Product
{
    private function __construct(
        private ProductId $productId,
        private ProductName $name,
        private ProductSelector $selector,
        private ProductPrice $price,
        private ProductStockQuantity $stockQuantity,
    ) {
    }

    public static function create(
        ProductId $productId,
        ProductName $name,
        ProductSelector $selector,
        ProductPrice $price,
        ProductStockQuantity $stockQuantity,
    ): self {
        return new self($productId, $name, $selector, $price, $stockQuantity);
    }

    public static function reconstitute(
        ProductId $productId,
        ProductName $name,
        ProductSelector $selector,
        ProductPrice $price,
        ProductStockQuantity $stockQuantity,
    ): self {
        return new self($productId, $name, $selector, $price, $stockQuantity);
    }

    /** @param array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int} $payload */
    public static function fromPrimitives(array $payload): self
    {
        return self::reconstitute(
            new ProductId($payload['productId']),
            new ProductName($payload['name']),
            ProductSelector::from($payload['selector']),
            ProductPrice::fromCents($payload['priceCents']),
            new ProductStockQuantity($payload['stockQuantity']),
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

    public function decrementStock(): void
    {
        $this->stockQuantity = $this->stockQuantity->decrement();
    }

    /** @return array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int} */
    public function toPrimitives(): array
    {
        return [
            'productId' => $this->productId->value(),
            'name' => $this->name->value(),
            'selector' => $this->selector->value,
            'priceCents' => $this->price->cents(),
            'stockQuantity' => $this->stockQuantity->value(),
        ];
    }
}
