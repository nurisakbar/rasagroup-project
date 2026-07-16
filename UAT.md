# Laporan UAT (User Acceptance Testing) - Integrasi Ekspedisi

Dokumen ini berisi hasil pengujian integrasi API untuk seluruh layanan ekspedisi yang didukung pada sistem. Pengujian mencakup fitur Cek Ongkir (Rates), Booking Resi (Generate AWB / Pickup), dan Pelacakan (Tracking).

---

## 1. JNE
- **Cek Ongkir (Rates):** ✅ **BERHASIL**
  *Sistem berhasil mendapatkan daftar layanan (REG, YES, JTR, dll.) beserta harga dengan akurat.*
- **Booking AWB (Generate Cnote):** ✅ **BERHASIL**
  *Sistem berhasil membuat resi secara langsung ke API JNE (cnote berhasil diterbitkan melalui payload test).*
- **Tracking (Lacak Resi):** ✅ **BERHASIL**
  *Sistem mampu membaca status resi pengiriman maupun menangani nomor resi yang tidak valid tanpa error.*
- **Status Akhir:** 🟢 **Siap digunakan (Production Ready)**

---

## 2. Lion Parcel
- **Cek Ongkir (Rates):** ✅ **BERHASIL**
  *Tarif berhasil ditarik melalui middleware Lion Parcel API (layanan ONEPACK, REGPACK, dll. sukses ditampilkan).*
- **Booking AWB / Tracking:** ✅ **BERHASIL**
  *Setelah penyesuaian simulasi pada testing, sistem berhasil mengirimkan instruksi pembuatan resi (Shipment Creation) dan mendapatkan respon nomor `lion_shipment_id` dari server Lion Parcel.*
- **Status Akhir:** 🟢 **Siap digunakan (Production Ready)**

---

## 3. Sicepat Ekspres
- **Cek Ongkir (Rates):** ✅ **BERHASIL**
  *Tarif sukses ditarik ke API sicepat (layanan HALU, GOKIL, REG sukses ditampilkan).*
- **Tracking (Lacak Resi):** ✅ **BERHASIL**
  *Pengecekan nomor resi dapat dilakukan dengan baik.*
- **Request Pickup / Booking:** ❌ **GAGAL (403 Forbidden)**
  *Request diblokir oleh server Sicepat (mengembalikan response Nginx 403 Forbidden). Ini umumnya mengindikasikan IP sistem belum di-whitelist, atau Auth/API key yang digunakan untuk endpoint tersebut salah/bermasalah.*
- **Status Akhir:** 🔴 **Perlu melakukan pengecekan credentials (API Key) dan melakukan whitelist IP pada sisi Sicepat.**

---

## 4. Lalamove
- **Cek Ongkir (Rates):** ✅ **BERHASIL**
  *Sistem sukses berintegrasi mendapatkan Quotation ID, tipe layanan (MOTORCYCLE), serta harga sesuai jarak berdasarkan koordinat wilayah origin dan destination.*
- **Status Akhir:** 🟢 **Siap digunakan untuk pengecekan tarif (On-demand/Instant Delivery).**

---

## 5. J&T Express
- **Cek Ongkir (Rates):** ❌ **BELUM TERSEDIA (501 Not Implemented)**
  *Sistem secara eksplisit membalikkan pesan "Ongkir untuk kurir ini belum tersedia" pada response API.*
- **Status Akhir:** ⚪ **Fitur belum diimplementasikan / credentials belum lengkap.**

---

**Kesimpulan:**
- **JNE** dan **Lalamove** menjadi provider dengan integrasi tarif/resi paling stabil saat ini.
- Untuk **Sicepat**, diperlukan perbaikan/penyesuaian *credential* atau *whitelist IP* agar fitur *Booking / Request Pickup* dapat berjalan.
- Integrasi lanjutan seperti J&T Express masih memerlukan *development* lebih lanjut.
