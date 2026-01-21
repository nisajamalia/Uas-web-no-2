Cara Menjalankan Aplikasi
Backend (Laravel)
cd Backend

# Install dependencies
composer install

# Setup environment
cp .env.example .env
php artisan key:generate

# Database setup
php artisan migrate
php artisan db:seed --class=MahasiswaSeeder

# Start server
php artisan serve --port=8001
Frontend (Vue.js)
cd Frontend/vue-project

# Install dependencies
npm install

# Start development server
npm run dev
Testing
cd Backend

# Run all tests
php artisan test

# Run specific test
php artisan test --filter=MahasiswaTest
API Documentation
