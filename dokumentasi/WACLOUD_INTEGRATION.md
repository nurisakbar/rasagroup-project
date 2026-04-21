# Integrasi WACloud

Dokumentasi integrasi WACloud WhatsApp Gateway API untuk mengirim pesan WhatsApp melalui aplikasi.

## Konfigurasi

1. Login ke admin panel: `http://103.125.181.245:8600/admin/settings`
2. Scroll ke bagian **Integrasi WACloud**
3. Isi **API Key WACloud** (format: `waha_xxxxxxxxxxxxx`)
4. Isi **Device ID WACloud** (format UUID, contoh: `550e8400-e29b-41d4-a716-446655440000`)
5. Klik **Simpan Pengaturan WACloud**

### Cara Mendapatkan API Key dan Device ID

1. Daftar/login ke [WACloud Dashboard](https://wacloud.id)
2. Buat API Key di dashboard
3. Hubungkan device WhatsApp melalui dashboard atau QR code
4. Ambil Device ID dari daftar devices di dashboard atau melalui endpoint GET /devices

## Penggunaan di Code

### Menggunakan WACloudService

```php
use App\Services\WACloudService;

// Inisialisasi service
$waCloud = new WACloudService();

// Cek apakah sudah dikonfigurasi
if (!$waCloud->isConfigured()) {
    // WACloud belum dikonfigurasi
    return;
}

// Kirim pesan teks
$result = $waCloud->sendTextMessage(
    '6281234567890', // Nomor tujuan (format: 62xxxxxxxxxx)
    'Halo, ini pesan dari aplikasi!'
);

if ($result) {
    echo "Pesan terkirim! Message ID: " . $result['message_id'];
} else {
    echo "Gagal mengirim pesan";
}

// Kirim pesan gambar
$result = $waCloud->sendImageMessage(
    '6281234567890',
    'https://example.com/image.jpg',
    'Ini adalah caption gambar' // Optional
);

// Kirim dokumen
$result = $waCloud->sendDocumentMessage(
    '6281234567890',
    'https://example.com/document.pdf',
    'document.pdf' // Optional filename
);

// Format nomor telepon (otomatis handle format Indonesia)
$formattedPhone = $waCloud->formatPhoneNumber('081234567890');
// Hasil: 6281234567890

// Cek apakah nomor terdaftar di WhatsApp
$checkResult = $waCloud->checkPhoneExists('6281234567890');
if ($checkResult && $checkResult['numberExists']) {
    echo "Nomor terdaftar di WhatsApp";
} else {
    echo "Nomor tidak terdaftar";
}

// Get list devices
$devices = $waCloud->getDevices();
if ($devices) {
    foreach ($devices as $device) {
        echo "Device: " . $device['name'] . " - " . $device['id'];
    }
}

// Get device details
$device = $waCloud->getDevice();
if ($device) {
    echo "Device Status: " . $device['status'];
}
```

### Contoh Penggunaan di Controller

```php
namespace App\Http\Controllers;

use App\Services\WACloudService;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function sendOrderNotification($order)
    {
        $waCloud = new WACloudService();
        
        if (!$waCloud->isConfigured()) {
            \Log::warning('WACloud not configured, skipping WhatsApp notification');
            return;
        }

        $phone = $waCloud->formatPhoneNumber($order->address->phone);
        $message = "Pesanan #{$order->order_number} telah dibuat. Total: Rp " . number_format($order->total_amount, 0, ',', '.');

        $result = $waCloud->sendTextMessage($phone, $message);
        
        if ($result) {
            \Log::info('WhatsApp notification sent', [
                'order_id' => $order->id,
                'message_id' => $result['message_id']
            ]);
        }
    }
}
```

## Format Nomor Telepon

Service ini secara otomatis memformat nomor telepon ke format WhatsApp:
- `081234567890` → `6281234567890`
- `+6281234567890` → `6281234567890`
- `0812-3456-7890` → `6281234567890`

## Error Handling

Semua method akan return `null` jika terjadi error. Error akan di-log ke Laravel log file (`storage/logs/laravel.log`).

```php
$result = $waCloud->sendTextMessage('6281234567890', 'Test');

if ($result === null) {
    // Error occurred, check logs for details
    \Log::error('Failed to send WhatsApp message');
}
```

## Method yang Tersedia

### `sendTextMessage(string $to, string $message): ?array`
Mengirim pesan teks.

### `sendImageMessage(string $to, string $imageUrl, ?string $caption = null): ?array`
Mengirim pesan gambar dengan caption opsional.

### `sendDocumentMessage(string $to, string $documentUrl, ?string $filename = null): ?array`
Mengirim dokumen dengan filename opsional.

### `getDevices(): ?array`
Mendapatkan daftar semua device WhatsApp yang terhubung.

### `getDevice(?string $deviceId = null): ?array`
Mendapatkan detail device tertentu (default: device yang dikonfigurasi).

### `checkPhoneExists(string $phone): ?array`
Memeriksa apakah nomor telepon terdaftar di WhatsApp.

### `formatPhoneNumber(string $phone): string`
Memformat nomor telepon ke format WhatsApp (62xxxxxxxxxx).

### `isConfigured(): bool`
Memeriksa apakah WACloud sudah dikonfigurasi.

## Dokumentasi Lengkap

Untuk dokumentasi lengkap API WACloud, kunjungi: https://wacloud.id/docs.html

## Catatan Penting

1. Pastikan device WhatsApp sudah terhubung dan terverifikasi di dashboard WACloud
2. API Key dan Device ID harus valid dan aktif
3. Semua error akan di-log ke Laravel log file
4. Format nomor telepon harus menggunakan kode negara (62 untuk Indonesia)
5. Rate limiting berlaku sesuai paket WACloud yang digunakan

