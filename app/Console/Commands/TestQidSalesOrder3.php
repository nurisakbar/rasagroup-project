<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class TestQidSalesOrder3 extends Command
{
    protected $signature = 'qid:test-so3';
    protected $description = 'Test Sales Order creation with exact values';

    public function handle(QidApiService $qidApi)
    {
        $this->info("Mencoba Pembuatan SO...");
        $salesOrderNumber = 'W2619999';
        $dateIso = "2026-10-02T00:00:00.000Z";

        $payload = [
            "domainCode" => "MCR",
            "siteCode" => "MCR",
            "salesOrderNumber" => $salesOrderNumber,
            "billToCustomerCode" => "CS00271",
            "soldToCustomerCode" => "CS00271",
            "shipToCustomerCode" => "CS00271",
            "orderDate" => $dateIso,
            "dueDate" => $dateIso,
            "requiredDate" => $dateIso,
            "shipDate" => $dateIso,
            "promiseDate" => $dateIso,
            "creditTermsCode" => "CIA",
            "remarks" => "TOT: pembayaran tempo 45",
            "purchaseOrderNumber" => "2610028199",
            "taxClass" => "PPN",
            "taxEnvironment" => "IDN",
            "isTaxable" => true,
            "isSelfBillingEnabled" => true,
            "currencyCode" => "IDR",
            "isConfirmed" => true,
            "salesOrderLines" => [
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 1,
                    "itemCode" => "FDB010-MA01",
                    "quantityOrdered" => 6,
                    "unitOfMeasure" => "PK",
                    "listPrice" => 196216,
                    "discountPercent" => 0,
                    "netPrice" => 196216,
                    "dueDate" => "2026-10-09T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "siteCode" => "MCR",
                    "locationCode" => "FG001"
                ]
            ]
        ];

        $result = $qidApi->post('/api/transaction/sales-orders/create', $payload);

        if ($result && !($result['error']['isError'] ?? false)) {
            $this->info('BERHASIL!');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        } else {
            $this->error('GAGAL.');
            $this->line(json_encode($result, JSON_PRETTY_PRINT));
        }

        return 0;
    }
}
