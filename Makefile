PHP_CONTAINER=lannify-php-1

# Démarrer les conteneurs
docker-start:
	docker compose up -d --build

# Arrêter les conteneurs
docker-stop:
	docker compose down

php-shell:
	docker exec -it -w /app $(PHP_CONTAINER) bash

tailwind-watch:
	docker exec -it -w /app $(PHP_CONTAINER) php bin/console tailwind:build --watch
