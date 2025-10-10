PHP_CONTAINER=lannify-php-1

# Démarrer les conteneurs
docker-start:
	docker compose --env-file .env.local up -d --build

# Arrêter les conteneurs
docker-stop:
	docker compose down

php-shell:
	docker exec -it -w /app $(PHP_CONTAINER) bash

tailwind-watch:
	docker exec -it -w /app $(PHP_CONTAINER) php bin/console tailwind:build --watch
new-migration:
	docker exec -it -w /app $(PHP_CONTAINER) php bin/console make:migration
migrate:
	docker exec -it -w /app $(PHP_CONTAINER) php bin/console doctrine:migrations:migrate
