# Error Handling Test - SAKTI Mini

## Test Cases untuk Login Error Handling

### 1. Password Salah
**Input:**
- Email: `test@example.com`
- Password: `wrongpassword`

**Expected Output:**
- Error message: "The provided credentials are incorrect."
- Password field dikosongkan
- Form tetap bisa digunakan

### 2. Email Tidak Ada
**Input:**
- Email: `nonexistent@example.com`
- Password: `password`

**Expected Output:**
- Error message: "The provided credentials are incorrect."
- Password field dikosongkan
- Form tetap bisa digunakan

### 3. Field Kosong
**Input:**
- Email: (kosong)
- Password: (kosong)

**Expected Output:**
- Error message: "Email address is required." (prioritas pertama)
- Form validation mencegah submit

### 4. Email Kosong
**Input:**
- Email: (kosong)
- Password: `password`

**Expected Output:**
- Error message: "Email address is required."
- Client-side validation aktif

### 5. Password Kosong
**Input:**
- Email: `test@example.com`
- Password: (kosong)

**Expected Output:**
- Error message: "Password is required."
- Client-side validation aktif

## Manual Testing Steps

1. **Buka aplikasi** di `http://localhost:3002`
2. **Navigate** ke login page
3. **Test setiap case** di atas
4. **Verify** bahwa:
   - Error message muncul dengan jelas
   - Password field dikosongkan setelah error
   - Form tetap responsif
   - Error message hilang saat user mulai mengetik

## API Response Examples

### Wrong Password Response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["The provided credentials are incorrect."]
  },
  "timestamp": "2026-01-21T19:24:20.680337Z"
}
```

### Empty Fields Response
```json
{
  "success": false,
  "message": "The given data was invalid.",
  "errors": {
    "email": ["Email address is required."],
    "password": ["Password is required."]
  },
  "timestamp": "2026-01-21T19:25:00.593554Z"
}
```

## Error Handling Flow

1. **User submits form** dengan data invalid
2. **API Service** mengirim request ke backend
3. **Backend** mengembalikan 422 dengan error details
4. **API Service** mengekstrak error message dari response
5. **Auth Store** menerima error dan menyimpannya
6. **LoginForm** menangkap error dan menampilkan pesan
7. **User** melihat error message yang jelas dan actionable

## Security Considerations

- ✅ Password field dikosongkan setelah error
- ✅ Error messages tidak leak informasi sistem
- ✅ Same error message untuk email tidak ada dan password salah
- ✅ Rate limiting diterapkan untuk mencegah brute force
- ✅ Client-side validation untuk UX yang lebih baik

---

**Status**: ✅ Error handling sudah diperbaiki dan siap untuk testing