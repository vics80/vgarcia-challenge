<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Event;

use App\VgarciaChallenge\Shared\Domain\Event\DomainEventSubscriber;
use App\VgarciaChallenge\Vending\Application\Command\ReturnChangeCommand;
use App\VgarciaChallenge\Vending\Application\Command\ReturnChangeCommandHandler;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Event\ProductWasPurchased;

final readonly class ReturnChangeOnProductWasPurchased implements DomainEventSubscriber
{
    public function __construct(
        private ReturnChangeCommandHandler $commandHandler,
    ) {
    }

    public function __invoke(ProductWasPurchased $event): void
    {
        ($this->commandHandler)(new ReturnChangeCommand($event->productPriceCents()));
    }

    public function subscribedTo(): string
    {
        return ProductWasPurchased::class;
    }
}
