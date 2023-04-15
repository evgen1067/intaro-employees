COMPOSE=docker compose
PHP=docker exec -it diploma-php
CONSOLE=$(PHP) php bin/console

up:
	@${COMPOSE} up -d

upb:
	@${COMPOSE} up --build -d

down:
	@${COMPOSE} down

clear:
	@${CONSOLE} cache:clear

watch:
	@${PHP} npm run watch

build:
	@${PHP} npm run build

-include local.mk