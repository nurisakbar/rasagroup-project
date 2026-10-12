# LAPORAN STANDAR OPERASIONAL & ARSITEKTUR SISTEM

**PT MULTI CITRA RASA (RASA GROUP)**  
*Platform E-Commerce & Marketplace Terintegrasi — RasaConnect*

---

## PANDUAN ALUR SUMBER PENGIRIMAN GUDANG, SKEMA PERHITUNGAN DISKON, DAN DISTRIBUSI DATA PEMESANAN

| Informasi Dokumen | Keterangan |
| :--- | :--- |
| **Nomor Dokumen** | SOP-RC-LOG-2026-001 |
| **Judul Dokumen** | Alur Sumber Pengiriman Gudang, Perhitungan Diskon, dan Distribusi Data Sistem |
| **Versi / Edisi** | Versi 2.0 (Resmi) |
| **Tanggal Efektif** | Oktober 2026 |
| **Status Dokumen** | Berlaku / Disetujui |
| **Divisi Penyusun** | Departemen Supply Chain, Logistik & Teknologi Informasi |
| **Klasifikasi** | Dokumen Internal Resmi (Confidential) |

<div style="page-break-after: always;"></div>

## RINGKASAN EKSEKUTIF

Dokumen standar operasional ini disusun sebagai acuan resmi bagi manajemen, divisi penjualan (*sales*), operasional pergudangan, rantai pasok (*supply chain*), dan keuangan dalam memahami tata kelola pemrosesan pesanan di platform **RasaConnect**. Dokumen ini mencakup 3 (tiga) pilar utama sistem:

1. **Alur Sumber Pengiriman Gudang:** Mekanisme penentuan lokasi asal pengiriman barang (Distribution Center atau Hub Regional) serta pilihan layanan ekspedisi berdasarkan total nilai transaksi dan peran akun (*Distributor*, *Outlet*, atau *eCommerce/Retail*).
2. **Skema Perhitungan Diskon Pembelian:** Tata cara kalkulasi potongan harga bertingkat, mulai dari diskon level produk, diskon berjenjang kategori produk (Syrup/Sauce dan Powder/Pulp), diskon khusus distributor B2B berbasis ERP QAD, diskon ongkos kirim ekspedisi JNE sebesar 20%, hingga urutan penentuan total pembayaran akhir (termasuk DPP, PPN, dan biaya pembayaran).
3. **Arsitektur Sistem & Distribusi Data:** Pemetaan integrasi aliran data secara otomatis antara platform RasaConnect dengan sistem pendukung utama, yaitu ERP QAD, Jubelio Omnichannel, Zoho Books, Faspay Payment Gateway, Manajemen Pergudangan (WMS), serta gateway notifikasi WhatsApp dan Email.

## DAFTAR ISI

| Bagian Dokumen | Topik & Pokok Bahasan |
| :--- | :--- |
| **BAGIAN I: Alur Sumber Pengiriman Gudang & Layanan Ekspedisi** | • Aturan Khusus Transaksi ($\ge$ Rp 25 Juta)<br/>• Aturan Pemilihan Gudang per Role (Distributor, Outlet, eCommerce)<br/>• Prioritas Pencarian Gudang Terdekat (Hierarki Geografis Wilayah) |
| **BAGIAN II: Skema Perhitungan Diskon Ketika Pembelian** | • Diskon Level Produk & Promo Katalog<br/>• Diskon Berjenjang Kategori Produk (Syrup/Sauce & Powder/Pulp)<br/>• Diskon Khusus Role Distributor (B2B Price Level ERP QAD)<br/>• Diskon Ongkos Kirim Ekspedisi JNE 20%<br/>• Urutan Perhitungan Total Pembayaran Saat Checkout |
| **BAGIAN III: Diagram Arsitektur & Distribusi Data Sistem** | • Diagram Alur Batching & Integrasi Sales Order (Gambar Resmi)<br/>• Diagram Flowchart Penentuan Sumber Gudang Pengiriman (Gambar Resmi)<br/>• Matriks Komparasi Integrasi Sistem Berdasarkan Role<br/>• Penjelasan Rinci Alur Distribusi Data per Saluran |

<div style="page-break-after: always;"></div>

## BAGIAN I: ALUR SUMBER PENGIRIMAN GUDANG & LAYANAN EKSPEDISI

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

---

## BAGIAN II: SKEMA PERHITUNGAN DISKON KETIKA PEMBELIAN

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

---

## BAGIAN III: DIAGRAM ARSITEKTUR & DISTRIBUSI DATA SISTEM

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

### 3. Matriks Komparasi Integrasi Sistem Berdasarkan Role

Tabel berikut merangkum ke mana saja data dialirkan untuk masing-masing tipe pesanan:

| Sistem Terintegrasi | Order Distributor (B2B) | Order Outlet (Mitra Sales) | Order eCommerce & Retail Umum |
| :--- | :--- | :--- | :--- |
| **QAD ERP** | **Ya** (Menerima Sales Order B2B & pembaruan batas piutang) | **Tidak** | **Tidak** |
| **Jubelio Omnichannel** | **Tidak** | **Ya** (Menerima Sales Order & alokasi stok Hub) | **Ya** (Menerima Sales Order & alokasi stok Hub) |
| **Zoho Books** | **Ya** (Pencatatan Sales Order & Faktur Penjualan B2B) | **Ya** (Pencatatan Faktur Penjualan & Pembayaran) | **Ya** (Pencatatan Faktur Penjualan & Pembayaran) |
| **Faspay Gateway** | Opsional (Jika pembayaran tunai digital) | **Ya** (Virtual Account BCA/Mandiri/BRI & QRIS) | **Ya** (Virtual Account BCA/Mandiri/BRI & QRIS) |
| **Gudang Pemenuhan** | DC MM2100, DC Surabaya, DC Medan (Gudang Kode FG) | Hub Bekasi, Hub Surabaya Tidar, Hub Maggiore | Hub Tomang, Hub Surabaya Tidar |
| **Pilihan Ekspedisi** | Diantar Ketempat & Pengambilan Ditempat | Lion Parcel, Diantar Ketempat (Jabodetabek), Pengambilan Ditempat | JNE Express (Diskon 20%), Lalamove Instant, Diantar Ketempat |
| **Notifikasi Pelanggan** | WhatsApp Gateway & Email Konfirmasi Order | WhatsApp Gateway & Email Notifikasi | WhatsApp Gateway & Email Notifikasi |

---

### 4. Penjelasan Rinci Alur Distribusi Data

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
