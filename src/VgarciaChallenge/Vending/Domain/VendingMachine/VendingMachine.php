<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Domain\VendingMachine;

use App\VgarciaChallenge\Shared\Domain\AggregateRoot;
use App\VgarciaChallenge\Shared\Domain\Timestampable;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\ProductInventory;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\CoinWasAdded;
use DateTimeInterface;

class VendingMachine extends AggregateRoot
{
    use Timestampable;

    public const string ID_KEY = 'vendingMachineId';

    private function __construct(
        private VendingMachineId $vendingMachineId,
        private Money $insertedMoney,
        private Money $availableChange,
        private ProductInventory $productInventory,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ) {
        $this->initializeTimestamps($createdAt, $updatedAt);
    }

    public static function create(
        VendingMachineId $vendingMachineId,
        Money $availableChange,
        ProductInventory $productInventory,
    ): self {
        return new self(
            $vendingMachineId,
            Money::empty(),
            $availableChange,
            $productInventory,
        );
    }

    public static function reconstitute(
        VendingMachineId $vendingMachineId,
        Money $insertedMoney,
        Money $availableChange,
        ProductInventory $productInventory,
        ?DateTimeInterface $createdAt = null,
        ?DateTimeInterface $updatedAt = null,
    ): self {
        return new self(
            $vendingMachineId,
            $insertedMoney,
            $availableChange,
            $productInventory,
            $createdAt,
            $updatedAt,
        );
    }

    public function vendingMachineId(): VendingMachineId
    {
        return $this->vendingMachineId;
    }

    public function insertedMoney(): Money
    {
        return $this->insertedMoney;
    }

    public function availableChange(): Money
    {
        return $this->availableChange;
    }

    public function productInventory(): ProductInventory
    {
        return $this->productInventory;
    }

    public function insertCoin(Coin $coin): void
    {
        $this->insertedMoney = $this->insertedMoney->addCoin($coin);
        $this->touch();

        $this->recordDomainEvent(new CoinWasAdded(
            $this->vendingMachineId->value(),
            $coin->cents(),
            $this->insertedMoney->totalCents(),
        ));
    }
}
