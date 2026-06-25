include .env
export

ROOT_DIR:=$(shell dirname $(realpath $(firstword $(MAKEFILE_LIST))))

install:
	composer install

generate-cloudflared-config:
	@sed \
		-e 's|$${TUNNEL_ID}|$(TUNNEL_ID)|g' \
		-e 's|$${PS_DOMAIN}|$(PS_DOMAIN)|g' \
		-e 's|$${CLOUDFLARED_DOMAIN}|$(CLOUDFLARED_DOMAIN)|g' \
		-e 's|$${MODULE_VERSION}|$(MODULE_VERSION)|g' \
		-e 's|$${PS_VERSION_TAG}|$(PS_VERSION_TAG)|g' \
		.cloudflared.yml.dist > .cloudflared.yml

up: install generate-cloudflared-config
	docker compose up -d

build:
	docker compose build --no-cache

stop:
	docker compose stop

down:
	docker compose down --remove-orphans --volumes

php-cs-fixer:
	composer cs:fix

autoindex:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/autoindex prestashop:add:index --exclude=vendor /var/www/html/modules/ps_checkout"

add-header-stamp:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} /bin/bash -c "cd modules/ps_checkout && php vendor/bin/header-stamp --license=vendor/prestashop/header-stamp/assets/afl.txt"

lint: php-cs-fixer autoindex

phpcompat-71:
	composer phpcompat:71

php-unit-api:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/api/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/api/tests/bootstrap.php"

php-unit-infrastructure:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/infrastructure/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/infrastructure/tests/bootstrap.php"

php-unit-utility:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/utility/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/utility/tests/bootstrap.php"

php-unit-core:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap.php"

php-unit-presentation:
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/presentation/tests/phpunit.xml --bootstrap=modules/ps_checkout/vendor/invertus/presentation/tests/bootstrap.php"

unit-test: php-unit-api php-unit-utility php-unit-core php-unit-presentation

create-test-db:
	docker exec -i $${MODULE_VERSION}-ps-mysql-$${PS_VERSION_TAG} mysql -uroot -pprestashop -e "DROP DATABASE IF EXISTS test_prestashop; CREATE DATABASE test_prestashop;"
	docker exec $${MODULE_VERSION}-ps-mysql-$${PS_VERSION_TAG} mysqldump -uroot -pprestashop prestashop | \
	  docker exec -i $${MODULE_VERSION}-ps-mysql-$${PS_VERSION_TAG} mysql -uroot -pprestashop test_prestashop
	docker exec -i $${MODULE_VERSION}-ps-mysql-$${PS_VERSION_TAG} mysql -uroot -pprestashop test_prestashop -e "\
	  ALTER TABLE ps_pscheckout_cart MODIFY COLUMN paypal_token text DEFAULT NULL;\
	  ALTER TABLE ps_pscheckout_cart MODIFY COLUMN paypal_status varchar(30) NULL;\
	  ALTER TABLE ps_pscheckout_cart ADD COLUMN IF NOT EXISTS environment varchar(20) DEFAULT NULL;\
	  ALTER TABLE ps_pscheckout_order ADD COLUMN IF NOT EXISTS tags varchar(255) DEFAULT NULL;\
	  ALTER TABLE ps_pscheckout_order ADD COLUMN IF NOT EXISTS date_add datetime DEFAULT NULL;\
	  ALTER TABLE ps_pscheckout_carrier ADD COLUMN IF NOT EXISTS disabled tinyint(1) NOT NULL DEFAULT 0;\
	  ALTER TABLE ps_pscheckout_authorization DROP COLUMN IF EXISTS seller_protection;\
	  ALTER TABLE ps_pscheckout_authorization ADD COLUMN IF NOT EXISTS create_time varchar(20) NOT NULL DEFAULT '';\
	  ALTER TABLE ps_pscheckout_authorization ADD COLUMN IF NOT EXISTS update_time varchar(20) NOT NULL DEFAULT '';\
	  INSERT INTO ps_currency (id_currency, iso_code, name, numeric_iso_code, \`precision\`, conversion_rate, deleted, active, unofficial, modified)\
	    VALUES (2, 'USD', 'US Dollar', '840', 2, 1.100000, 0, 1, 0, 0)\
	    ON DUPLICATE KEY UPDATE iso_code='USD', name='US Dollar';\
	  INSERT INTO ps_currency (id_currency, iso_code, name, numeric_iso_code, \`precision\`, conversion_rate, deleted, active, unofficial, modified)\
	    VALUES (1, 'EUR', 'Euro', '978', 2, 1.000000, 0, 1, 0, 0)\
	    ON DUPLICATE KEY UPDATE iso_code='EUR', name='Euro';\
	  "

php-integration-core: create-test-db
	docker exec -i $${MODULE_VERSION}-ps-prestashop-$${PS_VERSION_TAG} bash -c "php modules/ps_checkout/vendor/bin/phpunit --configuration=modules/ps_checkout/vendor/invertus/core/tests/phpunit-integration.xml --bootstrap=modules/ps_checkout/vendor/invertus/core/tests/bootstrap-integration.php"

integration-test: php-integration-core

test: unit-test integration-test

phpstan:
	cd $${MODULE_VERSION} && composer phpstan

phpstan-baseline:
	cd $${MODULE_VERSION} && composer phpstan:baseline

phpstan-baseline-all:
	cd ps17 && composer phpstan:baseline
	cd ps8 && composer phpstan:baseline
	cd ps9 && composer phpstan:baseline

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
