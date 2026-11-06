#!/bin/sh
set -eu

# Vercel exposes the HTTP port through PORT (defaults to 80).
: "${PORT:=80}"
export PORT

if [ -z "${APP_KEY:-}" ]; then
    echo "ERROR: APP_KEY is not set. Configure it in Vercel Environment Variables." >&2
    exit 1
fi

# APP_URL must be set to the Vercel deployment URL in project environment variables.
if [ -z "${APP_URL:-}" ]; then
    echo "ERROR: APP_URL is not set. Configure it to the Vercel deployment URL." >&2
    exit 1
fi

sed -ri "s/^Listen 80$/Listen ${PORT}/" /etc/apache2/ports.conf
sed -ri "s/<VirtualHost *:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-available/000-default.conf

mkdir -p \
    storage/app/private \
    storage/app/public \
    storage/app/property-images \
    storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache

chown -R www-data:www-data storage bootstrap/cache
chmod -R ug+rwX storage bootstrap/cache

php artisan config:clear >/dev/null 2>&1 || true
php artisan storage:link --force >/dev/null 2>&1 || true

# Never run database migrations automatically on Vercel startup.
# Run reviewed migrations separately against a dedicated test database if needed.
if [ "${RUN_MIGRATIONS:-false}" = "true" ]; then
    echo "ERROR: RUN_MIGRATIONS=true is blocked in this Vercel test image. Run migrations manually after review." >&2
    exit 1
fi

if [ "${APP_ENV:-production}" = "production" ]; then
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
fi

exec apache2-foreground
