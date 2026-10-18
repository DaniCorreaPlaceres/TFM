#!/bin/sh
set -e

cd /var/www/html

if [ ! -f .env ] && [ -f .env.example ]; then
    cp .env.example .env
fi

if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

mkdir -p var/cache var/log
chown -R www-data:www-data var || true

exec docker-php-entrypoint "$@"
