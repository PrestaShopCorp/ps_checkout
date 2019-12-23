help:
	@egrep "^#" Makefile

# target: docker-up|du                  - Start docker containers
du: docker-up
docker-up:
	docker-compose up -d --build

# target: docker-down|dd                - Stop docker containers
dd: docker-down
docker-down:
	docker-compose down

# target: docker-build|db               - Setup/Build PHP & (node)JS dependencies
db: docker-build
docker-build: build-composer build-front

build-composer:
	docker-compose exec app sh -c "composer install -o --prefer-dist --classmap-authoritative --no-progress --no-ansi --no-interaction"

build-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ install"
	docker-compose run --rm node sh -c "yarn --cwd _dev/ build"

# target: watch-front                   - Watcher for the vueJS files
watch-front:
	docker-compose run --rm node sh -c "yarn --cwd _dev/ dev"

# target: bash-app|ba                   - Connect to the app docker container
ba: bash-app
bash-app:
	docker-compose exec app bash
