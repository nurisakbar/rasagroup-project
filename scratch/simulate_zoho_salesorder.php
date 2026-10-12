<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(\Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Use file cache so it does not require database connection for access token caching
config(['cache.default' => 'file']);

use App\Services\ZohoService;
use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Collection;

echo "=== MEMULAI SIMULASI PENGIRIMAN SALES ORDER KE ZOHO ===\n\n";

$zohoService = new ZohoService();

// 1. Setup Dummy User
$dummyUser = new class extends User {
    public function update(array $attributes = [], array $options = []) {
        $this->forceFill($attributes);
        return true;
    }
};
$dummyUser->id = 'user-sim-001';
$dummyUser->name = 'Sarah Test Simulation';
$dummyUser->email = 'agent1@stt.co.id';
$dummyUser->zoho_customer_id = '7386545000005377027'; // Existing verified customer in Zoho

// 2. Setup Dummy Product
$dummyProduct = new class extends Product {
    public function update(array $attributes = [], array $options = []) {
        $this->forceFill($attributes);
        return true;
    }
};
$dummyProduct->id = 'prod-sim-001';
$dummyProduct->name = '3bt-Peach';
$dummyProduct->code = 'FDA010-PC04';
$dummyProduct->price = 100000;
$dummyProduct->zoho_item_id = '7386545000000884970'; // Existing verified item in Zoho

// 3. Setup Dummy Order Item
$dummyItem = new OrderItem();
$dummyItem->id = 'item-sim-001';
$dummyItem->product_id = $dummyProduct->id;
$dummyItem->quantity = 1;
$dummyItem->price = 100000;
$dummyItem->setRelation('product', $dummyProduct);

// 4. Setup Dummy Order
$dummyOrder = new class extends Order {
    public function loadMissing($relations) {
        return $this;
    }
    public function update(array $attributes = [], array $options = []) {
        $this->forceFill($attributes);
        return true;
    }
};

$orderNumber = 'SIM-RC-' . date('Ymd-His');
$dummyOrder->id = 'order-sim-' . uniqid();
$dummyOrder->order_number = $orderNumber;
$dummyOrder->shipping_cost = 10000;
$dummyOrder->total_amount = 110000;
$dummyOrder->payment_method = 'faspay_qris';
$dummyOrder->created_at = now();
$dummyOrder->finance_approved_at = now();

$dummyOrder->setRelation('user', $dummyUser);
$dummyOrder->setRelation('items', new Collection([$dummyItem]));

echo "Order Data:\n";
echo " - Order Number: {$dummyOrder->order_number}\n";
echo " - Customer: {$dummyUser->name} (Zoho ID: {$dummyUser->zoho_customer_id})\n";
echo " - Item: {$dummyProduct->name} (Zoho ID: {$dummyProduct->zoho_item_id}) x 1 @ Rp {$dummyItem->price}\n";
echo " - Shipping Cost: Rp {$dummyOrder->shipping_cost}\n";
echo " - Total Amount: Rp {$dummyOrder->total_amount}\n";
echo " - Payment Method: {$dummyOrder->payment_method}\n\n";

echo "Mengeksekusi createOrderToPaymentFlow()...\n";

try {
    $zohoService->createOrderToPaymentFlow($dummyOrder);

    echo "\n=== SIMULASI BERHASIL! ===\n";
    echo "Hasil Sinkronisasi ke Zoho:\n";
    echo "1. Zoho Sales Order ID : " . $dummyOrder->zoho_salesorder_id . "\n";
    echo "2. Zoho Invoice ID     : " . $dummyOrder->zoho_invoice_id . "\n";
    echo "3. Zoho Payment ID     : " . $dummyOrder->zoho_payment_id . "\n\n";

    echo "Memeriksa detail Sales Order langsung dari Zoho...\n";
    $soData = $zohoService->getSalesOrder($dummyOrder->zoho_salesorder_id);
    if ($soData) {
        echo " - SO Number   : " . ($soData['salesorder_number'] ?? '-') . "\n";
        echo " - Reference   : " . ($soData['reference_number'] ?? '-') . "\n";
        echo " - Status SO   : " . ($soData['status'] ?? '-') . "\n";
        
        // Cek custom fields cf_source
        $cfSourceValue = null;
        foreach ($soData['custom_fields'] ?? [] as $cf) {
            if (($cf['api_name'] ?? '') === 'cf_source') {
                $cfSourceValue = $cf['value'];
            }
        }
        echo " - Custom Field [cf_source]: " . ($cfSourceValue ?? '(tidak ditemukan)') . "\n";
    }

    echo "\nMemeriksa detail Invoice langsung dari Zoho...\n";
    $invData = $zohoService->getInvoice($dummyOrder->zoho_invoice_id);
    if ($invData) {
        echo " - Invoice Number : " . ($invData['invoice_number'] ?? '-') . "\n";
        echo " - Status Invoice : " . ($invData['status'] ?? '-') . "\n";
        echo " - Balance        : Rp " . number_format((float)($invData['balance'] ?? 0), 0, ',', '.') . "\n";
        echo " - Is Emailed     : " . (($invData['is_emailed'] ?? false) ? 'Yes' : 'No') . "\n";
    }

} catch (\Throwable $e) {
    echo "\n[ERROR SIMULASI]: " . $e->getMessage() . "\n";
    echo "Trace:\n" . $e->getTraceAsString() . "\n";
}
