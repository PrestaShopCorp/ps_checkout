help:
	@egrep "^#" Makefile

# target: docker-build|db               - Setup/Build PHP & (node)JS dependencies
db: docker-build
docker-build: build-front

build-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ install"
	docker-compose run --rm node sh -c "yarn --cwd _dev/ build"

# target: watch-front                   - Watcher for the vueJS files
watch-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ dev"

# target: test-front                   - Launch the front test suite
test-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ lint"
