<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\UpdateCoinInventoryResult;

final class UpdateCoinInventoryTestCommandBus implements CommandBus
{
    public ?Command $dispatchedCommand = null;

    public function __construct(
        private readonly UpdateCoinInventoryResult $result,
    ) {
    }

    public function dispatch(Command $command): mixed
    {
        $this->dispatchedCommand = $command;

        return $this->result;
    }
}
