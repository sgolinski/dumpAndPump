build:
	docker compose -p lp_pump -f docker/docker-compose.yaml build --no-cache

startup:
	docker compose -p lp_pump -f docker/docker-compose.yaml up -d

stop:
	docker compose -p lp_pump -f docker/docker-compose.yaml down --remove-orphans

composer_install:
	docker compose -p lp_pump -f docker/docker-compose.yaml run php-cli composer update

install: build composer_install
