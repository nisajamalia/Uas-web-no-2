#!/bin/bash

# Install PHP dependencies
composer install --no-dev --optimize-autoloader

# Generate application key if not exists
if [ ! -f .env ]; then
    cp .env.example .env
fi

# Generate app key
php artisan key:generate --force

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link if needed
php artisan storage:link

echo "Build completed successfully!"