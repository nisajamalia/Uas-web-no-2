#!/bin/bash

echo "Starting Render build..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Install Node dependencies
npm install

# Build assets
npm run build

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate --force

# Run database migrations
php artisan migrate --force

# Cache configuration for better performance
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "Render build completed!"