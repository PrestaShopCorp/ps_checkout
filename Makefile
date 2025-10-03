include .env
export

ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

run:
	composer install
	cd $${MODULE_VERSION} && composer install
	MODULE_VERSION=$(module_version) SSL_ENABLED=$(ssl_enabled) docker compose up -d --build
	docker exec -it $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} chmod -R 777 /var/www/html
	# make run module_version=ps8 ssl_enabled=1

build:
	MODULE_VERSION=$(module_version) docker compose build --no-cache
	# make build module_version=ps8

build-ci:
	composer install
	cd $${MODULE_VERSION} && composer install
	MODULE_VERSION=$(module_version) SSL_ENABLED=$(ssl_enabled) docker compose up -d --build
	# make build module_version=ps8

down:
	docker compose down --remove-orphans

php-cs-fixer:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/php-cs-fixer fix"
	# make php-cs-fixer module_version=ps8

php-cs-fixer-ci:
	docker exec -i ${MODULE_VERSION}-ps-prestashop-${PS_VERSION_TAG} /bin/bash -c 'cd modules/ps_checkout && php vendor/bin/php-cs-fixer fix --dry-run --diff --verbose; exit \$\$?'

autoindex:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/autoindex prestashop:add:index /var/www/html/modules/ps_checkout && php vendor/bin/autoindex prestashop:add:index /var/www/html/modules/ps_checkout/vendor"
	# make autoindex module_version=ps8

add-header-stamp:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt"
	# make add-header-stamp module_version=ps8

php-unit-infrastructure:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/infrastructure/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/infrastructure/tests/bootstrap.php"
	 # make php-unit-infrastructure module_version=ps8

php-unit-utility:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/utility/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/utility/tests/bootstrap.php"
	 # make php-unit-utility module_version=ps8

php-unit-infrastructure-integration:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/infrastructure/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/vendor/invertus/infrastructure/tests/bootstrap-integration.php"
	 # make php-unit-integration module_version=ps8

php-unit-core:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap.php"
	 # make php-unit-core module_version=ps8

php-unit-presentation:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/presentation/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/presentation/tests/bootstrap.php"
	 # php-unit-presentation module_version=ps8

php-integration-ps8:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/tests/bootstrap-integration.php"
	 # make php-integration-ps8 module_version=ps8


php-integration-core:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap-integration.php"
	 # make php-integration-core module_version=ps8

run-ps-only-local:
	docker compose -f docker-compose.yml -f docker-compose.local.yml up -d

run-local:
	make run-ps-only-local
	make module-composer-install

module-composer-install:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "(cd modules/$${PROJECT_NAME} && composer install --no-interaction --optimize-autoloader)"

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
