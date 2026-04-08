# QAD & QID API Integration

Dokumentasi integrasi QAD Web API dan QID API untuk kebutuhan autentikasi dan pengiriman notifikasi (WhatsApp) di ekosistem Rasa Group.

## 1. Konfigurasi

Pastikan variabel lingkungan berikut sudah dikonfigurasi di file `.env`:

```env
QIDAPI_BASE_URL=https://development-qadwebapi.rasagroupoffice.com
QIDAPI_USERNAME=user.zoho
QIDAPI_PASSWORD=your_password_here
QIDAPI_APPS_ID=86770460-cfd3-11ee-b044-d747f56a0d39
```

Konfigurasi ini dibaca melalui file `config/qidapi.php`.

---

## 2. Autentikasi & Manajemen Token

Sistem menggunakan **QidApiService** untuk mengelola autentikasi ke infrastruktur pusat Rasa Group.

### Mekanisme Token
- **Login Otomatis**: Service akan melakukan login secara otomatis saat dibutuhkan jika token tidak tersedia.
- **Penyimpanan Token**: Token JWT disimpan menggunakan Laravel **Cache** dengan key `qidapi_access_token`.
- **Masa Berlaku**: Token di-cache selama 55 menit (atau menyesuaikan field `expiresIn` dari API) sebelum melakukan login ulang secara otomatis.
- **Auto-Retry**: Jika request API mengembalikan status `401 Unauthorized`, service akan otomatis menghapus cache, melakukan login ulang, dan mengulangi request tersebut sekali lagi.

### Mengambil Token Secara Manual
```php
use App\Services\QidApiService;

$token = app(QidApiService::class)->getToken();
```

---

## 3. Integrasi WhatsApp (QAD Notification)

Gunakan **QadWhatsAppService** untuk mengirim pesan WhatsApp melalui endpoint QAD.

### Menggunakan Helper (Rekomendasi)
Pola ini paling mudah digunakan dari mana saja (Controller, Model, Job).

```php
use App\Helpers\QadWhatsAppHelper;

$result = QadWhatsAppHelper::sendText('089699935552', 'Halo! Ini pesan notifikasi dari sistem.');

if ($result['success']) {
    // Berhasil
} else {
    // Gagal: $result['message'] berisi detail error
}
```

### Menggunakan Service (Dependency Injection)
Gunakan pola ini di dalam Controller atau Service lain.

```php
use App\Services\QadWhatsAppService;

class NotificationController extends Controller
{
    protected $waService;

    public function __construct(QadWhatsAppService $waService)
    {
        $this->waService = $waService;
    }

    public function notify(Request $request)
    {
        $this->waService->sendText($request->phone, $request->message);
    }
}
```

---

## 4. Keamanan & Best Practices

1. **Format Nomor Telepon**:
   Service secara otomatis mengubah format nomor telepon lokal Indonesia menjadi format internasional:
   - `0812...` -> `62812...`
   - `+62812...` -> `62812...`

2. **Error Logging**:
   Semua kegagalan API akan dicatat ke dalam log Laravel (`storage/logs/laravel.log`) dengan prefix `QadWhatsAppService` atau `QidApiService` untuk memudahkan debugging.

3. **Rate Limiting**:
   Harap perhatikan batasan pengiriman (rate limit) dari QAD Web API agar akun tidak terblokir.

---

## 5. QadService (Master & Transaction)

**QadService** adalah extension dari `QidApiService` yang menyediakan method spesifik untuk setiap endpoint yang ada di Swagger (BOM, Customer, Item Master, Sales Order, dll). Ini adalah "one-stop service" untuk berinteraksi dengan QAD Web API.

### Contoh Penggunaan
```php
use App\Services\QadService;

$qad = app(QadService::class);

// Ambil data detail customer
$customer = $qad->getCustomer('CUST001');

// Buat Sales Order
$order = $qad->createSalesOrder([
    'soldToCustomerCode' => 'CUST001',
    'items' => [...]
]);
```

### Daftar Method Tersedia

#### Autentikasi
- `getToken()`: Mendapatkan token aktif (otomatis login jika perlu).
- `refreshAuthToken()`: Force logout dan ambil token baru.

#### Bill of Material (BOM)
- `saveBomFormulaCode(array $payload)`
- `saveBomFormulaDetail(array $payload)`
- `getBom(string $formulaCode)`
- `getAllBom(string $search = '')`

#### Business Relation
- `getBusinessRelation(string $code)`
- `listBusinessRelation(array $query = [])`
- `createBusinessRelation(array $payload)`
- `updateBusinessRelation(array $payload)`

#### Customer
- `getCustomer(string $customerCode, string $sharedSetCode = '')`
- `listCustomer(array $query = [])`
- `createCustomer(array $payload)`
- `createCustomerData(array $payload)`
- `updateCustomer(array $payload)`

#### Inventory
- `getInventoryLocation(array $query)`
- `getAllInventory(array $payload)`

#### Item Master
- `listItem(array $payload)`
- `getItem(string $itemCode)`
- `pagingItem(array $payload)`
- `saveItem(array $payload)`
- `updateItem(array $payload)`

#### QAD WSA Master
- `getQadTrailer(array $query = [])`
- `getQadCustomer(array $query = [])`
- `getQadGeneralLedger(array $query = [])`
- `getQadCostCentre(array $query = [])`
- `getQadTax(array $query = [])`

#### Sales Orders (Transaction)
- `getSalesOrder(string $code)`
- `listSalesOrder(array $query = [])`
- `createSalesOrder(array $payload)`
- `updateSalesOrder(array $payload)`

#### System / Notification
- `sendWhatsAppText(string $phone, string $message)`: Shortcut untuk kirim WA.

---

## 6. File Terkait
- **Base API Client**: `app/Services/QidApiService.php`
- **Full QAD Service**: `app/Services/QadService.php`
- **WhatsApp Service**: `app/Services/QadWhatsAppService.php`
- **Helper**: `app/Helpers/QadWhatsAppHelper.php` (Shortcut Statis)
- **Config**: `config/qidapi.php`
