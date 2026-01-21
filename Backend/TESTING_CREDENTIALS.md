# Testing Credentials - SAKTI Mini

## Default Test User

Untuk testing aplikasi, gunakan credentials berikut:

### Login Credentials
- **Email**: `test@example.com`
- **Password**: `password`

### User Information
- **Name**: Test User
- **Email Verified**: Yes
- **Created**: Auto-generated

## Creating Additional Test Users

Jika perlu membuat user test tambahan, gunakan command berikut:

```bash
# Masuk ke direktori Backend
cd Backend

# Buat user baru via tinker
php artisan tinker --execute="
\App\Models\User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'email_verified_at' => now()
]);
echo 'User created successfully';
"
```

## Reset Test User Password

Jika password test user berubah, reset dengan command:

```bash
cd Backend
php artisan tinker --execute="
\$user = \App\Models\User::where('email', 'test@example.com')->first();
if (\$user) {
    \$user->password = \Illuminate\Support\Facades\Hash::make('password');
    \$user->save();
    echo 'Password reset to: password';
} else {
    echo 'User not found';
}
"
```

## API Testing

### Manual API Testing dengan curl

```bash
# Test Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{"email":"test@example.com","password":"password"}'

# Test Register
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -H "Accept: application/json" \
  -d '{
    "name":"New User",
    "email":"newuser@example.com",
    "password":"password",
    "password_confirmation":"password"
  }'
```

### Frontend Testing

1. Buka browser ke `http://localhost:3002`
2. Klik "Create new account" atau gunakan login form
3. Gunakan credentials di atas untuk login

## Database Reset

Untuk reset database testing:

```bash
cd Backend
php artisan migrate:fresh
php artisan tinker --execute="
\App\Models\User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => \Illuminate\Support\Facades\Hash::make('password'),
    'email_verified_at' => now()
]);
echo 'Test user created';
"
```

---

**Note**: Credentials ini hanya untuk development/testing. Jangan gunakan di production!