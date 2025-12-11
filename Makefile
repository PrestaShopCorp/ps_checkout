include .env
export

ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

install:
	composer install
	cd $${MODULE_VERSION} && composer install

up: install
	docker compose up -d

build:
	docker compose build --no-cache

stop:
	docker compose stop

down:
	docker compose down --remove-orphans --volumes

php-cs-fixer:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/php-cs-fixer fix"

autoindex:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/autoindex prestashop:add:index /var/www/html/modules/ps_checkout && php vendor/bin/autoindex prestashop:add:index /var/www/html/modules/ps_checkout/vendor"

add-header-stamp:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt"

lint: php-cs-fixer autoindex add-header-stamp

php-unit-infrastructure:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/infrastructure/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/infrastructure/tests/bootstrap.php"

php-unit-utility:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/utility/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/utility/tests/bootstrap.php"

php-unit-core:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap.php"

php-unit-presentation:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/presentation/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/presentation/tests/bootstrap.php"

unit-test: php-unit-utility php-unit-core php-unit-presentation

php-integration:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/tests/bootstrap-integration.php"

php-integration-core:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap-integration.php"

php-integration-infrastructure:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/infrastructure/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/vendor/invertus/infrastructure/tests/bootstrap-integration.php"

integration-test: php-integration php-integration-core php-integration-infrastructure

test: unit-test integration-test

ssh:
	docker exec -it $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash

install-module:
	@if [ -f $${ROOT_DIR}/prestashop/$${PS_VERSION_TAG}/bin/console ]; then \
		docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "php bin/console prestashop:module install $${MODULE_NAME}"; \
	elif [ -f $${ROOT_DIR}/prestashop/$${PS_VERSION_TAG}/app/console ]; then \
		docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "php app/console prestashop:module install $${MODULE_NAME}"; \
	else \
		echo "Error: Neither bin/console nor app/console found."; \
	fi
