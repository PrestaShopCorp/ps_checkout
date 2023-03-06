help:
	@egrep "^#" Makefile

# target: docker-build|db               - Setup/Build PHP & (node)JS dependencies
db: docker-build
docker-build: build-back build-front

build-back:
	docker-compose run --rm php sh -c "composer install"

build-back-prod:
	docker-compose run --rm php sh -c "composer install --no-dev -o"

build-front:
	docker-compose run --rm node sh -c "npm i --prefix ./_dev/js/front"
	docker-compose run --rm node sh -c "npm run build --prefix ./_dev/js/front"

# target: watch-front                   - Watcher for the vueJS files
watch-front:
	docker-compose run --rm node sh -c "npm run watch --prefix ./_dev/js/front"

# target: test-front                   - Launch the front test suite
test-front:
	docker-compose run --rm node sh -c "npm test --prefix ./_dev/js/front"

build-zip:
	cp -Ra $(PWD) /tmp/ps_checkout
	rm -rf /tmp/ps_checkout/.env.test
	rm -rf /tmp/ps_checkout/.php_cs.*
	rm -rf /tmp/ps_checkout/.travis.yml
	rm -rf /tmp/ps_checkout/cloudbuild.yaml
	rm -rf /tmp/ps_checkout/composer.*
	rm -rf /tmp/ps_checkout/package.json
	rm -rf /tmp/ps_checkout/.npmrc
	rm -rf /tmp/ps_checkout/package-lock.json
	rm -rf /tmp/ps_checkout/.gitignore
	rm -rf /tmp/ps_checkout/deploy.sh
	rm -rf /tmp/ps_checkout/.editorconfig
	rm -rf /tmp/ps_checkout/.git
	rm -rf /tmp/ps_checkout/.github
	rm -rf /tmp/ps_checkout/_dev
	rm -rf /tmp/ps_checkout/tests
	rm -rf /tmp/ps_checkout/docker-compose.yml
	rm -rf /tmp/ps_checkout/Makefile
	mv -v /tmp/ps_checkout $(PWD)/ps_checkout
	zip -r ps_checkout.zip ps_checkout
	rm -rf $(PWD)/ps_checkout

# target: build-zip-prod                   - Launch prod zip generation of the module (will not work on windows)
build-zip-prod: build-back-prod test-front build-front build-zip
