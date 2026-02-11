.PHONY: help up down build rebuild shell logs lint analyse tests-unit tests-integration db-migrate db-reset cache-clear composer-install composer-update front-install front-dev front-build front-preview front-lint front-test front-test-watch front-hooks

# Default target
.DEFAULT_GOAL := help

# Colors
GREEN  := \033[0;32m
YELLOW := \033[0;33m
CYAN   := \033[0;36m
RESET  := \033[0m

## —— Signalist Makefile ——————————————————————————————————————————————————————

help: ## Show this help
	@echo ""
	@echo "$(CYAN)Signalist$(RESET) - Available commands:"
	@echo ""
	@grep -E '(^[a-zA-Z_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "$(GREEN)%-20s$(RESET) %s\n", $$1, $$2}' | sed -e 's/## //'
	@echo ""

## —— Docker ——————————————————————————————————————————————————————————————————

up: ## Start all containers
	docker compose up -d

down: ## Stop all containers
	docker compose down

build: ## Build containers
	docker compose build

rebuild: ## Rebuild containers from scratch
	docker compose down -v
	docker compose build --no-cache
	docker compose up -d

shell: ## Enter app container shell
	docker compose exec app sh

logs: ## Tail container logs
	docker compose logs -f

logs-app: ## Tail app container logs
	docker compose logs -f app

logs-messenger: ## Tail messenger worker logs
	docker compose logs -f messenger

ps: ## Show running containers
	docker compose ps

## —— Composer ————————————————————————————————————————————————————————————————

composer-install: ## Install composer dependencies
	docker compose exec app composer install

composer-update: ## Update composer dependencies
	docker compose exec app composer update

composer-require: ## Add a new composer package (usage: make composer-require PACKAGE=vendor/package)
	docker compose exec app composer require $(PACKAGE)

composer-require-dev: ## Add a new dev composer package (usage: make composer-require-dev PACKAGE=vendor/package)
	docker compose exec app composer require --dev $(PACKAGE)

## —— Code Quality ————————————————————————————————————————————————————————————

lint: ## Run PHP CS Fixer
	docker compose exec app vendor/bin/php-cs-fixer fix --diff --verbose

lint-dry: ## Run PHP CS Fixer in dry-run mode
	docker compose exec app vendor/bin/php-cs-fixer fix --diff --verbose --dry-run

analyse: ## Run PHPStan static analysis
	docker compose exec app vendor/bin/phpstan analyse

rector: ## Run Rector to refactor code
	docker compose exec app vendor/bin/rector process

rector-dry: ## Run Rector in dry-run mode
	docker compose exec app vendor/bin/rector process --dry-run

quality: lint analyse rector ## Run all code quality tools (CS Fixer, PHPStan, Rector)

## —— Testing —————————————————————————————————————————————————————————————————

tests-unit: ## Run unit tests
	docker compose exec app vendor/bin/phpunit --testsuite=Unit

tests-integration: ## Run integration tests
	docker compose exec app vendor/bin/phpunit --testsuite=Integration

tests: ## Run all tests
	docker compose exec app vendor/bin/phpunit

tests-coverage: ## Run tests with coverage report
	docker compose exec app vendor/bin/phpunit --coverage-html var/coverage

tests-api: ## Run Behat API tests
	docker compose exec app vendor/bin/behat --colors

tests-api-wip: ## Run Behat tests tagged @wip
	docker compose exec app vendor/bin/behat --colors --tags=@wip

## —— Database ————————————————————————————————————————————————————————————————

db-migrate: ## Run database migrations
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

db-diff: ## Generate migration from entity changes
	docker compose exec app php bin/console doctrine:migrations:diff

db-reset: ## Reset database (drop, create, migrate)
	docker compose exec app php bin/console doctrine:database:drop --force --if-exists
	docker compose exec app php bin/console doctrine:database:create
	docker compose exec app php bin/console doctrine:migrations:migrate --no-interaction

db-fixtures: ## Load database fixtures
	docker compose exec app php bin/console doctrine:fixtures:load --no-interaction

psql: ## Open PostgreSQL shell
	docker compose exec postgres psql -U signalist -d signalist

## —— Symfony —————————————————————————————————————————————————————————————————

cache-clear: ## Clear Symfony cache
	docker compose exec app php bin/console cache:clear

routes: ## List all routes
	docker compose exec app php bin/console debug:router

console: ## Run Symfony console command (usage: make console CMD="cache:clear")
	docker compose exec app php bin/console $(CMD)

## —— Messenger ———————————————————————————————————————————————————————————————

worker: ## Start messenger worker in foreground
	docker compose exec app php bin/console messenger:consume async -vv

worker-failed: ## Show failed messages
	docker compose exec app php bin/console messenger:failed:show

worker-retry: ## Retry failed messages
	docker compose exec app php bin/console messenger:failed:retry

## —— API Platform ————————————————————————————————————————————————————————————

api-docs: ## Open API documentation
	@echo "$(CYAN)API Documentation:$(RESET) http://localhost:8000/api"

## —— Frontend ————————————————————————————————————————————————————————————

front-install: ## Install frontend dependencies
	cd frontend && npm install

front-dev: ## Start frontend dev server (http://localhost:5173)
	cd frontend && npm run dev

front-build: ## Build frontend for production
	cd frontend && npm run build

front-preview: ## Preview production build locally
	cd frontend && npm run preview

front-lint: ## Run frontend linter (ESLint)
	cd frontend && npm run lint

front-test: ## Run frontend tests
	cd frontend && npm run test

front-test-watch: ## Run frontend tests in watch mode
	cd frontend && npm run test:watch

front-hooks: ## Set up frontend Git hooks (husky + lint-staged)
	cd frontend && npm run prepare

## —— Project Setup ———————————————————————————————————————————————————————————

install: build up composer-install db-migrate front-install front-hooks ## Full project setup
	@echo "$(GREEN)Signalist is ready!$(RESET)"
	@echo "Backend API at: $(CYAN)http://localhost:8000$(RESET)"
	@echo "API documentation at: $(CYAN)http://localhost:8000/api$(RESET)"
	@echo "Frontend: run $(CYAN)make front-dev$(RESET) to start at $(CYAN)http://localhost:5173$(RESET)"
