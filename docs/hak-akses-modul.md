# Hak akses modul panel admin

Akses ke modul panel admin (`/admin/*`) dikelola dinamis lewat menu **Hak Akses** (hanya `super_admin`).
Role pengguna tetap memakai kolom `users.role`; yang dinamis adalah modul apa saja yang boleh diakses tiap role
dan aksinya: **lihat, tambah, ubah, hapus**.

## Cara kerja

- **Modul** (`admin_modules`): nama, grup menu, ikon, rute menu, dan `route_patterns` (pola nama rute, mis. `admin.orders.*`).
- **Izin** (`admin_role_permissions`): per role per modul, empat aksi. Tambah/ubah/hapus mengharuskan lihat.
- **Penegakan**: middleware `admin.module` (`EnsureAdminModuleAccess`) memetakan nama rute ke modul (pola paling spesifik
  menang) dan aksi, lalu mengecek izin role. Aksi dikenali dari nama rute dan metode HTTP:
  GET = lihat, `create`/`store`/`import`/`add-*` = tambah, PUT/PATCH/POST lain = ubah, DELETE/`destroy`/`remove` = hapus.
- **Gagal tertutup**: rute `admin.*` yang tidak terpetakan ke modul ditolak (403) untuk non-`super_admin`.
  Daftar putih: `admin.dashboard`, `admin.logout`, `admin.login`, `admin.profile.*` (lihat `AdminAccess::WHITELIST`).
- `super_admin` selalu penuh. Modul `access_control` (Hak Akses) hanya untuk `super_admin` dan tidak bisa dinonaktifkan.
- Menu samping dibangun dari modul di database (`AdminAccess::menuFor`). Di Blade tersedia `@adminCan('modul', 'aksi')`.
- Data diambil dari cache (`admin_access.v1`) dan dibersihkan otomatis saat pengaturan diubah.

## Menambah modul baru

1. Buat rute dengan nama berawalan `admin.` (mis. `admin.laporan-stok.index`).
2. Daftarkan modulnya, salah satunya:
   - lewat UI: **Hak Akses → Kelola Modul → Tambah Modul** (isi pola rute `admin.laporan-stok.*`), lalu beri izin ke role di matriks; atau
   - untuk instalasi baru: tambahkan ke `App\Support\AdminAccess\DefaultModules::definitions()` beserta role bawaannya.
3. Periksa pemetaan: `php artisan admin-access:check-routes` (daftar rute admin yang belum terpetakan).

## Role yang bisa masuk panel

Daftar tunggal di `User::ADMIN_PANEL_ROLES` (dipakai halaman login dan middleware `agent`).

## Catatan operasional

- `ADMIN_RBAC_ENFORCE=false` di `.env` mematikan penegakan di server (hanya menu yang disaring). Tidak disarankan.
- Migrasi `2026_11_26_100000_create_admin_access_tables` mengisi modul dan izin awal yang **meniru akses menu sebelumnya**.
  Tombol "Kembalikan ke bawaan" di matriks mengembalikan satu role ke pengaturan awal itu.
- Aksi pada rute yang memakai pengikatan model (mis. `DELETE /admin/xxx/{id}`) untuk id yang tidak ada mengembalikan 404
  lebih dulu (pengikatan model berjalan sebelum middleware); untuk id yang ada, izin tetap ditegakkan.
