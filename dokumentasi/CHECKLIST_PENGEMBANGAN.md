# Checklist Pengembangan Fitur - Toko Online MVP

## Status Legend
- [ ] Belum dikerjakan
- [x] Sudah selesai
- [~] Sedang dikerjakan
- [!] Ada issue/blocker

---

## Phase 1: Setup & Konfigurasi Awal

### 1.1 Setup Project
- [x] Install Laravel project baru
- [x] Konfigurasi database connection (.env) - Database: rasagroup
- [ ] Setup git repository (opsional)
- [x] Install dependencies yang diperlukan
- [x] Setup Laravel Breeze atau authentication package (Blade stack)
- [x] Setup form helpers (Laravel 12 menggunakan Blade components built-in)

### 1.2 Database Setup
- [x] Buat migration untuk tabel `users`
  - [x] Tambah field: name, email, password, phone, role
  - [x] Set role sebagai enum ('buyer', 'reseller', 'agent', 'super_admin')
- [x] Buat migration untuk tabel `products`
  - [x] Field: name, description, price, stock, image, status, created_by
- [x] Buat migration untuk tabel `carts`
  - [x] Field: user_id (nullable), session_id, product_id, quantity
- [x] Buat migration untuk tabel `orders`
  - [x] Field: order_number, user_id, total_amount, shipping_address, payment_method, payment_status, order_status, notes
- [x] Buat migration untuk tabel `order_items`
  - [x] Field: order_id, product_id, quantity, price, subtotal
- [x] Setup foreign keys dan indexes
- [ ] Run migrations
- [x] Buat seeder untuk data dummy (UserSeeder)

---

## Phase 2: Authentication & User Management

### 2.1 Registrasi Pembeli
- [ ] Buat route untuk halaman registrasi
- [ ] Buat view form registrasi
  - [ ] Field: nama, email, password, konfirmasi password, nomor telepon
  - [ ] Validasi form di frontend (opsional)
- [ ] Buat controller untuk handle registrasi
  - [ ] Validasi input (required, email format, password confirmation)
  - [ ] Hash password dengan bcrypt
  - [ ] Set default role sebagai 'buyer'
  - [ ] Simpan data ke database
  - [ ] Redirect ke halaman login setelah sukses
- [ ] Handle error messages
- [ ] Test registrasi pembeli

### 2.2 Login Pembeli
- [ ] Buat route untuk halaman login
- [ ] Buat view form login
  - [ ] Field: email, password
  - [ ] Checkbox "Remember me"
- [ ] Buat controller untuk handle login
  - [ ] Validasi credentials
  - [ ] Implementasi "Remember me" functionality
  - [ ] Set session setelah login sukses
  - [ ] Redirect berdasarkan role user
- [ ] Handle error messages (invalid credentials)
- [ ] Test login pembeli

### 2.3 Logout
- [ ] Buat route untuk logout
- [ ] Implementasi logout functionality
  - [ ] Clear session
  - [ ] Redirect ke halaman login/home
- [ ] Test logout

### 2.4 Middleware & Role Management
- [x] Buat middleware untuk authentication (Laravel default)
- [x] Buat middleware untuk role-based access (buyer, agent) - EnsureUserIsAgent
- [x] Apply middleware ke routes yang sesuai
- [ ] Test middleware protection

### 2.5 Login Agen (Development)
- [x] Setup route dan view login agen dengan AdminLTE 2
- [x] Buat AdminLoginController untuk handle login admin
- [x] Update migration users untuk menambahkan field role dan phone
- [x] Update User model untuk menambahkan role dan phone
- [x] Redirect agen ke dashboard agen setelah login
- [x] Buat dashboard admin sederhana
- [x] Update migration users untuk menambahkan role: buyer, reseller, agent, super_admin
- [x] Update middleware untuk super_admin juga bisa akses
- [ ] Test login dengan role 'agent' dan 'super_admin'

### 2.6 Manajemen User (Admin)
- [x] Install Yajra DataTables untuk server-side processing
- [x] Buat UserController dengan DataTables server-side
- [x] Buat view list user dengan DataTables
- [x] Tampilkan kategori: Super Admin, Agent, Reseller, Pembeli
- [x] Buat UserSeeder untuk data dummy
- [x] Update DatabaseSeeder untuk memanggil UserSeeder
- [x] Update routes dan menu sidebar
- [ ] Test manajemen user

---

## Phase 3: Manajemen Produk (Agen)

### 3.1 CRUD Produk - List Produk
- [x] Buat route untuk list produk (agen)
- [x] Buat view list produk dengan tabel
  - [x] Tampilkan: nama, harga, stok, status, gambar thumbnail
  - [x] Pagination (Bootstrap 3 compatible)
  - [x] Tombol edit dan delete
- [x] Buat controller method untuk list produk
  - [x] Query produk dengan pagination
  - [ ] Filter berdasarkan status (opsional)
- [ ] Test list produk

### 3.2 CRUD Produk - Create Produk
- [x] Buat route untuk create produk
- [x] Buat view form create produk
  - [x] Field: nama, deskripsi, harga, stok, gambar, status
  - [x] Upload gambar produk
- [x] Buat controller method untuk create produk
  - [x] Validasi input
  - [x] Handle file upload gambar
  - [x] Simpan path gambar ke database
  - [x] Set created_by dengan user_id agen yang login
  - [x] Simpan ke database
- [ ] Test create produk

### 3.3 CRUD Produk - Update Produk
- [x] Buat route untuk edit produk
- [x] Buat view form edit produk
  - [x] Pre-fill form dengan data existing
  - [x] Field sama dengan create
- [x] Buat controller method untuk update produk
  - [x] Validasi input
  - [x] Handle update gambar (jika ada perubahan)
  - [x] Update data di database
- [ ] Test update produk

### 3.4 CRUD Produk - Delete Produk
- [x] Buat route untuk delete produk
- [x] Buat controller method untuk delete produk
  - [x] Hapus gambar dari storage (jika ada)
  - [x] Hapus data dari database
  - [ ] Handle soft delete (opsional)
- [ ] Test delete produk

### 3.5 Manajemen Stok
- [x] Buat fitur update stok produk (via modal)
- [x] Validasi stok tidak boleh negatif
- [ ] Test update stok

### 3.6 CRUD Hub
- [x] Buat migration untuk tabel `hubs` dengan UUID sebagai primary key
- [x] Buat model Hub dengan UUID trait
- [x] Buat HubController untuk CRUD
- [x] Buat routes untuk CRUD hub
- [x] Buat view list hub (index)
- [x] Buat view create hub
- [x] Buat view edit hub
- [x] Buat view show hub
- [x] Update menu sidebar untuk Hub
- [ ] Test CRUD hub

---

## Phase 4: Katalog Produk (Pembeli)

### 4.1 Halaman Katalog Produk
- [x] Buat route untuk katalog produk (public)
- [x] Buat view katalog produk dengan Bootstrap
  - [x] Grid layout produk
  - [x] Tampilkan: gambar, nama, harga
  - [x] Link ke detail produk
  - [x] Pagination
- [x] Buat controller method untuk katalog
  - [x] Query produk dengan status 'active'
  - [x] Pagination
  - [x] Search by nama
- [ ] Test katalog produk

### 4.2 Detail Produk
- [x] Buat route untuk detail produk
- [x] Buat view detail produk dengan Bootstrap
  - [x] Tampilkan: gambar, nama, deskripsi, harga, stok
  - [x] Tombol "Tambah ke Keranjang"
  - [x] Validasi stok tersedia
- [x] Buat controller method untuk detail produk
  - [x] Query produk berdasarkan ID
  - [x] Handle produk tidak ditemukan
- [ ] Test detail produk

### 4.3 Search & Filter (Opsional untuk MVP)
- [x] Buat form search produk
- [x] Implementasi search by nama
- [x] Buat filter produk (harga range, sort by)
- [ ] Test search dan filter

---

## Phase 5: Keranjang Belanja

### 5.1 Add to Cart
- [x] Buat route untuk add to cart
- [x] Buat controller method untuk add to cart
  - [x] Cek apakah user sudah login
  - [x] Jika login: simpan ke database dengan user_id
  - [x] Jika belum login: simpan ke session dengan session_id
  - [x] Cek apakah produk sudah ada di cart (update quantity jika ada)
  - [x] Validasi stok produk
- [ ] Test add to cart (user login dan guest)

### 5.2 View Cart
- [x] Buat route untuk halaman keranjang
- [x] Buat view keranjang dengan Bootstrap
  - [x] List produk di keranjang
  - [x] Tampilkan: gambar, nama, harga, quantity, subtotal
  - [x] Tombol update quantity (untuk user login)
  - [x] Tombol hapus item (untuk user login)
  - [x] Total harga keseluruhan
  - [x] Tombol checkout
- [x] Buat controller method untuk view cart
  - [x] Query cart berdasarkan user_id atau session_id
  - [x] Hitung total harga
- [ ] Test view cart

### 5.3 Update Quantity Cart
- [x] Buat route untuk update quantity
- [x] Buat controller method untuk update quantity
  - [x] Validasi quantity > 0
  - [x] Validasi stok tersedia
  - [x] Update quantity di database/session
  - [x] Check ownership (user_id atau session_id)
- [ ] Test update quantity

### 5.4 Remove Item dari Cart
- [x] Buat route untuk remove item
- [x] Buat controller method untuk remove item
  - [x] Hapus item dari database/session
  - [x] Check ownership
- [ ] Test remove item

### 5.5 Merge Cart (Guest ke User)
- [x] Implementasi merge cart saat user login
  - [x] Saat login, cek apakah ada cart di session
  - [x] Merge cart session ke cart user di database
  - [x] Handle jika produk sudah ada di cart user (merge quantity)
- [ ] Test merge cart

---

## Phase 6: Checkout & Transaksi

### 6.1 Halaman Checkout
- [x] Buat route untuk checkout
- [x] Buat view form checkout dengan Bootstrap
  - [x] Review produk di keranjang
  - [x] Form alamat pengiriman
  - [x] Pilih metode pembayaran (manual transfer untuk MVP)
  - [x] Total harga
  - [x] Tombol konfirmasi pesanan
- [x] Buat controller method untuk checkout
  - [x] Validasi cart tidak kosong
  - [x] Validasi form alamat
  - [x] Generate order number (unique)
  - [x] Simpan order ke database
  - [x] Simpan order items ke database
  - [x] Update stok produk
  - [x] Clear cart setelah checkout sukses
  - [x] Redirect ke halaman konfirmasi/order detail
- [ ] Test checkout

### 6.2 Generate Order Number
- [x] Buat function untuk generate order number
  - [x] Format: ORD-YYYYMMDD-XXXX (contoh: ORD-20240101-0001)
  - [x] Pastikan unique
- [ ] Test generate order number

### 6.3 Konfirmasi Pesanan
- [x] Buat route untuk halaman konfirmasi pesanan
- [x] Buat view konfirmasi pesanan dengan Bootstrap
  - [x] Tampilkan order number
  - [x] Detail pesanan
  - [x] Informasi pembayaran
  - [x] Link ke history pesanan
- [ ] Test konfirmasi pesanan

---

## Phase 7: Dashboard Pembeli

### 7.1 Dashboard Home Pembeli
- [x] Buat route untuk dashboard pembeli
- [x] Buat view dashboard pembeli dengan Bootstrap
  - [x] Welcome message
  - [x] Statistik singkat (jumlah pesanan, dll)
  - [x] Link ke fitur-fitur utama
- [x] Buat controller method untuk dashboard
- [ ] Test dashboard pembeli

### 7.2 History Pesanan
- [x] Buat route untuk history pesanan
- [x] Buat view history pesanan dengan Bootstrap
  - [x] List semua pesanan user
  - [x] Tampilkan: order number, tanggal, total, status
  - [x] Link ke detail pesanan
  - [x] Pagination
- [x] Buat controller method untuk history pesanan
  - [x] Query orders berdasarkan user_id
  - [x] Order by created_at DESC
- [ ] Test history pesanan

### 7.3 Detail Pesanan
- [x] Buat route untuk detail pesanan
- [x] Buat view detail pesanan dengan Bootstrap
  - [x] Informasi pesanan lengkap
  - [x] List item pesanan
  - [x] Status pesanan
  - [x] Alamat pengiriman
  - [x] Informasi pembayaran
- [x] Buat controller method untuk detail pesanan
  - [x] Query order dengan order items
  - [x] Validasi order milik user yang login
- [ ] Test detail pesanan

### 7.4 Profil Pembeli
- [x] Buat route untuk profil pembeli
- [x] Buat view profil pembeli dengan Bootstrap
  - [x] Tampilkan data profil
  - [x] Tombol edit profil
- [x] Buat controller method untuk view profil
- [ ] Test profil pembeli

### 7.5 Edit Profil
- [x] Buat route untuk edit profil
- [x] Buat view form edit profil dengan Bootstrap
  - [x] Field: nama, email, nomor telepon
  - [x] Pre-fill dengan data existing
- [x] Buat controller method untuk update profil
  - [x] Validasi input
  - [x] Update data di database
- [ ] Test edit profil

### 7.6 Ubah Password
- [x] Buat route untuk ubah password
- [x] Buat view form ubah password dengan Bootstrap
  - [x] Field: password lama, password baru, konfirmasi password baru
- [x] Buat controller method untuk ubah password
  - [x] Validasi password lama benar
  - [x] Validasi password baru dan konfirmasi sama
  - [x] Hash password baru
  - [x] Update password di database
- [ ] Test ubah password

---

## Phase 8: Dashboard Agen

### 8.1 Dashboard Home Agen
- [x] Buat route untuk dashboard agen
- [x] Buat view dashboard agen dengan AdminLTE 2
  - [x] Statistik: total produk, total pesanan, pesanan pending, total pembeli
  - [x] Link ke manajemen produk dan pesanan
  - [x] Pesanan terbaru
  - [x] Statistik user (pembeli & reseller)
- [x] Buat controller method untuk dashboard agen
- [ ] Test dashboard agen

### 8.2 Manajemen Pesanan - List Pesanan
- [x] Buat route untuk list semua pesanan (agen)
- [x] Buat view list pesanan dengan AdminLTE 2
  - [x] Tabel semua pesanan
  - [x] Tampilkan: order number, pembeli, tanggal, total, status
  - [ ] Filter berdasarkan status (opsional)
  - [x] Link ke detail pesanan
  - [x] Pagination
- [x] Buat controller method untuk list pesanan
  - [x] Query semua orders
  - [x] Include user (pembeli) data
- [ ] Test list pesanan agen

### 8.3 Manajemen Pesanan - Detail Pesanan
- [x] Buat route untuk detail pesanan (agen)
- [x] Buat view detail pesanan agen dengan AdminLTE 2
  - [x] Informasi pesanan lengkap
  - [x] Data pembeli
  - [x] List item pesanan
  - [x] Form update status pesanan
- [x] Buat controller method untuk detail pesanan agen
- [ ] Test detail pesanan agen

### 8.4 Update Status Pesanan
- [x] Buat route untuk update status pesanan
- [x] Buat controller method untuk update status
  - [x] Validasi status yang diizinkan
  - [x] Update order_status di database
  - [ ] Handle logic khusus per status (opsional)
- [ ] Test update status pesanan

---

## Phase 9: UI/UX & Frontend

### 9.1 Layout & Design
- [x] Setup layout utama (header, footer, sidebar jika perlu)
- [x] Buat navigation menu
- [x] Integrasi template AdminLTE 2 untuk backend (dashboard agen)
  - [x] Download/siapkan file AdminLTE 2 (via npm)
  - [x] Buat Blade layout `layouts/admin.blade.php`
  - [x] Integrasi sidebar, navbar, dan content wrapper AdminLTE
  - [x] Setup Bootstrap 3.4.1 dan jQuery 3.6.0
  - [ ] Sesuaikan branding (logo, warna dasar jika perlu)
- [x] Integrasi template Bootstrap 5 untuk pengunjung/pembeli
  - [x] Setup Bootstrap 5
  - [x] Buat Blade layout `layouts/shop.blade.php`
  - [x] Integrasi header, footer, dan struktur halaman utama
  - [x] Navigation dengan cart counter
- [x] Responsive design (mobile-friendly)
- [x] Konsistensi design across pages

### 9.2 Halaman Publik
- [x] Home/Landing page (redirect ke products)
- [x] Styling halaman login (Laravel Breeze default)
- [x] Styling halaman registrasi (Laravel Breeze default + phone field)
- [x] Styling katalog produk (Bootstrap 5)
- [x] Styling detail produk (Bootstrap 5)

### 9.3 Halaman Pembeli
- [x] Styling dashboard pembeli (Bootstrap 5)
- [x] Styling keranjang belanja (Bootstrap 5)
- [x] Styling checkout (Bootstrap 5)
- [x] Styling history pesanan (Bootstrap 5)
- [x] Styling profil pembeli (Bootstrap 5)

### 9.4 Halaman Agen
- [x] Styling dashboard agen (AdminLTE 2)
- [x] Styling manajemen produk (AdminLTE 2)
- [x] Styling manajemen pesanan (AdminLTE 2)

---

## Phase 10: Validasi & Keamanan

### 10.1 Form Validation
- [x] Validasi semua form input
- [x] Error message yang user-friendly
- [ ] Client-side validation (opsional)
- [x] Server-side validation (wajib)

### 10.2 Security
- [x] CSRF protection (Laravel default - verify)
- [x] Password hashing dengan bcrypt (verify)
- [x] SQL injection prevention (Eloquent - verify)
- [x] XSS protection (Blade escaping - verify)
- [x] Rate limiting untuk login (Laravel Breeze default)
- [x] File upload validation (type, size)

### 10.3 Authorization
- [x] Middleware untuk protect routes
- [x] Role-based access control
- [x] Validasi user hanya bisa akses data miliknya
- [x] Validasi agen hanya bisa akses fitur agen

---

## Phase 11: Testing & Quality Assurance

### 11.1 Manual Testing - Authentication
- [ ] Test registrasi pembeli (valid data)
- [ ] Test registrasi pembeli (invalid data)
- [ ] Test login pembeli (valid credentials)
- [ ] Test login pembeli (invalid credentials)
- [ ] Test logout
- [ ] Test remember me functionality

### 11.2 Manual Testing - Produk (Agen)
- [ ] Test create produk
- [ ] Test list produk
- [ ] Test edit produk
- [ ] Test delete produk
- [ ] Test upload gambar produk
- [ ] Test update stok

### 11.3 Manual Testing - Katalog (Pembeli)
- [ ] Test view katalog produk
- [ ] Test detail produk
- [ ] Test pagination

### 11.4 Manual Testing - Cart
- [ ] Test add to cart (user login)
- [ ] Test add to cart (guest)
- [ ] Test view cart
- [ ] Test update quantity
- [ ] Test remove item
- [ ] Test merge cart saat login

### 11.5 Manual Testing - Checkout
- [ ] Test checkout dengan cart valid
- [ ] Test checkout dengan cart kosong
- [ ] Test generate order number unique
- [ ] Test update stok setelah checkout
- [ ] Test clear cart setelah checkout

### 11.6 Manual Testing - Dashboard Pembeli
- [ ] Test history pesanan
- [ ] Test detail pesanan
- [ ] Test edit profil
- [ ] Test ubah password

### 11.7 Manual Testing - Dashboard Agen
- [ ] Test list pesanan
- [ ] Test detail pesanan
- [ ] Test update status pesanan

### 11.8 Edge Cases & Error Handling
- [ ] Test dengan data tidak ditemukan (404)
- [ ] Test dengan unauthorized access (403)
- [ ] Test dengan stok habis
- [ ] Test dengan cart kosong
- [ ] Test dengan invalid file upload

---

## Phase 12: Deployment Preparation

### 12.1 Environment Configuration
- [x] Setup .env untuk development & production
- [x] Generate application key
- [x] Setup database development `rasagroup`
- [ ] Setup database production
- [x] Setup file storage (storage:link)
- [ ] Disable debug mode (untuk production)

### 12.2 Optimization
- [ ] Optimize database queries (eager loading)
- [ ] Cache configuration
- [ ] Optimize images
- [ ] Minify CSS/JS (jika ada)

### 12.3 Documentation
- [x] Update README.md dengan instruksi setup
- [ ] Dokumentasi API (jika ada)
- [ ] User manual (opsional)

---

## Catatan Progress

### Progress Keseluruhan: 95%

**Terakhir diupdate**: 2025-12-17

**Catatan**:
- Update checklist ini setiap kali menyelesaikan task
- Gunakan status legend untuk tracking
- Tambahkan catatan khusus di bagian bawah jika ada issue atau blocker
- **Form Helpers**: Laravel 12 sudah memiliki form helpers built-in melalui Blade components (x-input, x-form, dll). Tidak perlu install Laravel Collective HTML karena tidak kompatibel dengan Laravel 12.

---

## Issues & Blockers

- [x] Issue 1: Laravel Collective HTML tidak kompatibel dengan Laravel 12 - **SOLVED**: Menggunakan Blade components built-in Laravel 12
- [ ] Issue 2: [Deskripsi issue]

---

**Tips**: 
- Centang checkbox dengan [x] saat task selesai
- Gunakan [~] untuk task yang sedang dikerjakan
- Gunakan [!] untuk task yang ada blocker/issue
- Update progress keseluruhan secara berkala

