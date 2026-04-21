# Toko Online MVP - Laravel

Aplikasi toko online dengan sistem multi-user (Pembeli, Reseller, Agent, Super Admin) menggunakan Laravel dan MySQL.

## Tech Stack

- **Backend**: Laravel 12
- **Database**: MySQL (rasagroup)
- **Frontend Admin**: AdminLTE 2
- **Frontend Pembeli**: Bootstrap 5
- **DataTables**: Yajra DataTables (server-side)

## Fitur

### Frontend Pembeli (Bootstrap 5)
- ✅ Katalog produk dengan search & filter
- ✅ Detail produk
- ✅ Keranjang belanja (guest & auth)
- ✅ Checkout & pesanan
- ✅ Dashboard pembeli
- ✅ History pesanan
- ✅ Profil & edit profil
- ✅ Ubah password

### Backend Admin (AdminLTE 2)
- ✅ Dashboard admin dengan statistik
- ✅ CRUD Produk (dengan upload gambar)
- ✅ CRUD Hub (dengan UUID)
- ✅ Manajemen User (DataTables server-side)
- ✅ Manajemen Pesanan (update status)

### Authentication
- ✅ Registrasi pembeli
- ✅ Login pembeli & admin
- ✅ Role-based access control
- ✅ Merge cart saat login

## Instalasi

1. **Clone repository**
   ```bash
   cd /Applications/MAMP/htdocs/mvp-abc
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Setup environment**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Konfigurasi database di `.env`**
   ```env
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=8889
   DB_DATABASE=rasagroup
   DB_USERNAME=root
   DB_PASSWORD=root
   ```

5. **Buat database**
   ```sql
   CREATE DATABASE rasagroup;
   ```

6. **Run migrations & seeder**
   ```bash
   php artisan migrate
   php artisan db:seed --class=UserSeeder
   ```

7. **Setup storage link**
   ```bash
   php artisan storage:link
   ```

8. **Build assets (jika diperlukan)**
   ```bash
   npm run build
   ```

9. **Jalankan server**
   ```bash
   php artisan serve
   ```

## User Default (dari Seeder)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | superadmin@example.com | password |
| Agent | agent@example.com | password |
| Reseller 1 | reseller1@example.com | password |
| Pembeli 1 | buyer1@example.com | password |

## Routes

### Public
- `/` - Home (redirect ke products)
- `/products` - Katalog produk
- `/products/{product}` - Detail produk
- `/cart` - Keranjang

### Buyer (Auth)
- `/buyer/dashboard` - Dashboard pembeli
- `/buyer/orders` - History pesanan
- `/buyer/profile` - Profil
- `/checkout` - Checkout

### Admin (Auth + Agent/Super Admin)
- `/admin/login` - Login admin
- `/admin/dashboard` - Dashboard admin
- `/admin/products` - CRUD Produk
- `/admin/hubs` - CRUD Hub
- `/admin/users` - Manajemen User
- `/admin/orders` - Manajemen Pesanan

## Struktur Database

- **users** - User dengan role: buyer, reseller, agent, super_admin
- **products** - Produk dengan gambar, harga, stok
- **hubs** - Hub/lokasi dengan UUID
- **carts** - Keranjang (user_id atau session_id)
- **orders** - Pesanan
- **order_items** - Item pesanan

## Dokumentasi

- `REQUIREMENT.md` - Dokumentasi requirement lengkap
- `CHECKLIST_PENGEMBANGAN.md` - Checklist pengembangan
- `ADMINLTE_SETUP.md` - Dokumentasi AdminLTE 2
- `ADMIN_LOGIN.md` - Dokumentasi login admin
- `FORM_HELPERS.md` - Dokumentasi form helpers
- `SUMMARY_MODUL.md` - Summary semua modul

## Progress

**95% Complete** - Semua modul utama sudah dibuat dan siap untuk testing.

## Catatan

- Frontend pembeli menggunakan Bootstrap 5
- Frontend admin menggunakan AdminLTE 2
- Semua modul sudah dibuat sesuai requirement
- Siap untuk testing dan deployment
