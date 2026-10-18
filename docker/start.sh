#!/bin/sh
set -eu

: "${APP_KEY:?Set APP_KEY in Render Environment before deploying}"
: "${DB_HOST:?Set DB_HOST from TiDB Connect}"
: "${DB_PASSWORD:?Set DB_PASSWORD from TiDB Connect}"

sed -i 's/^Listen 80$/Listen 10000/' /etc/apache2/ports.conf
php artisan config:cache
php artisan migrate --force
if [ -n "${INITIAL_ADMIN_PASSWORD:-}" ]; then
    php artisan deploy:admin --no-interaction
fi
php artisan view:cache
chown -R www-data:www-data storage bootstrap/cache
exec apache2-foreground
