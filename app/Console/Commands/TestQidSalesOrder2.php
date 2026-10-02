<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class TestQidSalesOrder2 extends Command
{
    protected $signature = 'qid:test-so2';
    protected $description = 'Test Sales Order creation with required SO number for ZH47310';

    public function handle(QidApiService $qidApi)
    {
        $this->info("Mencoba Pembuatan SO untuk ZH47310...");
        $salesOrderNumber = 'TESTSO' . now()->format('YmdHis');
        $dateIso = now()->format('Y-m-d') . "T00:00:00.000Z";

        $payload = [
            "domainCode" => "MCR",
            "salesOrderNumber" => $salesOrderNumber,
            "billToCustomerCode" => "ZH47310",
            "soldToCustomerCode" => "ZH47310",
            "shipToCustomerCode" => "ZH47310",
            "orderDate" => $dateIso,
            "dueDate" => $dateIso,
            "requiredDate" => $dateIso,
            "shipDate" => $dateIso,
            "promiseDate" => $dateIso,
            "creditTermsCode" => "CIA",
            "remarks" => "remarks",
            "purchaseOrderNumber" => "Poxx99",
            "taxClass" => "PPN",
            "taxEnvironment" => "IDN",
            "isTaxable" => true,
            "isConfirmed" => true,
            "salespersonCode_01" => "SLS00001",
            "isSelfBillingEnabled" => true,
            "currencyCode" => "IDR",
            "siteCode" => "MCR",
            "salesOrderLines" => [
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 1,
                    "itemCode" => "FDB010-MA01",
                    "quantityOrdered" => 6,
                    "listPrice" => 196216,
                    "netPrice" => 196216,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "siteCode" => "MCR",
                    "locationCode" => "FG001",
                    "unitOfMeasure" => "PK"
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
