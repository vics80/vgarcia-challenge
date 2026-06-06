<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Command;

interface CommandHandler
{
    /** @return class-string<Command> */
    public function handles(): string;
}
