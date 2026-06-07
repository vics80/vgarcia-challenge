.PHONY: help install up down restart logs shell composer-install composer-dump-autoload setup fixtures-load insert-coin return-coins select-product admin-stock test test-one test-coverage quality-tools quality-tools-dry quality-tools-fix migrate migration-diff schema-validate console

DOCKER_COMPOSE := docker compose -f docker-compose.yaml
APP_CONTAINER := vgarcia-challenge
WORKDIR := /srv/app
COVERAGE_DIR := var/reports/phpunit/coverage
QUALITY_PATHS := src/VgarciaChallenge
PHPSTAN_MEMORY_LIMIT ?= 1G
PHPMD_EXCLUDE := '*/tests/*'
PHPMD_PHP_FLAGS := -d 'error_reporting=E_ALL & ~E_DEPRECATED & ~E_USER_DEPRECATED'
DOCKER_EXEC := docker exec -w $(WORKDIR) $(APP_CONTAINER)
DOCKER_EXEC_CONSOLE := docker exec -e FORCE_COLOR=1 -w $(WORKDIR) $(APP_CONTAINER)
DOCKER_EXEC_COVERAGE := docker exec -e XDEBUG_MODE=coverage -w $(WORKDIR) $(APP_CONTAINER)
DOCKER_EXEC_IT := docker exec -it -w $(WORKDIR) $(APP_CONTAINER)
CONSOLE := $(DOCKER_EXEC_CONSOLE) php bin/console

help: ## Show available commands
	@grep -E '^[a-zA-Z_-]+:.*?## ' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "%-20s %s\n", $$1, $$2}'

install: up composer-install ## Build/start containers and install dependencies

up: ## Build and start the Docker environment
	$(DOCKER_COMPOSE) up --build -d --remove-orphans

down: ## Stop and remove containers
	$(DOCKER_COMPOSE) down

restart: down up ## Restart the Docker environment

logs: ## Tail Docker logs
	$(DOCKER_COMPOSE) logs -f

shell: ## Open a shell in the app container
	$(DOCKER_EXEC_IT) sh

composer-install: ## Install Composer dependencies inside the app container
	$(DOCKER_EXEC) composer install --prefer-dist --no-progress

composer-dump-autoload: ## Regenerate Composer autoload files
	$(DOCKER_EXEC) composer dump-autoload

setup: migrate fixtures-load ## Run migrations and load initial vending machine data

fixtures-load: ## Load Doctrine fixtures
	$(CONSOLE) doctrine:fixtures:load --no-interaction

insert-coin: ## Insert a coin, for example make insert-coin COIN=0.25
	@test -n "$(COIN)" || (echo "Set COIN=0.05, 0.10, 0.25 or 1.00" && exit 1)
	@$(CONSOLE) vending:insert-coin $(COIN)

return-coins: ## Return the inserted coins
	@$(CONSOLE) vending:return-coins

select-product: ## Select a product, for example make select-product SELECTOR=WATER
	@test -n "$(SELECTOR)" || (echo "Set SELECTOR=WATER, JUICE or SODA" && exit 1)
	@$(CONSOLE) vending:select-product $(SELECTOR)

admin-stock: ## Add/remove product stock, for example make admin-stock SELECTOR=WATER QUANTITY=5
	@test -n "$(SELECTOR)" || (echo "Set SELECTOR=WATER, JUICE or SODA" && exit 1)
	@test -n "$(QUANTITY)" || (echo "Set QUANTITY=5 or QUANTITY=-3" && exit 1)
	@$(CONSOLE) vending:admin:stock $(SELECTOR) $(QUANTITY)

test: ## Run the PHPUnit test suite
	$(DOCKER_EXEC) php vendor/bin/phpunit --colors=always

test-one: ## Run one test file, for example make test-one TEST=src/VgarciaChallenge/Vending/tests/Domain/Money/MoneyTest.php
	@test -n "$(TEST)" || (echo "Set TEST=path/to/Test.php" && exit 1)
	$(DOCKER_EXEC) php vendor/bin/phpunit --colors=always $(TEST)

test-coverage: ## Run the PHPUnit test suite with an HTML coverage report
	$(DOCKER_EXEC_COVERAGE) php vendor/bin/phpunit --colors=always --coverage-html $(COVERAGE_DIR) --coverage-text --only-summary-for-coverage-text
	@echo "Coverage report generated at $(COVERAGE_DIR)/index.html"

quality-tools: quality-tools-dry ## Run quality tools in dry/report mode

quality-tools-dry: ## Run PHPStan, PHPMD, ECS and Rector without applying changes
	$(DOCKER_EXEC) vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=$(PHPSTAN_MEMORY_LIMIT)
	$(DOCKER_EXEC) php $(PHPMD_PHP_FLAGS) vendor/bin/phpmd $(QUALITY_PATHS) text phpmd.xml --exclude $(PHPMD_EXCLUDE)
	$(DOCKER_EXEC) vendor/bin/ecs check --config ecs.php
	$(DOCKER_EXEC) vendor/bin/rector process --config rector.php --dry-run

quality-tools-fix: ## Run quality tools and apply ECS/Rector changes
	$(DOCKER_EXEC) vendor/bin/rector process --config rector.php
	$(DOCKER_EXEC) vendor/bin/ecs check --config ecs.php --fix
	$(DOCKER_EXEC) vendor/bin/phpstan analyse --configuration=phpstan.neon --memory-limit=$(PHPSTAN_MEMORY_LIMIT)
	$(DOCKER_EXEC) php $(PHPMD_PHP_FLAGS) vendor/bin/phpmd $(QUALITY_PATHS) text phpmd.xml --exclude $(PHPMD_EXCLUDE)

migrate: ## Run Doctrine migrations
	$(CONSOLE) doctrine:migrations:migrate --no-interaction

migration-diff: ## Generate a Doctrine migration from mapping changes
	$(CONSOLE) doctrine:migrations:diff

schema-validate: ## Validate Doctrine mapping and database schema
	$(CONSOLE) doctrine:schema:validate

console: ## Run a Symfony console command, for example make console CMD=debug:router
	@test -n "$(CMD)" || (echo "Set CMD=symfony:command" && exit 1)
	$(CONSOLE) $(CMD)

.DEFAULT_GOAL := help
