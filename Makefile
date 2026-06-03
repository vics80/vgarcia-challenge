.PHONY: help install up down restart logs shell composer-install composer-dump-autoload setup fixtures-load test test-one migrate migration-diff schema-validate console

DOCKER_COMPOSE := docker compose -f docker-compose.yaml
APP_CONTAINER := vgarcia-challenge
WORKDIR := /srv/app
DOCKER_EXEC := docker exec -w $(WORKDIR) $(APP_CONTAINER)
DOCKER_EXEC_IT := docker exec -it -w $(WORKDIR) $(APP_CONTAINER)
CONSOLE := $(DOCKER_EXEC) php bin/console

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

test: ## Run the PHPUnit test suite
	$(DOCKER_EXEC) php vendor/bin/phpunit --colors=always

test-one: ## Run one test file, for example make test-one TEST=src/VgarciaChallenge/Vending/tests/Domain/Money/MoneyTest.php
	@test -n "$(TEST)" || (echo "Set TEST=path/to/Test.php" && exit 1)
	$(DOCKER_EXEC) php vendor/bin/phpunit --colors=always $(TEST)

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
