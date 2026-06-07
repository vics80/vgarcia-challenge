<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\Product;

use App\VgarciaChallenge\Vending\Domain\Product\Exception\DuplicateProductSelectorException;

use function array_key_exists;
use function array_map;
use function array_values;

final class ProductInventory
{
    /** @var array<string, Product> */
    private array $productsBySelector = [];

    private function __construct(Product ...$products)
    {
        foreach ($products as $product) {
            $this->add($product);
        }
    }

    public static function empty(): self
    {
        return new self();
    }

    public static function fromProducts(Product ...$products): self
    {
        return new self(...$products);
    }

    /** @param list<array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int,maxStockQuantity?:int}> $products */
    public static function fromPrimitives(array $products): self
    {
        return self::fromProducts(...array_map(
            static fn (array $product): Product => Product::fromPrimitives($product),
            $products,
        ));
    }

    public function has(ProductSelector $selector): bool
    {
        return array_key_exists($selector->value, $this->productsBySelector);
    }

    public function find(ProductSelector $selector): ?Product
    {
        return $this->productsBySelector[$selector->value] ?? null;
    }

    /** @return list<Product> */
    public function products(): array
    {
        return array_values($this->productsBySelector);
    }

    /** @return list<array{productId:string,name:string,selector:string,priceCents:int,stockQuantity:int,maxStockQuantity:int}> */
    public function toPrimitives(): array
    {
        return array_map(
            static fn (Product $product): array => $product->toPrimitives(),
            $this->products(),
        );
    }

    private function add(Product $product): void
    {
        $this->ensureSelectorIsAvailable($product->selector());

        $this->productsBySelector[$product->selector()->value] = $product;
    }

    private function ensureSelectorIsAvailable(ProductSelector $selector): void
    {
        if ($this->has($selector)) {
            throw new DuplicateProductSelectorException();
        }
    }
}
