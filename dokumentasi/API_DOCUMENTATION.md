# Dokumentasi API - Rasa Group Project

## Daftar Isi
1. [Informasi Umum](#informasi-umum)
2. [Autentikasi](#autentikasi)
3. [Base URL](#base-url)
4. [Format Response](#format-response)
5. [Error Handling](#error-handling)
6. [Endpoints](#endpoints)
   - [Warehouses](#warehouses)
   - [Cart](#cart)
   - [Addresses](#addresses)
   - [Orders](#orders)

---

## Informasi Umum

API ini menggunakan RESTful architecture dengan format response JSON. Semua endpoint mengembalikan response dalam format JSON.

**Versi API:** v1  
**Content-Type:** `application/json`  
**Accept:** `application/json`

---

## Autentikasi

API ini menggunakan Laravel session-based authentication untuk endpoint yang memerlukan autentikasi. Untuk mengakses endpoint yang protected, user harus sudah login terlebih dahulu melalui web interface.

**Catatan:** Jika menggunakan Laravel Sanctum untuk token-based authentication, tambahkan header berikut:
```
Authorization: Bearer {token}
```

Untuk endpoint yang tidak memerlukan autentikasi, tidak perlu mengirimkan credentials.

---

## Base URL

```
http://localhost/api
```

Atau sesuaikan dengan konfigurasi `APP_URL` di file `.env`:
```
{APP_URL}/api
```

---

## Format Response

### Success Response
Semua response sukses mengikuti format berikut:
```json
{
  "success": true,
  "data": {},
  "message": "Optional success message"
}
```

### Error Response
Semua response error mengikuti format berikut:
```json
{
  "success": false,
  "message": "Error message description"
}
```

### Pagination Response
Untuk endpoint yang mendukung pagination:
```json
{
  "success": true,
  "data": [],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

---

## Error Handling

### HTTP Status Codes

- `200 OK` - Request berhasil
- `201 Created` - Resource berhasil dibuat
- `400 Bad Request` - Request tidak valid atau data tidak lengkap
- `401 Unauthorized` - Tidak terautentikasi
- `404 Not Found` - Resource tidak ditemukan
- `500 Internal Server Error` - Kesalahan server

### Validation Errors
Jika terjadi validation error, response akan mengembalikan status `422` dengan format:
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "field_name": ["Error message"]
  }
}
```

---

## Endpoints

## Warehouses

### 1. Get List of Warehouses
Mendapatkan daftar warehouse yang tersedia.

**Endpoint:** `GET /api/warehouses`

**Authentication:** Tidak diperlukan

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `province_id` | integer | No | Filter berdasarkan ID provinsi |
| `regency_id` | integer | No | Filter berdasarkan ID kabupaten/kota |
| `is_active` | boolean | No | Filter berdasarkan status aktif (true/false) |
| `search` | string | No | Pencarian berdasarkan nama warehouse |
| `per_page` | integer | No | Jumlah item per halaman (default: 15) |

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Warehouse Jakarta",
      "address": "Jl. Contoh No. 123",
      "phone": "021-12345678",
      "description": "Warehouse utama di Jakarta",
      "is_active": true,
      "province": {
        "id": 31,
        "name": "DKI Jakarta"
      },
      "regency": {
        "id": 3174,
        "name": "Jakarta Selatan"
      },
      "full_location": "Jakarta Selatan, DKI Jakarta",
      "products_count": 150,
      "stocks_sum_stock": 5000
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### 2. Get Warehouse Products
Mendapatkan daftar produk dan stok di warehouse tertentu.

**Endpoint:** `GET /api/warehouses/{warehouse}/products`

**Authentication:** Tidak diperlukan

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `warehouse` | integer | Yes | ID warehouse |

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `product_status` | string | No | Status produk (default: 'active') |
| `search` | string | No | Pencarian berdasarkan nama/kode produk |
| `brand_id` | integer | No | Filter berdasarkan ID brand |
| `category_id` | integer | No | Filter berdasarkan ID kategori |
| `stock_available` | string/boolean | No | Filter stok tersedia ('yes'/'no' atau true/false) |
| `sort_by` | string | No | Sort by: 'product_name', 'stock', 'product_code', 'price' (default: 'product_name') |
| `sort_order` | string | No | Sort order: 'asc' atau 'desc' (default: 'asc') |
| `per_page` | integer | No | Jumlah item per halaman (default: 15) |

**Response Example:**
```json
{
  "success": true,
  "warehouse": {
    "id": 1,
    "name": "Warehouse Jakarta",
    "address": "Jl. Contoh No. 123",
    "phone": "021-12345678",
    "description": "Warehouse utama di Jakarta",
    "is_active": true,
    "province": {
      "id": 31,
      "name": "DKI Jakarta"
    },
    "regency": {
      "id": 3174,
      "name": "Jakarta Selatan"
    },
    "full_location": "Jakarta Selatan, DKI Jakarta"
  },
  "data": [
    {
      "id": 1,
      "code": "PRD001",
      "name": "Produk Contoh",
      "commercial_name": "Commercial Name",
      "description": "Deskripsi produk",
      "price": 50000.00,
      "unit": "pcs",
      "size": "500ml",
      "weight": 500,
      "image": "http://localhost/images/products/product.jpg",
      "image_path": "products/product.jpg",
      "status": "active",
      "brand": {
        "id": 1,
        "name": "Brand Name"
      },
      "category": {
        "id": 1,
        "name": "Category Name"
      },
      "stock": 100,
      "stock_id": 1
    }
  ],
  "meta": {
    "current_page": 1,
    "last_page": 10,
    "per_page": 15,
    "total": 150
  }
}
```

**Error Response (404):**
```json
{
  "success": false,
  "message": "Warehouse not found"
}
```

---

## Cart

Semua endpoint Cart memerlukan autentikasi.

### 1. Get Cart Items
Mendapatkan semua item di keranjang user.

**Endpoint:** `GET /api/cart`

**Authentication:** Required

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "product": {
        "id": 1,
        "code": "PRD001",
        "name": "Produk Contoh",
        "price": 50000.00,
        "unit": "pcs",
        "image": "http://localhost/images/products/product.jpg",
        "brand": {
          "id": 1,
          "name": "Brand Name"
        },
        "category": {
          "id": 1,
          "name": "Category Name"
        }
      },
      "warehouse": {
        "id": 1,
        "name": "Warehouse Jakarta"
      },
      "quantity": 2,
      "subtotal": 100000.00
    }
  ],
  "summary": {
    "total_items": 5,
    "total_products": 3,
    "subtotal": 250000.00
  }
}
```

---

### 2. Add Product to Cart
Menambahkan produk ke keranjang.

**Endpoint:** `POST /api/cart`

**Authentication:** Required

**Request Body:**
```json
{
  "product_id": 1,
  "warehouse_id": 1,
  "quantity": 2
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `product_id` | integer | Yes | ID produk |
| `warehouse_id` | integer | Yes | ID warehouse |
| `quantity` | integer | Yes | Jumlah produk (min: 1) |

**Response Example (201):**
```json
{
  "success": true,
  "message": "Produk berhasil ditambahkan ke keranjang.",
  "data": {
    "id": 1,
    "product": {
      "id": 1,
      "name": "Produk Contoh",
      "price": 50000.00
    },
    "warehouse": {
      "id": 1,
      "name": "Warehouse Jakarta"
    },
    "quantity": 2,
    "subtotal": 100000.00
  }
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Stock tidak mencukupi. Tersedia: 5 unit."
}
```

atau

```json
{
  "success": false,
  "message": "Keranjang Anda memiliki produk dari hub lain (Warehouse Bandung). Kosongkan keranjang terlebih dahulu atau pilih hub yang sama."
}
```

---

### 3. Update Cart Item
Mengupdate jumlah item di keranjang.

**Endpoint:** `PUT /api/cart/{id}`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID cart item |

**Request Body:**
```json
{
  "quantity": 3
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `quantity` | integer | Yes | Jumlah baru (min: 1) |

**Response Example:**
```json
{
  "success": true,
  "message": "Keranjang berhasil diperbarui.",
  "data": {
    "id": 1,
    "quantity": 3,
    "subtotal": 150000.00
  }
}
```

---

### 4. Remove Cart Item
Menghapus item dari keranjang.

**Endpoint:** `DELETE /api/cart/{id}`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID cart item |

**Response Example:**
```json
{
  "success": true,
  "message": "Item berhasil dihapus dari keranjang."
}
```

---

### 5. Clear Cart
Mengosongkan semua item di keranjang.

**Endpoint:** `DELETE /api/cart`

**Authentication:** Required

**Response Example:**
```json
{
  "success": true,
  "message": "Keranjang berhasil dikosongkan."
}
```

---

## Addresses

Semua endpoint Addresses memerlukan autentikasi.

### 1. Get User Addresses
Mendapatkan daftar alamat user.

**Endpoint:** `GET /api/addresses`

**Authentication:** Required

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "label": "Rumah",
      "recipient_name": "John Doe",
      "phone": "081234567890",
      "address_detail": "Jl. Contoh No. 123",
      "postal_code": "12345",
      "notes": "Rumah di belakang",
      "is_default": true,
      "province": {
        "id": 31,
        "name": "DKI Jakarta"
      },
      "regency": {
        "id": 3174,
        "name": "Jakarta Selatan"
      },
      "district": {
        "id": 317401,
        "name": "Kebayoran Baru"
      },
      "village": {
        "id": 3174011001,
        "name": "Senayan"
      },
      "full_address": "Jl. Contoh No. 123, Senayan, Kec. Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12345"
    }
  ]
}
```

---

### 2. Create Address
Membuat alamat baru.

**Endpoint:** `POST /api/addresses`

**Authentication:** Required

**Request Body:**
```json
{
  "label": "Rumah",
  "recipient_name": "John Doe",
  "phone": "081234567890",
  "province_id": 31,
  "regency_id": 3174,
  "district_id": 317401,
  "village_id": 3174011001,
  "address_detail": "Jl. Contoh No. 123",
  "postal_code": "12345",
  "notes": "Rumah di belakang",
  "is_default": true
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `label` | string | Yes | Label alamat (max: 50) |
| `recipient_name` | string | Yes | Nama penerima (max: 255) |
| `phone` | string | Yes | Nomor telepon (max: 20) |
| `province_id` | integer | Yes | ID provinsi |
| `regency_id` | integer | Yes | ID kabupaten/kota |
| `district_id` | integer | Yes | ID kecamatan |
| `village_id` | integer | Yes | ID kelurahan |
| `address_detail` | string | Yes | Detail alamat |
| `postal_code` | string | No | Kode pos (max: 10) |
| `notes` | string | No | Catatan (max: 500) |
| `is_default` | boolean | No | Set sebagai alamat default |

**Response Example (201):**
```json
{
  "success": true,
  "message": "Alamat berhasil ditambahkan.",
  "data": {
    "id": 1,
    "label": "Rumah",
    "recipient_name": "John Doe",
    "phone": "081234567890",
    "address_detail": "Jl. Contoh No. 123",
    "postal_code": "12345",
    "notes": "Rumah di belakang",
    "is_default": true,
    "province": {
      "id": 31,
      "name": "DKI Jakarta"
    },
    "regency": {
      "id": 3174,
      "name": "Jakarta Selatan"
    },
    "district": {
      "id": 317401,
      "name": "Kebayoran Baru"
    },
    "village": {
      "id": 3174011001,
      "name": "Senayan"
    },
    "full_address": "Jl. Contoh No. 123, Senayan, Kec. Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12345"
  }
}
```

---

### 3. Update Address
Mengupdate alamat yang sudah ada.

**Endpoint:** `PUT /api/addresses/{id}`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID alamat |

**Request Body:** (sama dengan Create Address)

**Response Example:**
```json
{
  "success": true,
  "message": "Alamat berhasil diperbarui.",
  "data": {
    "id": 1,
    "label": "Rumah",
    "recipient_name": "John Doe",
    "phone": "081234567890",
    "address_detail": "Jl. Contoh No. 123",
    "postal_code": "12345",
    "notes": "Rumah di belakang",
    "is_default": true,
    "province": {
      "id": 31,
      "name": "DKI Jakarta"
    },
    "regency": {
      "id": 3174,
      "name": "Jakarta Selatan"
    },
    "district": {
      "id": 317401,
      "name": "Kebayoran Baru"
    },
    "village": {
      "id": 3174011001,
      "name": "Senayan"
    },
    "full_address": "Jl. Contoh No. 123, Senayan, Kec. Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12345"
  }
}
```

---

### 4. Delete Address
Menghapus alamat.

**Endpoint:** `DELETE /api/addresses/{id}`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID alamat |

**Response Example:**
```json
{
  "success": true,
  "message": "Alamat berhasil dihapus."
}
```

---

### 5. Get Provinces
Mendapatkan daftar provinsi (helper untuk form alamat).

**Endpoint:** `GET /api/addresses/provinces`

**Authentication:** Required

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 31,
      "name": "DKI Jakarta"
    },
    {
      "id": 32,
      "name": "Jawa Barat"
    }
  ]
}
```

---

### 6. Get Regencies
Mendapatkan daftar kabupaten/kota berdasarkan provinsi.

**Endpoint:** `GET /api/addresses/regencies`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `province_id` | integer | Yes | ID provinsi |

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3174,
      "name": "Jakarta Selatan"
    },
    {
      "id": 3171,
      "name": "Jakarta Pusat"
    }
  ]
}
```

---

### 7. Get Districts
Mendapatkan daftar kecamatan berdasarkan kabupaten/kota.

**Endpoint:** `GET /api/addresses/districts`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `regency_id` | integer | Yes | ID kabupaten/kota |

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 317401,
      "name": "Kebayoran Baru"
    },
    {
      "id": 317402,
      "name": "Kebayoran Lama"
    }
  ]
}
```

---

### 8. Get Villages
Mendapatkan daftar kelurahan berdasarkan kecamatan.

**Endpoint:** `GET /api/addresses/villages`

**Authentication:** Required

**Query Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `district_id` | integer | Yes | ID kecamatan |

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 3174011001,
      "name": "Senayan"
    },
    {
      "id": 3174011002,
      "name": "Gunung"
    }
  ]
}
```

---

## Orders

Semua endpoint Orders memerlukan autentikasi.

### 1. Get Expeditions
Mendapatkan daftar ekspedisi yang tersedia.

**Endpoint:** `GET /api/orders/expeditions`

**Authentication:** Required

**Response Example:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "code": "jne",
      "name": "JNE",
      "logo": "jne.png",
      "description": "Jalur Nugraha Ekakurir",
      "base_cost": 10000,
      "est_days_min": 1,
      "est_days_max": 3
    },
    {
      "id": 2,
      "code": "tiki",
      "name": "TIKI",
      "logo": "tiki.png",
      "description": "Titipan Kilat",
      "base_cost": 12000,
      "est_days_min": 2,
      "est_days_max": 4
    }
  ]
}
```

---

### 2. Get Expedition Services
Mendapatkan daftar layanan ekspedisi beserta perhitungan ongkos kirim.

**Endpoint:** `POST /api/orders/expeditions/services`

**Authentication:** Required

**Request Body:**
```json
{
  "expedition_id": 1,
  "address_id": 1
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `expedition_id` | integer | Yes | ID ekspedisi |
| `address_id` | integer | Yes | ID alamat pengiriman |

**Response Example:**
```json
{
  "success": true,
  "expedition": {
    "id": 1,
    "code": "jne",
    "name": "JNE"
  },
  "services": [
    {
      "code": "REG",
      "name": "REG (Regular)",
      "cost": 15000,
      "cost_formatted": "Rp 15.000",
      "estimated_days": "1-3 hari"
    },
    {
      "code": "OKE",
      "name": "OKE (Ongkos Kirim Ekonomis)",
      "cost": 12000,
      "cost_formatted": "Rp 12.000",
      "estimated_days": "3-5 hari"
    }
  ],
  "total_weight": 2000,
  "total_weight_formatted": "2.0 kg"
}
```

**Error Response (400):**
```json
{
  "success": false,
  "message": "Keranjang kosong."
}
```

---

### 3. Create Order
Membuat pesanan baru.

**Endpoint:** `POST /api/orders`

**Authentication:** Required

**Request Body:**
```json
{
  "address_id": 1,
  "expedition_id": 1,
  "expedition_service": "REG",
  "payment_method": "xendit",
  "notes": "Tolong dibungkus dengan rapi"
}
```

**Request Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `address_id` | integer | Yes | ID alamat pengiriman |
| `expedition_id` | integer | Yes | ID ekspedisi |
| `expedition_service` | string | Yes | Kode layanan ekspedisi (contoh: 'REG', 'OKE') |
| `payment_method` | string | Yes | Metode pembayaran: 'xendit' atau 'manual_transfer' |
| `notes` | string | No | Catatan pesanan (max: 500) |

**Response Example (201):**
```json
{
  "success": true,
  "message": "Pesanan berhasil dibuat.",
  "data": {
    "id": 1,
    "order_number": "ORD-20240101-0001",
    "subtotal": 250000.00,
    "shipping_cost": 15000.00,
    "total_amount": 265000.00,
    "payment_method": "xendit",
    "payment_status": "pending",
    "order_status": "pending",
    "xendit_invoice_url": "https://checkout.xendit.co/web/...",
    "xendit_invoice_id": "inv_1234567890",
    "items": [
      {
        "product": {
          "id": 1,
          "name": "Produk Contoh",
          "code": "PRD001"
        },
        "quantity": 2,
        "price": 50000.00,
        "subtotal": 100000.00
      }
    ]
  }
}
```

**Error Responses:**

**Keranjang kosong (400):**
```json
{
  "success": false,
  "message": "Keranjang kosong."
}
```

**Stock tidak mencukupi (400):**
```json
{
  "success": false,
  "message": "Stock tidak mencukupi di hub Warehouse Jakarta:\nProduk Contoh: dipesan 10, tersedia 5"
}
```

**Gagal membuat invoice (500):**
```json
{
  "success": false,
  "message": "Gagal membuat invoice pembayaran. Silakan coba lagi atau pilih metode pembayaran lain."
}
```

---

### 4. Get Order Details
Mendapatkan detail pesanan.

**Endpoint:** `GET /api/orders/{id}`

**Authentication:** Required

**Path Parameters:**
| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `id` | integer | Yes | ID pesanan |

**Response Example:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_number": "ORD-20240101-0001",
    "order_type": "regular",
    "subtotal": 250000.00,
    "shipping_cost": 15000.00,
    "total_amount": 265000.00,
    "payment_method": "xendit",
    "payment_status": "pending",
    "order_status": "pending",
    "xendit_invoice_url": "https://checkout.xendit.co/web/...",
    "xendit_invoice_id": "inv_1234567890",
    "notes": "Tolong dibungkus dengan rapi",
    "points_earned": 0,
    "created_at": "2024-01-01T10:00:00.000000Z",
    "address": {
      "id": 1,
      "label": "Rumah",
      "recipient_name": "John Doe",
      "phone": "081234567890",
      "full_address": "Jl. Contoh No. 123, Senayan, Kec. Kebayoran Baru, Jakarta Selatan, DKI Jakarta 12345"
    },
    "expedition": {
      "id": 1,
      "name": "JNE",
      "service": "REG"
    },
    "warehouse": {
      "id": 1,
      "name": "Warehouse Jakarta"
    },
    "items": [
      {
        "id": 1,
        "product": {
          "id": 1,
          "code": "PRD001",
          "name": "Produk Contoh",
          "price": 50000.00,
          "image": "http://localhost/images/products/product.jpg"
        },
        "quantity": 2,
        "price": 50000.00,
        "subtotal": 100000.00
      }
    ]
  }
}
```

---

## Contoh Penggunaan

### Menggunakan cURL

**Get Warehouses:**
```bash
curl -X GET "http://localhost/api/warehouses" \
  -H "Accept: application/json"
```

**Add to Cart (dengan session cookie):**
```bash
curl -X POST "http://localhost/api/cart" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=your_session_token" \
  -d '{
    "product_id": 1,
    "warehouse_id": 1,
    "quantity": 2
  }'
```

**Create Order:**
```bash
curl -X POST "http://localhost/api/orders" \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -H "Cookie: laravel_session=your_session_token" \
  -d '{
    "address_id": 1,
    "expedition_id": 1,
    "expedition_service": "REG",
    "payment_method": "xendit",
    "notes": "Tolong dibungkus dengan rapi"
  }'
```

### Menggunakan JavaScript (Fetch API)

```javascript
// Get warehouses
fetch('http://localhost/api/warehouses', {
  method: 'GET',
  headers: {
    'Accept': 'application/json',
  },
  credentials: 'include' // Include cookies for session
})
.then(response => response.json())
.then(data => console.log(data));

// Add to cart
fetch('http://localhost/api/cart', {
  method: 'POST',
  headers: {
    'Accept': 'application/json',
    'Content-Type': 'application/json',
  },
  credentials: 'include',
  body: JSON.stringify({
    product_id: 1,
    warehouse_id: 1,
    quantity: 2
  })
})
.then(response => response.json())
.then(data => console.log(data));
```

---

## Catatan Penting

1. **Session Authentication**: API ini menggunakan session-based authentication. Pastikan untuk mengirimkan session cookie dalam setiap request yang memerlukan autentikasi.

2. **CORS**: Jika menggunakan API dari frontend yang berbeda domain, pastikan CORS sudah dikonfigurasi dengan benar di Laravel.

3. **Rate Limiting**: Beberapa endpoint mungkin memiliki rate limiting. Jika terjadi error 429 (Too Many Requests), tunggu beberapa saat sebelum mencoba lagi.

4. **Stock Validation**: Sistem akan memvalidasi stok sebelum menambahkan ke cart dan sebelum membuat order. Pastikan stok tersedia sebelum melakukan operasi tersebut.

5. **Warehouse Consistency**: Semua produk di cart harus berasal dari warehouse yang sama. Jika menambahkan produk dari warehouse berbeda, sistem akan menolak dan meminta untuk mengosongkan cart terlebih dahulu.

6. **Xendit Payment**: Untuk payment method 'xendit', sistem akan membuat invoice melalui Xendit dan mengembalikan URL pembayaran. User harus menyelesaikan pembayaran melalui URL tersebut.

7. **Order Number Format**: Format order number adalah `ORD-YYYYMMDD-XXXX` dimana XXXX adalah nomor urut 4 digit.

---

## Changelog

### Version 1.0.0 (2024-01-01)
- Initial API documentation
- Endpoints: Warehouses, Cart, Addresses, Orders
- Session-based authentication

---

## Support

Untuk pertanyaan atau bantuan lebih lanjut, silakan hubungi tim development.

