# Alur Sumber Pengiriman Gudang & Perhitungan Diskon Pembelian

Dokumen ini menjelaskan alur penentuan lokasi asal pengiriman barang (Gudang/Hub/DC), ketersediaan ekspedisi berdasarkan peran pengguna (role), serta skema perhitungan diskon pembelian secara menyeluruh pada sistem e-commerce Rasa Connect.

---

## BAGIAN I: ALUR SUMBER PENGIRIMAN GUDANG & EKSPEDISI

### 1. Aturan Khusus (Prioritas Utama)
Sistem memiliki satu aturan mutlak yang mengabaikan semua aturan tipe pengguna:
- **Kondisi:** Jika **Total Belanja (Subtotal) ≥ Rp 25.000.000**.
- **Sumber Pengiriman:** Otomatis dikirim dari Gudang Utama Pusat, yaitu **DC MM2100**.

---

### 2. Aturan Berdasarkan Tipe Pengguna (Role)
Jika total belanja < Rp 25.000.000, maka pengiriman ditentukan oleh peran akun pemesan dengan pembagian sebagai berikut:

#### A. Distributor
- **Kondisi:** Pesanan dilakukan oleh akun dengan role **Distributor**.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: Distributor**.
- **Opsi Ekspedisi:**
  - **Kurir Toko** (Diantar Ketempat)
  - **Self Pickup** (Ambil Sendiri di Gudang)
- **Tujuan Sinkronisasi Order:** Dikirim ke **QAD** (ERP) dan diteruskan ke **WMS** (Warehouse Management System). *(Tidak disinkronisasi ke Jubelio)*.
- **Daftar Gudang Aktif:**
  - **DC MM2100** (`WHMPRG001`)
  - **DC Surabaya** (`WHSBY001`)
  - **DC Medan** (`WHMDN01`)

#### B. Outlet (Dengan Kode Sales)
- **Kondisi:** Pesanan dilakukan oleh akun **Outlet** dan akun tersebut **memiliki/memasukkan Kode Sales**.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: Outlet**.
- **Opsi Ekspedisi:**
  - **Lion Parcel**
  - **Self Pickup**
  - **Kurir Toko** *(Khusus jika alamat pengiriman berada di area Jabodetabek)*
- **Tujuan Sinkronisasi Order:** Dikirim ke **Jubelio** dan diteruskan ke **WMS**. *(Tidak dikirim ke QAD)*.
- **Daftar Gudang Aktif:**
  - **Hub Bekasi** (`WHRG002`)
  - **Hub Surabaya Tidar** (`WHMPSBY`)
  - **Hub Maggiore** (`WHMPRG003`)

#### C. Outlet / Pembeli Umum (Tanpa Kode Sales / eCommerce Biasa)
- **Kondisi:** Pesanan dilakukan oleh akun **Outlet** atau **Pembeli Umum**, dan **TIDAK memakai Kode Sales**.
- **Gudang Sumber:** Sistem akan mencarikan gudang terdekat dari daftar gudang **Target Role: eCommerce**.
- **Opsi Ekspedisi:**
  - Semua layanan ekspedisi umum aktif via API (misal: **JNE**, **Lalamove**)
  - **Kurir Toko**
  - *(Self Pickup **TIDAK** tersedia)*
- **Tujuan Sinkronisasi Order:** Dikirim ke **Jubelio** dan diteruskan ke **WMS**. *(Tidak dikirim ke QAD)*.
- **Daftar Gudang Aktif:**
  - **Ecommarce Tomang** (`WHECOMM1`)
  - **Hub Surabaya Tidar** (`WHMPSBY`)

---

### 3. Prioritas Pencarian Gudang Terdekat
Setelah sistem mengelompokkan gudang sesuai tipe pengguna di atas, penentuan 1 gudang spesifik dilakukan berdasarkan urutan prioritas geografis:
1. **Titik Koordinat (Latitude / Longitude):** Dicari gudang yang jarak titik koordinatnya paling dekat dengan titik koordinat alamat pengiriman.
2. **Kecamatan (District):** Jika titik koordinat tidak ditemukan, mencari gudang di Kecamatan yang sama.
3. **Kota / Kabupaten (Regency):** Jika belum ditemukan, mencari gudang di Kota/Kabupaten yang sama.
4. **Provinsi (Province):** Jika belum ditemukan, mencari gudang di Provinsi yang sama.
5. **Fallback:** Jika tetap tidak ada yang satu daerah, sistem memilih gudang aktif sesuai rolenya dengan prioritas default ke **DC MM2100**.

---

## BAGIAN II: PERHITUNGAN DISKON KETIKA PEMBELIAN

Sistem e-commerce menerapkan skema diskon bertingkat yang dihitung secara otomatis saat checkout berdasarkan kategori produk, volume pembelian, dan role pengguna.

### 1. Diskon Level Produk (Promo Katalog)
- Jika produk memiliki potongan promo langsung dari harga aslinya, harga satuan yang masuk ke keranjang belanja otomatis menggunakan harga promo aktif (`final_price`).
- Pada tampilan katalog, sistem memperlihatkan harga coret dan harga promo.

---

### 2. Diskon Berjenjang Kategori Produk (Tiered Category Discount)
Skema diskon ini berlaku bagi pembeli bertipe **Outlet** dan **Subdist / Reseller** (Non-Distributor).

#### A. Pembagian Grup Kategori Produk
Produk dikelompokkan ke dalam 2 grup kategori:
1. **Grup 1: Syrup & Sauce**
   - Kategori: `Syrup` dan `SAUCE`.
2. **Grup 2: Powder, Pulp & Lainnya**
   - Kategori: `PULP`, `POWDER`, `COCOA`, `PUREE`, `TEA`, `TOPPING`, `JELLY`, `RAMOE`.

#### B. Konversi Volume ke Satuan Karton
Akumulasi kuantitas produk dalam satu grup dikonversikan ke satuan karton (1 karton = 6 pcs):
- **Jumlah Karton** = Total botol/pack per grup dibagi 6, lalu dibulatkan ke bawah.
- Contoh: Membeli 8 botol Syrup dihitung sebagai **1 karton** (sisa 2 botol di bawah 1 karton tidak masuk perhitungan tier karton berikutnya).

#### C. Tabel Persentase Diskon Berjenjang

| Role Pembeli | Akumulasi Pembelian per Grup | Diskon Syrup & Sauce | Diskon Powder, Pulp & Lainnya |
| :--- | :--- | :---: | :---: |
| **Outlet** *(Default)* | **1 – 2 Karton** (6 – 17 pcs) | **2.5%** | **2.0%** |
| **Outlet** *(Default)* | **3 – 8 Karton** (18 – 53 pcs) | **5.0%** | **4.0%** |
| **Outlet** *(Default)* | **9 – 18 Karton** (54 – 113 pcs) | **7.5%** | **5.0%** |
| **Outlet** *(Default)* | **≥ 19 Karton** (≥ 114 pcs) | **10.0%** | **6.0%** |
| **Subdist / Reseller** | **> 50 Karton** (> 300 pcs) | **15.0%** | **8.0%** |

#### D. Perhitungan Nilai Potongan Diskon
- **Diskon per Grup** = Subtotal belanja pada grup tersebut dikalikan persentase diskon grup yang tercapai.
- **Total Diskon Berjenjang** = Diskon Grup 1 (Syrup & Sauce) + Diskon Grup 2 (Powder, Pulp & Lainnya).

---

### 3. Diskon Khusus Role Distributor
Akun bertipe **Distributor** menggunakan struktur harga net B2B khusus yang terhubung dengan ERP QAD:
- Distributor tidak menggunakan skema diskon berjenjang retail di atas.
- Harga produk mengacu pada master **Price Level** dan/atau **Diskon Kategori Distributor** yang ditetapkan untuk akun distributor tersebut.
- Selisih antara harga katalog sebelum PPN dengan harga net distributor ditampilkan sebagai **Diskon Distributor** pada rincian ringkasan belanja.

---

### 4. Diskon Ongkos Kirim (Shipping Discount)
- **Ekspedisi JNE:** Pesanan yang memilih pengiriman JNE otomatis memperoleh potongan ongkir sebesar **20%** dari tarif normal ekspedisi.

---

### 5. Urutan Perhitungan Total Pembayaran Saat Checkout

Alur perhitungan nilai akhir pesanan dari awal hingga pembayaran adalah:

1. **Subtotal Produk:** Total harga satuan produk dikalikan kuantitas masing-masing item.
2. **Potongan Diskon:** Total diskon berjenjang (atau diskon distributor) dikurangkan dari subtotal produk.
3. **DPP (Dasar Pengenaan Pajak):** Nilai subtotal bersih setelah dikurangi potongan diskon.
4. **PPN:** Dihitung dari nilai DPP (jika profil akun atau perusahaan diatur kena PPN). Jika akun non-PPN, nilai PPN adalah Rp 0.
5. **Ongkos Kirim:** Biaya pengiriman sesuai ekspedisi yang dipilih (sudah termasuk potongan diskon 20% jika memakai JNE).
6. **Biaya Pembayaran (Payment Fee):** Biaya admin gateway pembayaran (misal Virtual Account atau QRIS, jika ada).
7. **Total Pembayaran:** **DPP + PPN + Ongkos Kirim + Biaya Pembayaran**.
