<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Event;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;
use App\VgarciaChallenge\Vending\Application\Command\CreateOrderCommand;
use App\VgarciaChallenge\Vending\Application\Command\CreateOrderCommandHandler;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;

final readonly class CreateOrderOnProductWasPurchased implements DomainEventSubscriber
{
    public function __construct(
        private CreateOrderCommandHandler $commandHandler,
    ) {
    }

    public function __invoke(ProductWasPurchased $event): void
    {
        ($this->commandHandler)(new CreateOrderCommand(
            $event->aggregateId(),
            $event->productSelector(),
            $event->productPriceCents(),
        ));
    }

    public function subscribedTo(): string
    {
        return ProductWasPurchased::class;
    }
}
