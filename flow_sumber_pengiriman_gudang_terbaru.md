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
- Jika produk memiliki potongan promo langsung dari harga aslinya, harga satuan yang masuk ke keranjang belanja otomatis menggunakan harga promo aktif.
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

---

## BAGIAN III: DIAGRAM ARSITEKTUR & DISTRIBUSI DATA SISTEM

Bagian ini memetakan bagaimana data pesanan dari platform **RasaConnect** didistribusikan ke berbagai sistem terintegrasi (ERP, Omnichannel, Pembayaran, Logistik, dan Notifikasi) berdasarkan tipe pesanan dan peran pengguna (role).

### 1. Diagram Alur Batching & Sales Order

Berikut adalah diagram alur resmi integrasi pemesanan (*Flow Batching & Sales Order*) pada platform RasaConnect:

![Diagram Alur Batching & Sales Order](./untitled%20folder/WhatsApp%20Image%202026-10-07%20at%2007.41.09.jpeg)

*Diagram 1: Alur Pemesanan dan Integrasi Sistem (Jalur Distributor & Outlet melalui MWS/QAD vs Jalur E-commerce melalui Jubelio).*

---

### 2. Diagram Flowchart Penentuan Sumber Gudang Pengiriman

Berikut adalah diagram alur logika penentuan gudang pengiriman berdasarkan nilai belanja, peran akun, dan kedekatan wilayah:

![Diagram Alur Penentuan Gudang Pengiriman](./untitled%20folder/WhatsApp%20Image%202026-10-09%20at%2008.58.37.jpeg)

*Diagram 2: Flowchart Logika Penentuan 1 Gudang Pengiriman.*

---

### 3. Matriks Komparasi Integrasi Sistem Berdasarkan Role

Tabel berikut merangkum ke mana saja data dialirkan untuk masing-masing tipe pesanan:

| Sistem Terintegrasi | Order Distributor (B2B) | Order Outlet (Mitra Sales) | Order eCommerce & Retail Umum |
| :--- | :--- | :--- | :--- |
| **QAD ERP** | **Ya** (Menerima Sales Order B2B & pembaruan batas piutang) | **Tidak** | **Tidak** |
| **Jubelio Omnichannel** | **Tidak** | **Ya** (Menerima Sales Order & alokasi stok Hub) | **Ya** (Menerima Sales Order & alokasi stok Hub) |
| **Zoho Books** | **Ya** (Pencatatan Sales Order & Faktur Penjualan B2B) | **Ya** (Pencatatan Faktur Penjualan & Pembayaran) | **Ya** (Pencatatan Faktur Penjualan & Pembayaran) |
| **Faspay Gateway** | Opsional (Jika pembayaran tunai digital) | **Ya** (Virtual Account BCA/Mandiri/BRI & QRIS) | **Ya** (Virtual Account BCA/Mandiri/BRI & QRIS) |
| **Gudang Pemenuhan** | DC MM2100, DC Surabaya, DC Medan (Gudang Kode FG) | Hub Bekasi, Hub Surabaya Tidar, Hub Maggiore | Hub Tomang, Hub Surabaya Tidar |
| **Pilihan Ekspedisi** | Armada Truk DC & Pengambilan Mandiri (*Self Pickup*) | Lion Parcel, Kurir Toko Jabodetabek, Self Pickup | JNE Express (Diskon 20%), Lalamove Instant, Kurir Toko |
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
6. **Pengiriman Logistik:**
   - Pengiriman dilakukan menggunakan armada truk internal DC langsung ke gudang tujuan distributor, atau disiapkan di area pemuatan untuk diambil mandiri (*Self Pickup*).
7. **Notifikasi Otomatis:**
   - Sistem mengirimkan pesan WhatsApp dan email kepada kontak penanggung jawab distributor berisi konfirmasi pesanan dan rincian barang.

#### B. Alur Order Outlet (Dengan Kode Sales)
1. **Penerimaan Pesanan:** Pesanan dilakukan oleh mitra outlet dengan menyertakan kode sales lapangan. Skema diskon berjenjang kategori produk (Syrup/Sauce hingga 10%, Powder hingga 6%) otomatis diterapkan.
2. **Pembayaran Digital:** Pembayaran diselesaikan melalui Virtual Account perbankan atau QRIS. Setelah dana diterima, sistem otomatis mengonfirmasi status pesanan menjadi lunas.
3. **Penerusan Data ke Jubelio Omnichannel:**
   - Data pesanan dikirimkan ke sistem Jubelio Omnichannel untuk mengunci dan memotong persediaan fisik pada Hub Regional yang dituju (Hub Bekasi, Surabaya Tidar, atau Maggiore).
4. **Fulfillment Gudang Regional:**
   - Petugas Hub Regional melakukan pengambilan barang dari rak, pengecekan mutu, dan pengemasan sesuai standar operasional.
5. **Pengiriman Logistik:**
   - Paket diserahkan ke layanan logistik pilihan pelanggan: Lion Parcel, Kurir Toko untuk area Jabodetabek, atau diambil langsung di Hub (*Self Pickup*).
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
5. **Pencatatan Faktur & Notifikasi:**
   - Transaksi tercatat rapi di Zoho Books.
   - Pembeli otomatis menerima pesan WhatsApp dan email berisi konfirmasi pesanan lunas serta tautan pelacakan pengiriman kurir.

