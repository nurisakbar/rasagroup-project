# Panduan Integrasi API Faspay QRIS & Webhook Relay
**PT Rasa Distribusi Indonesia (Rasa Group)**  
Versi Dokumen: `1.0.0`  
Tanggal Pembaruan: `11 Oktober 2026`  
Base URL: `https://rasaconnect.com`

---

## 1. Ringkasan & Arsitektur Alur

Layanan ini memungkinkan aplikasi pihak ketiga (3rd Party Application) untuk:
1. **Membuat QRIS Dinamis (Paydia QRIS)** atas nama entitas **PT Rasa Distribusi Indonesia (RDI)** melalui Faspay SNAP BI.
2. Mendapatkan URL gambar QR code langsung (`.png`) yang dapat ditampilkan di aplikasi kasir, web, atau mobile app.
3. **Menerima Webhook Otomatis**: Ketika customer melakukan pembayaran dan Faspay memverifikasi transaksi, server Rasa Group secara *real-time* meneruskan (*relay*) notifikasi sukses ke endpoint webhook milik pihak ketiga.

### Diagram Alur Transaksi:

```
+----------------+              +-----------------------+              +---------------+
| Pihak Ketiga   |              | Server Rasa Connect   |              | Faspay SNAP   |
| (Aplikasi Anda)|              | (API & Webhook Relay) |              | Payment GW    |
+----------------+              +-----------------------+              +---------------+
       |                                    |                                  |
       | 1. POST /api/v1/external/qris/     |                                  |
       |    rdi/generate                    |                                  |
       |    (ref_no, amount, callback_url)  |                                  |
       |----------------------------------->| 2. Generate Standalone QRIS      |
       |                                    |    (SNAP Service RDI)            |
       |                                    |--------------------------------->|
       |                                    |<---------------------------------|
       | 3. Response: QR Image URL (.png)   |    Return QR Image URL           |
       |<-----------------------------------|                                  |
       |                                    |                                  |
       |   [ Customer Memindai QRIS & Melakukan Pembayaran di Mobile Banking ] |
       |                                    |                                  |
       |                                    | 4. Payment Notification (00)     |
       |                                    |<---------------------------------|
       |                                    |    (Faspay memanggil endpoint kita)
       |                                    |                                  |
       | 5. Webhook Relay HTTP POST         | 6. Simpan Status 'paid' & Balas  |
       |    ke callback_url Pihak Ketiga    |    HTTP 200 (Success) ke Faspay  |
       |<-----------------------------------|--------------------------------->|
       |                                    |                                  |
```

---

## 2. Autentikasi API

Setiap panggilan request API dari aplikasi pihak ketiga **wajib** menyertakan header:

| Header Name | Value | Keterangan |
| :--- | :--- | :--- |
| `X-API-KEY` | `rasa_qris_rdi_sec_2026!` | Kunci otentikasi rahasia |
| `Content-Type` | `application/json` | Format request data |
| `Accept` | `application/json` | Format respon yang diinginkan |

---

## 3. Spesifikasi Endpoint API

### 3.1. Generate QRIS Dinamis (Dedicated RDI)
Membuat QRIS baru untuk nominal transaksi tertentu dan mendaftarkan URL webhook pihak ketiga untuk notifikasi otomatis.

- **Method**: `POST`
- **URL**: `https://rasaconnect.com/api/v1/external/qris/rdi/generate`

#### Parameter Request (JSON Body):

| Parameter | Tipe | Wajib | Keterangan |
| :--- | :--- | :---: | :--- |
| `reference_no` | String | **Ya** | ID referensi/invoice unik dari aplikasi pihak ketiga (maks. 64 karakter). |
| `amount` | Numeric | **Ya** | Nominal pembayaran dalam Rupiah (min. 1000). |
| `callback_url` | String (URL) | Opsional | URL webhook pihak ketiga yang akan dihubungi saat pembayaran sukses. |
| `callback_token` | String | Opsional | Token/kunci rahasia pihak ketiga untuk autentikasi header callback. |
| `customer_name` | String | Opsional | Nama customer (default: `"Customer"`). |
| `customer_phone` | String | Opsional | Nomor telepon customer. |
| `description` | String | Opsional | Keterangan transaksi (default: `"Payment #{reference_no}"`). |
| `validity_minutes` | Integer | Opsional | Masa berlaku QR dalam menit (default: `60`, min: 5, max: 1440). |
| `format` | String | Opsional | Format response: `"json"` (default), `"image"` (stream PNG), `"redirect"`. |

#### Contoh Request CURL:
```bash
curl -X POST https://rasaconnect.com/api/v1/external/qris/rdi/generate \
  -H "Content-Type: application/json" \
  -H "X-API-KEY: rasa_qris_rdi_sec_2026!" \
  -d '{
    "reference_no": "INV-APP3-20261011-001",
    "amount": 50000,
    "customer_name": "Ahmad Fauzi",
    "customer_phone": "081234567890",
    "description": "Pembayaran Invoice INV-APP3-20261011-001",
    "callback_url": "https://api.pihakketiga.com/webhook/payment",
    "callback_token": "secret_pihak_ketiga_token_123"
  }'
```

#### Contoh Respon JSON (HTTP 200 OK):
```json
{
  "status": "success",
  "responseCode": "2004700",
  "responseMessage": "QRIS generated successfully",
  "data": {
    "id": 1,
    "partner_reference_no": "INV-APP3-20261011-001",
    "reference_no": "3702083645902822",
    "amount": "50000.00",
    "currency": "IDR",
    "qr_image_url": "https://web.faspay.co.id/__assets/qr/paydia/37020-3702083645902822.png",
    "qr_url": "https://web.faspay.co.id/__assets/qr/paydia/37020-3702083645902822.png",
    "channel_code": "836",
    "company": "rdi",
    "expires_at": "2026-10-11T16:50:00+07:00",
    "callback_url": "https://api.pihakketiga.com/webhook/payment",
    "callback_status": "pending"
  }
}
```

> **Tips Tampilan Gambar:**  
> Anda dapat langsung menggunakan nilai dari field `data.qr_image_url` pada tag HTML `<img src="..." />` atau image viewer aplikasi Anda.

---

### 3.2. Cek Status Pembayaran (Inquiry Status)
Melakukan pengecekan status terkini apakah transaksi sudah dibayar atau belum.

- **Method**: `GET`
- **URL**: `https://rasaconnect.com/api/v1/external/qris/status/{referenceNo}`

#### Contoh Request CURL:
```bash
curl -X GET "https://rasaconnect.com/api/v1/external/qris/status/INV-APP3-20261011-001" \
  -H "X-API-KEY: rasa_qris_rdi_sec_2026!"
```

#### Contoh Respon (Ketika Pembayaran Sudah Lunas):
```json
{
  "status": "success",
  "responseCode": "2005100",
  "responseMessage": "Payment has been completed",
  "data": {
    "reference_no": "INV-APP3-20261011-001",
    "faspay_reference_no": "3702083645902822",
    "amount": 50000,
    "status": "paid",
    "paid_at": "2026-10-11T15:48:32+07:00",
    "callback_url": "https://api.pihakketiga.com/webhook/payment",
    "callback_status": "delivered",
    "callback_sent_at": "2026-10-11T15:48:33+07:00"
  }
}
```

---

### 3.3. Manual Resend / Test Webhook
Endpoint pembantu untuk menguji penerimaan webhook di server pihak ketiga tanpa harus melakukan transaksi pembayaran ulang.

- **Method**: `POST`
- **URL**: `https://rasaconnect.com/api/v1/external/qris/resend-webhook/{referenceNo}`

#### Contoh Request CURL:
```bash
curl -X POST "https://rasaconnect.com/api/v1/external/qris/resend-webhook/INV-APP3-20261011-001" \
  -H "X-API-KEY: rasa_qris_rdi_sec_2026!"
```

---

## 4. Spesifikasi Webhook Pihak Ketiga (Callback Notification)

Ketika Faspay memverifikasi bahwa pembayaran telah berhasil, server Rasa Connect akan langsung mengirimkan request HTTP POST ke `callback_url` yang Anda daftarkan.

### 4.1. Header Request Webhook yang Dikirimkan ke Server Anda:

| Header Name | Nilai Contoh | Keterangan |
| :--- | :--- | :--- |
| `Content-Type` | `application/json` | Format payload JSON |
| `User-Agent` | `RasaGroup-Webhook-Relay/1.0` | Identitas pengirim |
| `X-Event` | `payment.verified` | Tipe event notifikasi |
| `X-Timestamp` | `1791708522` | UNIX Timestamp pengiriman |
| `X-Callback-Token` | `secret_pihak_ketiga_token_123` | Nilai `callback_token` yang Anda daftarkan |
| `X-Signature` | `77b6758b1e13...` | HMAC-SHA256 signature payload |

### 4.2. Cara Verifikasi Signature (Opsional namun Direkomendasikan):
Formula pembuatan signature:
```
signature = hash_hmac('sha256', timestamp + '.' + json_body, callback_token)
```
Server Anda dapat mencocokkan nilai header `X-Signature` dengan formula di atas untuk memastikan notifikasi benar-benar berasal dari Rasa Connect.

### 4.3. Body Payload Webhook (JSON):
```json
{
  "event": "payment.verified",
  "status": "paid",
  "reference_no": "INV-APP3-20261011-001",
  "faspay_reference_no": "3702083645902822",
  "company": "rdi",
  "channel_code": "836",
  "amount": 50000,
  "currency": "IDR",
  "customer_name": "Ahmad Fauzi",
  "customer_phone": "081234567890",
  "description": "Pembayaran Invoice INV-APP3-20261011-001",
  "paid_at": "2026-10-11T15:48:32+07:00",
  "created_at": "2026-10-11T15:47:39+07:00",
  "raw_notification": {
    "originalReferenceNo": "3702083645902822",
    "originalPartnerReferenceNo": "INV-APP3-20261011-001",
    "latestTransactionStatus": "00",
    "paidAmount": {
      "value": "50000.00",
      "currency": "IDR"
    }
  }
}
```

### 4.4. Respon yang Diharapkan dari Server Pihak Ketiga:
Server Anda wajib membalas request webhook dengan HTTP Status Code **`200 OK`**. Respon berupa JSON sederhana seperti `{"status": "ok"}` sudah cukup.

---

## 5. Contoh Implementasi Webhook Receiver (Pihak Ketiga)

### Contoh Handler di Node.js (Express):
```javascript
const express = require('express');
const app = express();

app.use(express.json());

app.post('/webhook/payment', (req, res) => {
    const callbackToken = req.headers['x-callback-token'];
    
    // 1. Verifikasi callback token
    if (callbackToken !== 'secret_pihak_ketiga_token_123') {
        return res.status(401).json({ error: 'Unauthorized token' });
    }

    const payload = req.body;
    console.log('Notifikasi Pembayaran Diterima:', payload);

    if (payload.event === 'payment.verified' && payload.status === 'paid') {
        const referenceNo = payload.reference_no;
        const amount = payload.amount;
        
        // 2. Update status invoice di database sistem Anda menjadi LUNAS
        console.log(`Invoice ${referenceNo} sebesar Rp ${amount} telah LUNAS!`);
    }

    // 3. Kembalikan respons 200 OK
    return res.status(200).json({ status: 'success' });
});

app.listen(3000, () => console.log('Webhook server running on port 3000'));
```

### Contoh Handler di PHP:
```php
<?php
// Ambil body JSON
$payload = json_decode(file_get_contents('php://input'), true);
$callbackToken = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? '';

// Verifikasi token
if ($callbackToken !== 'secret_pihak_ketiga_token_123') {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

if (($payload['event'] ?? '') === 'payment.verified' && ($payload['status'] ?? '') === 'paid') {
    $referenceNo = $payload['reference_no'];
    $amount = $payload['amount'];

    // Update database Anda menjadi LUNAS
    // updateInvoiceStatus($referenceNo, 'PAID');
}

// Balas 200 OK
http_response_code(200);
echo json_encode(['status' => 'success']);
```

---

## 6. Kontak Dukungan Teknis
Untuk bantuan integrasi dan pengujian, silakan hubungi tim IT Rasa Group:
- **Perusahaan**: PT Rasa Distribusi Indonesia
- **Sistem**: Rasa Connect Payment Gateway Integration
