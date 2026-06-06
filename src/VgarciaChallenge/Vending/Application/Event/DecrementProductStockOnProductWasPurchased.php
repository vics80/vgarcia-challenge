<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Event;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;
use App\VgarciaChallenge\Vending\Application\Command\DecrementProductStockCommand;
use App\VgarciaChallenge\Vending\Application\Command\DecrementProductStockCommandHandler;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;

final readonly class DecrementProductStockOnProductWasPurchased implements DomainEventSubscriber
{
    public function __construct(
        private DecrementProductStockCommandHandler $commandHandler,
    ) {
    }

    public function __invoke(ProductWasPurchased $event): void
    {
        ($this->commandHandler)(new DecrementProductStockCommand($event->productSelector()));
    }

    public function subscribedTo(): string
    {
        return ProductWasPurchased::class;
    }
}
