# Skenario Test E2E per Role (Browser)

Setiap role diuji dengan urutan yang sama: **daftar, verifikasi email, login, order**.
Role ditentukan oleh cara daftar.

| Role | Cara daftar | Sumber stok & alamat asal | Diskon MOQ |
|---|---|---|---|
| **Ecom** | Daftar biasa, tanpa kode | Gudang ecom terdekat | Tidak ada |
| **Outlet** | Daftar dengan **kode sales** | Hub outlet terdaftar | Strata outlet, perlu kode sales |
| **Subdist** | Daftar lewat link **kode distributor** `/register?distributor=DSTxxxxx` | Gudang **distributor** induk | Strata subdist (di atas 50 karton) |
| **Distributor** | Daftar sebagai ecom, ajukan di `/distributor/apply`, **disetujui admin** | Belanja ke gudang pusat distributor | Harga level distributor |

Status tiap skenario: `Belum` / `Lulus` / `Gagal` / `Blokir`.
Terakhir dijalankan 2026-11-24 di `rasa_e2e` (salinan dev), dengan Playwright Chromium headless.

## Lingkungan dan data uji

- Aplikasi `http://127.0.0.1:8010`, database `rasa_e2e` (salinan dev, lokal). Jangan pernah mengarah ke `rasa_prod_asli` atau `rasa_prod_fix`.
- Browser: Playwright Chromium. Email ke log (`storage/logs/laravel.log`), link verifikasi diambil dari sana.
- QAD, Jubelio, WMS, Zoho, dan WhatsApp dimatikan.
- Akun uji `e2e.<role>@example.test`, password `Password123!`. Admin: `admin@rasagroup.com`.
- Kode sales `MCR001`. Distributor uji `DST00048` (gudang aktif, alamat lengkap, stok terisi).
- Produk: grup Syrup/Sauce dan grup Powder/Pulp/Cocoa. Alamat uji: Jabodetabek dan luar Jabodetabek.

---

## 1. Ecom

| ID | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| EC1 | Daftar tanpa kode | `role=ecom`, `sales_code` kosong | Lulus |
| EC2 | Login sebelum verifikasi email | Ditolak dengan pesan verifikasi | Lulus |
| EC3 | Buka link verifikasi dari log, lalu login | Masuk. `/buyer/profile` terbuka | Lulus |
| EC4 | Tambah alamat, tambah produk ke keranjang | Hub keranjang = gudang ecom | Lulus |
| EC5 | Checkout, bayar Faspay | Order dibuat, tanpa diskon MOQ (20 karton tanpa kode sales pun tidak diskon), `fulfilled_by_distributor=0` | Lulus |
| EC6 | Pilihan pengiriman | Ada kurir API dan `kurir_toko`, tanpa `self_pickup` | Temuan (lihat T1) |

## 2. Outlet

| ID | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| OU1 | Daftar dengan kode sales `MCR001` | `role=outlet`, `sales_code=MCR001` | Lulus |
| OU2 | Verifikasi email lalu login | Masuk | Lulus |
| OU3 | Tambah produk ke keranjang | Hub = hub outlet terdaftar | Lulus (catatan T3) |
| OU4 | Checkout 3 karton Syrup | Diskon 4%. Pilihan kurir: `kurir_toko`, `self_pickup` | Lulus |
| OU5 | Batas strata Syrup: 2, 3, 5, 6, 10, 11, 20, 21, 33 karton | 0, 4, 4, 5, 5, 6, 6, 7, 7 persen | Lulus |
| OU6 | Batas strata Powder pada jumlah yang sama | 0, 2, 2, 4, 4, 5, 5, 6, 6 persen | Lulus |
| OU7 | Checkout tanpa kode sales | Tidak ada diskon MOQ | Lulus |

## 3. Subdist

| ID | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| SD1 | Buka `/register?distributor=DST00048` | Banner "mendaftar sebagai subdist melalui distributor", pilihan kode sales tidak ada | Lulus |
| SD2 | Daftar lewat link itu | `role=subdist`, `sales_code=DST00048` | Lulus |
| SD3 | Link palsu `DST99999`, atau distributor nonaktif | Peringatan tampil atau pendaftaran ditolak, tidak ada user baru | Lulus |
| SD4 | Kode sales dimanipulasi bersamaan dengan link distributor | Kode distributor yang dipakai | Lulus |
| SD5 | Verifikasi email lalu login | Masuk | Lulus |
| SD6 | Tambah produk ke keranjang | Hub = gudang distributor. Memilih hub lain ditolak | Lulus |
| SD7 | Bandingkan harga dengan ecom | Harga satuan sama | Lulus |
| SD8 | Checkout (alamat Jabodetabek), bayar Faspay | `source_warehouse_id` = gudang distributor, `fulfilled_by_distributor=1` | Lulus |
| SD9 | Alamat luar Jabodetabek | `kurir_toko` tetap tersedia | Lulus |
| SD10 | Pilih `self_pickup` | Teks "Pengambilan Ditempat dari <gudang distributor>" | Sebagian (catatan T4) |
| SD11 | Metode pembayaran | Hanya Faspay. Tanpa TOP dan transfer manual. Memaksa `term_of_payment` lewat request ditolak | Lulus |
| SD12 | 50 karton Syrup, lalu 51 karton Syrup | Tanpa diskon, lalu 10% (Powder 7%) | Lulus |
| SD13 | Stok gudang distributor 100, order 4 | Stok jadi 96, riwayat stok `-4` | Lulus |
| SD14 | Pesan melebihi stok | Order ditolak, stok tidak berubah | Lulus |
| SD15 | Order dibatalkan, lalu dibatalkan lagi | Stok kembali sekali, tidak dobel | Lulus |
| SD16 | Order belum dibayar lewat 30 menit lalu `php artisan orders:cancel-unpaid` | Order batal, stok kembali | Lulus |
| SD17 | Order dilunasi | Tidak ada job atau log QAD, Jubelio, WMS. `qad_so_number` dan `jubelio_salesorder_id` kosong | Lulus |
| SD18 | Gudang distributor dinonaktifkan, atau alamat gudang dikosongkan | Keranjang dan checkout diblokir dengan pesan jelas | Lulus |
| SD19 | Distributor induk dinonaktifkan | Sama dengan SD18 | Lulus |
| SD20 | Distributor mengubah stok manual | Angka stok di toko subdist ikut berubah | Lulus |

## 4. Distributor

| ID | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| DI1 | Daftar sebagai ecom, verifikasi email, login | `role=ecom` | Lulus |
| DI2 | Ajukan di `/distributor/apply` | `distributor_status=pending`. Pengajuan kedua ditolak | Lulus |
| DI3 | Admin menyetujui | `role=distributor`, `distributor_status=approved`, kode `DST...` terbentuk | Lulus |
| DI4 | Admin menolak pengajuan lain | `distributor_status=rejected`, user tetap ecom | Belum |
| DI5 | Login di `/distributor/login` | Masuk panel distributor | Lulus |
| DI6 | Isi stok awal di menu Stok (sinkronkan produk, ubah angka) | `warehouse_stocks` terisi, riwayat tercatat | Lulus |
| DI7 | Halaman Rekrut Subdist | Link `?distributor=<kode>` tampil, subdist SD2 ada di daftar | Lulus |
| DI8 | Daftar order panel distributor | Order subdist SD8 tampil dengan label SUBDIST setelah lunas | Lulus |
| DI9 | Belanja ke gudang pusat (checkout distributor) | Order bertipe distributor. Opsi TOP muncul bila `term_of_payment` > 0 | Belum |

## 5. Lintas role

| ID | Skenario | Hasil yang diharapkan | Status |
|---|---|---|---|
| X1 | Email atau telepon duplikat, password lemah, syarat tidak dicentang | Pesan validasi tampil, tidak ada user baru | Lulus |
| X2 | Login password salah | Ditolak | Lulus |
| X3 | Logout lalu buka halaman akun | Diarahkan ke login | Lulus |
| X4 | Lupa password: minta tautan dari log, ganti password, login | Berhasil dengan password baru | Lulus |
| X5 | Akun biasa membuka `/admin`, `/warehouse`, `/distributor` | Ditolak atau diarahkan ke login | Lulus |
| X6 | Rincian order (halaman sukses dan detail) | Total, diskon, dan PPN sama dengan tampilan checkout | Belum |
| X7 | Pelunasan disimulasikan | `payment_status=paid`, `finance_approved=1` | Lulus |
| X8 | Admin: halaman Outlet | Tab OUTLET, SUBDIST, RESELLER, ECOM dengan jumlah benar, akun uji di tab yang tepat | Lulus |
| X9 | Admin: tandai subdist sebagai reseller lalu cabut | Pindah tab Reseller dan kembali, riwayat level tercatat | Lulus |

## 6. Regresi migrasi (sudah dijalankan di `rasa_e2e`)

| ID | Hasil | Status |
|---|---|---|
| M1 | 27 user `buyer` jadi `ecom`, tidak ada role NULL, `buyer` hilang dari enum | Lulus |
| M2 | 1 outlet ber-kode distributor jadi `subdist`, 6 outlet kode sales tidak berubah | Lulus |

## Bukti dan batasan

- Tiap skenario menyimpan screenshot dan hasil query database. Bug dicatat dengan ID skenario, langkah, hasil nyata, dan hasil yang diharapkan, lalu skenario diulang setelah perbaikan.
- Halaman Faspay sungguhan tidak diselesaikan di browser. Skenario berhenti saat VA atau QRIS terbentuk, lalu pelunasan disimulasikan.
- Kurir API (JNE, J&T, dan lainnya) tidak diuji karena layanan EkspedisiKu lokal tidak berjalan.
- WhatsApp dimatikan. Login Google tidak diuji.

---

## Hasil putaran pertama (2026-11-24)

Ringkasan: dari 53 skenario bernomor, 48 lulus, 1 sebagian (SD10), 1 temuan (EC6), dan 3 belum dijalankan (DI4, DI9, X6).
Skenario subdist SD1 sampai SD20: 19 lulus dan 1 sebagian. F9 (reseller) diblokir lingkungan (lihat T5).

### Bug yang ditemukan dan sudah diperbaiki

| No | Temuan | Asal | Perbaikan |
|---|---|---|---|
| B1 | Migrasi role gagal di MySQL (error 1093, subquery ke tabel yang sedang di-update) | Perubahan kita | Kode distributor diambil dulu lalu `whereIn` |
| B2 | Halaman pengajuan distributor error 500: `User` tidak di-import di dua controller Buyer | Refactor kita | Ditambah `use App\Models\User` |
| B3 | Form pengajuan distributor tidak bisa terkirim: validasi memakai tabel `provinces` dan `regencies` yang tidak ada | Sudah ada di `main` | Validasi memakai view wilayah |
| B4 | Halaman detail pengajuan di admin error 500: relasi `distributorProvince` dan `distributorRegency` tidak ada | Sudah ada di `main` | Relasi ditambahkan di model `User` |
| B5 | Kolom `distributor_applied_at` dan `distributor_approved_at` tidak ada di database | Sudah ada di `main` | Migrasi baru, plus fillable dan cast |
| B6 | Persetujuan distributor di admin selalu gagal: `province_id` dibaca tetapi tidak divalidasi | Sudah ada di `main` | `province_id` ditambahkan ke aturan validasi |
| B7 | Subdist tidak melihat pilihan "Diantar Ketempat" dan "Ambil di tempat" di checkout (JS hanya menampilkan bila URL memuat `sales_code` dan alamat Jabodetabek) | Fitur subdist | Subdist selalu melihat keduanya |
| B8 | Teks "outlet" masih tampil di halaman daftar lewat link distributor dan menu Rekrut | Penamaan | Diganti subdist |

### Temuan yang menunggu keputusan

| No | Temuan |
|---|---|
| T1 | Ecom tidak melihat "Diantar Ketempat" di UI, tetapi server menerimanya bila dikirim manual (order D2611007 membuktikan), dengan ongkir 0. Perlu ditutup atau dibenarkan |
| T2 | Pesan validasi telepon duplikat berbahasa Inggris ("The phone has already been taken") |
| T3 | Outlet berkode sales tanpa hub: keranjang memakai Ecommarce Tomang, lalu pindah ke Hub Bekasi saat checkout. Diskon MOQ outlet hanya berlaku bila URL checkout memuat `?sales_code=`, kode di profil tidak cukup |
| T4 | Opsi ambil di tempat menampilkan "Dikirim dari: <gudang>" tetapi tidak menampilkan teks "Pengambilan Ditempat dari <gudang>" di ringkasan |
| T5 | F9 (reseller 51 karton tanpa kode sales) diblokir: reseller membaca stok dari WMS yang dimatikan di lingkungan test. Aturan tier-nya sudah teruji di unit test |

### Belum dijalankan

DI4 (tolak pengajuan), DI9 (distributor belanja ke gudang pusat), dan X6 (kesesuaian rincian order).
