<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Order\OrderId;

final class OrderIdType extends UuidValueObjectType
{
    public const string NAME = 'order_id';

    protected function valueObjectClass(): string
    {
        return OrderId::class;
    }
}
