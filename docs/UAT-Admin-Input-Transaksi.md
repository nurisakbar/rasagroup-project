# UAT Input Transaksi Admin

| | |
| --- | --- |
| Kode | UAT-ORD-ADM-001 |
| Halaman | Admin → Input Transaksi |
| Tanggal | 25 September 2026 |
| Tujuan | Memastikan admin bisa membuat pesanan dengan benar: batch, harga diskon, kirim ke QAD/WMS, dan status pengiriman kembali dari WMS |

---

## Yang diuji

Pesanan yang dibuat **admin** (bukan checkout pembeli).

**Tidak diuji:** belanja di website, kasir POS, pembayaran Faspay.

---

## Persiapan singkat

- Login sebagai admin.
- Siapkan 1 pelanggan **distributor** (sudah ada diskon kategori) dan 1 **outlet**.
- Siapkan 1 produk yang stok batch-nya ada di hub.
- Pilih hub yang kodenya diawali **FG** (contoh Bekasi FG008) untuk uji QAD & WMS.
- Pastikan antrian background (`queue:work`) berjalan.

**Cara hitung harga (catatan untuk tester)**  
Harga katalog sudah termasuk pajak. Pajak dikeluarkan dulu, baru diskon kategori.  
Contoh: katalog 111.000, pajak 11%, diskon 20% → harga jual **80.000**.

---

## Skenario uji (7 kasus)

Isi kolom Hasil saat menjalankan: **Lulus** / **Tidak lulus**.

### 1. Admin bisa membuat pesanan lengkap

**Langkah:** Buka Input Transaksi → pilih pelanggan, alamat, hub, ekspedisi, produk, batch, pembayaran tunai → simpan.

**Harus terjadi:** Pesanan tersimpan, muncul di detail, ada nomor transaksi. Produk tidak bisa dipilih sebelum pelanggan dipilih. Batch wajib.

Hasil: __________ &nbsp;&nbsp; Catatan: __________

---

### 2. Batch sesuai stok di hub

**Langkah:** Pilih hub dan produk. Lihat daftar batch. Pilih satu lot. Simpan. Buka detail pesanan.

**Harus terjadi:** Yang muncul hanya batch produk itu di hub tersebut (nomor lot, stok, expired). Lot tercatat di detail pesanan. Tidak boleh simpan tanpa batch. Qty tidak boleh melebihi stok lot.

Hasil: __________ &nbsp;&nbsp; Catatan: __________

---

### 3. Harga distributor mengikuti diskon kategori

**Langkah:** Cek tab Diskon Kategori distributor (catat persennya). Buat pesanan untuk pelanggan itu. Bandingkan harga di form, detail, dan kwitansi.

**Harus terjadi:** Harga jual = rumus di atas, **bukan** harga katalog. Footer: subtotal, diskon, pajak, ongkir, total. Pajak dihitung setelah diskon.

Hasil: __________ &nbsp;&nbsp; Harga jual: __________

---

### 4. Harga outlet

**Langkah:** Ulangi pesanan yang sama untuk pelanggan outlet.

**Harus terjadi:** Jika outlet punya diskon kategori, hitungan sama seperti distributor. Jika tidak, harga = katalog. Catat perbedaan yang tidak sesuai harapan bisnis.

Hasil: __________ &nbsp;&nbsp; Harga jual: __________

---

### 5. Pesanan lunas dari hub FG masuk QAD

**Langkah:** Pesanan tunai (otomatis lunas) dari hub kode **FG…**. Tunggu beberapa saat, buka detail.

**Harus terjadi:** Pesanan terkirim ke QAD. Harga di QAD = harga jual (sudah diskon), bukan harga katalog. Jika tidak ada kode sales, sales person di QAD kosong.

Hasil: __________ &nbsp;&nbsp; Nomor SO QAD: __________

---

### 6. WMS mendapat notifikasi ada order masuk

**Langkah:** Setelah kasus 5, cek panel WMS di detail pesanan **dan** dashboard WMS.

**Harus terjadi:** WMS menerima sales order baru (nomor sama, produk, qty, dan batch yang dipilih). Status WMS di admin terisi (berhasil / antre), bukan gagal tanpa alasan.

Hasil: __________ &nbsp;&nbsp; Status WMS: __________ &nbsp;&nbsp; Terlihat di WMS: Ya / Tidak

---

### 7. WMS update status pengiriman ke sistem kita

**Langkah:** Setelah SO ada di WMS, proses pengiriman di WMS (atau minta WMS menandai sudah dikirim). Refresh detail pesanan admin.

**Harus terjadi:** Status pesanan di sistem menjadi **dikirim**, tanggal kirim terisi, pelanggan melihat status yang sama. Tidak perlu admin mengubah status secara manual.

Hasil: __________ &nbsp;&nbsp; Tanggal kirim: __________

---

## Catatan routing hub (cukup diingat, bukan kasus terpisah)

- Kode lokasi diawali **FG** → ke QAD.  
- Selain itu → ke Jubelio.  
- Yang belum lunas (transfer belum ditandai bayar) **belum** dikirim ke QAD/WMS.

---

## Keputusan

| | |
| --- | --- |
| Kesimpulan | ☐ Lulus &nbsp; ☐ Lulus dengan catatan &nbsp; ☐ Tidak lulus |
| Catatan | |
| Tester | Nama / tanggal |
| Disetujui | Nama / tanggal |
