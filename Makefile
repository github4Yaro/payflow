.PHONY: up down build install schema migrate seed test sh logs

up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build

install:
	docker compose exec php composer install

schema:
	docker compose exec php php bin/console doctrine:schema:create

migrate:
	docker compose exec php php bin/console doctrine:migrations:migrate -n

seed:
	docker compose exec -T db mysql -uapp -papp app < dump.sql

test:
	docker compose exec -e APP_ENV=test php php bin/phpunit

sh:
	docker compose exec php bash

logs:
	docker compose logs -f
