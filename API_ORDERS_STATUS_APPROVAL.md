# Dokumentasi API: Update Status & Finance Approval

Dokumen ini secara khusus merinci cara melakukan perubahan status pengiriman dan persetujuan finance (Term of Payment) pada pesanan.

---

## 1. Update Status Pengiriman (Order Status)

Endpoint ini digunakan untuk memperbarui status perjalanan/pengiriman sebuah pesanan. Status yang diizinkan adalah: `pending`, `processing`, `shipped`, `delivered`, `completed`, `cancelled`.

### Detail Endpoint
- **Method**: `PUT`
- **URL**: `/api/orders/{id}/status` *(ID bisa berupa UUID dari `id` tabel orders, maupun `order_number`)*
- **Headers**:
  - `Content-Type: application/json`
  - `X-Token`: *(Token autentikasi session user)*

### Contoh Request (cURL)
```bash
curl -X PUT https://dev.rasaconnect.com/api/orders/01a07c49-9576-708a-960d-6b38ab3955b2/status \
-H "Content-Type: application/json" \
-H "X-Token: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoiZjQ3YWMxMGIt..." \
-d '{
  "order_status": "shipped" 
}'
```

### Contoh Response (200 OK)
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

---

## 2. Finance Approval (Persetujuan TOP)

Endpoint ini digunakan oleh pihak Finance atau tim internal untuk memberikan persetujuan pembayaran berjangka waktu tempo (Term of Payment) pada suatu pesanan yang ditangguhkan.

### Detail Endpoint
- **Method**: `POST`
- **URL**: `/api/orders/finance-approval`
- **Headers**:
  - `Content-Type: application/json`
  - `X-Token`: *(Token autentikasi session user approval)*

### Contoh Request (cURL)
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
*(Catatan: Kirimkan nilai `finance_approved: 1` untuk menyetujui secara resmi, atau `0` untuk membatalkan).*

### Contoh Response (200 OK)
```json
{
  "success": true,
  "message": "Term of Payment telah disetujui (1).",
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

## 3. Notifikasi Approval ke EDMS

Endpoint ini digunakan untuk mengirimkan data rincian pesanan ke sistem EDMS (secara otomatis dari backend, atau disimulasikan).

### Detail Endpoint
- **Method**: `POST`
- **URL**: `https://edms.rasagroupoffice.com/api/v1/approval_notification`
- **Headers**:
  - `Content-Type: application/json`

### Contoh Request (cURL)
```bash
curl -X POST https://edms.rasagroupoffice.com/api/v1/approval_notification \
-H "Content-Type: application/json" \
-d '{
  "transaction_id": "01a07c49-9576-708a-960d-6b38ab3955b2",
  "transaction_number": "ORD-202609-00105",
  "user_id": "f47ac10b-58cc-4372-a567-0e02b2c3d479",
  "customer_name": "Budi Santoso",
  "amount": 325000,
  "payment_method": "term_of_payment",
  "status": "pending_approval",
  "items": [
    {
      "product_id": "9b12a81c-6d34-4069-95e2-63b7e23b2c15",
      "product_name": "Kopi Arabica 1Kg",
      "quantity": 2,
      "price": 150000,
      "subtotal": 300000
    }
  ],
  "requested_at": "2026-09-15 10:45:00"
}'
```

### Contoh Response (200 OK)
```json
{
  "status": "success",
  "message": "Notification received"
}
```
