<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputInterface;

use function array_slice;
use function filter_var;
use function preg_match;
use function str_starts_with;

use const FILTER_VALIDATE_INT;

abstract class AbstractSignedQuantityConsoleCommand extends Command
{
    final protected function quantityFrom(InputInterface $input, string $previousArgumentName): int
    {
        $quantity = filter_var(
            $input->getArgument('quantity') ?? $this->quantityFromArgvAfter((string) $input->getArgument($previousArgumentName)),
            FILTER_VALIDATE_INT,
        );

        if (false === $quantity) {
            throw new InvalidArgumentException('Quantity must be an integer.');
        }

        return $quantity;
    }

    private function quantityFromArgvAfter(string $previousArgument): ?string
    {
        $previousArgumentIndex = $this->previousArgumentIndex($previousArgument);

        if (null === $previousArgumentIndex) {
            return null;
        }

        foreach (array_slice($_SERVER['argv'], $previousArgumentIndex + 1) as $argument) {
            if ('--' === $argument || $this->isNonNumericOption($argument)) {
                continue;
            }

            return $argument;
        }

        return null;
    }

    private function previousArgumentIndex(string $previousArgument): ?int
    {
        foreach ($_SERVER['argv'] ?? [] as $index => $argument) {
            if ($previousArgument === $argument) {
                return $index;
            }
        }

        return null;
    }

    private function isNonNumericOption(string $argument): bool
    {
        return str_starts_with($argument, '-') && 1 !== preg_match('/^-\d+$/', $argument);
    }
}
