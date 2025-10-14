#!/bin/sh
set -e

# Generate application key if not set
if ! grep -q "APP_KEY=base64:" /var/www/html/.env 2>/dev/null; then
  echo "Generating application key..."
  php artisan key:generate --force
fi

# Wait for database to be ready with timeout
# Adds max_tries to avoid infinite loop
echo "Waiting for MongoDB connection..."
max_tries=30
count=0
until php artisan db:show > /dev/null 2>&1; do
  count=$((count + 1))
  if [ $count -gt $max_tries ]; then
    echo "MongoDB connection failed after $max_tries attempts"
    exit 1
  fi
  echo "Attempt $count/$max_tries..."
  sleep 2
done

echo "MongoDB is ready!"

# Seed the database to create collections
php artisan db:seed --force

# Flush existing Scout indexes
php artisan scout:flush "App\Models\User"
php artisan scout:flush "App\Models\Post"

# Sleep to ensure indexes are flushed before re-creating them
sleep 2

# Prepare indexes for MongoDB Atlas Search
php artisan scout:index "App\Models\User"
php artisan scout:index "App\Models\Post"

# Import posts and users into MongoDB Atlas Search indexes
php artisan scout:import "App\Models\User"
php artisan scout:import "App\Models\Post"

# Clear caches
php artisan config:cache
php artisan cache:clear || true

# Start PHP-FPM
exec php-fpm
