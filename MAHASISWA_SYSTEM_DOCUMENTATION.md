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
