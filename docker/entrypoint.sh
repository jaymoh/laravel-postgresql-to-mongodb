#!/bin/sh
set -e

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

# Wait for database to be ready
until php artisan db:show 2>/dev/null; do
  echo "Waiting for database connection..."
  sleep 2
done

echo "Database is ready!"

# Run migrations
php artisan cache:table || true
php artisan migrate --force

# Seed the database
php artisan db:seed --force

# Clear caches
php artisan config:cache
php artisan cache:clear || true

# Start PHP-FPM
exec php-fpm
