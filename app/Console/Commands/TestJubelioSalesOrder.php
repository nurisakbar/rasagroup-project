<?php

namespace App\Console\Commands;

use App\Jobs\SyncOrderToJubelio;
use App\Models\Order;
use App\Services\JubelioService;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class TestJubelioSalesOrder extends Command
{
    protected $signature = 'jubelio:test-so
        {order? : UUID atau nomor pesanan lokal}
        {--check : Hanya cek login, lokasi, customer, dan item to-sell}
        {--execute : POST /sales/orders/ ke Jubelio (default: dry-run)}
        {--force : Lewati konfirmasi saat --execute}
        {--sync : Jalankan SyncOrderToJubelio job & simpan ke database}
        {--contact= : Override contact_id}
        {--location= : Override location_id}
        {--item= : Override item_code untuk test minimal tanpa order}';

    protected $description = 'Uji pembuatan Sales Order ke Jubelio (dry-run, execute, atau sync dari order lokal)';

    public function handle(JubelioService $jubelio): int
    {
        if (! config('jubelio.email') && ! env('JUBELIO_EMAIL')) {
            $this->error('JUBELIO_EMAIL belum di-set di .env');

            return self::FAILURE;
        }

        $this->info('=== Jubelio Sales Order Test ===');
        $this->line('Base URL: ' . config('jubelio.base_url'));
        $this->line('SO enabled: ' . (config('jubelio.sales_order.enabled') ? 'yes' : 'no'));

        try {
            $token = $jubelio->token();
            $this->info('Login Jubelio: OK');
        } catch (\Throwable $e) {
            $this->error('Login Jubelio gagal: ' . $e->getMessage());

            return self::FAILURE;
        }

        if ($this->option('check')) {
            return $this->runPreflightChecks($jubelio, $token);
        }

        $order = $this->resolveOrder($this->argument('order'));
        if ($order) {
            return $this->testFromOrder($jubelio, $order);
        }

        return $this->testMinimalPayload($jubelio, $token);
    }

    private function runPreflightChecks(JubelioService $jubelio, string $token): int
    {
        $this->newLine();
        $this->info('--- Preflight checks ---');

        $locations = $jubelio->fetchAllLocations($token);
        $this->line('Locations: ' . count($locations));
        foreach (array_slice($locations, 0, 5) as $loc) {
            $this->line(sprintf(
                '  - [%s] %s (code: %s)',
                $loc['location_id'] ?? '?',
                $loc['location_name'] ?? '-',
                $loc['location_code'] ?? '-'
            ));
        }
        if (count($locations) > 5) {
            $this->line('  ...');
        }

        $customers = $jubelio->fetchCustomers($token);
        $this->line('Customers: ' . count($customers));
        foreach (array_slice($customers, 0, 3) as $customer) {
            $this->line(sprintf(
                '  - [%s] %s (%s)',
                $customer['contact_id'] ?? '?',
                $customer['contact_name'] ?? '-',
                $customer['email'] ?? '-'
            ));
        }

        $locationId = $this->resolveTestLocationId($jubelio, $token, $locations);
        if (! $locationId) {
            $this->error('Tidak ada location_id untuk test item to-sell');

            return self::FAILURE;
        }

        $items = $jubelio->fetchItemsToSell($token, $locationId);
        $this->line("Items to-sell @ location {$locationId}: " . count($items));
        foreach (array_slice($items, 0, 5) as $item) {
            $this->line(sprintf(
                '  - [%s] %s (code: %s, price: %s)',
                $item['item_id'] ?? '?',
                Str::limit((string) ($item['item_name'] ?? '-'), 40),
                $item['item_code'] ?? '-',
                $item['sell_price'] ?? '-'
            ));
        }

        $this->newLine();
        $this->info('Preflight selesai. Jalankan tanpa --check untuk dry-run payload.');

        return self::SUCCESS;
    }

    private function testFromOrder(JubelioService $jubelio, Order $order): int
    {
        $this->newLine();
        $this->info('Order lokal: ' . $order->order_number . ' (' . $order->id . ')');
        $this->line('Payment: ' . $order->payment_status . ' | Items: ' . $order->items->count());
        $this->line('Hub: ' . ($order->sourceWarehouse?->name ?? '-') . ' | kode_hub: ' . ($order->sourceWarehouse?->kode_hub ?? '-'));
        $this->line('Jubelio SO: ' . ($order->jubelio_salesorder_no ?: ($order->jubelio_salesorder_id ?: 'belum')));

        if ($this->option('sync')) {
            if ($order->jubelio_salesorder_id) {
                $this->warn('Order sudah tersinkron ke Jubelio (ID: ' . $order->jubelio_salesorder_id . ')');

                return self::SUCCESS;
            }

            $this->info('Menjalankan SyncOrderToJubelio (sync)...');
            SyncOrderToJubelio::dispatchSync($order->fresh());
            $order->refresh();

            if ($order->jubelio_salesorder_id) {
                $this->info('Sync berhasil!');
                $this->line('jubelio_salesorder_id: ' . $order->jubelio_salesorder_id);
                $this->line('jubelio_salesorder_no: ' . ($order->jubelio_salesorder_no ?? '-'));

                return self::SUCCESS;
            }

            $this->error('Sync selesai tapi jubelio_salesorder_id masih kosong. Cek storage/logs/jubelio-sales-order.log');

            return self::FAILURE;
        }

        $execute = (bool) $this->option('execute');
        $job = new SyncOrderToJubelio($order);
        $result = $job->debugSalesOrderToJubelio($jubelio, $execute);

        return $this->renderDebugResult($result, $execute);
    }

    private function testMinimalPayload(JubelioService $jubelio, string $token): int
    {
        $this->newLine();
        $this->info('Test minimal (tanpa order lokal)');

        $locations = $jubelio->fetchAllLocations($token);
        $locationId = $this->resolveTestLocationId($jubelio, $token, $locations);
        if (! $locationId) {
            $this->error('location_id tidak ditemukan. Set JUBELIO_TEST_LOCATION_ID atau sync hub Jubelio.');

            return self::FAILURE;
        }

        $contactId = (int) ($this->option('contact') ?: config('jubelio.test.contact_id') ?: config('jubelio.sales_order.default_contact_id'));
        $customerName = 'Test Customer';

        if ($contactId <= 0) {
            $customers = $jubelio->fetchCustomers($token);
            if (empty($customers)) {
                $this->error('Tidak ada customer di Jubelio. Set JUBELIO_TEST_CONTACT_ID.');

                return self::FAILURE;
            }
            $contactId = (int) $customers[0]['contact_id'];
            $customerName = (string) ($customers[0]['contact_name'] ?? 'Test Customer');
        }

        $itemCode = (string) ($this->option('item') ?: config('jubelio.test.item_code') ?: '');
        $jubelioItem = null;
        if ($itemCode !== '') {
            $jubelioItem = $jubelio->findItemToSellByCode($token, $locationId, $itemCode);
        } else {
            $items = $jubelio->fetchItemsToSell($token, $locationId);
            $jubelioItem = $items[0] ?? null;
            $itemCode = (string) ($jubelioItem['item_code'] ?? '');
        }

        if (! $jubelioItem) {
            $this->error("Item tidak ditemukan di location {$locationId}. Gunakan --item=KODE_SKU atau set JUBELIO_TEST_ITEM_CODE.");

            return self::FAILURE;
        }

        $price = (int) round((float) ($jubelioItem['sell_price'] ?? 0));
        if ($price <= 0) {
            $price = 10000;
        }

        $transactionDate = now()->utc()->format('Y-m-d\TH:i:s.000\Z');
        $refNo = 'TEST-' . now()->format('YmdHis');

        $payload = [
            'salesorder_id' => 0,
            'salesorder_no' => '[auto]',
            'contact_id' => $contactId,
            'customer_name' => $customerName,
            'transaction_date' => $transactionDate,
            'is_tax_included' => false,
            'note' => 'Test SO dari artisan jubelio:test-so',
            'sub_total' => $price,
            'total_disc' => 0,
            'total_tax' => 0,
            'grand_total' => $price,
            'ref_no' => $refNo,
            'location_id' => $locationId,
            'source' => (int) config('jubelio.sales_order.source', 1),
            'is_canceled' => false,
            'cancel_reason' => '',
            'cancel_reason_detail' => '',
            'channel_status' => 'Paid',
            'shipping_cost' => 0,
            'insurance_cost' => 0,
            'is_paid' => true,
            'shipping_full_name' => $customerName,
            'shipping_phone' => '6281234567890',
            'shipping_address' => 'Alamat test',
            'shipping_area' => 'Test Area',
            'shipping_city' => 'Jakarta',
            'shipping_province' => 'DKI Jakarta',
            'shipping_post_code' => '10110',
            'shipping_country' => 'Indonesia',
            'add_disc' => 0,
            'add_fee' => 0,
            'salesmen_id' => null,
            'store_id' => null,
            'service_fee' => 0,
            'payment_method' => 'online',
            'items' => [[
                'salesorder_detail_id' => 0,
                'item_id' => (int) $jubelioItem['item_id'],
                'serial_no' => null,
                'description' => (string) ($jubelioItem['item_short_name'] ?? $jubelioItem['item_name'] ?? $itemCode),
                'tax_id' => (int) ($jubelioItem['sell_tax_id'] ?? 1),
                'price' => $price,
                'unit' => (string) ($jubelioItem['sell_unit'] ?? 'Buah'),
                'qty_in_base' => 1,
                'disc' => 0,
                'disc_amount' => 0,
                'tax_amount' => 0,
                'amount' => $price,
                'location_id' => $locationId,
                'shipper' => 'Internal',
                'channel_order_detail_id' => null,
            ]],
        ];

        $this->line('Location ID: ' . $locationId);
        $this->line('Contact ID: ' . $contactId . ' (' . $customerName . ')');
        $this->line('Item: ' . $itemCode . ' (item_id: ' . $jubelioItem['item_id'] . ')');
        $this->line('Ref No: ' . $refNo);
        $this->newLine();
        $this->line('Endpoint: POST ' . rtrim((string) config('jubelio.base_url'), '/') . '/sales/orders/');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (! $this->option('execute')) {
            $this->newLine();
            $this->comment('Dry run selesai. Tambahkan --execute untuk POST ke Jubelio.');

            return self::SUCCESS;
        }

        if (! $this->option('force') && ! $this->confirm('POST sales order test ke Jubelio?', true)) {
            $this->warn('Dibatalkan.');

            return self::SUCCESS;
        }

        try {
            $result = $jubelio->createSalesOrder($token, $payload);
            $salesOrderId = (int) ($result['id'] ?? $result['salesorder_id'] ?? 0);

            $this->newLine();
            $this->info('POST berhasil!');
            $this->line('Response: ' . json_encode($result, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

            if ($salesOrderId > 0) {
                $detail = $jubelio->getSalesOrder($token, $salesOrderId);
                $this->line('Sales Order No: ' . ($detail['salesorder_no'] ?? '-'));
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            $this->error('POST gagal: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  array<int, array<string, mixed>>  $locations
     */
    private function resolveTestLocationId(JubelioService $jubelio, string $token, array $locations): ?int
    {
        $override = $this->option('location') ?: config('jubelio.test.location_id') ?: config('jubelio.sales_order.default_location_id');
        if ($override) {
            return (int) $override;
        }

        if (! empty($locations)) {
            return (int) ($locations[0]['location_id'] ?? 0) ?: null;
        }

        return null;
    }

    private function resolveOrder(?string $reference): ?Order
    {
        if (! $reference) {
            return null;
        }

        $query = Order::query()->with([
            'user',
            'items.product',
            'address.province',
            'address.regency',
            'address.district',
            'sourceWarehouse',
            'expedition',
        ]);

        if (Str::isUuid($reference)) {
            return $query->find($reference);
        }

        return $query->where('order_number', $reference)->first();
    }

    /**
     * @param  array<string, mixed>  $result
     */
    private function renderDebugResult(array $result, bool $executed): int
    {
        if (! ($result['ok'] ?? false)) {
            $this->error($result['error'] ?? 'Payload gagal disiapkan');
            if (! empty($result['issues'])) {
                $this->line(json_encode($result['issues'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
            }

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('Payload siap');
        if (! empty($result['meta'])) {
            $this->line('Meta: ' . json_encode($result['meta'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));
        }
        $this->line('Endpoint: ' . ($result['method'] ?? 'POST') . ' ' . ($result['endpoint'] ?? ''));
        $this->newLine();
        $this->line(json_encode($result['payload'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (! empty($result['skipped_http'])) {
            $this->warn($result['note'] ?? 'POST dilewati');

            return self::SUCCESS;
        }

        if (! $executed) {
            $this->newLine();
            $this->comment($result['note'] ?? 'Dry run. Tambahkan --execute untuk POST.');

            return self::SUCCESS;
        }

        $this->newLine();
        $this->info('POST response:');
        $this->line(json_encode($result['response'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        if (! empty($result['jubelio_salesorder_id'])) {
            $this->info('Jubelio SO ID: ' . $result['jubelio_salesorder_id']);
            $this->line('Jubelio SO No: ' . ($result['jubelio_salesorder_no'] ?? '-'));
        }

        $this->comment($result['note'] ?? '');

        return ($result['jubelio_salesorder_id'] ?? null) ? self::SUCCESS : self::FAILURE;
    }
}
