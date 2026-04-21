# WACloud Helper - Panduan Penggunaan

Helper class untuk memudahkan pengiriman pesan WhatsApp melalui WACloud API.

## Instalasi

Helper sudah tersedia di `app/Helpers/WACloudHelper.php`. Tidak perlu instalasi tambahan.

## Penggunaan

### Import Helper

```php
use App\Helpers\WACloudHelper;
```

### 1. Mengirim Pesan Text

```php
// Format sederhana
$result = WACloudHelper::sendText('081234567890', 'Halo, ini pesan dari aplikasi!');

if ($result) {
    echo "Pesan terkirim! Message ID: " . $result['message_id'];
} else {
    echo "Gagal mengirim pesan";
}
```

**Parameter:**
- `$phone` (string): Nomor telepon (format apapun, akan otomatis diformat)
- `$message` (string): Teks pesan yang akan dikirim

**Return:**
- `array|null`: Data pesan jika berhasil, `null` jika gagal

### 2. Mengirim Pesan Document

```php
// Mengirim dokumen dengan URL
$result = WACloudHelper::sendDocument(
    '081234567890',
    'https://example.com/invoice.pdf',
    'invoice.pdf' // Optional: nama file
);

if ($result) {
    echo "Dokumen terkirim! Message ID: " . $result['message_id'];
}
```

**Parameter:**
- `$phone` (string): Nomor telepon (format apapun, akan otomatis diformat)
- `$documentUrl` (string): URL lengkap ke dokumen (harus accessible via HTTP/HTTPS)
- `$filename` (string|null): Nama file untuk dokumen (optional)

**Return:**
- `array|null`: Data pesan jika berhasil, `null` jika gagal

### 3. Mengirim dengan Auto-Retry

Helper menyediakan method dengan auto-retry untuk menangani kegagalan sementara:

```php
// Text message dengan retry (default: 3 kali)
$result = WACloudHelper::sendTextWithRetry('081234567890', 'Pesan penting!');

// Document dengan retry (default: 3 kali)
$result = WACloudHelper::sendDocumentWithRetry(
    '081234567890',
    'https://example.com/file.pdf',
    'file.pdf',
    5 // Custom: retry maksimal 5 kali
);
```

**Parameter Retry:**
- `$maxRetries` (int): Jumlah maksimal percobaan (default: 3)
- Menggunakan exponential backoff (1s, 2s, 4s, ...)

### 4. Format Nomor Telepon

```php
$formatted = WACloudHelper::formatPhone('081234567890');
// Hasil: 6281234567890

$formatted = WACloudHelper::formatPhone('+6281234567890');
// Hasil: 6281234567890

$formatted = WACloudHelper::formatPhone('0812-3456-7890');
// Hasil: 6281234567890
```

### 5. Cek Konfigurasi

```php
if (WACloudHelper::isConfigured()) {
    // WACloud sudah dikonfigurasi, bisa kirim pesan
} else {
    // WACloud belum dikonfigurasi
}
```

## Contoh Penggunaan di Controller

### Contoh 1: Mengirim Notifikasi Order

```php
namespace App\Http\Controllers;

use App\Helpers\WACloudHelper;
use App\Models\Order;

class OrderController extends Controller
{
    public function sendOrderNotification(Order $order)
    {
        $phone = $order->address->phone;
        $message = "Pesanan #{$order->order_number} telah dibuat.\n";
        $message .= "Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
        $message .= "Terima kasih atas pesanan Anda!";

        $result = WACloudHelper::sendText($phone, $message);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Notifikasi WhatsApp berhasil dikirim',
                'message_id' => $result['message_id']
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim notifikasi WhatsApp'
        ], 500);
    }
}
```

### Contoh 2: Mengirim Invoice PDF

```php
namespace App\Http\Controllers;

use App\Helpers\WACloudHelper;
use App\Models\Order;
use Illuminate\Support\Facades\Storage;

class InvoiceController extends Controller
{
    public function sendInvoice(Order $order)
    {
        // Generate invoice PDF
        $invoicePath = $this->generateInvoice($order);
        $invoiceUrl = Storage::url($invoicePath);

        // Kirim via WhatsApp
        $result = WACloudHelper::sendDocument(
            $order->address->phone,
            url($invoiceUrl),
            "Invoice-{$order->order_number}.pdf"
        );

        if ($result) {
            return back()->with('success', 'Invoice berhasil dikirim via WhatsApp');
        }

        return back()->with('error', 'Gagal mengirim invoice via WhatsApp');
    }
}
```

### Contoh 3: Mengirim Notifikasi dengan Retry

```php
namespace App\Http\Controllers;

use App\Helpers\WACloudHelper;

class NotificationController extends Controller
{
    public function sendImportantNotification($phone, $message)
    {
        // Menggunakan retry untuk pesan penting
        $result = WACloudHelper::sendTextWithRetry($phone, $message, 5);

        if ($result) {
            \Log::info('Important notification sent', [
                'phone' => $phone,
                'message_id' => $result['message_id']
            ]);
            return true;
        }

        \Log::error('Failed to send important notification after retries', [
            'phone' => $phone
        ]);
        return false;
    }
}
```

## Contoh Penggunaan di Model/Event

### Menggunakan Event Listener

```php
namespace App\Listeners;

use App\Events\OrderCreated;
use App\Helpers\WACloudHelper;

class SendOrderNotification
{
    public function handle(OrderCreated $event)
    {
        $order = $event->order;
        
        if (!$order->address || !$order->address->phone) {
            return;
        }

        $phone = $order->address->phone;
        $message = "Pesanan #{$order->order_number} telah dibuat.\n";
        $message .= "Total: Rp " . number_format($order->total_amount, 0, ',', '.');

        WACloudHelper::sendText($phone, $message);
    }
}
```

## Format Nomor Telepon yang Didukung

Helper otomatis memformat nomor telepon ke format WhatsApp (62xxxxxxxxxx):

- `081234567890` → `6281234567890`
- `+6281234567890` → `6281234567890`
- `0812-3456-7890` → `6281234567890`
- `0812 3456 7890` → `6281234567890`
- `6281234567890` → `6281234567890` (sudah benar)

## Error Handling

Semua method helper akan:
- Return `null` jika terjadi error
- Log error ke Laravel log file (`storage/logs/laravel.log`)
- Tidak throw exception (fail silently)

Untuk error handling yang lebih detail:

```php
$result = WACloudHelper::sendText($phone, $message);

if ($result === null) {
    // Cek log untuk detail error
    \Log::error('Failed to send WhatsApp message', [
        'phone' => $phone
    ]);
    
    // Handle error sesuai kebutuhan
    return response()->json(['error' => 'Gagal mengirim pesan'], 500);
}
```

## Response Format

Jika berhasil, method akan return array dengan struktur:

```php
[
    'message_id' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
    'whatsapp_message_id' => '3EB0...',
    'status' => 'sent',
    'ack' => 1,
    'to' => '6281234567890'
]
```

## Catatan Penting

1. **URL Document Harus Accessible**: URL dokumen harus bisa diakses secara publik via HTTP/HTTPS
2. **Format Nomor**: Helper otomatis memformat nomor, tapi pastikan nomor valid
3. **Rate Limiting**: Perhatikan rate limiting dari WACloud sesuai paket Anda
4. **Error Logging**: Semua error akan di-log, cek `storage/logs/laravel.log` untuk troubleshooting
5. **Konfigurasi**: Pastikan API Key dan Device ID sudah dikonfigurasi di admin settings

## Troubleshooting

### Pesan tidak terkirim
1. Cek apakah WACloud sudah dikonfigurasi: `WACloudHelper::isConfigured()`
2. Cek log file: `storage/logs/laravel.log`
3. Pastikan device WhatsApp sudah terhubung di dashboard WACloud
4. Cek quota tersedia di admin settings

### Document tidak terkirim
1. Pastikan URL document bisa diakses secara publik
2. Pastikan format file didukung (PDF, DOC, XLS, dll)
3. Cek ukuran file (ada batas maksimal sesuai paket WACloud)

## Referensi

- [Dokumentasi WACloud](https://wacloud.id/docs.html)
- [WACloud API Docs](https://app.wacloud.id/api-docs/messages)

