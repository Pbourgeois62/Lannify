#!/bin/sh
set -e

if [ "$1" = 'frankenphp' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then
	# Installation initiale du projet
	if [ ! -f composer.json ]; then
		rm -Rf tmp/
		composer create-project "symfony/skeleton $SYMFONY_VERSION" tmp --stability="$STABILITY" --prefer-dist --no-progress --no-interaction
		cd tmp && cp -Rp . .. && cd - && rm -Rf tmp/
		composer require "php:>=$PHP_VERSION" runtime/frankenphp-symfony
		composer config --json extra.symfony.docker 'true'
		if grep -q ^DATABASE_URL= .env; then
			echo 'To finish the installation please press Ctrl+C to stop Docker Compose and run: docker compose up --build --wait'
			sleep infinity
		fi
	fi

	# Installation des dépendances PHP
	if [ -z "$(ls -A 'vendor/' 2>/dev/null)" ]; then
		composer install --prefer-dist --no-progress --no-interaction
	fi

	# Informations de projet
	php bin/console -V

	# Attente base de données
	if grep -q ^DATABASE_URL= .env; then
		echo 'Waiting for database to be ready...'
		ATTEMPTS_LEFT_TO_REACH_DATABASE=60
		until [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ] || DATABASE_ERROR=$(php bin/console dbal:run-sql -q "SELECT 1" 2>&1); do
			if [ $? -eq 255 ]; then
				ATTEMPTS_LEFT_TO_REACH_DATABASE=0
				break
			fi
			sleep 1
			ATTEMPTS_LEFT_TO_REACH_DATABASE=$((ATTEMPTS_LEFT_TO_REACH_DATABASE - 1))
			echo "Still waiting for database to be ready... ($ATTEMPTS_LEFT_TO_REACH_DATABASE left)"
		done

		if [ $ATTEMPTS_LEFT_TO_REACH_DATABASE -eq 0 ]; then
			echo 'Database not reachable:'
			echo "$DATABASE_ERROR"
			exit 1
		else
			echo '✅ Database ready'
		fi

		if [ "$(find ./migrations -iname '*.php' -print -quit)" ]; then
			php bin/console doctrine:migrations:migrate --no-interaction --all-or-nothing
		fi
	fi

	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

	# --- 💡 Ajout spécifique Tailwind / AssetMap selon environnement ---
	if [ "$APP_ENV" = "dev" ]; then
		echo "🔵 Starting Tailwind in watch mode..."
		# Lancer le watch en arrière-plan pour ne pas bloquer
		php bin/console tailwind:build --watch &
	else
		echo "🟢 Building Tailwind and Asset Map for production..."
		php bin/console tailwind:build
		php bin/console asset-map:compile
	fi

	echo 'PHP app ready!'
fi

exec docker-php-entrypoint "$@"
