.PHONY: up down build install schema migrate seed test sh logs

up:
	docker compose up -d --build

down:
	docker compose down

build:
	docker compose build

install:
	docker compose exec php composer install

#DATABASE
db-up:
	docker compose up -d --wait

migrate:
	docker compose exec php bin/console doctrine:migrations:migrate --no-interaction --allow-no-migration

test-db:
	docker compose exec php bin/console doctrine:database:create --env=test --if-not-exists
	docker compose exec php bin/console doctrine:migrations:migrate --env=test --no-interaction --allow-no-migration

#TOOLS
fix:
	docker compose exec php vendor/bin/php-cs-fixer fix

analyse:
	docker compose exec php vendor/bin/phpstan analyse

test:
	docker compose exec -e APP_ENV=test php php bin/phpunit

fullScan:
	$(MAKE) fix && $(MAKE) analyse && $(MAKE) test

sh:
	docker compose exec php bash

logs:
	docker compose logs -f

db-reset:
	docker compose down -v && $(MAKE) db-up && $(MAKE) migrate test-db