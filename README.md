# Vending Machine Challenge

Backend technical test in PHP and Symfony. The first iteration models the initial domain for a vending machine using DDD and hexagonal architecture, with Doctrine ORM and migrations prepared for persistence.

The vending machine accepts `0.05`, `0.10`, `0.25` and `1.00` coins and starts with three product selectors: `WATER`, `JUICE` and `SODA`.

## Requirements

- Docker
- Docker Compose
- Make

## Setup

```bash
make install
```

This builds and starts the Docker environment and installs Composer dependencies inside the PHP container.

## Environment

Start and stop the environment with:

```bash
make up
make down
```

The application container is named `vgarcia-challenge`. MySQL runs through Docker and is configured through `DATABASE_URL`.

## Database

Run migrations with:

```bash
make migrate
```

Create the initial vending machine and known products with:

```bash
make setup
```

This runs migrations and reloads Doctrine fixtures, so mapped tables are purged before inserting the seed data.

Generate a new migration after mapping changes with:

```bash
make migration-diff
```

Validate Doctrine mapping and schema with:

```bash
make schema-validate
```

## Tests

Insert a coin through the Symfony console command with:

```bash
make insert-coin COIN=0.25
```

Return the inserted coins with:

```bash
make return-coins
```

Select a product with:

```bash
make select-product SELECTOR=WATER
```

Add or remove product stock as a machine admin with:

```bash
make admin-stock SELECTOR=WATER QUANTITY=5
make admin-stock SELECTOR=SODA QUANTITY=-3
```

Add or remove coins from the available change inventory as a machine admin with:

```bash
make admin-coins COIN=0.25 QUANTITY=5
make admin-coins COIN=1.00 QUANTITY=-3
```

Run the full PHPUnit suite with:

```bash
make test
```

Run the full PHPUnit suite and generate an HTML coverage report with:

```bash
make test-coverage
```

The coverage report is generated at `var/reports/phpunit/coverage/index.html`.

Run code quality tools in dry/report mode with:

```bash
make quality-tools
```

Apply automatic ECS and Rector changes, then run the reporting tools with:

```bash
make quality-tools-fix
```

Run a single test file with:

```bash
make test-one TEST=src/VgarciaChallenge/Vending/tests/Domain/Money/MoneyTest.php
```

## Useful Make Commands

- `make install`: build/start containers and install dependencies.
- `make up`: start the Docker environment.
- `make down`: stop and remove containers.
- `make logs`: follow Docker logs.
- `make shell`: open a shell in the PHP container.
- `make composer-install`: install Composer dependencies.
- `make composer-dump-autoload`: regenerate autoload files.
- `make setup`: run migrations and load initial vending machine data.
- `make insert-coin COIN=0.25`: insert a coin into the vending machine.
- `make return-coins`: return the inserted coins from the vending machine.
- `make select-product SELECTOR=WATER`: select a product from the vending machine.
- `make admin-stock SELECTOR=WATER QUANTITY=5`: add or remove product stock.
- `make admin-coins COIN=0.25 QUANTITY=5`: add or remove coins from available change.
- `make test`: run tests.
- `make test-coverage`: run tests and generate the HTML coverage report.
- `make quality-tools`: run PHPStan, PHPMD, ECS and Rector in dry/report mode.
- `make quality-tools-fix`: apply ECS/Rector changes and run PHPStan/PHPMD reports.
- `make migrate`: run Doctrine migrations.
- `make migration-diff`: generate a Doctrine migration.
- `make schema-validate`: validate Doctrine mapping and schema.

## Technical Decisions

- The domain is kept under `src/VgarciaChallenge/Vending/Domain`.
- Shared domain primitives live under `src/VgarciaChallenge/Shared/Domain`.
- Domain entities and value objects do not use Doctrine attributes; persistence metadata is defined with XML mapping under infrastructure.
- Money is represented in cents and stored as a collection of accepted coins.
- Available change is modeled as a coin inventory with configurable maximum quantities per coin.
- `VendingMachine` is the main aggregate root and contains inserted money, available change and product inventory.
- Domain events are prepared in Shared through a minimal interface and aggregate event recording/pulling.
- Tests focus on domain invariants and application behavior, not on testing Doctrine itself.
