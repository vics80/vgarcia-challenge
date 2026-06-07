<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Shared\Application\Query\QueryBus;
use App\VgarciaChallenge\Shared\Domain\Event\DomainEventBus;
use App\VgarciaChallenge\Vending\Application\Query\FindPurchasableProductQuery;
use App\VgarciaChallenge\Vending\Domain\Money\ChangeCalculator;
use App\VgarciaChallenge\Vending\Domain\Money\Money;
use App\VgarciaChallenge\Vending\Domain\Product\Product;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class SelectProductCommandHandler implements CommandHandler
{
    public function __construct(
        private QueryBus $queryBus,
        private VendingMachineRepository $vendingMachineRepository,
        private ChangeCalculator $changeCalculator,
        private DomainEventBus $domainEventBus,
    ) {
    }

    public function __invoke(SelectProductCommand $command): SelectProductResult
    {
        $product = $this->purchasableProductFrom($command);
        $vendingMachine = $this->configuredVendingMachine();

        $returnedChange = $this->returnedChange($vendingMachine, $product);

        $vendingMachine->purchaseProduct($product);
        $this->domainEventBus->publish(...$vendingMachine->pullDomainEvents());

        return new SelectProductResult($product, $returnedChange);
    }

    public function handles(): string
    {
        return SelectProductCommand::class;
    }

    private function configuredVendingMachine(): VendingMachine
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        return $vendingMachine;
    }

    private function purchasableProductFrom(SelectProductCommand $command): Product
    {
        /** @var Product $product */
        $product = $this->queryBus->ask(new FindPurchasableProductQuery($command->selector()));

        return $product;
    }

    private function returnedChange(VendingMachine $vendingMachine, Product $product): Money
    {
        return $this->changeCalculator->calculate(
            $vendingMachine->availableChange(),
            $vendingMachine->insertedMoney()->totalCents() - $product->price()->cents(),
        );
    }
}
