#!/bin/sh
set -e

# Fix storage permissions on startup
chown -R www:www /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true
chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache 2>/dev/null || true

# Run database migrations on container start
php artisan migrate --force --no-interaction 2>/dev/null || true

# Use PORT env var (Render) or default to 8080
PORT=${PORT:-8080}
HOST=${HOST:-0.0.0.0}

exec php artisan serve --host=$HOST --port=$PORT
