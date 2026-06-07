<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Vending\Support\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use App\VgarciaChallenge\Shared\Application\Command\CommandBus;

final class RecordingCommandBus implements CommandBus
{
    public ?Command $dispatchedCommand = null;

    public function dispatch(Command $command): mixed
    {
        $this->dispatchedCommand = $command;

        return null;
    }
}
