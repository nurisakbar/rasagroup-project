# Dokumentasi Requirement - Toko Online MVP

## 1. Deskripsi Proyek

Aplikasi toko online dengan sistem multi-user yang terdiri dari **Pembeli** (end user) dan **Agen**. Aplikasi ini dibangun menggunakan Laravel dan MySQL sebagai database.

## 2. User Roles

### 2.1 Pembeli (Buyer/Customer)
- End user yang melakukan pembelian produk
- Dapat melakukan registrasi dan login
- Dapat melihat katalog produk
- Dapat melakukan transaksi pembelian

### 2.2 Agen (Agent)
- User yang mengelola produk dan transaksi
- Memiliki akses ke dashboard admin/agen
- Dapat mengelola produk, stok, dan pesanan

## 3. Fitur MVP (Minimum Viable Product)

### 3.1 Fitur Autentikasi & Registrasi
- **Registrasi Pembeli**
  - Form registrasi dengan validasi
  - Field: nama, email, password, konfirmasi password, nomor telepon (opsional)
  - Verifikasi email (opsional untuk MVP)
  - Hash password menggunakan bcrypt

- **Login Pembeli**
  - Form login dengan email/username dan password
  - Remember me functionality
  - Session management
  - Logout functionality

- **Registrasi/Login Agen** (untuk development)
  - Sistem login terpisah untuk agen
  - Atau menggunakan middleware untuk membedakan role

### 3.2 Fitur Katalog Produk
- **Halaman Produk**
  - List semua produk yang tersedia
  - Pagination untuk produk
  - Filter dan search produk (opsional untuk MVP)
  - Detail produk (nama, deskripsi, harga, stok, gambar)

### 3.3 Fitur Keranjang Belanja
- **Keranjang (Cart)**
  - Tambah produk ke keranjang
  - Update quantity produk di keranjang
  - Hapus produk dari keranjang
  - Hitung total harga
  - Session-based cart (untuk user belum login)
  - Database cart (untuk user yang sudah login)

### 3.4 Fitur Checkout & Transaksi
- **Checkout**
  - Form alamat pengiriman
  - Pilih metode pembayaran (untuk MVP: manual transfer)
  - Review pesanan sebelum checkout
  - Generate invoice/order number
  - Simpan data transaksi ke database

- **Status Pesanan**
  - Pending (menunggu pembayaran)
  - Paid (sudah dibayar)
  - Processing (sedang diproses)
  - Shipped (sedang dikirim)
  - Delivered (sudah diterima)
  - Cancelled (dibatalkan)

### 3.5 Fitur Dashboard Pembeli
- **Profil User**
  - Lihat dan edit profil
  - Ubah password
  - History pesanan
  - Detail pesanan

### 3.6 Fitur Dashboard Agen (Minimal)
- **Manajemen Produk**
  - CRUD produk (Create, Read, Update, Delete)
  - Upload gambar produk
  - Kelola stok produk
  - Set harga produk

- **Manajemen Pesanan**
  - Lihat semua pesanan
  - Update status pesanan
  - Lihat detail pesanan

## 4. Tech Stack

- **Backend Framework**: Laravel (versi terbaru)
- **Database**: MySQL
- **Frontend Backend/Admin**: Template AdminLTE 2 yang diintegrasikan dengan Blade
- **Frontend Pengunjung**: Template e-commerce EShopper yang diintegrasikan dengan Blade
- **Authentication**: Laravel Breeze atau Laravel Sanctum
- **File Storage**: Local storage (untuk MVP), dapat upgrade ke S3/cloud storage nanti

### 4.1 Template UI

- **AdminLTE 2 (Backend/Agen)**:
  - Digunakan untuk tampilan dashboard agen dan manajemen produk/pesanan
  - Versi: AdminLTE 2.4
  - Integrasi melalui Blade layout (`layouts/admin.blade.php`)
  - Menggunakan komponen siap pakai untuk sidebar, navbar, tabel, dan form
  - Dependencies: Bootstrap 3.4.1, jQuery 3.6.0, Font Awesome 4.7.0

- **EShopper (Frontend Pengunjung/Pembeli)**:
  - Digunakan untuk halaman publik dan area pembeli (home, katalog, detail produk, cart, checkout, profil)
  - Integrasi melalui Blade layout (`layouts/shop.blade.php` misalnya)
  - Penyesuaian warna/logo sesuai brand toko online

## 5. Struktur Database (Konseptual)

### 5.1 Tabel Users
```
- id (primary key)
- name
- email (unique)
- email_verified_at (nullable)
- password
- phone (nullable)
- role (enum: 'buyer', 'agent')
- remember_token
- created_at
- updated_at
```

### 5.2 Tabel Products
```
- id (primary key)
- name
- description (text)
- price (decimal)
- stock (integer)
- image (string, path)
- status (enum: 'active', 'inactive')
- created_by (foreign key -> users.id)
- created_at
- updated_at
```

### 5.3 Tabel Carts
```
- id (primary key)
- user_id (foreign key -> users.id, nullable untuk guest)
- session_id (string, untuk guest cart)
- product_id (foreign key -> products.id)
- quantity (integer)
- created_at
- updated_at
```

### 5.4 Tabel Orders
```
- id (primary key)
- order_number (string, unique)
- user_id (foreign key -> users.id)
- total_amount (decimal)
- shipping_address (text)
- payment_method (string)
- payment_status (enum: 'pending', 'paid', 'failed')
- order_status (enum: 'pending', 'processing', 'shipped', 'delivered', 'cancelled')
- notes (text, nullable)
- created_at
- updated_at
```

### 5.5 Tabel Order Items
```
- id (primary key)
- order_id (foreign key -> orders.id)
- product_id (foreign key -> products.id)
- quantity (integer)
- price (decimal, snapshot harga saat order)
- subtotal (decimal)
- created_at
- updated_at
```

## 6. Flow Aplikasi

### 6.1 Flow Registrasi & Login Pembeli
1. User mengakses halaman registrasi
2. User mengisi form registrasi
3. Sistem validasi data
4. Simpan data user dengan role 'buyer'
5. Redirect ke halaman login
6. User login dengan email dan password
7. Sistem cek credentials
8. Set session dan redirect ke dashboard/home

### 6.2 Flow Pembelian Produk
1. Pembeli melihat katalog produk
2. Pembeli memilih produk dan tambah ke keranjang
3. Pembeli melihat keranjang
4. Pembeli klik checkout
5. Pembeli isi alamat pengiriman
6. Pembeli review pesanan
7. Pembeli konfirmasi pesanan
8. Sistem generate order number
9. Pesanan masuk dengan status 'pending'
10. Agen update status pesanan setelah pembayaran

## 7. Halaman yang Dibutuhkan

### 7.1 Halaman Publik
- Home/Landing page
- Katalog produk
- Detail produk
- Login
- Registrasi

### 7.2 Halaman Pembeli (Setelah Login)
- Dashboard pembeli
- Keranjang belanja
- Checkout
- History pesanan
- Detail pesanan
- Profil pembeli
- Edit profil

### 7.3 Halaman Agen
- Login agen
- Dashboard agen
- Manajemen produk (list, create, edit, delete)
- Manajemen pesanan (list, detail, update status)

## 8. Validasi & Keamanan

- Validasi form (required fields, email format, password strength)
- CSRF protection (Laravel default)
- Password hashing (bcrypt)
- SQL injection prevention (Laravel Eloquent)
- XSS protection
- Role-based access control (middleware)
- Rate limiting untuk login

## 9. Prioritas Pengembangan

### Phase 1 (MVP Core)
1. Setup Laravel project
2. Database migration dan seeder
3. Authentication system (register & login pembeli)
4. CRUD produk (untuk agen)
5. Katalog produk (untuk pembeli)
6. Keranjang belanja
7. Checkout dan order system
8. Dashboard pembeli (history pesanan)

### Phase 2 (Enhancement)
- Dashboard agen lengkap
- Upload gambar produk
- Search dan filter produk
- Email notification
- Payment gateway integration
- Report dan analytics

## 10. Catatan Pengembangan

- Gunakan Laravel migrations untuk database schema
- Gunakan Laravel seeder untuk data dummy/testing
- Implementasi middleware untuk role-based access
- Gunakan Laravel validation untuk form validation
- Implementasi soft delete untuk produk (opsional)
- Gunakan Laravel storage untuk file upload
- Implementasi pagination untuk list produk dan pesanan

## 11. Testing Requirements

- Unit test untuk model dan controller (opsional untuk MVP)
- Manual testing untuk semua flow
- Test case untuk:
  - Registrasi dan login
  - CRUD produk
  - Add to cart
  - Checkout
  - Update status pesanan

---

**Catatan**: Dokumen ini dapat diupdate seiring perkembangan pengembangan aplikasi.

