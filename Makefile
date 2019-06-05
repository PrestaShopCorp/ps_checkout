DKC=docker-compose -f docker-compose.yml -f docker-compose.override.yml -p presta_ckt
DK_PS=docker exec -t ps_ckt bash -c 

.PHONY: help

help:
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

init: ## Initialise configuration
	cp -n .env.dist .env || true
	cp -n docker-compose.override.yml.dist docker-compose.override.yml || true


build: ## Build and run the app
	docker exec -t ps_ckt bash -c "yarn build"

up: init ## Up all your containers `make up opt="--force-recreate --build"`
	$(DKC) up -d --remove-orphans --build
	# $(MAKE) build
	bash bin/docker.sh
	docker exec -t ps_ckt bash -c "chgrp -R www-data /var/www/html"
	$(MAKE) dependancies
	# FIX psaddonsconnect
	docker exec -t ps_ckt bash -c "rm -rf /var/www/html/modules/psaddonsconnect"
	$(DKC) ps

dependancies: ## Install dependancies
	$(DK_PS) "yarn install"
	$(DK_PS) "composer install"
	# docker exec -t ps_ckt bash -c "chmod -R 0777 node_modules"

test: ## Run all tests
	$(MAKE) test-unit
	$(MAKE) test-functional
	$(MAKE) test-integration

test-unit: ## Run units tests
	$(DK_PS) "vendor/bin/phpunit"
	$(DK_PS) "vendor/bin/phpstan analyse"

# test-functional: ## Run functional tests
# Launch functional tests. e.g behat, JBehave, Behave, CucumberJS, Cucumber etc...

test-integration: ## Run integration tests
	$(DK_PS) "yarn test"

bash: ## Go to preta container
	docker exec -it ps_ckt bash

logs: ## Log container
	$(DKC) logs -f

down: ## Down all contenairs and volumes
	$(DKC) kill
	$(DKC) down -v

format: ## Format code.
	$(MAKE) run-cmd cmd="vendor/bin/php-cs-fixer fix --no-interaction -vvv --rules=@Symfony && rm .php_cs.cache"

style: ## Check lint, code styling rules.
	$(MAKE) run-cmd cmd="vendor/bin/php-cs-fixer fix --no-interaction --dry-run --diff -vvv --rules=@Symfony && rm .php_cs.cache"

%:
	@:
