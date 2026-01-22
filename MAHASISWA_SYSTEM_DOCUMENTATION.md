# Sistem Manajemen Mahasiswa - SAKTI Mini

## Deskripsi Proyek

Sistem manajemen mahasiswa yang dibangun menggunakan Laravel (Backend) dan Vue.js (Frontend) dengan fitur CRUD lengkap, pencarian, filtering, sorting, dan pagination.

## Struktur Proyek

```
├── Backend/                    # Laravel API Backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/
│   │   │   │   └── MahasiswaController.php
│   │   │   └── Requests/
│   │   │       ├── StoreMahasiswaRequest.php
│   │   │       └── UpdateMahasiswaRequest.php
│   │   └── Models/
│   │       └── Mahasiswa.php
│   ├── database/
│   │   ├── factories/
│   │   │   └── MahasiswaFactory.php
│   │   ├── migrations/
│   │   │   └── create_mahasiswas_table.php
│   │   └── seeders/
│   │       └── MahasiswaSeeder.php
│   └── tests/
│       └── Feature/
│           └── MahasiswaTest.php
└── Frontend/                   # Vue.js Frontend
    └── vue-project/
        └── src/
            ├── components/
            │   ├── MahasiswaList.vue
            │   ├── MahasiswaForm.vue
            │   ├── MahasiswaDetail.vue
            │   ├── ConfirmDialog.vue
            │   └── NotificationToast.vue
            └── stores/
                ├── mahasiswa.ts
                └── notification.ts
```

## Backend Laravel - Implementasi

### 1. Model Mahasiswa

**File:** `Backend/app/Models/Mahasiswa.php`

**Fitur:**
- Soft Delete untuk keamanan data
- Scopes untuk pencarian dan filtering
- Validasi dan casting data
- Factory untuk testing

**Fields:**
- `nim` (string, unique, required) - Nomor Induk Mahasiswa
- `nama` (string, required) - Nama lengkap mahasiswa
- `email` (string, unique, required) - Email mahasiswa
- `prodi` (string, required) - Program studi
- `angkatan` (integer, required) - Tahun angkatan (4 digit)
- `status` (enum, required) - Status: aktif, cuti, lulus, dropout
- `created_at`, `updated_at` - Timestamp otomatis
- `deleted_at` - Soft delete timestamp

### 2. Migration dan Database

**File:** `Backend/database/migrations/create_mahasiswas_table.php`

**Optimasi Query:**
- Index pada kolom `nim` untuk pencarian cepat
- Composite index pada `prodi` dan `status` untuk filtering
- Index pada `angkatan` untuk filtering tahun
- Soft delete support

### 3. Validation Requests

**StoreMahasiswaRequest & UpdateMahasiswaRequest:**
- Validasi server-side lengkap
- Custom error messages dalam Bahasa Indonesia
- Validasi unique untuk NIM dan email
- Validasi rentang angkatan (10 tahun ke belakang dari tahun sekarang)
- Validasi enum untuk status

### 4. Controller API

**File:** `Backend/app/Http/Controllers/MahasiswaController.php`

**Endpoints:**

#### GET /api/mahasiswa
- **Pagination:** Support per_page, page
- **Search:** Parameter `q` untuk pencarian nama/nim/email
- **Filtering:** 
  - `prodi` - Filter berdasarkan program studi
  - `status` - Filter berdasarkan status mahasiswa
  - `angkatan` - Filter berdasarkan tahun angkatan
- **Sorting:** 
  - `sortBy` - Field untuk sorting
  - `sortDir` - Arah sorting (asc/desc)

#### GET /api/mahasiswa/{id}
- Menampilkan detail mahasiswa berdasarkan ID
- Error handling untuk data tidak ditemukan

#### POST /api/mahasiswa
- Membuat mahasiswa baru
- Validasi lengkap server-side
- Response dengan data mahasiswa yang baru dibuat

#### PUT/PATCH /api/mahasiswa/{id}
- Update data mahasiswa
- Validasi unique dengan pengecualian ID yang sedang diupdate
- Response dengan data mahasiswa yang sudah diupdate

#### DELETE /api/mahasiswa/{id}
- **Soft Delete** - Data tidak benar-benar dihapus
- Alasan menggunakan soft delete:
  1. **Keamanan Data:** Mencegah kehilangan data permanen
  2. **Audit Trail:** Mempertahankan riwayat untuk keperluan audit
  3. **Recovery:** Memungkinkan pemulihan data jika diperlukan
  4. **Referential Integrity:** Menjaga konsistensi relasi database
  5. **Compliance:** Memenuhi requirement regulasi penyimpanan data

### 5. Seeder dan Factory

**MahasiswaSeeder:** Data sample untuk development dan testing
**MahasiswaFactory:** Generator data fake untuk testing

### 6. Testing

**File:** `Backend/tests/Feature/MahasiswaTest.php`

**Test Coverage:**
- CRUD operations lengkap
- Validation testing
- Search dan filtering
- Pagination
- Soft delete
- Error handling

## Frontend Vue.js - Implementasi

### 1. Store Management (Pinia)

**File:** `Frontend/vue-project/src/stores/mahasiswa.ts`

**Fitur:**
- State management untuk data mahasiswa
- API integration dengan error handling
- Pagination state management
- Loading states

### 2. Komponen UI

#### MahasiswaList.vue
- **Tabel dengan pagination:** Menampilkan data dalam format tabel dengan navigasi halaman
- **Search box:** Pencarian real-time dengan debouncing
- **Filter dropdown:** Filter berdasarkan prodi, status, angkatan
- **Sorting kolom:** Klik header untuk sorting ascending/descending
- **Action buttons:** Detail, Edit, Delete untuk setiap row

#### MahasiswaForm.vue
- **Form tambah/edit:** Modal form dengan validasi client-side
- **Validasi client-side:** Validasi real-time sebelum submit
- **Error handling:** Menampilkan error validasi dari server (422)
- **Loading states:** Indikator loading saat submit

#### MahasiswaDetail.vue
- **Modal detail:** Menampilkan informasi lengkap mahasiswa
- **Format data:** Tanggal dalam format Indonesia
- **Status badge:** Visual indicator untuk status mahasiswa

#### ConfirmDialog.vue
- **Konfirmasi delete:** Modal konfirmasi sebelum menghapus
- **User-friendly:** Pesan konfirmasi yang jelas

### 3. Notification System

**NotificationToast.vue & NotificationContainer.vue:**
- **Success/Error notifications:** Feedback visual untuk setiap aksi
- **Auto-dismiss:** Notifikasi hilang otomatis setelah 5 detik
- **Multiple notifications:** Support multiple toast bersamaan

### 4. Validasi Client-Side

**Validasi yang diimplementasi:**
- Required fields validation
- Email format validation
- NIM length validation
- Angkatan range validation
- Status enum validation

### 5. Error Handling

**Server Error Handling (422):**
- Parsing error response dari server
- Menampilkan field-specific errors
- Fallback ke general error message

## Optimasi Query

### 1. Database Indexes
```sql
-- Index untuk pencarian NIM
INDEX `mahasiswas_nim_index` (`nim`)

-- Composite index untuk filtering
INDEX `mahasiswas_prodi_status_index` (`prodi`, `status`)

-- Index untuk filtering angkatan
INDEX `mahasiswas_angkatan_index` (`angkatan`)
```

### 2. Query Scopes
- **Search scope:** Optimasi pencarian dengan LIKE query
- **Filter scopes:** Conditional filtering untuk menghindari query yang tidak perlu

### 3. Pagination
- Limit hasil query dengan pagination
- Efficient counting untuk total records

## Cara Menjalankan Aplikasi

### Backend (Laravel)

```bash
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
```

### Frontend (Vue.js)

```bash
cd Frontend/vue-project

# Install dependencies
npm install

# Start development server
npm run dev
```

### Testing

```bash
cd Backend

# Run all tests
php artisan test

# Run specific test
php artisan test --filter=MahasiswaTest
```

## API Documentation

### Base URL
```
http://localhost:8001/api
```

### Endpoints

#### GET /mahasiswa
**Query Parameters:**
- `q` (string) - Search query untuk nama/nim/email
- `prodi` (string) - Filter berdasarkan program studi
- `status` (string) - Filter berdasarkan status (aktif|cuti|lulus|dropout)
- `angkatan` (integer) - Filter berdasarkan tahun angkatan
- `sortBy` (string) - Field untuk sorting
- `sortDir` (string) - Arah sorting (asc|desc)
- `per_page` (integer) - Jumlah item per halaman (default: 15, max: 100)
- `page` (integer) - Nomor halaman

**Response:**
```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "current_page": 1,
    "last_page": 1,
    "per_page": 15,
    "total": 8,
    "from": 1,
    "to": 8
  }
}
```

#### POST /mahasiswa
**Request Body:**
```json
{
  "nim": "2024001",
  "nama": "John Doe",
  "email": "john@example.com",
  "prodi": "Teknik Informatika",
  "angkatan": 2024,
  "status": "aktif"
}
```

## Keputusan Desain

### 1. Soft Delete
**Alasan:** Keamanan data, audit trail, dan compliance requirement

### 2. Client-Side + Server-Side Validation
**Alasan:** UX yang baik dengan keamanan yang terjamin

### 3. Pagination
**Alasan:** Performance untuk dataset besar

### 4. Debounced Search
**Alasan:** Mengurangi API calls yang tidak perlu

### 5. Notification System
**Alasan:** User feedback yang jelas untuk setiap aksi

## Fitur Keamanan

1. **Input Validation:** Validasi lengkap di client dan server
2. **SQL Injection Prevention:** Menggunakan Eloquent ORM
3. **XSS Prevention:** Vue.js automatic escaping
4. **CSRF Protection:** Laravel built-in protection
5. **Rate Limiting:** API throttling untuk mencegah abuse

## Performance Optimizations

1. **Database Indexing:** Index pada kolom yang sering diquery
2. **Pagination:** Membatasi jumlah data yang diload
3. **Debouncing:** Mengurangi API calls pada search
4. **Lazy Loading:** Component lazy loading untuk route
5. **Caching:** Browser caching untuk static assets

## Konsep Security dan Mitigasi Risiko

### 1. Validasi Server-Side yang Kuat + Aturan Unik

**Implementasi:**
- **Request Validation Classes:** `RegisterRequest`, `LoginRequest`, `StoreMahasiswaRequest`, `UpdateMahasiswaRequest`
- **Unique Constraints:** NIM dan email memiliki validasi unique di database dan request validation
- **Custom Messages:** Pesan error dalam Bahasa Indonesia untuk UX yang baik

**Mitigasi Spesifik:**
```php
// Validasi NIM unik
'nim' => ['required', 'string', 'max:20', 'unique:mahasiswas,nim'],

// Validasi email unik dengan pengecualian saat update
'email' => ['required', 'email', 'unique:mahasiswas,email,' . $this->route('mahasiswa')],

// Validasi enum untuk status
'status' => ['required', 'in:aktif,cuti,lulus,dropout'],
```

**Risiko yang Dimitigasi:**
- Data corruption dari input tidak valid
- Duplicate data (NIM/email ganda)
- SQL injection melalui input validation
- Business logic violation

### 2. Proteksi Mass Assignment

**Implementasi:**
```php
// Model Mahasiswa.php
protected $fillable = [
    'nim', 'nama', 'email', 'prodi', 'angkatan', 'status'
];

// Tidak menggunakan $guarded = [] untuk keamanan maksimal
```

**Mitigasi Spesifik:**
- Hanya field yang diizinkan yang bisa di-assign secara mass
- Mencegah assignment field sensitif seperti `id`, `created_at`, `updated_at`
- Proteksi terhadap parameter pollution attacks

**Risiko yang Dimitigasi:**
- Unauthorized field modification
- Privilege escalation melalui mass assignment
- Data integrity compromise

### 3. Rate Limiting untuk Endpoint Write

**Implementasi:**
```php
// routes/api.php
Route::post('/register', [AuthController::class, 'register'])
    ->middleware('throttle:60,1'); // 60 requests per minute

Route::post('/login', [AuthController::class, 'login'])
    ->middleware('throttle:60,1');

// Untuk production, akan dikurangi menjadi:
// ->middleware('throttle:5,1'); // 5 attempts per minute
```

**Mitigasi Spesifik:**
- Mencegah brute force attacks pada login
- Membatasi spam registration
- Proteksi terhadap automated attacks
- Rate limiting berdasarkan IP address

**Risiko yang Dimitigasi:**
- Brute force password attacks
- Account enumeration attacks
- Resource exhaustion (DoS)
- Automated spam/abuse

### 4. Sistem Autentikasi dan Pembatasan Akses

**Implementasi Saat Ini:**
- **Laravel Sanctum:** Token-based authentication
- **Security Logging:** Comprehensive logging untuk semua aktivitas auth
- **Token Management:** Proper token creation dan deletion

**Pembatasan Akses:**
```php
// Protected routes
Route::middleware(['auth:sanctum', 'throttle:auth'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
});

// Mahasiswa endpoints (currently public, akan diproteksi)
Route::apiResource('mahasiswa', MahasiswaController::class);
```

**Rencana Role-Based Access Control (RBAC):**
```php
// Akan diimplementasi:
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/mahasiswa', [MahasiswaController::class, 'store']);
    Route::put('/mahasiswa/{id}', [MahasiswaController::class, 'update']);
    Route::delete('/mahasiswa/{id}', [MahasiswaController::class, 'destroy']);
});

Route::middleware(['auth:sanctum', 'role:admin,staff'])->group(function () {
    Route::get('/mahasiswa', [MahasiswaController::class, 'index']);
    Route::get('/mahasiswa/{id}', [MahasiswaController::class, 'show']);
});
```

**Risiko Tanpa Auth pada Mahasiswa Endpoints:**
- **Data Exposure:** Siapa saja bisa mengakses data mahasiswa
- **Unauthorized Modification:** Tidak ada kontrol siapa yang bisa CRUD
- **No Audit Trail:** Tidak ada tracking siapa yang melakukan perubahan
- **Compliance Issues:** Melanggar regulasi perlindungan data pribadi

**Rencana Implementasi Auth:**
1. **Tambah Role System:** Admin, Staff, Mahasiswa
2. **Middleware Authorization:** Proteksi setiap endpoint
3. **Audit Logging:** Track semua perubahan data
4. **Data Filtering:** User hanya bisa akses data sesuai role

### 5. Security Headers dan Middleware

**Implementasi:**
```php
// SecurityHeaders Middleware
$response->headers->set('X-Content-Type-Options', 'nosniff');
$response->headers->set('X-Frame-Options', 'DENY');
$response->headers->set('X-XSS-Protection', '1; mode=block');
$response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
$response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
```

**Input Sanitization:**
- Automatic removal of null bytes dan control characters
- Trim whitespace dari input
- Recursive sanitization untuk nested arrays

### 6. Security Logging dan Monitoring

**Implementasi:**
```php
// SecurityLogger Service
SecurityLogger::logSecurityEvent('login_attempt', [
    'email' => $credentials['email'],
    'ip' => $ip,
    'user_agent' => $userAgent,
]);
```

**Events yang Dilog:**
- Login attempts (success/failure)
- Registration attempts
- Profile access
- Failed authentication
- Suspicious activities

### 7. CORS Configuration

**Implementasi:**
```php
// config/cors.php
'allowed_origins' => [
    env('FRONTEND_URL', 'http://localhost:3000'),
    'https://app.kampus.ac.id', // Production domain
],

'allowed_origins_patterns' => [
    '/^https:\/\/.*\.kampus\.ac\.id$/', // Subdomains
],
```

## Panduan Deployment End-to-End

### 1. Persiapan Server (VPS Ubuntu 22.04)

**Instalasi Dependencies:**
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install Nginx
sudo apt install nginx -y

# Install PHP 8.2 dan extensions
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.2-fpm php8.2-mysql php8.2-xml php8.2-curl php8.2-zip php8.2-mbstring php8.2-gd php8.2-intl -y

# Install MySQL
sudo apt install mysql-server -y

# Install Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y

# Install Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Install Certbot untuk SSL
sudo apt install certbot python3-certbot-nginx -y
```

### 2. Setup Database

```bash
# Secure MySQL installation
sudo mysql_secure_installation

# Create database dan user
sudo mysql -u root -p
```

```sql
CREATE DATABASE sakti_mini_production;
CREATE USER 'sakti_user'@'localhost' IDENTIFIED BY 'secure_password_here';
GRANT ALL PRIVILEGES ON sakti_mini_production.* TO 'sakti_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 3. Deploy Backend Laravel

```bash
# Clone repository
cd /var/www
sudo git clone https://github.com/username/sakti-mini.git
sudo chown -R www-data:www-data sakti-mini
cd sakti-mini/Backend

# Install dependencies
sudo -u www-data composer install --optimize-autoloader --no-dev

# Setup environment
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env
```

**Konfigurasi .env Production:**
```env
APP_NAME="SAKTI Mini"
APP_ENV=production
APP_KEY=base64:GENERATE_NEW_KEY_HERE
APP_DEBUG=false
APP_URL=https://sakti.kampus.ac.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sakti_mini_production
DB_USERNAME=sakti_user
DB_PASSWORD=secure_password_here

# Cache dan Session
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Mail (gunakan service seperti Mailgun/SendGrid)
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=your_mailgun_username
MAIL_PASSWORD=your_mailgun_password
MAIL_FROM_ADDRESS="noreply@kampus.ac.id"
MAIL_FROM_NAME="SAKTI Mini"

# Frontend URL untuk CORS
FRONTEND_URL=https://sakti.kampus.ac.id
```

**Setup Laravel:**
```bash
# Generate application key
sudo -u www-data php artisan key:generate

# Run migrations
sudo -u www-data php artisan migrate --force

# Seed database (optional)
sudo -u www-data php artisan db:seed --force

# Cache configuration
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Set permissions
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### 4. Build dan Deploy Frontend Vue.js

```bash
cd /var/www/sakti-mini/Frontend/vue-project

# Install dependencies
sudo -u www-data npm ci --production

# Create production environment file
sudo -u www-data nano .env.production
```

**Konfigurasi .env.production:**
```env
VITE_API_BASE_URL=https://api.sakti.kampus.ac.id
VITE_APP_NAME="SAKTI Mini"
VITE_APP_ENV=production
```

**Build Frontend:**
```bash
# Build untuk production
sudo -u www-data npm run build

# Copy build hasil ke direktori web
sudo cp -r dist/* /var/www/sakti-frontend/
sudo chown -R www-data:www-data /var/www/sakti-frontend
```

### 5. Konfigurasi Nginx

**Backend API Configuration:**
```bash
sudo nano /etc/nginx/sites-available/sakti-api
```

```nginx
server {
    listen 80;
    server_name api.sakti.kampus.ac.id;
    root /var/www/sakti-mini/Backend/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req_zone $binary_remote_addr zone=login:10m rate=5r/m;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/api/(login|register) {
        limit_req zone=login burst=3 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ ^/api/ {
        limit_req zone=api burst=20 nodelay;
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

**Frontend SPA Configuration:**
```bash
sudo nano /etc/nginx/sites-available/sakti-frontend
```

```nginx
server {
    listen 80;
    server_name sakti.kampus.ac.id;
    root /var/www/sakti-frontend;
    index index.html;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header Referrer-Policy "strict-origin-when-cross-origin" always;

    # SPA routing - semua request diarahkan ke index.html
    location / {
        try_files $uri $uri/ /index.html;
    }

    # Cache static assets
    location ~* \.(js|css|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        try_files $uri =404;
    }

    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types text/plain text/css text/xml text/javascript application/javascript application/xml+rss application/json;
}
```

**Enable Sites:**
```bash
sudo ln -s /etc/nginx/sites-available/sakti-api /etc/nginx/sites-enabled/
sudo ln -s /etc/nginx/sites-available/sakti-frontend /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### 6. Setup SSL dengan Let's Encrypt

```bash
# Obtain SSL certificates
sudo certbot --nginx -d sakti.kampus.ac.id -d api.sakti.kampus.ac.id

# Test auto-renewal
sudo certbot renew --dry-run

# Setup auto-renewal cron job
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### 7. Konfigurasi CORS untuk Production

**Update Backend CORS:**
```bash
sudo -u www-data nano /var/www/sakti-mini/Backend/config/cors.php
```

```php
'allowed_origins' => [
    'https://sakti.kampus.ac.id',
    'https://www.sakti.kampus.ac.id',
],

'allowed_origins_patterns' => [
    '/^https:\/\/.*\.kampus\.ac\.id$/',
],
```

### 8. Setup Process Management

**Install Supervisor untuk Queue Workers:**
```bash
sudo apt install supervisor -y

sudo nano /etc/supervisor/conf.d/sakti-worker.conf
```

```ini
[program:sakti-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/sakti-mini/Backend/artisan queue:work --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=2
redirect_stderr=true
stdout_logfile=/var/www/sakti-mini/Backend/storage/logs/worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start sakti-worker:*
```

### 9. Setup Monitoring dan Backup

**Log Rotation:**
```bash
sudo nano /etc/logrotate.d/sakti-mini
```

```
/var/www/sakti-mini/Backend/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    notifempty
    create 0644 www-data www-data
    postrotate
        sudo systemctl reload php8.2-fpm
    endscript
}
```

**Database Backup Script:**
```bash
sudo nano /usr/local/bin/sakti-backup.sh
```

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/sakti-mini"
DATE=$(date +%Y%m%d_%H%M%S)
DB_NAME="sakti_mini_production"
DB_USER="sakti_user"
DB_PASS="secure_password_here"

mkdir -p $BACKUP_DIR

# Database backup
mysqldump -u $DB_USER -p$DB_PASS $DB_NAME | gzip > $BACKUP_DIR/db_backup_$DATE.sql.gz

# Keep only last 7 days
find $BACKUP_DIR -name "db_backup_*.sql.gz" -mtime +7 -delete

# Application backup
tar -czf $BACKUP_DIR/app_backup_$DATE.tar.gz -C /var/www sakti-mini --exclude=node_modules --exclude=vendor
find $BACKUP_DIR -name "app_backup_*.tar.gz" -mtime +7 -delete
```

```bash
sudo chmod +x /usr/local/bin/sakti-backup.sh

# Setup cron untuk backup harian
sudo crontab -e
# Add: 0 2 * * * /usr/local/bin/sakti-backup.sh
```

### 10. Deployment Script

**Create Deployment Script:**
```bash
sudo nano /usr/local/bin/deploy-sakti.sh
```

```bash
#!/bin/bash
set -e

echo "🚀 Starting SAKTI Mini deployment..."

# Variables
APP_DIR="/var/www/sakti-mini"
FRONTEND_DIR="/var/www/sakti-frontend"
BACKUP_DIR="/var/backups/sakti-mini"
DATE=$(date +%Y%m%d_%H%M%S)

# Create backup before deployment
echo "📦 Creating backup..."
mkdir -p $BACKUP_DIR
tar -czf $BACKUP_DIR/pre_deploy_backup_$DATE.tar.gz -C /var/www sakti-mini sakti-frontend

# Pull latest code
echo "📥 Pulling latest code..."
cd $APP_DIR
sudo -u www-data git pull origin main

# Backend deployment
echo "🔧 Deploying backend..."
cd $APP_DIR/Backend
sudo -u www-data composer install --optimize-autoloader --no-dev
sudo -u www-data php artisan migrate --force
sudo -u www-data php artisan config:cache
sudo -u www-data php artisan route:cache
sudo -u www-data php artisan view:cache

# Frontend deployment
echo "🎨 Deploying frontend..."
cd $APP_DIR/Frontend/vue-project
sudo -u www-data npm ci --production
sudo -u www-data npm run build
sudo rm -rf $FRONTEND_DIR/*
sudo cp -r dist/* $FRONTEND_DIR/
sudo chown -R www-data:www-data $FRONTEND_DIR

# Restart services
echo "🔄 Restarting services..."
sudo systemctl reload php8.2-fpm
sudo systemctl reload nginx
sudo supervisorctl restart sakti-worker:*

echo "✅ Deployment completed successfully!"
echo "🌐 Frontend: https://sakti.kampus.ac.id"
echo "🔌 API: https://api.sakti.kampus.ac.id"
```

```bash
sudo chmod +x /usr/local/bin/deploy-sakti.sh
```

### 11. Health Check dan Monitoring

**Setup Health Check Endpoint:**
```bash
# Already implemented in routes/api.php
# GET /api/health
```

**Monitoring Script:**
```bash
sudo nano /usr/local/bin/sakti-monitor.sh
```

```bash
#!/bin/bash
API_URL="https://api.sakti.kampus.ac.id/api/health"
FRONTEND_URL="https://sakti.kampus.ac.id"

# Check API health
if curl -f -s $API_URL > /dev/null; then
    echo "✅ API is healthy"
else
    echo "❌ API is down"
    # Send alert (email, Slack, etc.)
fi

# Check frontend
if curl -f -s $FRONTEND_URL > /dev/null; then
    echo "✅ Frontend is healthy"
else
    echo "❌ Frontend is down"
    # Send alert
fi

# Check database connection
cd /var/www/sakti-mini/Backend
if sudo -u www-data php artisan tinker --execute="DB::connection()->getPdo(); echo 'DB OK';" > /dev/null 2>&1; then
    echo "✅ Database is healthy"
else
    echo "❌ Database connection failed"
fi
```

### 12. Perintah Deployment Lengkap

**Initial Deployment:**
```bash
# 1. Setup server dan install dependencies (langkah 1)
# 2. Setup database (langkah 2)
# 3. Clone dan setup aplikasi
sudo /usr/local/bin/deploy-sakti.sh

# 4. Setup SSL
sudo certbot --nginx -d sakti.kampus.ac.id -d api.sakti.kampus.ac.id
```

**Update Deployment:**
```bash
# Simple update
sudo /usr/local/bin/deploy-sakti.sh

# Dengan rollback capability
sudo /usr/local/bin/sakti-backup.sh  # Manual backup
sudo /usr/local/bin/deploy-sakti.sh
```

**Rollback (jika diperlukan):**
```bash
# Restore dari backup
cd /var/backups/sakti-mini
sudo tar -xzf pre_deploy_backup_YYYYMMDD_HHMMSS.tar.gz -C /var/www
sudo systemctl reload nginx php8.2-fpm
```

## Kesimpulan

Sistem manajemen mahasiswa ini telah memenuhi semua requirement yang diminta dengan implementasi security yang komprehensif dan panduan deployment yang detail:

**Security Implementation:**
✅ Server-side validation dengan unique constraints
✅ Mass assignment protection
✅ Rate limiting untuk endpoints sensitif
✅ Authentication system dengan role planning
✅ Security headers dan input sanitization
✅ Comprehensive security logging
✅ CORS configuration untuk production

**Deployment Ready:**
✅ End-to-end deployment guide
✅ Production environment configuration
✅ SSL/HTTPS setup dengan Let's Encrypt
✅ Nginx configuration untuk SPA dan API
✅ Process management dengan Supervisor
✅ Backup dan monitoring scripts
✅ Health check endpoints
