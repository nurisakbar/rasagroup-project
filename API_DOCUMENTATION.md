# Dokumentasi API Rasa Group

Dokumen ini berisi daftar endpoint API yang tersedia pada project Rasa Group, khususnya yang terdefinisi pada `routes/api.php`. Seluruh endpoint menggunakan prefix `/api/`.

> **Catatan Autentikasi:** 
> Untuk endpoint yang membutuhkan identitas user (seperti Cart, Addresses, Orders), Anda mungkin perlu menyertakan header `X-Token` (atau `Authorization: Bearer <token>`) sesuai konfigurasi sistem Anda.

---

## 1. Warehouses (Gudang/Hub)
Endpoint ini digunakan untuk mengelola data gudang dan produk yang ada di dalamnya.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/warehouses` | Mengambil daftar gudang (hub) yang tersedia. |
| `GET` | `/api/warehouses/all-with-products` | Mengambil semua daftar gudang beserta relasi produk di dalamnya. |
| `GET` | `/api/warehouses/{warehouse}/products` | Mengambil daftar produk berdasarkan ID atau spesifik gudang (hub) tertentu. |

---

## 2. Products
Endpoint ini bersifat publik dan dapat digunakan (misalnya untuk chatbot knowledge base) dalam menampilkan produk.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/products` | Mengambil daftar produk dengan pagination/pencarian. |
| `GET` | `/api/products/all` | Mengambil seluruh daftar produk tanpa pagination. |

---

## 3. Users
Endpoint untuk data pengguna. Beberapa endpoint mungkin memerlukan parameter `user_id` apabila tidak ada session auth aktif.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/users/current` | Mengambil data pengguna yang sedang login saat ini. |
| `GET` | `/api/users/search` | Mencari data pengguna spesifik. |

---

## 4. Cart (Keranjang Belanja)
Manajemen keranjang belanja pengguna.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/cart` | Mengambil daftar item di keranjang belanja. |
| `POST` | `/api/cart` | Menambahkan item baru ke keranjang belanja. |
| `PUT` | `/api/cart/{id}` | Mengubah data item di keranjang (misal: qty). |
| `DELETE` | `/api/cart/{id}` | Menghapus spesifik item dari keranjang belanja. |
| `DELETE` | `/api/cart` | Mengosongkan keranjang belanja (Clear cart). |

### Contoh Request & Response

#### POST /api/cart
**Request (cURL):**
```bash
curl -X POST https://dev.rasaconnect.com/api/cart \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "product_id": "9b12a81c-6d34-4069-95e2-63b7e23b2c15",
  "warehouse_id": "8a09f3b1-4c12-4f3a-a19e-1d5e381b8e11",
  "quantity": 2,
  "uom": "base"
}'
```
**Response (201 Created):**
```json
{
  "success": true,
  "message": "Produk \"Kopi Arabica 1Kg\" berhasil ditambahkan.",
  "data": {
    "id": "1e453fa8-1f6b-4e83-8a3b-2524a10c9d91",
    "product": {
      "id": "9b12a81c-6d34-4069-95e2-63b7e23b2c15",
      "name": "Kopi Arabica 1Kg",
      "price": 150000
    },
    "warehouse": {
      "id": "8a09f3b1-4c12-4f3a-a19e-1d5e381b8e11",
      "name": "Hub Utama Jakarta"
    },
    "quantity": 2,
    "order_uom": "base",
    "quantity_ordered": 2,
    "subtotal": 300000
  }
}
```

#### PUT /api/cart/{id}
**Request (cURL):**
```bash
curl -X PUT https://dev.rasaconnect.com/api/cart/1e453fa8-1f6b-4e83-8a3b-2524a10c9d91 \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "quantity": 5
}'
```
**Response (200 OK):**
```json
{
  "success": true,
  "message": "Keranjang berhasil diperbarui.",
  "data": {
    "id": "1e453fa8-1f6b-4e83-8a3b-2524a10c9d91",
    "quantity": 5,
    "order_uom": "base",
    "quantity_ordered": 5,
    "subtotal": 750000
  }
}
```

---

## 5. Addresses (Alamat Pengguna)
Manajemen alamat dan helper data wilayah Indonesia.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/addresses` | Mengambil daftar alamat milik user. |
| `POST` | `/api/addresses` | Menyimpan alamat baru untuk user. |
| `PUT` | `/api/addresses/{id}` | Memperbarui data alamat yang sudah ada. |
| `DELETE` | `/api/addresses/{id}` | Menghapus data alamat. |
| `GET` | `/api/addresses/provinces` | Helper: Mengambil daftar provinsi. |
| `GET` | `/api/addresses/regencies` | Helper: Mengambil daftar kabupaten/kota. |
| `GET` | `/api/addresses/districts` | Helper: Mengambil daftar kecamatan. |
| `GET` | `/api/addresses/villages` | Helper: Mengambil daftar kelurahan/desa. |

### Contoh Request & Response

#### POST /api/addresses
**Request (cURL):**
```bash
curl -X POST https://dev.rasaconnect.com/api/addresses \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "label": "Rumah",
  "recipient_name": "Budi Santoso",
  "phone": "081234567890",
  "province_id": "31",
  "regency_id": "3171",
  "district_id": "317101",
  "address_detail": "Jalan Kebon Jeruk Raya No 12",
  "postal_code": "11530",
  "notes": "Pagar warna hitam",
  "is_default": true
}'
```
**Response (201 Created):**
```json
{
  "success": true,
  "message": "Alamat berhasil disimpan.",
  "data": {
    "id": "2d9c1f6b-8e1f-4a92-b43d-9f8a71c3d2e5",
    "label": "Rumah",
    "recipient_name": "Budi Santoso",
    "phone": "081234567890",
    "full_address": "Jalan Kebon Jeruk Raya No 12, Kebon Jeruk, Jakarta Barat, DKI Jakarta, 11530"
  }
}
```

---

## 6. Orders (Pesanan)
Manajemen pembuatan hingga pelacakan status pesanan.

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/orders` | Mengambil daftar seluruh pesanan. |
| `GET` | `/api/orders/expeditions` | Mengambil daftar layanan ekspedisi yang tersedia. |
| `POST` | `/api/orders/expeditions/services` | Menghitung biaya layanan ekspedisi. |
| `POST` | `/api/orders/finance-approval` | Menyetujui atau membatalkan approval (finance) untuk pesanan Term of Payment. |
| `POST` | `/api/orders` | Membuat pesanan baru (Checkout). |
| `GET` | `/api/orders/{id}` | Mengambil detail sebuah pesanan. |
| `PUT` | `/api/orders/{id}/status` | Mengupdate status pengiriman (`order_status`). |

### Contoh Request & Response

#### POST /api/orders/expeditions/services
**Request (cURL):**
```bash
curl -X POST https://dev.rasaconnect.com/api/orders/expeditions/services \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "expedition_id": "6a9f1b2c-8d3e-4b71-a42c-7f9e8d1b3a5c",
  "address_id": "2d9c1f6b-8e1f-4a92-b43d-9f8a71c3d2e5"
}'
```

#### POST /api/orders
**Request (cURL):**
```bash
curl -X POST https://dev.rasaconnect.com/api/orders \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "address_id": "2d9c1f6b-8e1f-4a92-b43d-9f8a71c3d2e5",
  "expedition_id": "6a9f1b2c-8d3e-4b71-a42c-7f9e8d1b3a5c",
  "expedition_service": "REG",
  "payment_method": "faspay",
  "notes": "Tolong dipacking kayu"
}'
```
**Response (201 Created):**
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat.",
  "data": {
    "id": "01a07c49-9576-708a-960d-6b38ab3955b2",
    "order_number": "ORD-202609-00105",
    "subtotal": 300000,
    "shipping_cost": 25000,
    "total_amount": 325000,
    "payment_method": "faspay",
    "payment_status": "pending",
    "order_status": "pending",
    "faspay_bill_no": "8234850934",
    "faspay_invoice_url": "https://faspay.co.id/redirect/...",
    "items": [
      {
        "product": {
          "id": "9b12a81c-6d34-4069-95e2-63b7e23b2c15",
          "name": "Kopi Arabica 1Kg",
          "code": "KP-ARB-1"
        },
        "quantity": 2,
        "price": 150000,
        "subtotal": 300000
      }
    ]
  }
}
```

#### PUT /api/orders/{id}/status
**Request (cURL):**
```bash
curl -X PUT https://dev.rasaconnect.com/api/orders/01a07c49-9576-708a-960d-6b38ab3955b2/status \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "order_status": "shipped" 
}'
```
*(Nilai `order_status` yang tersedia: `pending`, `processing`, `shipped`, `delivered`, `completed`, `cancelled`)*

**Response (200 OK):**
```json
{
  "success": true,
  "message": "Status pengiriman berhasil diperbarui.",
  "data": {
    "id": "01a07c49-9576-708a-960d-6b38ab3955b2",
    "order_number": "ORD-202609-00105",
    "order_status": "shipped",
    "shipped_at": "2026-09-15T10:00:00.000000Z",
    "received_at": null
  }
}
```

#### POST /api/orders/finance-approval
**Request (cURL):**
```bash
curl -X POST https://dev.rasaconnect.com/api/orders/finance-approval \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "order_id": "01a07c49-9576-708a-960d-6b38ab3955b2", 
  "finance_approved": 1
}'
```
**Response (200 OK):**
```json
{
  "success": true,
  "message": "Term of Payment telah disetujui.",
  "data": {
    "order_id": "01a07c49-9576-708a-960d-6b38ab3955b2",
    "order_number": "ORD-202609-00105",
    "finance_approved": 1,
    "finance_approved_at": "2026-09-15 10:30:00",
    "finance_approved_by": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
    "payment_method": "term_of_payment",
    "payment_status": "term_of_payment"
  }
}
```

---

## 7. Ongkir (Ongkos Kirim Tambahan)
| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/ongkir` | Mengambil data atau perhitungan ongkos Kirim. |

---

## 8. Webhooks
Endpoint yang disediakan untuk menerima callback/webhook dari service eksternal (contoh: Meta/WhatsApp).

| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `GET` | `/api/webhooks/meta` | Verifikasi webhook Meta (Token challenge). |
| `POST` | `/api/webhooks/meta` | Menerima payload/event dari Meta (WhatsApp). |

---

## 9. Faspay & SNAP BI
Endpoint untuk integrasi pembayaran Faspay dan standar protokol SNAP BI V1.0.

### Faspay Base (Prefix: `/api/faspay`)
| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `POST` | `/api/faspay/snap/inquiry` | Faspay Snap Inquiry VA. |
| `POST` | `/api/faspay/snap/payment` | Faspay Snap Payment VA. |
| `POST` | `/api/faspay/payment-inquiry` | Faspay standar Payment Inquiry. |
| `POST` | `/api/faspay/payment-notification` | Faspay standar Payment Notification. |
| `POST` | `/api/faspay/v1.0/transfer-va/inquiry` | Faspay SNAP V1.0 VA Inquiry. |
| `POST` | `/api/faspay/v1.0/transfer-va/payment` | Faspay SNAP V1.0 VA Payment. |

### Standar SNAP BI V1.0 (Prefix: `/api/v1.0`)
| Method | Endpoint | Deskripsi |
| --- | --- | --- |
| `POST` | `/api/v1.0/transfer-va/inquiry` | SNAP V1.0 Virtual Account Inquiry. |
| `POST` | `/api/v1.0/transfer-va/payment` | SNAP V1.0 Virtual Account Payment. |
| `POST` | `/api/v1.0/qr/qr-mpm-notify` | SNAP V1.0 Notifikasi pembayaran QRIS (MPM). |
| `POST` | `/api/v1.0/debit/notify` | SNAP V1.0 Notifikasi Direct Debit. |
