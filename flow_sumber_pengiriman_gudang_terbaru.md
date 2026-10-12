# LAPORAN BLUEPRINT DAN ARSITEKTUR PLATFORM RASACONNECT

**PT MULTI CITRA RASA (RASA GROUP)**  
*Platform E-Commerce & Marketplace Terintegrasi — RasaConnect*

---

## PANDUAN ARSITEKTUR SISTEM, ALUR PENGIRIMAN GUDANG, SKEMA DISKON, DAN DISTRIBUSI DATA

| Informasi Dokumen | Keterangan |
| :--- | :--- |
| **Nomor Dokumen** | BP-RC-ARCH-2026-001 |
| **Judul Dokumen** | Laporan Blueprint dan Arsitektur Platform RasaConnect |
| **Versi / Edisi** | Versi 2.0 (Resmi) |
| **Tanggal Efektif** | Oktober 2026 |
| **Status Dokumen** | Berlaku / Disetujui |
| **Tujuan Dokumen** | Memberikan gambaran komprehensif cara kerja platform RasaConnect |
| **Klasifikasi** | Dokumen Internal Resmi (Confidential) |

<div style="page-break-after: always;"></div>

## RINGKASAN & TUJUAN DOKUMEN

Dokumen ini disusun sebagai **Blueprint Arsitektur Resmi** yang bertujuan murni untuk memberikan gambaran komprehensif dan mendalam tentang **bagaimana platform RasaConnect bekerja** secara menyeluruh dari hulu ke hilir. Dokumen ini menjabarkan mekanisme internal sistem dalam mengelola transaksi, integrasi antar-sistem, penentuan logistik pergudangan, serta kalkulasi finansial belanja:

1. **Mekanisme Penentuan Lokasi Gudang & Ekspedisi:** Logika cerdas sistem dalam menentukan titik asal pengiriman pesanan (Distribution Center, Hub Regional, atau Hub eCommerce) serta pilihan metode pengiriman (**Diantar Ketempat** dan **Pengambilan Ditempat** sebagai armada internal, serta ekspedisi eksternal pihak ketiga) berdasarkan skala nominal belanja dan klasifikasi peran akun pengguna.
2. **Kalkulasi & Skema Finansial Diskon Pembelian:** Logika bisnis perhitungan harga bertingkat, mencakup harga promo katalog, diskon berjenjang volume karton per grup kategori (Syrup/Sauce dan Powder/Pulp), diskon khusus Distributor yang dikonfigurasi per kategori produk (brand dan kategori) di platform RasaConnect, diskon ongkos kirim ekspedisi JNE 20%, hingga hierarki kalkulasi DPP, PPN, biaya pembayaran, dan total tagihan akhir saat checkout.
3. **Arsitektur Aliran Data & Integrasi Eksternal:** Alur distribusi data otomatis yang membagi jalur transaksi sesuai peran pengguna: pesanan Distributor dan Outlet diteruskan ke ERP QAD dan WMS, sedangkan pesanan eCommerce / Retail Umum dialirkan ke Jubelio Omnichannel; serta integrasi pelengkap dengan Zoho Books, Faspay Payment Gateway, dan gateway notifikasi.

<div style="page-break-after: always;"></div>

## DAFTAR ISI

<table style="width: 100%; border: none; margin: 15px 0; font-size: 9.5pt;">
  <tr style="border-bottom: 1.5px solid #6A1B1B;">
    <th style="border: none; padding: 6px 0; text-align: left; background: none; color: #6A1B1B; font-size: 10pt;">Pokok Pembahasan & Struktur Dokumen</th>
    <th style="border: none; padding: 6px 0; text-align: right; background: none; color: #6A1B1B; font-size: 10pt; width: 80px;">Halaman</th>
  </tr>
  <tr>
    <td style="border: none; padding: 5px 0;"><b>RINGKASAN & TUJUAN DOKUMEN</b> ..........................................................................................</td>
    <td style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #6A1B1B;">Hal. 2</td>
  </tr>
  <tr>
    <td style="border: none; padding: 5px 0;"><b>BAB I: ALUR SUMBER PENGIRIMAN GUDANG & LAYANAN EKSPEDISI</b> .........................</td>
    <td style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #6A1B1B;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.1 Aturan Khusus Nilai Transaksi (Prioritas Utama: Subtotal &gt;= Rp 25.000.000) .......................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.2 Aturan Sumber Gudang & Ekspedisi Berdasarkan Peran Pengguna (Role) ........................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• A. Akun Distributor (Aliran QAD & WMS, Ekspedisi Internal) ........................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• B. Akun Outlet dengan Kode Sales (Aliran QAD & WMS) ...............................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• C. Akun eCommerce / Pembeli Umum (Aliran Jubelio & Gudang eCommerce) ...............</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 5</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.3 Klasifikasi & Kesiapan Layanan Ekspedisi (Eksternal 3PL & Internal) ..............................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 5</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.4 Prioritas Pencarian Gudang Terdekat (Hierarki Geografis Wilayah) .................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 5</td>
  </tr>
  <tr>
    <td style="border: none; padding: 5px 0;"><b>BAB II: SKEMA PERHITUNGAN DISKON KETIKA PEMBELIAN</b> .......................................</td>
    <td style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #6A1B1B;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.1 Diskon Level Produk (Promo Katalog) ............................................................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.2 Diskon Berjenjang Kategori Produk (Tiered Category Discount) ......................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• A. Pembagian Grup Kategori (Syrup/Sauce & Powder/Pulp) .........................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• B. Konversi Volume ke Satuan Karton (1 Karton = 6 Pcs) .............................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• C. Tabel Persentase Diskon Berjenjang ..........................................................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• D. Perhitungan Nilai Potongan Diskon per Grup .............................................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 7</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.3 Diskon Khusus Role Distributor (Konfigurasi Kategori di RasaConnect) ..........................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 7</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.4 Diskon Ongkos Kirim (Ekspedisi JNE 20%) .....................................................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 7</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.5 Urutan Perhitungan Total Pembayaran Saat Checkout ...................................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 7</td>
  </tr>
  <tr>
    <td style="border: none; padding: 5px 0;"><b>BAB III: DIAGRAM ARSITEKTUR & DISTRIBUSI DATA SISTEM</b> .......................................</td>
    <td style="border: none; padding: 5px 0; text-align: right; font-weight: bold; color: #6A1B1B;">Hal. 8</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">3.1 Diagram Alur Batching & Integrasi Sales Order (Gambar Resmi) .....................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 8</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">3.2 Diagram Flowchart Penentuan Sumber Gudang Pengiriman (Gambar Resmi) ....................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 9</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">3.3 Penjelasan Rinci Alur Distribusi Data Pesanan ................................................................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 10</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• A. Alur Order Distributor B2B (QAD, WMS, Zoho Books) ...............................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 10</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• B. Alur Order Outlet dengan Kode Sales (QAD, WMS, Zoho Books) .............................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 10</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• C. Alur Order eCommerce & Retail Umum (Jubelio, Ekspedisi Ready, Zoho) ................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 11</td>
  </tr>
</table>

<div style="page-break-after: always;"></div>

## BAB I: ALUR SUMBER PENGIRIMAN GUDANG & LAYANAN EKSPEDISI

### 1. Aturan Khusus Nilai Transaksi (Prioritas Utama)
Sistem menerapkan satu aturan mutlak berprioritas tertinggi yang mengabaikan tipe pengguna apa pun:
- **Kondisi:** Jika **Total Belanja (Subtotal) >= Rp 25.000.000**.
- **Sumber Pengiriman:** Otomatis dialokasikan dan dikirim dari Gudang Utama Pusat, yaitu **DC MM2100**.

---

### 2. Aturan Berdasarkan Tipe Pengguna (Role)
Apabila total belanja bernilai kurang dari Rp 25.000.000, lokasi asal pengiriman dan opsi layanan pengiriman ditentukan berdasarkan peran akun pengguna:

#### A. Distributor
- **Kondisi:** Pesanan dilakukan oleh akun terverifikasi dengan role **Distributor**.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: Distributor**.
- **Opsi Layanan Pengiriman Resmi:**
  - **Diantar Ketempat** (Armada logistik internal perusahaan)
  - **Pengambilan Ditempat** (Pengambilan mandiri internal di lokasi gudang)
- **Tujuan Aliran Data Pesanan:** Diteruskan ke sistem **ERP QAD** dan **WMS** *(tidak dialirkan ke Jubelio)*.
- **Daftar Gudang Aktif:**
  - **DC MM2100** (Kode: `WHMPRG001`)
  - **DC Surabaya** (Kode: `WHSBY001`)
  - **DC Medan** (Kode: `WHMDN01`)

#### B. Outlet (Dengan Kode Sales)
- **Kondisi:** Pesanan dilakukan oleh akun bertipe **Outlet** dan menyertakan **Kode Sales** representatif lapangan.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: Outlet**.
- **Opsi Layanan Pengiriman Resmi:**
  - Layanan ekspedisi eksternal (termasuk Lion Parcel, JNE, Lalamove, SiCepat, J&T)
  - **Pengambilan Ditempat** (Internal)
  - **Diantar Ketempat** (Armada internal, khususnya pengantaran wilayah Jabodetabek dan coverage hub)
- **Tujuan Aliran Data Pesanan:** Diteruskan ke sistem **ERP QAD** dan **WMS** *(alur terpadu bersama Distributor, tidak dialirkan ke Jubelio)*.
- **Daftar Gudang Aktif:**
  - **Hub Bekasi** (Kode: `WHRG002`)
  - **Hub Surabaya Tidar** (Kode: `WHMPSBY`)
  - **Hub Maggiore** (Kode: `WHMPRG003`)

#### C. Outlet / Pembeli Umum (Tanpa Kode Sales / eCommerce)
- **Kondisi:** Pesanan dilakukan oleh akun **Outlet** atau **Pembeli Umum**, dan **TIDAK mencantumkan Kode Sales**.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: eCommerce**.
- **Opsi Layanan Pengiriman Resmi:**
  - Layanan ekspedisi eksternal yang aktif (misal: **JNE**, **Lalamove**)
  - **Diantar Ketempat** (Armada internal perusahaan)
  - *(Opsi Pengambilan Ditempat **TIDAK** tersedia untuk jalur eCommerce umum)*
- **Tujuan Aliran Data Pesanan:** Diteruskan hanya ke sistem **Jubelio Omnichannel** *(tidak dialirkan ke QAD)*.
- **Daftar Gudang Sumber eCommerce:**
  - **Ecommarce Tomang** (Kode Hub: `WHECOMM1`, Kode Lokasi QAD: `WHEV001`) — Alamat: Jl. Mandala Timur No.11 / No.55, Tomang, Jakarta Barat (melayani area Jabodetabek dan wilayah barat).
  - **Hub Surabaya Tidar** (Kode Hub: `WHMPSBY`) — Alamat: Jl. Tidar No.47, Surabaya, Jawa Timur (gudang multi-role yang melayani area Jawa Timur dan wilayah timur).

---

### 3. Klasifikasi & Kesiapan Layanan Ekspedisi

Platform RasaConnect mengintegrasikan dua kategori pengiriman logistik untuk melayani seluruh channel pemesanan:

#### A. Layanan Ekspedisi Eksternal (Pihak Ketiga / 3PL)
Sistem telah mendukung konektivitas dengan 5 perusahaan ekspedisi nasional terkemuka:
- **JNE Express:** **Ready / Aktif**. Terintegrasi penuh via API untuk layanan reguler maupun kargo, lengkap dengan fasilitas pemotongan diskon ongkos kirim otomatis 20%.
- **Lalamove:** **Ready / Aktif**. Terintegrasi via API untuk pengiriman instan / *same day delivery* armada roda dua dan roda empat.
- **SiCepat:** Tersedia dalam arsitektur sistem (disiapkan untuk aktivasi tahap berikutnya).
- **J&T Express:** Tersedia dalam arsitektur sistem (disiapkan untuk aktivasi tahap berikutnya).
- **Lion Parcel:** Tersedia dalam arsitektur sistem (disiapkan untuk aktivasi tahap berikutnya).

#### B. Layanan Ekspedisi Internal Perusahaan
Layanan logistik mandiri yang dikelola oleh tim internal Rasa Group:
- **Diantar Ketempat:** Layanan armada transportasi internal perusahaan yang mengantarkan pesanan langsung ke gudang atau gerai pemesan sesuai jadwal dan rute operasional internal yang berlaku.
- **Pengambilan Ditempat:** Pelanggan atau armada utusan pemesan mengambil sendiri barang yang telah disiapkan dan dikemas di Distribution Center atau Hub resmi sesuai regulasi dan jadwal operasional internal yang berlaku.

---

### 4. Prioritas Pencarian Gudang Terdekat (Hierarki Geografis Wilayah)
Setelah sistem mengelompokkan kandidat gudang sesuai peran akun pemesan, pemilihan 1 (satu) gudang definitif dilakukan secara bertingkat berdasarkan kedekatan geografis alamat pengiriman:
1. **Titik Koordinat (Latitude / Longitude):** Mencari gudang dengan jarak garis lurus terdekat dari koordinat alamat pemesan.
2. **Kecamatan (District):** Apabila titik koordinat tidak tersedia, mencari gudang yang berada di wilayah Kecamatan yang sama.
3. **Kota / Kabupaten (Regency):** Apabila belum ditemukan, mencari gudang yang berada di Kota/Kabupaten yang sama.
4. **Provinsi (Province):** Apabila belum ditemukan, mencari gudang yang berada di Provinsi yang sama.
5. **Fallback (Pilihan Cadangan):** Jika tidak ada gudang yang satu provinsi, sistem menunjuk gudang aktif sesuai perannya dengan prioritas default ke **DC MM2100**.

<div style="page-break-after: always;"></div>

## BAB II: SKEMA PERHITUNGAN DISKON KETIKA PEMBELIAN

Sistem e-commerce menerapkan skema diskon bertingkat yang dihitung secara otomatis saat proses checkout berdasarkan kategori produk, volume pembelian, dan profil pengguna.

### 1. Diskon Level Produk (Promo Katalog)
- Jika produk memiliki potongan harga promo langsung dari harga normal, harga satuan yang masuk ke keranjang belanja otomatis menggunakan harga promo aktif.
- Pada etalase katalog, sistem menampilkan informasi harga coret dan harga promo akhir.

---

### 2. Diskon Berjenjang Kategori Produk (Tiered Category Discount)
Skema diskon ini berlaku bagi pembeli bertipe **Outlet** dan **Subdist / Reseller** (Non-Distributor).

#### A. Pembagian Grup Kategori Produk
Seluruh produk minuman dan bahan baku dikelompokkan ke dalam 2 grup kategori:
1. **Grup 1: Syrup & Sauce**
   - Meliputi kategori: `Syrup` dan `SAUCE`.
2. **Grup 2: Powder, Pulp & Lainnya**
   - Meliputi kategori: `PULP`, `POWDER`, `COCOA`, `PUREE`, `TEA`, `TOPPING`, `JELLY`, dan `RAMOE`.

#### B. Konversi Volume ke Satuan Karton
Akumulasi kuantitas produk dalam satu grup dihitung berdasarkan satuan karton standar industri di mana **1 Karton = 6 pcs (botol / pack)**:
- **Contoh Perhitungan yang Mudah Dipahami:**
  - Pembelian **6 botol** Syrup = Terhitung **1 karton** (mendapatkan Diskon Karton Tier 1 sebesar **2.5%**).
  - Pembelian **12 botol** Syrup = Terhitung **2 karton** (tetap di Tier 1–2 karton sebesar **2.5%**).
  - Pembelian **18 botol** Syrup = Terhitung **3 karton** (naik ke Diskon Karton Tier 2 sebesar **5.0%**).
  - Pembelian **54 botol** Syrup = Terhitung **9 karton** (naik ke Diskon Karton Tier 3 sebesar **7.5%**).
  - Pembelian **114 botol** Syrup = Terhitung **19 karton** (mencapai Diskon Karton Maksimal Outlet sebesar **10.0%**).
- Ketentuan yang sama berlaku untuk grup **Powder & Pulp** dengan kelipatan 6 pack per karton sesuai tier persentasenya.

#### C. Tabel Persentase Diskon Berjenjang

| Role Pembeli | Akumulasi Pembelian per Grup | Diskon Syrup & Sauce | Diskon Powder, Pulp & Lainnya |
| :--- | :--- | :---: | :---: |
| **Outlet** *(Default)* | **1 – 2 Karton** (6 – 17 pcs) | **2.5%** | **2.0%** |
| **Outlet** *(Default)* | **3 – 8 Karton** (18 – 53 pcs) | **5.0%** | **4.0%** |
| **Outlet** *(Default)* | **9 – 18 Karton** (54 – 113 pcs) | **7.5%** | **5.0%** |
| **Outlet** *(Default)* | **&gt;= 19 Karton** (&gt;= 114 pcs) | **10.0%** | **6.0%** |
| **Subdist / Reseller** | **&gt; 50 Karton** (&gt; 300 pcs) | **15.0%** | **8.0%** |

#### D. Perhitungan Nilai Potongan Diskon
- **Diskon per Grup** = Subtotal belanja pada grup tersebut dikalikan persentase diskon grup yang tercapai.
- **Total Diskon Berjenjang** = Diskon Grup 1 (Syrup & Sauce) + Diskon Grup 2 (Powder, Pulp & Lainnya).

---

### 3. Diskon Khusus Role Distributor (Konfigurasi Mandiri di RasaConnect)
Akun bertipe **Distributor** memiliki mekanisme diskon tersendiri yang diatur langsung di dalam sistem **RasaConnect**:
- **Konfigurasi per Kategori Produk:** Diskon distributor tidak menggunakan skema tier karton retail, melainkan memiliki pengaturan khusus untuk setiap kategori produk (*Brand* dan *Category*) yang dikonfigurasi melalui sistem manajemen RasaConnect.
- **Fleksibilitas Kemitraan:** Setiap kategori produk (misalnya Multibev Syrup, Powder, atau Sauce) dapat diberikan persentase diskon distributor tersendiri sesuai kesepakatan komersial.
- **Fallback Price Level:** Apabila suatu produk belum memiliki konfigurasi diskon kategori spesifik, sistem secara otomatis mengacu pada persentase diskon default (*Price Level*) distributor yang tersimpan di RasaConnect.
- **Mesin Kalkulasi Mandiri:** Mesin kalkulasi RasaConnect langsung menerapkan persentase diskon tersebut pada saat transaksi dibuat, sehingga harga akhir terbentuk secara transparan di dalam platform.

---

### 4. Diskon Ongkos Kirim (Shipping Discount)
- **Ekspedisi JNE:** Pesanan yang memilih pengiriman JNE otomatis memperoleh potongan biaya kirim sebesar **20%** dari tarif normal ekspedisi melalui integrasi sistem API JNE.

---

### 5. Urutan Perhitungan Total Pembayaran Saat Checkout
Alur perhitungan nilai akhir pesanan dari awal hingga pembayaran adalah:
1. **Subtotal Produk:** Total harga satuan produk dikalikan kuantitas masing-masing item.
2. **Potongan Diskon:** Total diskon berjenjang (atau diskon kategori distributor) dikurangkan dari subtotal produk.
3. **DPP (Dasar Pengenaan Pajak):** Nilai subtotal bersih setelah dikurangi potongan diskon (`Subtotal Produk - Potongan Diskon`).
4. **PPN:** Dihitung dari nilai DPP (jika profil akun atau perusahaan diatur kena PPN). Jika akun non-PPN, nilai PPN adalah Rp 0.
5. **Ongkos Kirim:** Biaya pengiriman sesuai ekspedisi pilihan (otomatis terpotong diskon 20% jika memakai JNE).
6. **Biaya Pembayaran (Payment Fee):** Biaya administrasi gateway pembayaran (misal Virtual Account atau QRIS, jika ada).
7. **Total Pembayaran:** **DPP + PPN + Ongkos Kirim + Biaya Pembayaran**.

<div style="page-break-after: always;"></div>

## BAB III: DIAGRAM ARSITEKTUR & DISTRIBUSI DATA SISTEM

Bagian ini memetakan bagaimana data pesanan dari platform **RasaConnect** didistribusikan ke berbagai sistem terintegrasi (ERP, Omnichannel, Pembayaran, Logistik, dan Notifikasi) berdasarkan tipe pesanan dan peran pengguna (*role*).

### 1. Diagram Alur Batching & Sales Order

Berikut adalah diagram alur resmi integrasi pemesanan (*Flow Batching & Sales Order*) pada platform RasaConnect:

![Diagram Alur Batching & Sales Order](./untitled%20folder/WhatsApp%20Image%202026-10-07%20at%2007.41.09.jpeg)

*Diagram 1: Alur Pemesanan dan Integrasi Sistem — Jalur A (Distributor & Outlet melalui MWS & QAD) vs Jalur B (E-commerce melalui Jubelio).*

<div style="page-break-after: always;"></div>

### 2. Diagram Flowchart Penentuan Sumber Gudang Pengiriman

Berikut adalah diagram alur logika penentuan gudang pengiriman berdasarkan nilai belanja, peran akun, dan kedekatan wilayah:

![Diagram Alur Penentuan Gudang Pengiriman](./untitled%20folder/WhatsApp%20Image%202026-10-09%20at%2008.58.37.jpeg)

*Diagram 2: Flowchart Logika Penentuan 1 Gudang Pengiriman.*

<div style="page-break-after: always;"></div>

### 3. Penjelasan Rinci Alur Distribusi Data

#### A. Alur Order Distributor (B2B)
1. **Penerimaan Pesanan:** Pesanan diproses melalui portal B2B RasaConnect dengan diskon distributor yang telah dikonfigurasi per kategori produk di dalam sistem RasaConnect.
2. **Validasi Batas Kredit (Term of Payment):**
   - Sistem secara otomatis membandingkan nilai total pesanan baru dengan sisa batas kredit piutang akun distributor.
   - Jika total tagihan masih dalam batas kredit yang diizinkan, pesanan langsung disetujui otomatis oleh sistem keuangan. Jika batas kredit terlampaui, pesanan masuk ke antrean persetujuan manual oleh tim Finance.
3. **Penerusan Data ke ERP QAD & WMS:**
   - Seluruh rincian pesanan (nomor order, identitas distributor, daftar produk, kuantitas, harga diskon, dan jangka waktu pembayaran) otomatis diteruskan ke sistem ERP QAD dan WMS.
   - QAD menerbitkan nomor pesanan resmi (Sales Order) dan otomatis memperbarui saldo kredit piutang distributor.
4. **Instruksi Pengambilan Barang di Gudang (DC):**
   - Data dialokasikan ke Distribution Center terdekat (DC MM2100, DC Surabaya, atau DC Medan).
   - Petugas gudang menyiapkan barang dengan ketentuan ketat masa simpan produk (*shelf-life* minimal 9 bulan sebelum tanggal kedaluwarsa).
5. **Pencatatan Akuntansi di Zoho Books:**
   - Faktur penjualan dicatat ke Zoho Books untuk rekonsiliasi keuangan dan pelaporan faktur pajak.
6. **Layanan Pengiriman:**
   - Pengiriman dilakukan menggunakan layanan armada internal **Diantar Ketempat** langsung ke gudang distributor, atau disiapkan untuk **Pengambilan Ditempat** sesuai regulasi operasional internal perusahaan.
7. **Notifikasi Otomatis:**
   - Sistem mengirimkan pesan WhatsApp dan email kepada PIC distributor berisi konfirmasi pesanan dan status invoice.

#### B. Alur Order Outlet (Dengan Kode Sales)
1. **Penerimaan Pesanan:** Pesanan dilakukan oleh mitra outlet dengan menyertakan kode sales lapangan. Skema diskon berjenjang kategori produk (Syrup/Sauce hingga 10%, Powder hingga 6%) otomatis diterapkan.
2. **Pembayaran Digital:** Pembayaran diselesaikan melalui Virtual Account perbankan atau QRIS via gateway pembayaran Faspay. Setelah pembayaran berhasil, sistem otomatis mengonfirmasi status pesanan menjadi lunas.
3. **Penerusan Data ke ERP QAD & WMS:**
   - Sesuai dengan arsitektur sistem (Jalur A), data pesanan outlet **diteruskan ke sistem ERP QAD dan WMS** untuk penerbitan Sales Order dan pemotongan alokasi inventori fisik pada Hub Regional yang dituju (Hub Bekasi, Hub Surabaya Tidar, atau Hub Maggiore).
4. **Fulfillment Gudang Regional:**
   - Petugas Hub Regional melakukan penyiapan barang dari rak, pengecekan mutu, dan pengemasan pesanan.
5. **Layanan Pengiriman:**
   - Pesanan dikirimkan menggunakan ekspedisi eksternal yang ready (**JNE**, **Lalamove**) atau ekspedisi lain yang tersedia, armada internal **Diantar Ketempat**, atau diambil langsung melalui **Pengambilan Ditempat** di Hub sesuai regulasi internal perusahaan.
6. **Pencatatan Faktur & Notifikasi:**
   - Data transaksi dicatat di Zoho Books sebagai penjualan lunas, dan nomor resi pelacakan dikirimkan otomatis via WhatsApp dan email ke akun outlet.

#### C. Alur Order eCommerce & Retail Umum (Konsumen Langsung & Reseller)
1. **Penerimaan Pesanan:** Pembelian langsung oleh konsumen akhir atau reseller melalui toko online, lengkap dengan promo harga katalog aktif dan akumulasi poin loyalitas.
2. **Pembayaran Instan:** Pembayaran diproses seketika melalui gateway pembayaran Faspay (QRIS / Virtual Account) dengan konfirmasi lunas otomatis dalam hitungan detik.
3. **Penerusan Data ke Jubelio Omnichannel:**
   - Data transaksi diteruskan **hanya ke Jubelio Omnichannel** (Jalur B) untuk memotong stok pada Hub eCommerce yang ditugaskan (**Ecommarce Tomang** atau **Hub Surabaya Tidar**) sekaligus menjaga sinkronisasi stok di seluruh kanal marketplace. Pesanan eCommerce **tidak dialirkan ke QAD**.
4. **Penerusan Data ke Layanan Ekspedisi Terintegrasi:**
   - **JNE Express:** Sistem memanggil API JNE secara otomatis untuk menerbitkan nomor resi (*AWB / Cnote*) dengan fasilitas potongan ongkos kirim sebesar 20%.
   - **Lalamove:** Untuk opsi pengiriman instan / *same day*, sistem memanggil driver Lalamove ke lokasi hub eCommerce.
   - **Diantar Ketempat:** Pengiriman langsung menggunakan armada internal perusahaan bila dipilih dan masuk dalam rute pengantaran.
5. **Pencatatan Faktur & Notifikasi:**
   - Transaksi tercatat di Zoho Books, dan pembeli menerima konfirmasi otomatis serta nomor resi pengiriman via WhatsApp dan email.
