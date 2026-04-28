<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class TestQidSalesOrder extends Command
{
    protected $signature = 'qid:test-so';
    protected $description = 'Test Auto-Generated SO Number';

    public function handle(QidApiService $qidApi)
    {
        $this->info("Mencoba Pembuatan SO dengan Nomor Otomatis...");

        $payload = [
            "domainCode" => "MCR",
            "salesOrderNumber" => "", // Kosongkan untuk auto-generate
            "billToCustomerCode" => "CS00271",
            "soldToCustomerCode" => "CS00271",
            "shipToCustomerCode" => "CS00271",
            "orderDate" => date('Y-m-d') . "T00:00:00",
            "creditTermsCode" => "CIA",
            "taxClass" => "",
            "taxEnvironment" => "IDN",
            "isTaxable" => true,
            "isConfirmed" => true,
            "currencyCode" => "IDR",
            "siteCode" => "MCR",
            "salesOrderLines" => [
                [
                    "salesOrderLine" => 1,
                    "itemCode" => "FDA010-PI01",
                    "quantityOrdered" => 1,
                    "unitOfMeasure" => "BT",
                    "listPrice" => 52440,
                    "netPrice" => 52440,
                    "siteCode" => "MCR",
                    "locationCode" => "FG001"
                ]
            ]
        ];

        $result = $qidApi->post('/api/transaction/sales-orders/create', $payload);

        if ($result) {
            $this->info('BERHASIL!');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->error('GAGAL (BadRequest).');
        }

        return 0;
    }
}
