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
| **Divisi Penyusun** | Departemen Teknologi Informasi & Rekayasa Sistem (Supply Chain) |
| **Klasifikasi** | Dokumen Internal Resmi (Confidential) |

<div style="page-break-after: always;"></div>

## RINGKASAN & TUJUAN DOKUMEN

Dokumen ini disusun sebagai **Blueprint Arsitektur Resmi** yang bertujuan murni untuk memberikan gambaran komprehensif dan mendalam tentang **bagaimana platform RasaConnect bekerja** secara menyeluruh dari hulu ke hilir. Dokumen ini menjabarkan mekanisme internal sistem dalam mengelola transaksi, integrasi antar-sistem, penentuan logistik pergudangan, serta kalkulasi finansial belanja:

1. **Mekanisme Penentuan Lokasi Gudang & Ekspedisi:** Logika cerdas sistem dalam menentukan titik asal pengiriman pesanan (Distribution Center atau Hub Regional) serta pilihan metode pengiriman (**Diantar Ketempat** dan **Pengambilan Ditempat**) berdasarkan skala nominal belanja dan klasifikasi peran akun pengguna.
2. **Kalkulasi & Skema Finansial Diskon Pembelian:** Logika bisnis perhitungan harga bertingkat, mencakup harga promo katalog, diskon berjenjang volume karton per grup kategori (Syrup/Sauce dan Powder/Pulp), diskon harga net khusus Distributor B2B yang tersinkronisasi dari master Price Level ERP QAD, diskon ongkos kirim ekspedisi JNE 20%, hingga hierarki kalkulasi DPP, PPN, biaya pembayaran, dan total tagihan akhir saat checkout.
3. **Arsitektur Aliran Data & Integrasi Eksternal:** Alur distribusi data otomatis yang menghubungkan RasaConnect dengan ekosistem enterprise: ERP QAD, Jubelio Omnichannel, Zoho Books, Faspay Payment Gateway, sistem WMS pergudangan, serta gateway notifikasi WhatsApp dan Email.

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
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.1 Aturan Khusus Nilai Transaksi (Prioritas Utama: Subtotal &ge; Rp 25.000.000) .......................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.2 Aturan Sumber Gudang & Ekspedisi Berdasarkan Peran Pengguna (Role) ........................</td>
    <td style="border: none; padding: 3px 0; text-align: right; color: #555;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• A. Akun Distributor (Diantar Ketempat & Pengambilan Ditempat) ................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• B. Akun Outlet dengan Kode Sales .................................................................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 4</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• C. Akun Outlet / Pembeli Umum Tanpa Kode Sales (eCommerce) .................................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 5</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">1.3 Prioritas Pencarian Gudang Terdekat (Hierarki Geografis Wilayah) .................................</td>
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
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 6</td>
  </tr>
  <tr>
    <td style="border: none; padding: 3px 0 3px 15px; color: #444;">2.3 Diskon Khusus Role Distributor (B2B Price Level ERP QAD) ...........................................</td>
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
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• B. Alur Order Outlet dengan Kode Sales (Jubelio, Hub Regional, Zoho) ........................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 10</td>
  </tr>
  <tr>
    <td style="border: none; padding: 2px 0 2px 25px; color: #666;">• C. Alur Order eCommerce & Retail Umum (Jubelio, Ekspedisi API, Zoho) ......................</td>
    <td style="border: none; padding: 2px 0; text-align: right; color: #666;">Hal. 11</td>
  </tr>
</table>

<div style="page-break-after: always;"></div>

## BAB I: ALUR SUMBER PENGIRIMAN GUDANG & LAYANAN EKSPEDISI

### 1. Aturan Khusus Nilai Transaksi (Prioritas Utama)
Sistem menerapkan satu aturan mutlak berprioritas tertinggi yang mengabaikan tipe pengguna apa pun:
- **Kondisi:** Jika **Total Belanja (Subtotal) ≥ Rp 25.000.000**.
- **Sumber Pengiriman:** Otomatis dialokasikan dan dikirim dari Gudang Utama Pusat, yaitu **DC MM2100**.

---

### 2. Aturan Berdasarkan Tipe Pengguna (Role)
Apabila total belanja bernilai kurang dari Rp 25.000.000, lokasi asal pengiriman dan opsi layanan pengiriman ditentukan berdasarkan peran akun pengguna:

#### A. Distributor
- **Kondisi:** Pesanan dilakukan oleh akun terverifikasi dengan role **Distributor**.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: Distributor**.
- **Opsi Layanan Pengiriman Resmi:**
  - **Diantar Ketempat**
  - **Pengambilan Ditempat**
- **Tujuan Aliran Data Pesanan:** Diteruskan ke sistem **ERP QAD** dan **WMS** *(tidak dialirkan ke Jubelio)*.
- **Daftar Gudang Aktif:**
  - **DC MM2100** (Kode: `WHMPRG001`)
  - **DC Surabaya** (Kode: `WHSBY001`)
  - **DC Medan** (Kode: `WHMDN01`)

#### B. Outlet (Dengan Kode Sales)
- **Kondisi:** Pesanan dilakukan oleh akun bertipe **Outlet** dan menyertakan **Kode Sales** representatif lapangan.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: Outlet**.
- **Opsi Layanan Pengiriman Resmi:**
  - **Lion Parcel**
  - **Pengambilan Ditempat**
  - **Diantar Ketempat** *(Khusus alamat tujuan di wilayah Jabodetabek)*
- **Tujuan Aliran Data Pesanan:** Diteruskan ke sistem **Jubelio Omnichannel** dan **WMS** *(tidak dialirkan ke QAD)*.
- **Daftar Gudang Aktif:**
  - **Hub Bekasi** (Kode: `WHRG002`)
  - **Hub Surabaya Tidar** (Kode: `WHMPSBY`)
  - **Hub Maggiore** (Kode: `WHMPRG003`)

#### C. Outlet / Pembeli Umum (Tanpa Kode Sales / eCommerce Biasa)
- **Kondisi:** Pesanan dilakukan oleh akun **Outlet** atau **Pembeli Umum**, dan **TIDAK mencantumkan Kode Sales**.
- **Gudang Sumber:** Sistem memilihkan gudang terdekat dari daftar gudang **Target Role: eCommerce**.
- **Opsi Layanan Pengiriman Resmi:**
  - Semua layanan ekspedisi umum aktif via integrasi sistem (misal: **JNE**, **Lalamove**)
  - **Diantar Ketempat**
  - *(Opsi Pengambilan Ditempat **TIDAK** tersedia)*
- **Tujuan Aliran Data Pesanan:** Diteruskan ke sistem **Jubelio Omnichannel** dan **WMS** *(tidak dialirkan ke QAD)*.
- **Daftar Gudang Aktif:**
  - **Ecommarce Tomang** (Kode: `WHECOMM1`)
  - **Hub Surabaya Tidar** (Kode: `WHMPSBY`)

---

### 3. Prioritas Pencarian Gudang Terdekat (Hierarki Geografis Wilayah)
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
Akumulasi kuantitas produk dalam satu grup dikonversikan ke satuan karton standar industri (1 karton = 6 pcs):
- **Jumlah Karton** = Total botol/pack per grup dibagi 6, lalu dibulatkan ke bawah.
- Contoh: Pembelian 8 botol Syrup dihitung sebagai **1 karton** (sisa 2 botol di bawah 1 karton tidak dihitung untuk tier karton berikutnya).

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
Akun bertipe **Distributor** menggunakan struktur harga net B2B khusus yang terhubung langsung dengan ERP QAD:
- Distributor tidak menggunakan skema diskon berjenjang retail di atas.
- Harga produk mengacu pada master penetapan harga (*Price Level*) dan/atau diskon kategori distributor resmi di ERP QAD.
- Selisih antara harga katalog sebelum PPN dengan harga net distributor ditampilkan sebagai **Diskon Distributor** pada rincian ringkasan belanja.

---

### 4. Diskon Ongkos Kirim (Shipping Discount)
- **Ekspedisi JNE:** Pesanan yang memilih pengiriman JNE otomatis memperoleh potongan biaya kirim sebesar **20%** dari tarif normal ekspedisi.

---

### 5. Urutan Perhitungan Total Pembayaran Saat Checkout
Alur perhitungan nilai akhir pesanan dari awal hingga pembayaran adalah:
1. **Subtotal Produk:** Total harga satuan produk dikalikan kuantitas masing-masing item.
2. **Potongan Diskon:** Total diskon berjenjang (atau diskon distributor) dikurangkan dari subtotal produk.
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

*Diagram 1: Alur Pemesanan dan Integrasi Sistem (Jalur Distributor & Outlet melalui MWS/QAD vs Jalur E-commerce melalui Jubelio).*

<div style="page-break-after: always;"></div>

### 2. Diagram Flowchart Penentuan Sumber Gudang Pengiriman

Berikut adalah diagram alur logika penentuan gudang pengiriman berdasarkan nilai belanja, peran akun, dan kedekatan wilayah:

![Diagram Alur Penentuan Gudang Pengiriman](./untitled%20folder/WhatsApp%20Image%202026-10-09%20at%2008.58.37.jpeg)

*Diagram 2: Flowchart Logika Penentuan 1 Gudang Pengiriman.*

<div style="page-break-after: always;"></div>

### 3. Penjelasan Rinci Alur Distribusi Data

#### A. Alur Order Distributor (B2B)
1. **Penerimaan Pesanan:** Pesanan diproses melalui portal B2B dengan harga net distributor yang telah disinkronkan dari penetapan harga khusus di ERP QAD.
2. **Validasi Batas Kredit (Term of Payment):**
   - Sistem secara otomatis membandingkan nilai total pesanan baru dengan sisa batas kredit piutang akun distributor.
   - Jika total tagihan masih dalam batas kredit yang diizinkan, pesanan langsung disetujui otomatis oleh sistem keuangan. Jika batas kredit terlampaui, pesanan masuk ke antrean persetujuan manual oleh tim Finance.
3. **Penerusan Data ke ERP QAD:**
   - Seluruh rincian pesanan (nomor order, identitas distributor, daftar produk, kuantitas, harga kesepakatan, dan jangka waktu pembayaran) otomatis diteruskan ke sistem ERP QAD.
   - QAD menerbitkan nomor pesanan resmi (Sales Order) dan otomatis memperbarui saldo kredit piutang distributor.
4. **Instruksi Pengambilan Barang di Gudang (DC):**
   - Data dialokasikan ke Distribution Center terdekat (DC MM2100, DC Surabaya, atau DC Medan).
   - Petugas gudang menyiapkan barang dengan ketentuan ketat masa simpan produk (*shelf-life* minimal 9 bulan sebelum tanggal kedaluwarsa).
5. **Pencatatan Akuntansi di Zoho Books:**
   - Faktur penjualan resmi dicatat ke Zoho Books untuk keperluan rekonsiliasi keuangan dan pelaporan faktur pajak.
6. **Layanan Pengiriman:**
   - Pengiriman dilakukan menggunakan layanan resmi **Diantar Ketempat** langsung ke gudang tujuan distributor, atau disiapkan di area pemuatan untuk **Pengambilan Ditempat**.
7. **Notifikasi Otomatis:**
   - Sistem mengirimkan pesan WhatsApp dan email kepada kontak penanggung jawab distributor berisi konfirmasi pesanan dan rincian barang.

#### B. Alur Order Outlet (Dengan Kode Sales)
1. **Penerimaan Pesanan:** Pesanan dilakukan oleh mitra outlet dengan menyertakan kode sales lapangan. Skema diskon berjenjang kategori produk (Syrup/Sauce hingga 10%, Powder hingga 6%) otomatis diterapkan.
2. **Pembayaran Digital:** Pembayaran diselesaikan melalui Virtual Account perbankan atau QRIS. Setelah dana diterima, sistem otomatis mengonfirmasi status pesanan menjadi lunas.
3. **Penerusan Data ke Jubelio Omnichannel:**
   - Data pesanan dikirimkan ke sistem Jubelio Omnichannel untuk mengunci dan memotong persediaan fisik pada Hub Regional yang dituju (Hub Bekasi, Surabaya Tidar, atau Maggiore).
4. **Fulfillment Gudang Regional:**
   - Petugas Hub Regional melakukan pengambilan barang dari rak, pengecekan mutu, dan pengemasan sesuai standar operasional.
5. **Layanan Pengiriman:**
   - Paket diserahkan ke layanan logistik pilihan pelanggan: Lion Parcel, layanan **Diantar Ketempat** untuk area Jabodetabek, atau **Pengambilan Ditempat** langsung di Hub.
6. **Pencatatan Faktur & Notifikasi:**
   - Data transaksi dicatat di Zoho Books sebagai penjualan lunas, dan nomor resi pelacakan dikirimkan secara otomatis via WhatsApp dan email ke nomor outlet.

#### C. Alur Order eCommerce & Retail Umum (Konsumen Langsung & Reseller)
1. **Penerimaan Pesanan:** Pembelian langsung oleh konsumen akhir atau reseller melalui toko online, lengkap dengan promo harga coret aktif dan akumulasi poin loyalitas.
2. **Pembayaran Instan:** Pembayaran diproses seketika melalui gateway pembayaran Faspay (QRIS / Virtual Account) dengan konfirmasi lunas otomatis dalam hitungan detik.
3. **Penerusan Data ke Jubelio Omnichannel:**
   - Data transaksi diteruskan ke Jubelio untuk memotong stok pada Hub eCommerce (Tomang atau Surabaya Tidar) sekaligus menjaga sinkronisasi stok di seluruh kanal penjualan.
4. **Penerusan Data ke Layanan Ekspedisi Terintegrasi:**
   - **JNE Express:** Sistem secara otomatis memanggil layanan JNE untuk menerbitkan nomor resi resmi (*AWB / Cnote*) dengan fasilitas potongan ongkos kirim sebesar 20%.
   - **Lalamove:** Untuk opsi pengiriman di hari yang sama (*same day* / instan), sistem secara otomatis memanggil pengemudi Lalamove ke lokasi hub pengirim.
   - **Diantar Ketempat:** Pengiriman khusus armada internal pengantaran langsung bila dipilih pelanggan.
5. **Pencatatan Faktur & Notifikasi:**
   - Transaksi tercatat rapi di Zoho Books.
   - Pembeli otomatis menerima pesan WhatsApp dan email berisi konfirmasi pesanan lunas serta tautan pelacakan pengiriman kurir.
