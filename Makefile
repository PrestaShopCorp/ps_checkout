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
	docker-compose run --rm node sh -c "yarn --cwd _dev/ install"
	docker-compose run --rm node sh -c "yarn --cwd _dev/ build"

# target: watch-front                   - Watcher for the vueJS files
watch-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ dev"

# target: test-front                   - Launch the front test suite
test-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ lint"

build-zip:
	cp -Ra $(PWD) $(PWD)/../buildedZIP
	rm -rf $(PWD)/../buildedZIP/.php_cs.*
	rm -rf $(PWD)/../buildedZIP/.travis.yml
	rm -rf $(PWD)/../buildedZIP/cloudbuild.yaml
	rm -rf $(PWD)/../buildedZIP/composer.*
	rm -rf $(PWD)/../buildedZIP/.gitignore
	rm -rf $(PWD)/../buildedZIP/deploy.sh
	rm -rf $(PWD)/../buildedZIP/.editorconfig
	rm -rf $(PWD)/../buildedZIP/.git
	rm -rf $(PWD)/../buildedZIP/.github
	rm -rf $(PWD)/../buildedZIP/_dev
	rm -rf $(PWD)/../buildedZIP/tests
	rm -rf $(PWD)/../buildedZIP/docker-compose.yml
	rm -rf $(PWD)/../buildedZIP/Makefile
	zip ps_checkout.zip $(PWD)/../buildedZIP
	rm -rf $(PWD)/../buildedZIP

build-zip-prod: build-back-prod build-front test-front build-zip
