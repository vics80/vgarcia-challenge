<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Command;

interface CommandBus
{
    public function dispatch(Command $command): mixed;
}
