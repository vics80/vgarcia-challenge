<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\Persistence\Doctrine\Type;

use App\VgarciaChallenge\Vending\Domain\Order\OrderTotalAmount;

final class OrderTotalAmountType extends IntegerValueObjectType
{
    public const string NAME = 'order_total_amount';

    protected function valueObjectClass(): string
    {
        return OrderTotalAmount::class;
    }
}
