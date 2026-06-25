#!/usr/bin/env bash
set -e

cd /var/www/html

# If vendor dir is missing (e.g. fresh volume mount), install dependencies
if [ ! -d "vendor" ] || [ -z "$(ls -A vendor 2>/dev/null)" ]; then
    echo "Installing composer dependencies..."
    composer install --no-interaction --prefer-dist --optimize-autoloader
fi

# Make sure Laravel's cached package list matches what is actually installed
php artisan package:discover --ansi || true

# Copy .env if it doesn't exist
if [ ! -f ".env" ]; then
    echo "Creating .env from .env.example..."
    cp .env.example .env
fi

# Generate application key if not set
if ! grep -q "^APP_KEY=base64" .env 2>/dev/null; then
    echo "Generating application key..."
    php artisan key:generate --force
fi

# Wait for the database to be ready
echo "Waiting for database connection..."
until php artisan db:show > /dev/null 2>&1; do
    echo "Database is unavailable - waiting..."
    sleep 2
done
echo "Database is up."

# Run database migrations
echo "Running migrations..."
php artisan migrate --force

echo "Running seeds..."
# Create the storage symlink (ignore error if it already exists)
php artisan storage:link || true

# Clear/cache config so env changes are picked up
php artisan config:clear
php artisan view:clear
php artisan route:clear

# Fix permissions (helpful when running with bind mounts)
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache || true

echo "Application ready."

exec "$@"