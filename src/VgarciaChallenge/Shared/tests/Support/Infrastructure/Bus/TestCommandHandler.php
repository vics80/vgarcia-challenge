<?php

declare(strict_types=1);

namespace App\Tests\VgarciaChallenge\Shared\Support\Infrastructure\Bus;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;

final class TestCommandHandler implements CommandHandler
{
    public bool $wasCalled = false;

    public function __invoke(TestCommand $command): string
    {
        $this->wasCalled = true;

        return 'handled';
    }

    public function handles(): string
    {
        return TestCommand::class;
    }
}
