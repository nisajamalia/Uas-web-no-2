<?php

// Simple migration runner for production
require __DIR__ . '/vendor/autoload.php';

$app = require_once __DIR__ . '/bootstrap/app.php';

// Run migrations
Artisan::call('migrate', ['--force' => true]);

echo "Migrations completed!\n";