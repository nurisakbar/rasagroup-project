<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QidApiService;

class TestQidSalesOrder extends Command
{
    protected $signature = 'qid:test-so';
    protected $description = 'Test Sales Order creation with required SO number';

    public function handle(QidApiService $qidApi)
    {
        $this->info("Mencoba Pembuatan SO dengan salesOrderNumber wajib...");
        $salesOrderNumber = 'TESTSO' . now()->format('YmdHis');
        $dateIso = now()->format('Y-m-d') . "T00:00:00.000Z";

        $payload = [
            "domainCode" => "MCR",
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
                    "itemCode" => "FDA010-PI01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT",
                ],
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 2,
                    "itemCode" => "FDA010-MI01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT",
                ],
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 3,
                    "itemCode" => "FDA010-CM01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT",
                ],
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 4,
                    "itemCode" => "FDA010-JE01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT",
                ],
                [
                    "salesOrderNumber" => $salesOrderNumber,
                    "salesOrderLine" => 5,
                    "itemCode" => "FDA010-FZ01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => $dateIso,
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "siteCode" => "MCR",
                    "locationCode" => "FG001",
                    "unitOfMeasure" => "BT"
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
