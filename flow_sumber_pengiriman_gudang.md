# Alur Sumber Pengiriman Gudang

Dokumen ini menjelaskan alur penentuan lokasi asal pengiriman barang (Gudang/Hub/DC) berdasarkan peran pengguna dan ketentuan khusus pada sistem e-commerce.

## 1. Aturan Khusus (Prioritas Utama)
Sistem memiliki satu aturan *override* mutlak yang mengabaikan semua aturan tipe pengguna:
- **Kondisi:** Jika **Total Belanja (Subtotal) ≥ Rp 25.000.000**.
- **Sumber Pengiriman:** Otomatis dikirim dari Gudang Utama Pusat, yaitu **DC MM2100**.

---

## 2. Aturan Berdasarkan Tipe Pengguna (Role)
Jika total belanja < Rp 25.000.000, maka pengiriman ditentukan oleh peran akun pemesan dengan pembagian sebagai berikut:

### A. Distributor
- **Kondisi:** Pesanan dilakukan oleh akun dengan tipe *Distributor*.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: Distributor**.
- **Opsi Ekspedisi:** Kurir Toko & Self Pickup.
- **Tujuan Sinkronisasi Order:** Dikirim ke **QAD** (ERP) dan diteruskan ke **WMS** (Warehouse Management System). (Tidak disinkronisasi ke Jubelio).
- **Daftar Gudang (Aktif):**
  - DC MM2100
  - DC Surabaya
  - DC Medan

### B. Outlet (Dengan Kode Sales)
- **Kondisi:** Pesanan dilakukan oleh akun *Outlet*, dan akun tersebut **memiliki/memasukkan Kode Sales**.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: Outlet**.
- **Opsi Ekspedisi:** Lion Parcel, Self Pickup, dan Kurir Toko (khusus jika alamat pengiriman berada di area Jabodetabek).
- **Tujuan Sinkronisasi Order:** Dikirim ke **Jubelio** dan diteruskan ke **WMS**. (Tidak dikirim ke QAD).
- **Daftar Gudang (Aktif):**
  - Hub Bekasi
  - Hub Surabaya Tidar
  - Hub Maggiore

### C. Outlet (Tanpa Kode Sales / eCommerce Biasa)
- **Kondisi:** Pesanan dilakukan oleh akun *Outlet* atau Pembeli, dan **TIDAK memakai Kode Sales**.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: eCommerce**.
- **Opsi Ekspedisi:** Semua layanan ekspedisi umum (JNE, J&T, SiCepat, dll. yang aktif di API), ditambah Kurir Toko. (*Self Pickup TIDAK tersedia*).
- **Tujuan Sinkronisasi Order:** Dikirim ke **Jubelio** dan diteruskan ke **WMS**. (Tidak dikirim ke QAD).
- **Daftar Gudang (Aktif):**
  - Ecommarce Tomang
  - Hub Surabaya Tidar

---

## 3. Prioritas Pencarian Gudang Terdekat
Setelah sistem mengelompokkan gudang sesuai tipe pengguna di atas, sistem tidak asal memilih. Pemilihan 1 gudang terakhir dilakukan dengan metode jarak secara berurutan:
1. **Titik Koordinat (Lat/Long):** Dicari gudang yang jarak titik koordinatnya paling dekat dengan titik koordinat alamat pengiriman.
2. **Kecamatan (District):** Jika titik koordinat tidak ditemukan, mencari gudang di Kecamatan yang sama.
3. **Kota/Kabupaten (Regency):** Jika belum ketemu, dicari di Kota/Kab yang sama.
4. **Provinsi (Province):** Jika belum ketemu, dicari di Provinsi yang sama.
5. **Fallback:** Jika tetap tidak ada yang se-daerah, sistem memilih gudang sesuai rolenya secara acak, dengan prioritas ke MM2100.
