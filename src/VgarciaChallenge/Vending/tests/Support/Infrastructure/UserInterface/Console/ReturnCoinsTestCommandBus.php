<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Domain\Money\Money;

final class ReturnCoinsTestCommandBus implements CommandBus
{
    public ?Command $dispatchedCommand = null;

    public function __construct(
        private readonly Money $returnedMoney,
    ) {
    }

    public function dispatch(Command $command): mixed
    {
        $this->dispatchedCommand = $command;

        return $this->returnedMoney;
    }
}
