# Vending Machine Challenge

Symfony application to manage a vending machine from console commands.

The project is designed to run inside Docker. The recommended way to use it is through the `Makefile`, which runs the commands inside the container and keeps the console output with colors.

## Quick Start

Start the containers and install dependencies:

```bash
make install
```

Prepare the vending machine with migrations and initial data:

```bash
make setup
```

After that, the application is ready to insert coins, return money, buy products, and manage product stock or available change coins.

## Docker

Start the environment:

```bash
make up
```

Stop and remove the containers:

```bash
make down
```

Restart the environment:

```bash
make restart
```

Show logs:

```bash
make logs
```

Open a shell inside the PHP container:

```bash
make shell
```

The application container is named `vgarcia-challenge`, and the code lives inside the container at `/srv/app`.

## Machine Setup

Run migrations:

```bash
make migrate
```

Load the initial data:

```bash
make fixtures-load
```

Run both steps at once:

```bash
make setup
```

`make setup` creates the initial vending machine with its products, initial stock, and coins available for change. Reloading fixtures replaces the previous machine data with the initial state.

## Commands Through Makefile

This is the recommended way to run the application.

### Insert Coins

Accepted coins: `0.05`, `0.10`, `0.25`, `1.00`.

```bash
make insert-coin COIN=0.25
```

### Return Inserted Coins

Returns exactly the coins inserted by the user and clears the inserted money from the machine.

```bash
make return-coins
```

### Select Product

Available selectors: `WATER`, `JUICE`, `SODA`.

```bash
make select-product SELECTOR=WATER
```

If there is enough inserted money, available stock, and enough change, the machine dispenses the product, creates the order, decreases stock, and returns the corresponding change.

### Manage Product Stock

Adds or removes product units. The quantity must be a positive or negative integer.

```bash
make admin-stock SELECTOR=WATER QUANTITY=5
make admin-stock SELECTOR=SODA QUANTITY=-3
```

When the quantity is negative, stock is decreased. The application validates that enough stock is available before updating it. When the quantity is positive, stock is increased and validated against the configured maximum for that product.

### Manage Coin Inventory

Adds or removes coins available for returning change. The quantity must be a positive or negative integer.

```bash
make admin-coins COIN=0.25 QUANTITY=5
make admin-coins COIN=1.00 QUANTITY=-3
```

When the quantity is negative, coins are removed from the inventory. The application validates that enough coins are available before updating it. When the quantity is positive, coins are added and validated against the configured maximum for that coin.

## Alternative Mode Inside The Container

Commands can also be run manually by entering the container:

```bash
make shell
```

Once inside:

```bash
bin/console vending:insert-coin 0.25
bin/console vending:return-coins
bin/console vending:select-product WATER
bin/console vending:admin:stock WATER 5
bin/console vending:admin:stock WATER -3
bin/console vending:admin:coins 0.25 5
bin/console vending:admin:coins 0.25 -3
```

Symfony allows command abbreviations when the abbreviation is not ambiguous. For example:

```bash
bin/console ven:adm:sto WATER -3
bin/console ven:adm:coi 0.25 -3
```

## Tests

Run the full test suite:

```bash
make test
```

Run a single test file:

```bash
make test-one TEST=src/VgarciaChallenge/Vending/tests/Domain/Money/MoneyTest.php
```

Run tests with an HTML coverage report:

```bash
make test-coverage
```

The report is generated at:

```text
var/reports/phpunit/coverage/index.html
```

## Code Quality

Run PHPStan, PHPMD, ECS, and Rector in dry/report mode:

```bash
make quality-tools
```

Apply automatic ECS/Rector changes and then run the reporting tools:

```bash
make quality-tools-fix
```

## Useful Commands

Show all available `Makefile` commands:

```bash
make help
```

Run any Symfony command inside the container:

```bash
make console CMD=debug:router
```

Regenerate Composer autoload files:

```bash
make composer-dump-autoload
```

Validate Doctrine mapping and schema:

```bash
make schema-validate
```

Generate a migration from mapping changes:

```bash
make migration-diff
```
