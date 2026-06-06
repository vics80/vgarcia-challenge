<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Command\Exception;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use RuntimeException;

use function sprintf;

final class InvalidCommandHandlerException extends RuntimeException
{
    public static function forHandler(CommandHandler $handler): self
    {
        return new self(sprintf('Command handler [%s] must be callable.', $handler::class));
    }
}
