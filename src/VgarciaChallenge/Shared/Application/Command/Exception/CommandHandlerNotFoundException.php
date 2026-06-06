<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Shared\Application\Command\Exception;

use App\VgarciaChallenge\Shared\Application\Command\Command;
use RuntimeException;

use function sprintf;

final class CommandHandlerNotFoundException extends RuntimeException
{
    public static function forCommand(Command $command): self
    {
        return new self(sprintf('No command handler found for [%s].', $command::class));
    }
}
