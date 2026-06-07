<?php

declare(strict_types=1);

namespace App\VgarciaChallenge\Vending\Application\Command;

use App\VgarciaChallenge\Shared\Application\Command\CommandHandler;
use App\VgarciaChallenge\Vending\Domain\Money\Coin;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\Exception\VendingMachineNotFoundException;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachine;
use App\VgarciaChallenge\Vending\Domain\VendingMachine\VendingMachineRepository;

final readonly class UpdateCoinInventoryCommandHandler implements CommandHandler
{
    public function __construct(
        private VendingMachineRepository $vendingMachineRepository,
    ) {
    }

    public function __invoke(UpdateCoinInventoryCommand $command): UpdateCoinInventoryResult
    {
        $vendingMachine = $this->configuredVendingMachine();
        $coin = Coin::fromDecimalString($command->coin());

        $coinInventory = $vendingMachine->changeCoinInventory($coin, $command->quantity());

        $this->vendingMachineRepository->save($vendingMachine);

        return new UpdateCoinInventoryResult(
            $coin,
            $command->quantity(),
            $coinInventory->inventoryQuantityOf($coin),
            $coinInventory->maxQuantityOf($coin),
        );
    }

    public function handles(): string
    {
        return UpdateCoinInventoryCommand::class;
    }

    private function configuredVendingMachine(): VendingMachine
    {
        $vendingMachine = $this->vendingMachineRepository->findFirst();

        if (null === $vendingMachine) {
            throw VendingMachineNotFoundException::becauseNoMachineWasConfigured();
        }

        return $vendingMachine;
    }
}
