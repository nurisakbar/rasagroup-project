# Admin Login - Dokumentasi

## Fitur Login Admin

Login admin sudah dibuat dengan menggunakan AdminLTE 2 sebagai template.

## File yang Dibuat

### 1. Controller
- **Location**: `/app/Http/Controllers/Admin/Auth/AdminLoginController.php`
- **Fungsi**:
  - `create()`: Menampilkan halaman login admin
  - `store()`: Handle proses login admin dengan validasi role 'agent'

### 2. Middleware
- **Location**: `/app/Http/Middleware/EnsureUserIsAgent.php`
- **Fungsi**: Memastikan user yang mengakses route admin memiliki role 'agent'
- **Alias**: `agent` (terdaftar di `bootstrap/app.php`)

### 3. View
- **Location**: `/resources/views/admin/auth/login.blade.php`
- **Template**: AdminLTE 2 login page
- **Fitur**:
  - Form login dengan email dan password
  - Remember me checkbox
  - Error handling
  - Link ke login pembeli

### 4. Dashboard Admin
- **Location**: `/resources/views/admin/dashboard.blade.php`
- **Template**: AdminLTE 2 dashboard dengan statistik box

## Routes

### Admin Login (Guest)
- **GET** `/admin/login` - Halaman login admin
- **POST** `/admin/login` - Proses login admin
- **Route Name**: `admin.login`

### Admin Dashboard (Protected)
- **GET** `/admin/dashboard` - Dashboard admin
- **Route Name**: `admin.dashboard`
- **Middleware**: `auth`, `agent`

## Database

### Migration Users
Field yang ditambahkan:
- `phone` (nullable) - Nomor telepon user
- `role` (enum: 'buyer', 'agent') - Role user, default 'buyer'

### User Model
Field `role` dan `phone` sudah ditambahkan ke `$fillable`.

## Cara Penggunaan

### 1. Membuat User Admin

Untuk membuat user dengan role agent, bisa menggunakan tinker atau seeder:

```php
// Via Tinker
php artisan tinker

User::create([
    'name' => 'Admin User',
    'email' => 'admin@example.com',
    'password' => bcrypt('password'),
    'role' => 'agent',
    'phone' => '081234567890'
]);
```

### 2. Login sebagai Admin

1. Buka `/admin/login`
2. Masukkan email dan password user dengan role 'agent'
3. Jika berhasil, akan di-redirect ke `/admin/dashboard`
4. Jika user bukan agent, akan muncul error "Anda tidak memiliki akses sebagai admin"

### 3. Redirect Berdasarkan Role

Sistem sudah dikonfigurasi untuk redirect otomatis berdasarkan role:
- **Buyer**: Login via `/login` → redirect ke `/dashboard` (pembeli)
- **Agent**: Login via `/admin/login` → redirect ke `/admin/dashboard` (admin)

Jika buyer login via `/admin/login`, akan muncul error.
Jika agent login via `/login`, akan di-redirect ke `/admin/dashboard`.

## Middleware Protection

Route admin dilindungi dengan middleware `agent`:

```php
Route::middleware(['auth', 'agent'])->group(function () {
    // Admin routes
});
```

Jika user yang bukan agent mencoba akses, akan mendapat error 403.

## Testing

Untuk test login admin:

1. **Buat user agent** (via tinker atau seeder)
2. **Akses** `/admin/login`
3. **Login** dengan credentials user agent
4. **Verifikasi** redirect ke `/admin/dashboard`
5. **Test** dengan user buyer - harus muncul error

## Catatan

- Login admin terpisah dari login pembeli
- User dengan role 'agent' bisa login via `/admin/login`
- User dengan role 'buyer' tidak bisa login via `/admin/login`
- Dashboard admin menggunakan template AdminLTE 2
- Sidebar menu sudah dikonfigurasi di `layouts/admin.blade.php`









