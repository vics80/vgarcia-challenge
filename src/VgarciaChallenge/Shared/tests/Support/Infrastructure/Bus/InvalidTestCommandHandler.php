<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;

final class InvalidTestCommandHandler implements CommandHandler
{
    public function handles(): string
    {
        return TestCommand::class;
    }
}
