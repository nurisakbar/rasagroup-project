# API Pencatatan Penjualan Distributor

Panduan bagi tim IT distributor untuk mengirim data penjualan dari sistem Anda ke RasaConnect.

Data yang dikirim **hanya dicatat untuk laporan**. Pengiriman ini tidak membuat pesanan ke Rasa, tidak memotong stok Rasa, dan tidak memengaruhi tagihan Anda. Untuk memesan barang, tetap gunakan RasaConnect.

## Autentikasi

Minta API key ke admin Rasa, lalu kirim di setiap request:

```
X-API-KEY: rdk_xxxxxxxxxxxx
Content-Type: application/json
```

Key hanya ditampilkan sekali dan mewakili satu distributor, jadi simpan di server dan jangan dibagikan. Jika key bocor, minta admin mencabutnya.

Base URL: `https://<domain-rasaconnect>/api/v1/distributor`

Batas request: 120 per menit per key.

## Kirim atau perbarui penjualan

`POST /sales`

```json
{
  "external_order_number": "SO-ERP-2026-00123",
  "sold_at": "2026-11-07T10:30:00+07:00",
  "status": "confirmed",
  "customer": { "code": "CUST-881", "name": "Toko Maju Jaya" },
  "items": [
    { "product_code": "RSA001", "product_name": "Rasa Sirup 1L", "quantity": 10, "uom": "CTN", "unit_price": 150000 }
  ],
  "discount": 0,
  "tax": 165000,
  "total": 1665000
}
```

| Field | Wajib | Keterangan |
|---|---|---|
| `external_order_number` | Ya | Nomor order unik di sistem Anda, maksimal 100 karakter |
| `sold_at` | Ya | Waktu transaksi. Tanpa zona waktu dianggap WIB. Tanggal lampau diperbolehkan |
| `status` | Tidak | `confirmed` (default) atau `cancelled` |
| `customer.name` | Ya | Nama pelanggan Anda |
| `customer.code` | Tidak | Kode pelanggan di sistem Anda |
| `items` | Ya | 1 sampai 500 baris |
| `items[].product_code` | Ya | Kode produk, sebaiknya kode Rasa dari `GET /products` |
| `items[].product_name` | Tidak | Nama produk di sistem Anda |
| `items[].quantity` | Ya | Lebih dari 0 |
| `items[].uom` | Tidak | Satuan, misalnya `CTN` |
| `items[].unit_price` | Ya | Harga jual per satuan |
| `discount` | Tidak | Diskon total order, default 0 |
| `tax` | Tidak | Pajak total order, default 0 |
| `total` | Ya | Harus sama dengan jumlah `quantity × unit_price` − `discount` + `tax`, dengan toleransi Rp1 |

Jika harga sudah termasuk pajak, kirim `tax` 0.

**Mengirim ulang aman.** Nomor yang sama akan memperbarui data lama dan menggantikan semua itemnya, bukan membuat data ganda. Untuk membatalkan, kirim ulang order dengan `"status": "cancelled"`.

Respons `201` (baru) atau `200` (diperbarui):

```json
{
  "success": true,
  "message": "Penjualan berhasil dicatat.",
  "data": {
    "external_order_number": "SO-ERP-2026-00123",
    "status": "confirmed",
    "sold_date": "2026-11-07",
    "total": 1665000,
    "items_count": 1,
    "unmapped_product_codes": []
  }
}
```

`unmapped_product_codes` berisi kode yang tidak dikenali RasaConnect. Datanya tetap tersimpan, tetapi kode tersebut sebaiknya disesuaikan dengan `GET /products`.

## Cek data

`GET /sales/{external_order_number}` mengembalikan data order yang sudah diterima, hanya untuk distributor pemilik key.

## Data master: produk, kategori, brand

Ketiganya hanya untuk dibaca (`GET`) dan memakai `X-API-KEY` yang sama. Hanya produk aktif yang ditampilkan. Harga tidak disertakan karena bergantung pada level harga masing-masing customer.

### `GET /products`

Daftar produk aktif beserta kode, kode alternatif, nama, satuan, brand, dan kategori. Gunakan `code` (atau salah satu `alternate_codes`) sebagai `items[].product_code` saat mengirim penjualan.

Parameter query (semua opsional):

| Parameter | Keterangan |
|---|---|
| `brand` | `id` atau `slug` brand, mis. `?brand=dripp` |
| `category` | `id` atau `slug` kategori, mis. `?category=syrup` |
| `search` | Cari di nama atau kode produk |

```json
{
  "success": true,
  "data": [
    {
      "code": "FDA010-BN01",
      "alternate_codes": [],
      "name": "DRiPP Banana Syrup",
      "unit": "BTL",
      "large_unit": "CTN",
      "units_per_large": 6,
      "brand": { "id": "019f94da-...", "name": "DRiPP", "slug": "dripp" },
      "category": { "id": "019f94da-...", "name": "Syrup", "slug": "syrup" }
    }
  ]
}
```

`units_per_large` adalah isi satu satuan besar (mis. 1 CTN = 6 BTL), `null` bila produk tidak punya satuan besar.

### `GET /categories`

Daftar kategori aktif beserta jumlah produk aktifnya.

```json
{ "success": true, "data": [ { "id": "019f94da-...", "name": "Syrup", "slug": "syrup", "products_count": 42 } ] }
```

### `GET /brands`

Daftar brand aktif beserta jumlah produk aktifnya.

```json
{ "success": true, "data": [ { "id": "019f94da-...", "name": "DRiPP", "slug": "dripp", "products_count": 120 } ] }
```

## Kode error

| HTTP | Arti | Tindakan |
|---|---|---|
| `401` | Header `X-API-KEY` tidak ada | Tambahkan header |
| `403` | Key salah atau sudah dicabut | Hubungi admin Rasa |
| `404` | Order tidak ditemukan | Periksa nomor order |
| `422` | Data tidak valid, detail ada di field `errors` | Perbaiki data, jangan dikirim ulang apa adanya |
| `429` | Terlalu banyak request | Tunggu, lalu kirim ulang |
| `5xx` | Gangguan di server | Kirim ulang beberapa saat kemudian |

Contoh `422`:

```json
{
  "message": "Total tidak cocok dengan item, diskon, dan pajak. Hasil hitung: 1665000.00",
  "errors": { "total": ["Total tidak cocok dengan item, diskon, dan pajak. Hasil hitung: 1665000.00"] }
}
```
