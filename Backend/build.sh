#!/bin/bash

echo "Starting Laravel build for Vercel..."

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction

# Copy environment file
cp .env.example .env

# Generate application key
php artisan key:generate --force --no-interaction

echo "Laravel build completed successfully!"