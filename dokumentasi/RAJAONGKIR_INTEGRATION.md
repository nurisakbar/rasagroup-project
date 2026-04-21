# Catatan Teknis Integrasi RajaOngkir (Komerce)

Dokumentasi ini merangkum teknis integrasi RajaOngkir melalui endpoint Komerce berdasarkan tautan yang diberikan.

## Konfigurasi
- **Base URL**: `https://rajaongkir.komerce.id/api/v1`
- **Header**:
    - `Key`: `{YOUR_API_KEY}`
    - `Content-Type`: `application/x-www-form-urlencoded` (untuk POST)

---

## 1. Get Provinsi
Digunakan untuk mendapatkan daftar provinsi di Indonesia.
- **Endpoint**: `GET /destination/province`
- **Penjelasan**: Level pertama hirarki lokasi.
- **Contoh Request**:
  ```bash
  curl --location 'https://rajaongkir.komerce.id/api/v1/destination/province' \
  --header 'Key: YOUR_API_KEY'
  ```
- **Struktur Response**:
  ```json
  {
      "meta": { "message": "Success Get Province", "code": 200, "status": "success" },
      "data": [
          { "id": 1, "name": "NUSA TENGGARA BARAT (NTB)" },
          ...
      ]
  }
  ```

## 2. Get Kota (City)
Digunakan untuk mendapatkan daftar kota berdasarkan ID Provinsi.
- **Endpoint**: `GET /destination/city/{province_id}`
- **Penjelasan**: Level kedua hirarki lokasi.
- **Contoh Request**:
  ```bash
  curl --location 'https://rajaongkir.komerce.id/api/v1/destination/city/12' \
  --header 'Key: YOUR_API_KEY'
  ```

## 3. Get Kecamatan (District)
Digunakan untuk mendapatkan daftar kecamatan berdasarkan ID Kota.
- **Endpoint**: `GET /destination/district/{city_id}`
- **Penjelasan**: Level ketiga hirarki lokasi. Diperlukan untuk kalkulasi ongkir yang lebih akurat.
- **Contoh Request**:
  ```bash
  curl --location 'https://rajaongkir.komerce.id/api/v1/destination/district/575' \
  --header 'Key: YOUR_API_KEY'
  ```

## 4. Kalkulasi Ongkir (Calculate Cost)
Menghitung biaya pengiriman antar kecamatan.
- **Endpoint**: `POST /calculate/district/domestic-cost`
- **Method**: `POST`
- **Body Params (x-www-form-urlencoded)**:
    - `origin` (int): ID Kecamatan asal.
    - `destination` (int): ID Kecamatan tujuan.
    - `weight` (int): Berat paket dalam gram.
    - `courier` (string): Kode kurir (pisahkan dengan titik dua `:` untuk multiple).
      Contoh: `jne:sicepat:jnt:pos`
    - `price` (string, optional): Gunakan `lowest` untuk mendapatkan harga termurah.

- **Contoh Request**:
  ```bash
  curl --location 'https://rajaongkir.komerce.id/api/v1/calculate/district/domestic-cost' \
  --header 'Key: YOUR_API_KEY' \
  --header 'Content-Type: application/x-www-form-urlencoded' \
  --data-urlencode 'origin=1391' \
  --data-urlencode 'destination=1376' \
  --data-urlencode 'weight=1000' \
  --data-urlencode 'courier=jne:sicepat:jnt'
  ```

---

## Alur Implementasi (Checkout)
1. User memilih Provinsi (Fetch via API).
2. User memilih Kota berdasarkan Provinsi (Fetch via API).
3. User memilih Kecamatan berdasarkan Kota (Fetch via API).
4. Sistem menghitung total berat produk di keranjang.
5. Panggil API `calculate-cost` menggunakan `origin` (Kecamatan Toko/Gudang) dan `destination` (Kecamatan User).
6. Tampilkan pilihan layanan kurir dan biaya kepada User.

---

## Persiapan Database & Model
Untuk mendukung kalkulasi ongkir ke tingkat kecamatan:
1. **Tabel `warehouses`**: Perlu ditambahkan kolom `district_id` untuk menentukan lokasi asal (*origin*) pengiriman.
2. **Tabel `addresses`**: Sudah memiliki kolom `district_id`, pastikan ID yang disimpan sesuai dengan ID dari API RajaOngkir Komerce.
3. **Konfigurasi API Key**: Tambahkan `RAJAONGKIR_KEY` di file `.env`.

