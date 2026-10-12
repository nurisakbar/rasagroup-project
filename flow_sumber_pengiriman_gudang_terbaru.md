# Alur Sumber Pengiriman Gudang & Perhitungan Diskon Pembelian

Dokumen ini menjelaskan alur penentuan lokasi asal pengiriman barang (Gudang/Hub/DC), ketersediaan ekspedisi berdasarkan peran pengguna (role), serta skema perhitungan diskon pembelian secara menyeluruh pada sistem e-commerce Rasa Connect.

---

## BAGIAN I: ALUR SUMBER PENGIRIMAN GUDANG & EKSPEDISI

### 1. Aturan Khusus (Prioritas Utama)
Sistem memiliki satu aturan *override* mutlak yang mengabaikan semua aturan tipe pengguna:
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

Sistem e-commerce menerapkan skema diskon transparan bertingkat yang dihitung secara otomatis saat checkout berdasarkan kategori produk, volume pembelian, dan role pengguna.

### 1. Diskon Level Produk (Promo Katalog)
- **Mekanisme:** Jika produk memiliki potongan harga promo langsung (`discount_price < price` atau `compare_price > price`), harga satuan dasar yang masuk ke keranjang belanja adalah `final_price`.
- Tampilan pada katalog: Menampilkan harga coret (`old-price`) dan harga jual promo aktif.

---

### 2. Diskon Berjenjang Kategori Produk (Tiered Category Discount)
Skema ini berlaku bagi pembeli bertipe **Outlet** dan **Subdist / Reseller** (Non-Distributor), dihitung oleh `App\Services\DiscountService`.

#### A. Pembagian Grup Kategori Produk
Seluruh produk dalam keranjang belanja dikelompokkan ke dalam 2 grup kategori utama:
1. **Grup 1: Syrup & Sauce**
   - Kategori yang termasuk: `Syrup`, `SAUCE`.
2. **Grup 2: Powder, Pulp & Lainnya**
   - Kategori yang termasuk: `PULP`, `POWDER`, `COCOA`, `PUREE`, `TEA`, `TOPPING`, `JELLY`, `RAMOE`.

#### B. Rumus Konversi Volume ke Karton
Perhitungan jenjang diskon didasarkan pada akumulasi total botol/pack per grup kategori yang dikonversikan ke satuan karton:
$$\text{Total Karton per Grup} = \left\lfloor \frac{\sum \text{Quantity Item pada Grup}}{6} \right\rfloor$$
*(Catatan: 1 karton setara dengan 6 pcs produk standar).*

#### C. Tabel Persentase Diskon Berjenjang

| Role Pembeli | Akumulasi Pembelian per Grup | Persentase Diskon: Syrup & Sauce | Persentase Diskon: Powder, Pulp & Lainnya |
| :--- | :--- | :---: | :---: |
| **Outlet** *(Default)* | **1 – 2 Karton** (6 – 17 pcs) | **2.5%** | **2.0%** |
| **Outlet** *(Default)* | **3 – 8 Karton** (18 – 53 pcs) | **5.0%** | **4.0%** |
| **Outlet** *(Default)* | **9 – 18 Karton** (54 – 113 pcs) | **7.5%** | **5.0%** |
| **Outlet** *(Default)* | **≥ 19 Karton** (≥ 114 pcs) | **10.0%** | **6.0%** |
| **Subdist / Reseller** | **> 50 Karton** (> 300 pcs) | **15.0%** | **8.0%** |

#### D. Rumus Perhitungan Diskon Kategori
$$\text{Diskon Grup} = \frac{\text{Subtotal Grup} \times \text{Persentase Diskon}}{100}$$
$$\text{Total Diskon Berjenjang} = \text{Diskon Grup 1 (Syrup \& Sauce)} + \text{Diskon Grup 2 (Powder, Pulp \& Lainnya)}$$

---

### 3. Diskon Khusus Role Distributor
Akun bertipe **Distributor** memiliki skema harga B2B khusus yang terintegrasi dengan ERP QAD:
- **Tidak Menggunakan Diskon Berjenjang Retail:** Distributor tidak memakai skema berjenjang di atas, melainkan menggunakan struktur harga net grosir.
- **Price Level & Category Discounts:**
  - Setiap distributor memiliki penetapan `PriceLevel` dan diskon per kategori (`user->categoryDiscounts`).
  - **Potongan Distributor:** Selisih antara harga katalog dasar (DPP) dengan harga net distributor:
    $$\text{Potongan Distributor} = \text{Catalog Subtotal DPP} - \text{Retail Subtotal Distributor}$$
  - Pada rincian checkout, potongan ini dicantumkan sebagai **Diskon Distributor** atau **Diskon Kategori**.

---

### 4. Diskon Ongkos Kirim (Shipping Discount)
- **Ekspedisi JNE:**
  - Pesanan yang menggunakan layanan **JNE** otomatis mendapatkan **Diskon Ongkir 20%**:
    $$\text{Ongkos Kirim Akhir} = \text{Tarif Asli JNE} \times 0.80$$

---

### 5. Simulasi Perhitungan Total Pembayaran (Summary Formula)

Secara menyeluruh, perhitungan akhir nilai pesanan saat checkout adalah sebagai berikut:

$$\text{Subtotal Produk} = \sum (\text{Harga Satuan} \times \text{Qty})$$

$$\text{DPP (Dasar Pengenaan Pajak)} = \text{Subtotal Produk} - \text{Total Diskon Berjenjang}$$

$$\text{PPN} = \begin{cases} \text{DPP} \times \text{Tarif PPN}, & \text{jika akun kena pajak} \\ 0, & \text{jika non-PPN} \end{cases}$$

$$\text{Total Pembayaran} = \text{DPP} + \text{PPN} + \text{Ongkos Kirim (setelah diskon ongkir)} + \text{Biaya Pembayaran (Payment Fee)}$$
