<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QadService;

class QadTestingManual extends Command
{
    protected $signature = 'qad:test-manual {--new}';
    protected $description = 'Manual testing for Customer and Sales Order recreation';

    public function handle(QadService $qadService)
    {
        $this->info("Starting QAD Manual Testing...");

        $customerCode = "ZH00002";
        if ($this->option('new')) {
            $customerCode = "ZH" . rand(1000, 9999);
            $this->info("Using new customer code: $customerCode");
        }

        // 1. Create Customer
        $this->comment("1. Creating Customer ($customerCode)...");
        $customerPayload = [
            "addressName" => "PT Test",
            "addressSearchName" => "PT Test",
            "businessRelationCode" => $customerCode,
            "city" => "Jakarta",
            "countryCode" => "ID",
            "languageCode" => "us",
            "street1" => "jalan xxxx",
            "street2" => "jalan xxxx",
            "isTaxInCity" => true,
            "taxZone" => "IDN",
            "taxClass" => "PPN",
            "reminderCountryCode" => "ID",
            "reminderLanguageCode" => "us",
            "reminderTaxZone" => "IDN",
            "customerCode" => $customerCode,
            "isActive" => true,
            "isBusinessRelationActive" => true,
            "businessRelationName" => "PT Test",
            "invoiceControlGLProfileCode" => "12101",
            "creditNoteControlGLProfileCode" => "12101",
            "prePaymentControlGLProfileCode" => "12101",
            "salesAccountGLProfileCode" => "41101",
            "currencyCode" => "IDR",
            "customerTypeCode" => "LOC",
            "creditTermsCode" => "CIA",
            "creditTermsType" => "NORMAL",
            "invoiceStatusCode" => "APPROVED-AR",
            "isTaxable" => true,
            "sharedSetCode" => "MCR-CUST",
            "vatDeliveryType" => "SERVICE",
            "vatPercentageLevel" => "NONE",
            "addressTypeCode" => "HEADOFFICE",
            "isBusinessRelationFieldsEnabled" => true,
            "customerCurrencyCode" => "IDR",
            "isOverruleAllowedSOCreditLimit" => true
        ];

        $customerResult = $qadService->createCustomer($customerPayload);
        if ($customerResult) {
            $this->info("Customer Creation SUCCESS!");
            $this->line(json_encode($customerResult, JSON_PRETTY_PRINT));
        } else {
            $this->error("Customer Creation FAILED (Check Logs).");
            
            $this->comment("Checking if customer already exists...");
            $check = $qadService->getCustomer($customerCode, 'MCR-CUST');
            if ($check) {
                $this->info("Customer already exists. Attempting UPDATE...");
                $updateRes = $qadService->updateCustomer($customerPayload);
                if ($updateRes) {
                    $this->info("Customer Update SUCCESS!");
                } else {
                    $this->error("Customer Update FAILED.");
                }
            }
        }

        $this->newLine();

        // 2. Create Sales Order
        $soNumber = "ZH-00992";
        if ($this->option('new')) {
            $soNumber = "ZH-" . rand(1000, 9999);
            $this->info("Using new SO number: $soNumber");
        }

        $this->comment("2. Creating Sales Order ($soNumber)...");
        $soPayload = [
            "domainCode" => "MCR",
            "salesOrderNumber" => $soNumber,
            "billToCustomerCode" => "CS00003",
            "soldToCustomerCode" => "CS00003",
            "shipToCustomerCode" => "CS00003",
            "orderDate" => "2026-05-01T00:00:00.000Z",
            "dueDate" => "2026-05-01T00:00:00.000Z",
            "requiredDate" => "2026-05-01T00:00:00.000Z",
            "shipDate" => "2026-05-01T00:00:00.000Z",
            "promiseDate" => "2026-05-01T00:00:00.000Z",
            "creditTermsCode" => "CIA",
            "remarks" => "remarks",
            "purchaseOrderNumber" => "Poxx99",
            "taxClass" => "PPN",
            "isTaxable" => true,
            "salespersonCode_01" => "SLS00001",
            "isSelfBillingEnabled" => true,
            "salesOrderLines" => [
                [
                    "salesOrderNumber" => $soNumber,
                    "salesOrderLine" => 1,
                    "itemCode" => "FDA010-SK01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => "2026-05-01T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT"
                ],
                [
                    "salesOrderNumber" => $soNumber,
                    "salesOrderLine" => 2,
                    "itemCode" => "FDA010-MI01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => "2026-05-01T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT"
                ],
                [
                    "salesOrderNumber" => $soNumber,
                    "salesOrderLine" => 3,
                    "itemCode" => "FDA010-CM01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => "2026-05-01T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT"
                ],
                [
                    "salesOrderNumber" => $soNumber,
                    "salesOrderLine" => 4,
                    "itemCode" => "FDA010-JE01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => "2026-05-01T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT"
                ],
                [
                    "salesOrderNumber" => $soNumber,
                    "salesOrderLine" => 5,
                    "itemCode" => "FDA010-FZ01",
                    "quantityOrdered" => 6,
                    "listPrice" => 111000,
                    "netPrice" => 111000,
                    "discountPercent" => 0,
                    "dueDate" => "2026-05-01T00:00:00.000Z",
                    "isTaxable" => true,
                    "salesAcct" => "41101",
                    "salesCC" => "",
                    "discountAcct" => "41101",
                    "discountCC" => "",
                    "unitOfMeasure" => "BT"
                ]
            ]
        ];

        $soResult = $qadService->createSalesOrder($soPayload);
        if ($soResult) {
            $this->info("Sales Order Creation SUCCESS!");
            $this->line(json_encode($soResult, JSON_PRETTY_PRINT));
        } else {
            $this->error("Sales Order Creation FAILED (Check Logs).");
        }

        return 0;
    }
}
