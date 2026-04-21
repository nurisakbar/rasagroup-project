# Xendit Payment Integration Setup

## Konfigurasi

### 1. Tambahkan Environment Variables

Tambahkan konfigurasi berikut ke file `.env`:

```env
XENDIT_SECRET_KEY=xnd_development_xxxxxxxxxxxxxxxxxxxxx
XENDIT_PUBLIC_KEY=xnd_public_development_xxxxxxxxxxxxxxxxxxxxx
XENDIT_WEBHOOK_TOKEN=your_webhook_token_here
```

**Cara mendapatkan API keys:**
1. Login ke [Xendit Dashboard](https://dashboard.xendit.co/)
2. Buka Settings > API Keys
3. Copy Secret Key dan Public Key
4. Untuk Webhook Token, buka Settings > Webhooks dan copy token yang diberikan

### 2. Jalankan Migration

Jalankan migration untuk menambahkan field Xendit ke tabel orders:

```bash
php artisan migrate
```

### 3. Konfigurasi Webhook di Xendit Dashboard

1. Login ke [Xendit Dashboard](https://dashboard.xendit.co/)
2. Buka Settings > Webhooks
3. Tambahkan webhook endpoint:
   - **URL**: `https://yourdomain.com/webhooks/xendit`
   - **Events**: Pilih semua event terkait invoice (invoice.paid, invoice.expired, invoice.failed)
4. Simpan webhook token yang diberikan ke `.env` sebagai `XENDIT_WEBHOOK_TOKEN`

## Fitur

### Metode Pembayaran yang Didukung

Xendit mendukung berbagai metode pembayaran:
- **Kartu Kredit/Debit** (Visa, Mastercard, JCB)
- **E-Wallet** (OVO, DANA, LinkAja, ShopeePay)
- **QRIS** (QR Code)
- **Virtual Account** (BCA, Mandiri, BNI, BRI)
- **PayLater** (Kredivo, Akulaku)

### Alur Pembayaran

1. User memilih "Pembayaran Online (Xendit)" di halaman checkout
2. Sistem membuat invoice di Xendit
3. User diarahkan ke halaman pembayaran Xendit
4. User melakukan pembayaran
5. Xendit mengirim webhook ke sistem
6. Sistem memperbarui status pembayaran order secara otomatis

### Webhook Events

Sistem menangani event berikut dari Xendit:
- **PAID**: Pembayaran berhasil, order status diupdate ke 'paid' dan 'processing'
- **EXPIRED**: Invoice expired, payment status diupdate ke 'failed'
- **FAILED**: Pembayaran gagal, payment status diupdate ke 'failed'

## Testing

### Test Mode

Untuk testing, gunakan Xendit Test Mode:
1. Gunakan API keys dari test mode di Xendit Dashboard
2. Test dengan kartu kredit test: `4000000000000002` (selalu approved)
3. Test dengan kartu kredit test: `4000000000009995` (selalu declined)

### Webhook Testing

Untuk test webhook secara lokal, gunakan:
- [ngrok](https://ngrok.com/) untuk expose local server
- Atau gunakan Xendit webhook testing tool di dashboard

## Troubleshooting

### Invoice tidak terbuat

- Pastikan `XENDIT_SECRET_KEY` sudah benar di `.env`
- Check log di `storage/logs/laravel.log` untuk error detail
- Pastikan amount minimal sesuai dengan Xendit requirements (biasanya minimal Rp 10.000)

### Webhook tidak diterima

- Pastikan URL webhook sudah benar di Xendit Dashboard
- Pastikan server dapat diakses dari internet (tidak di localhost)
- Check log untuk melihat apakah webhook diterima
- Pastikan `XENDIT_WEBHOOK_TOKEN` sudah dikonfigurasi (opsional untuk signature verification)

### Payment status tidak terupdate

- Check apakah webhook sudah dikonfigurasi dengan benar
- Pastikan order ditemukan berdasarkan `xendit_invoice_id` atau `order_number`
- Check log untuk melihat error yang terjadi

## Support

Untuk bantuan lebih lanjut:
- [Xendit Documentation](https://docs.xendit.co/)
- [Xendit Support](https://xendit.co/support)



