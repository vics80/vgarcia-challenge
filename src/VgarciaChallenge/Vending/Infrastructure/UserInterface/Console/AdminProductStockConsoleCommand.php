<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Infrastructure\UserInterface\Console;

use App\VgarciaChallenge\Shared\Application\Command\CommandBus;
use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockCommand;
use App\VgarciaChallenge\Vending\Application\Command\UpdateProductStockResult;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

use function array_search;
use function array_slice;
use function filter_var;
use function preg_match;
use function sprintf;
use function str_starts_with;

use const FILTER_VALIDATE_INT;

#[AsCommand(
    name: 'vending:admin:stock',
    description: 'Add or remove product stock from the vending machine.',
)]
final class AdminProductStockConsoleCommand extends Command
{
    public function __construct(
        private readonly CommandBus $commandBus,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->ignoreValidationErrors();

        $this
            ->addArgument(
                'selector',
                InputArgument::REQUIRED,
                'Product selector: WATER, JUICE or SODA',
            )
            ->addArgument(
                'quantity',
                InputArgument::OPTIONAL,
                'Positive quantity to add or negative quantity to remove',
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $selector = (string) $input->getArgument('selector');
        $quantity = $this->quantityFrom($input);

        /** @var UpdateProductStockResult $result */
        $result = $this->commandBus->dispatch(new UpdateProductStockCommand($selector, $quantity));

        (new SymfonyStyle($input, $output))->success(sprintf(
            'Updated stock for %s by %+d. Current stock: %d/%d.',
            $result->selector()->value,
            $result->quantity(),
            $result->stockQuantity()->value(),
            $result->maxStockQuantity()->value(),
        ));

        return Command::SUCCESS;
    }

    private function quantityFrom(InputInterface $input): int
    {
        $quantity = filter_var(
            $input->getArgument('quantity') ?? $this->negativeQuantityFromArgv(),
            FILTER_VALIDATE_INT,
        );

        if (false === $quantity) {
            throw new InvalidArgumentException('Quantity must be an integer.');
        }

        return $quantity;
    }

    private function negativeQuantityFromArgv(): ?string
    {
        $commandIndex = array_search($this->getName(), $_SERVER['argv'] ?? [], true);

        if (false === $commandIndex) {
            return null;
        }

        $positionals = [];

        foreach (array_slice($_SERVER['argv'], $commandIndex + 1) as $argument) {
            if ('--' === $argument || $this->isNonNumericOption($argument)) {
                continue;
            }

            $positionals[] = $argument;
        }

        return $positionals[1] ?? null;
    }

    private function isNonNumericOption(string $argument): bool
    {
        return str_starts_with($argument, '-') && 1 !== preg_match('/^-\d+$/', $argument);
    }
}
