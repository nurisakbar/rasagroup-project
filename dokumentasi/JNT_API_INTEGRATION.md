# J&T Express API Integration & UAT Testcase Guide

Dokumentasi lengkap integrasi J&T Express Indonesia (API Create Order, Tariff Check, Tracking/Pelacakan Resi, dan Cancel Order) untuk ekosistem Rasa Group (PT Rasa Distribusi Indonesia - PTRDI) serta panduan pemetaan dokumen UAT `J&T_API_Testcase_V3.0 x PTRDI.docx`.

---

## 1. Kredensial & Lingkungan (Environment)

Kredensial resmi yang diterbitkan oleh J&T Express untuk PT Rasa Distribusi Indonesia:

| Parameter | Kredensial Sandbox (Testing) | Kredensial Production |
| :--- | :--- | :--- |
| **Username / e-company ID** | `PTRDI` | Menyesuaikan profil produksi |
| **API Key (Order)** | `ICF16T` | Menyesuaikan profil produksi |
| **Secret Key (Order Signature)** | `AKe62df84bJ3d8e4b1hea2R45j11klsb` | Menyesuaikan profil produksi |
| **Tariff Key** | `jG3zVl60nTCi` | Menyesuaikan profil produksi |
| **Track Password (Basic Auth)** | `jG3zVl60nTCi` | Menyesuaikan profil produksi |

### Daftar Endpoint API

| Layanan | Testing Environment (Sandbox) | Production Environment |
| :--- | :--- | :--- |
| **Create Order** | `https://demo-ecommerce.inuat-jntexpress.id/jts-idn-ecommerce-api/api/order/create` | `https://ecommerce.jntexpress.id/jts-idn-ecommerce-api/api/order/create` |
| **Cancel Order** | `https://demo-ecommerce.inuat-jntexpress.id/jts-idn-ecommerce-api/api/order/cancel` | `https://ecommerce.jntexpress.id/jts-idn-ecommerce-api/api/order/cancel` |
| **Tariff Check** | `https://demo-general.inuat-jntexpress.id/jandt_track/inquiry.action` | `https://general.jntexpress.id/jandt_track/inquiry.action` |
| **Tracking API** | `https://demo-general.inuat-jntexpress.id/jandt_track/track/trackAction!tracking.action` | `https://general.jntexpress.id/jandt_track/track/trackAction!tracking.action` |

---

## 2. Spesifikasi Tracking API (Pelacakan Resi / Scan AWB)

Berdasarkan dokumentasi resmi developer J&T Express dan hasil verifikasi UAT pada resi aktif **`JO9001461001`**:

### Detail Header & Method
- **Method**: `POST`
- **Authorization**: `Basic Authorization`
  - **Username**: `PTRDI`
  - **Password**: `jG3zVl60nTCi`
  *(Base64 Encode: `Basic UFRSREk6akczelZsNjBuVENp`)*
- **Content-Type**: `text/plain` *(Raw Text)*

### Parameter Request Body (Raw Text / JSON)
```json
{
  "awb": "JO9001461001",
  "eccompanyid": "PTRDI"
}
```

| Parameter | Type | Required | Keterangan |
| :--- | :---: | :---: | :--- |
| `awb` | String | Y | Nomor resi / Air Waybill J&T (misal: `JO9001461001`) |
| `eccompanyid` | String | Y | ID perusahaan pengirim yang terdaftar di sistem J&T (`PTRDI`) |

### Contoh Perintah cURL

```bash
curl -i -X POST "https://demo-general.inuat-jntexpress.id/jandt_track/track/trackAction!tracking.action" \
  -u "PTRDI:jG3zVl60nTCi" \
  -H "Content-Type: text/plain" \
  -d '{"awb":"JO9001461001","eccompanyid":"PTRDI"}'
```

### Contoh Response Sukses (Paket Telah Di-scan Operasional)

```http
HTTP/1.1 200 OK
Content-Type: text/plain;charset=UTF-8
```
```json
{
  "awb": "JO9001461001",
  "detail": {
    "actual_amount": 0,
    "delivDriver": {
      "id": "",
      "name": "",
      "phone": "",
      "photo": ""
    },
    "detail_cost": {
      "add_cost": 0,
      "cod": 0,
      "insurance_cost": 0,
      "return_cost": 0,
      "shipping_cost": 0
    },
    "driver": {
      "name": "1667"
    },
    "itemname": "Snack Rasa",
    "note": "Produk Rasa",
    "qty": 1,
    "receiver": {
      "addr": "Jl Buah Batu No 12, Bandung",
      "city": "BANDUNG",
      "geoloc": "",
      "name": "BUDI SANTOSO",
      "zipcode": "40265"
    },
    "sender": {
      "addr": "Jl Palmerah Barat No 9 Jakarta",
      "city": "JAKARTA",
      "geoloc": "",
      "name": "PT RASA DISTRIBUSI INDONESIA",
      "zipcode": ""
    },
    "services_code": "EZ",
    "services_type": "",
    "shipped_date": "2026-10-26 09:33:49",
    "weight": 1000
  },
  "history": [
    {
      "agentName": "",
      "city_name": "JAKARTA",
      "date_time": "2026-10-23 13:17:20",
      "driverName": "",
      "driverPhone": "",
      "nextSiteName": "SLIPI",
      "note": "",
      "presenter": "",
      "presentername": "",
      "receiver": "",
      "status": "Manifes",
      "status_code": 101,
      "storeName": ""
    },
    {
      "agentName": "",
      "city_name": "JAKARTA",
      "date_time": "2026-10-26 09:33:49",
      "distributionFlag": "0",
      "driverName": "1667",
      "driverPhone": "+6289654723",
      "nextSiteName": "SLIPI",
      "note": "",
      "presenter": "",
      "presentername": "",
      "receiver": "",
      "status": "Paket telah diterima oleh SLIPI",
      "status_code": 100,
      "storeName": "SLIPI"
    }
  ],
  "orderid": "CURL-TEST-1792736239"
}
```

### Response Error Handling (Resi Tidak Terdaftar / Belum Di-scan)

```json
{
  "error_id": "404",
  "error_message": "Invalid AWB number"
}
```

---

## 3. Implementasi PHP / Laravel

Contoh implementasi tracking pada service PHP / Laravel:

```php
use Illuminate\Support\Facades\Http;

public function trackShipment(string $resi): array
{
    $url = config('jnt.track_endpoint');
    $username = config('jnt.username'); // PTRDI
    $password = config('jnt.track_password'); // jG3zVl60nTCi

    $payload = json_encode([
        'awb' => $resi,
        'eccompanyid' => $username,
    ]);

    $response = Http::withBasicAuth($username, $password)
        ->withBody($payload, 'text/plain')
        ->post($url);

    if ($response->successful()) {
        $body = $response->json();
        
        // Cek error handling J&T
        if (isset($body['error_id']) && $body['error_id'] === '404') {
            return [
                'success' => false,
                'message' => 'Resi tidak ditemukan atau belum mengalami scan operasional.',
            ];
        }

        return [
            'success' => true,
            'data' => $body,
        ];
    }

    return [
        'success' => false,
        'message' => 'Gagal terhubung ke server tracking J&T.',
    ];
}
```

---

## 4. Status Dokumen UAT (`J&T_API_Testcase_V3.0 x PTRDI.docx`)

File dokumen pengujian `J&T_API_Testcase_V3.0 x PTRDI.docx` yang berada di root repository ini telah disinkronkan dengan hasil pengujian aktual:

| Case | Modul | Skenario | Status | Catatan Teknis |
| :---: | :--- | :--- | :---: | :--- |
| **1.1** | API Order | Jakarta $\rightarrow$ Buah Batu (Bandung) | **PASS** | Resi terbit: `JO9001543805`, kode sortir `BDO-BDO93-34B`. |
| **1.2** | API Order | Jakarta $\rightarrow$ Woyla (Aceh Barat) | **PASS** | Resi terbit: `JO9001746655`, kode sortir `BTJ-112`. |
| **1.3** | API Order | Jakarta $\rightarrow$ Gondang-BJE (Bojonegoro) | **PASS** | Resi terbit: `JO9001732616`, kode sortir `LMG`. |
| **1.4** | API Order | Jakarta $\rightarrow$ Gondang-MJK (Mojokerto) | **PASS** | Resi terbit: `JO9001274831`, rute Mojokerto valid. |
| **2** | API Order | Order dengan Asuransi (Rp500) | **PASS** | Order terbit dengan nominal barang Rp250.000. |
| **3** | API Order | Order tanpa Asuransi (insurance: 0) | **PASS** | Order terbit normal. |
| **4** | API Order | Order dengan COD (Rp150.000) | **PASS** | Order terbit dengan nilai COD Rp150.000. |
| **5** | API Order | Order tanpa COD (cod: 0) | **PASS** | Order non-COD terbit normal. |
| **6** | API Order | Duplikasi orderid yang sama | **PASS** | Server J&T menolak duplikasi orderid (Orderid tidak boleh sama). |
| **7** | **API Tracking** | **Pelacakan Resi (Scan AWB)** | **PASS** | Resi **`JO9001461001`** berhasil dilacak lengkap dengan histori scan Manifes dan Slipi. |
| **8** | API Tracking | Tracking perubahan bobot paket | **SIMULATED** | Klausul non-mandatory production, membutuhkan revisi timbangan fisik tim J&T. |
| **9** | API Tracking | Tracking AWB tidak valid | **PASS** | Respon `404 Invalid AWB number` tervalidasi tepat. |
| **10.1** | API Tariff | Cek Ongkir Kalideres 1 kg | **PASS** | Layanan EZ tarif Rp7.500. |
| **10.2** | API Tariff | Cek Ongkir Kembangan 2 kg | **NEEDS VERIF** | Respon `content: []` kosong di staging demo-general. |
| **11** | API Cancel | Pembatalan Order sebelum pickup | **PASS** | Order dibatalkan sukses oleh server J&T. |
| **12** | API Cancel | Pembatalan Order setelah pickup | **SIMULATED** | Klausul non-mandatory production, membutuhkan status Picked Up dari tim J&T. |
