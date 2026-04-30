<?php

namespace App\Console\Commands;

use App\Services\QadService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

/**
 * Payload selaras contoh internal (swagger SalesOrder_Create + SalesOrderLineCreate tanpa field ekstra).
 */
class TestQidSalesOrderFormat extends Command
{
    protected $signature = 'qid:test-so-format
        {--customer= : bill/sold/ship (default config qidapi.test_so_customer)}
        {--item= : Kode item baris (default config qidapi.test_so_item)}
        {--ws= : salesOrderNumber; jika kosong diisi WS acak 961000–979999}
        {--remarks= : Teks remarks (default mirip contoh ORD-…)}
        {--po= : purchaseOrderNumber; jika kosong diisi unik numerik 10 digit}
        {--date= : Tanggal order Y-m-d (default hari ini)}';

    protected $description = 'Uji create Sales Order QID dengan format payload minimal (contoh WS000001 + ZH + FMB010-MD03 + PK)';

    public function handle(QadService $qad): int
    {
        if (! $qad->isConfigured()) {
            $this->error('QIDAPI belum dikonfigurasi (.env: QIDAPI_BASE_URL, USERNAME, PASSWORD, APPS_ID).');

            return self::FAILURE;
        }

        $customer = (string) ($this->option('customer') ?: config('qidapi.test_so_customer', 'ZH78584'));
        $item = (string) ($this->option('item') ?: config('qidapi.test_so_item', 'FMB010-MD03'));

        $ws = $this->option('ws');
        if (! $ws) {
            $ws = 'WS' . str_pad((string) random_int(961000, 979999), 6, '0', STR_PAD_LEFT);
        }

        $day = $this->option('date') ? Carbon::parse($this->option('date'))->startOfDay() : now()->startOfDay();
        $dateIso = $day->format('Y-m-d') . 'T00:00:00.000Z';
        $lineDueIso = $day->copy()->addDays(7)->format('Y-m-d') . 'T00:00:00.000Z';

        $remarks = (string) ($this->option('remarks') ?: ('ORD-' . $day->format('Ymd') . '-TEST'));
        $po = $this->option('po');
        if (! $po) {
            $po = substr(preg_replace('/\D/', '', (string) microtime(true)), -10);
            $po = str_pad($po, 10, '0', STR_PAD_LEFT);
        }

        $payload = [
            'domainCode' => 'MCR',
            'salesOrderNumber' => $ws,
            'billToCustomerCode' => $customer,
            'soldToCustomerCode' => $customer,
            'shipToCustomerCode' => $customer,
            'orderDate' => $dateIso,
            'dueDate' => $dateIso,
            'requiredDate' => $dateIso,
            'shipDate' => $dateIso,
            'promiseDate' => $dateIso,
            'creditTermsCode' => 'CIA',
            'remarks' => $remarks,
            'purchaseOrderNumber' => $po,
            'taxClass' => 'PPN',
            'isTaxable' => true,
            'salespersonCode_01' => 'SLS00001',
            'isSelfBillingEnabled' => true,
            'salesOrderLines' => [
                [
                    'salesOrderNumber' => $ws,
                    'salesOrderLine' => 1,
                    'itemCode' => $item,
                    'quantityOrdered' => 1,
                    'unitOfMeasure' => 'PK',
                    'listPrice' => 111000,
                    'discountPercent' => 0,
                    'netPrice' => 111000,
                    'dueDate' => $lineDueIso,
                    'isTaxable' => true,
                    'salesAcct' => '41101',
                    'salesCC' => '',
                    'discountAcct' => '41101',
                    'discountCC' => '',
                ],
            ],
        ];

        $this->info('Payload (create):');
        $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $result = $qad->createSalesOrder($payload);

        if (! is_array($result)) {
            $this->error('Tidak ada respons (null). Cek koneksi / log.');

            return self::FAILURE;
        }

        if (($result['error']['isError'] ?? false)) {
            $this->error('GAGAL: ' . json_encode($result['error'] ?? $result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            return self::FAILURE;
        }

        $this->info('BERHASIL membuat Sales Order.');
        $this->line(json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
