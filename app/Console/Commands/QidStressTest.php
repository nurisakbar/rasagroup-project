<?php

namespace App\Console\Commands;

use App\Services\QadService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Pool;
use Illuminate\Support\Str;

class QidStressTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'qid:stress-test 
                            {count=5 : Number of test iterations} 
                            {--concurrent=1 : Number of concurrent requests}
                            {--type=both : Test type (customer, order, or both)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform a stress test on the QID API by creating dummy customers and sales orders';

    /**
     * Execute the console command.
     */
    public function handle(QadService $qadService)
    {
        $count = (int) $this->argument('count');
        $concurrent = (int) $this->option('concurrent');
        $type = $this->option('type');

        $this->info("Starting QID Stress Test...");
        $this->info("Target: {$count} iterations");
        $this->info("Concurrency: {$concurrent}");
        $this->info("Type: {$type}");

        if (!$qadService->isConfigured()) {
            $this->error("QID API is not configured. Check your .env file.");
            return 1;
        }

        $startTime = microtime(true);
        $results = [];

        if ($concurrent <= 1) {
            $this->runSequential($qadService, $count, $type, $results);
        } else {
            $this->runConcurrent($qadService, $count, $concurrent, $type, $results);
        }

        $totalTime = microtime(true) - $startTime;
        $this->displaySummary($results, $totalTime);
        $this->displayDetailedTable($results);

        return 0;
    }

    protected function displayDetailedTable(array $results)
    {
        $this->newLine();
        $this->info("Detailed Iteration Report:");
        $rows = [];
        foreach ($results as $res) {
            $rows[] = [
                $res['iteration'],
                $res['success'] ? 'SUCCESS' : 'FAILED',
                $res['status'],
                number_format($res['time'], 2) . 's',
                Str::limit($res['message'], 50)
            ];
        }

        $this->table(['Iter', 'Result', 'Status', 'Duration', 'Message'], $rows);
    }

    protected function runSequential(QadService $qadService, int $count, string $type, array &$results)
    {
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        for ($i = 0; $i < $count; $i++) {
            $iterStart = microtime(true);
            $iterResult = $this->performIteration($qadService, $i, $type);
            $iterResult['time'] = microtime(true) - $iterStart;
            $results[] = $iterResult;
            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function runConcurrent(QadService $qadService, int $count, int $concurrent, string $type, array &$results)
    {
        $this->warn("Concurrent mode uses direct HTTP calls. Timeout set to 120s.");
        
        $token = $qadService->getToken();
        $baseUrl = rtrim(config('qidapi.base_url'), '/');
        
        $iterations = [];
        for ($i = 0; $i < $count; $i++) {
            $iterations[] = $i;
        }

        $chunks = array_chunk($iterations, $concurrent);
        $bar = $this->output->createProgressBar($count);
        $bar->start();

        foreach ($chunks as $chunk) {
            $chunkStart = microtime(true);
            $responses = Http::timeout(120)->pool(function (Pool $pool) use ($chunk, $baseUrl, $token) {
                $poolRequests = [];
                foreach ($chunk as $i) {
                    $payload = $this->getDummyOrderPayload($i, 'ZH78584');
                    $poolRequests[] = $pool->as($i)->withToken($token)
                        ->post("{$baseUrl}/api/transaction/sales-orders/create", $payload);
                }
                return $poolRequests;
            });
            $chunkDuration = microtime(true) - $chunkStart;

            foreach ($responses as $i => $response) {
                if ($response instanceof \Illuminate\Http\Client\Response) {
                    $results[] = [
                        'iteration' => $i + 1,
                        'success' => $response->successful(),
                        'status' => $response->status(),
                        'message' => $response->successful() ? 'OK' : $response->body(),
                        'time' => $chunkDuration,
                    ];
                } else {
                    $results[] = [
                        'iteration' => $i + 1,
                        'success' => false,
                        'status' => 0,
                        'message' => 'Connection Error: ' . (method_exists($response, 'getMessage') ? $response->getMessage() : 'Unknown Error'),
                        'time' => $chunkDuration,
                    ];
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
    }

    protected function performIteration(QadService $qadService, int $i, string $type): array
    {
        $res = ['iteration' => $i + 1, 'success' => true, 'message' => 'OK', 'status' => 200];
        $id = Str::random(8);

        // Increase timeout for sequential too
        config(['http.global_timeout' => 120]);

        try {
            if ($type === 'customer' || $type === 'both') {
                $custCode = 'STRS' . strtoupper(Str::random(4));
                $payload = [
                    'businessRelationCode' => $custCode,
                    'businessRelationName1' => "STRESS TEST CUST {$i} {$id}",
                    'businessRelationName2' => '',
                    'businessRelationName3' => '',
                    'businessRelationSearchName' => substr("STRESS TEST CUST {$i} {$id}", 0, 20),
                    'corporateGroupCode' => '',
                    'headOfficeAddressName' => "STRESS TEST CUST {$i} {$id}",
                    'headOfficeAddressSearchName' => substr("STRESS TEST CUST {$i} {$id}", 0, 20),
                    'headOfficeAddressTypeCode' => 'HEADOFFICE',
                    'headOfficeBusinessRelationCode' => $custCode,
                    'headOfficeCity' => 'Jakarta',
                    'headOfficeLanguageCode' => 'us',
                    'headOfficeLatitude' => 0,
                    'headOfficeLongitude' => 0,
                    'headOfficeStreet1' => 'Stress Test Alamat',
                    'headOfficeStreet2' => 'Stress Test Alamat 2',
                    'headOfficeTaxClass' => 'PPN',
                    'headOfficeTaxZone' => 'IDN',
                    'headOfficeTelephone' => '628123456789',
                    'headOfficeWebSite' => '',
                    'headOfficeZipCode' => '10110',
                    'isActive' => true,
                ];
                
                $brRes = $qadService->createBusinessRelation($payload);
                if (!$brRes || ($brRes['error']['isError'] ?? false)) {
                    $msg = json_encode($brRes['error']['errorMessages'] ?? $brRes);
                    return ['iteration' => $i + 1, 'success' => false, 'message' => 'BR Create Failed: ' . $msg, 'status' => 500];
                }

                $customerPayload = [
                    'addressName' => "STRESS TEST CUST {$i} {$id}",
                    'addressSearchName' => substr("STRESS TEST CUST {$i} {$id}", 0, 20),
                    'businessRelationCode' => $custCode,
                    'city' => 'Jakarta',
                    'countryCode' => 'ID',
                    'languageCode' => 'us',
                    'street1' => 'Stress Test Alamat',
                    'street2' => 'Stress Test Alamat 2',
                    'isTaxInCity' => true,
                    'taxZone' => 'IDN',
                    'taxClass' => 'PPN',
                    'reminderCountryCode' => 'ID',
                    'reminderLanguageCode' => 'us',
                    'reminderTaxZone' => 'IDN',
                    'customerCode' => $custCode,
                    'isActive' => true,
                    'isBusinessRelationActive' => true,
                    'businessRelationName' => "STRESS TEST CUST {$i} {$id}",
                    'invoiceControlGLProfileCode' => '12101',
                    'creditNoteControlGLProfileCode' => '12101',
                    'prePaymentControlGLProfileCode' => '12101',
                    'salesAccountGLProfileCode' => '41101',
                    'currencyCode' => 'IDR',
                    'customerTypeCode' => 'LOC',
                    'creditTermsCode' => 'CIA',
                    'creditTermsType' => 'NORMAL',
                    'invoiceStatusCode' => 'APPROVED-AR',
                    'isTaxable' => true,
                    'sharedSetCode' => 'MCR-CUST',
                    'vatDeliveryType' => 'SERVICE',
                    'vatPercentageLevel' => 'NONE',
                    'addressTypeCode' => 'HEADOFFICE',
                    'isBusinessRelationFieldsEnabled' => true,
                    'customerCurrencyCode' => 'IDR',
                    'isOverruleAllowedSOCreditLimit' => true,
                ];

                $cRes = $qadService->createCustomer($customerPayload);
                if (!$cRes || ($cRes['error']['isError'] ?? false)) {
                    $msg = json_encode($cRes['error']['errorMessages'] ?? $cRes);
                    return ['iteration' => $i + 1, 'success' => false, 'message' => 'Cust Create Failed: ' . $msg, 'status' => 500];
                }

                $qadService->createCustomerData(['customerCode' => $custCode]);
            }

            if ($type === 'order' || $type === 'both') {
                $orderPayload = $this->getDummyOrderPayload($i, $custCode ?? 'ZH78584');
                $soRes = $qadService->createSalesOrder($orderPayload);
                
                if (!$soRes || ($soRes['error']['isError'] ?? false)) {
                    return ['iteration' => $i + 1, 'success' => false, 'message' => 'SO Create Failed: ' . json_encode($soRes), 'status' => 500];
                }
            }
        } catch (\Exception $e) {
            return ['iteration' => $i + 1, 'success' => false, 'message' => $e->getMessage(), 'status' => 500];
        }

        return $res;
    }

    protected function getDummyOrderPayload(int $i, string $customerCode): array
    {
        $id = Str::random(4);
        // Use shorter, numeric-only suffix to avoid BadRequest for SO number
        $wsNumber = 'WS' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
        $date = now()->format('Y-m-d') . 'T00:00:00.000Z';

        return [
            'domainCode' => 'MCR',
            'salesOrderNumber' => $wsNumber,
            'billToCustomerCode' => $customerCode,
            'soldToCustomerCode' => $customerCode,
            'shipToCustomerCode' => $customerCode,
            'orderDate' => $date,
            'dueDate' => $date,
            'requiredDate' => $date,
            'shipDate' => $date,
            'promiseDate' => $date,
            'creditTermsCode' => 'CIA',
            'remarks' => "STRS-{$i}-{$id}",
            'purchaseOrderNumber' => 'PO' . date('ymd') . str_pad((string) $i, 3, '0', STR_PAD_LEFT),
            'taxClass' => 'PPN',
            'isTaxable' => true,
            'salespersonCode_01' => 'SLS00001',
            'isSelfBillingEnabled' => true,
            'salesOrderLines' => [
                [
                    'salesOrderNumber' => $wsNumber,
                    'salesOrderLine' => 1,
                    'itemCode' => 'FMB010-MD03',
                    'quantityOrdered' => 1,
                    'unitOfMeasure' => 'PK',
                    'listPrice' => 111000,
                    'discountPercent' => 0,
                    'netPrice' => 111000,
                    'dueDate' => $date,
                    'isTaxable' => true,
                    'salesAcct' => '41101',
                    'salesCC' => '',
                    'discountAcct' => '41101',
                    'discountCC' => ''
                ]
            ]
        ];
    }

    protected function displaySummary(array $results, float $totalTime)
    {
        $successCount = collect($results)->where('success', true)->count();
        $failCount = collect($results)->where('success', false)->count();
        $avgTime = $totalTime / count($results);

        $this->table(
            ['Total', 'Success', 'Failed', 'Total Time (s)', 'Avg Time/Iter (s)'],
            [[count($results), $successCount, $failCount, number_format($totalTime, 2), number_format($avgTime, 2)]]
        );

        if ($failCount > 0) {
            $this->error("Some requests failed. Check logs for details.");
            foreach (collect($results)->where('success', false)->take(5) as $fail) {
                $this->warn("Iteration {$fail['iteration']}: {$fail['message']}");
            }
        } else {
            $this->info("All requests completed successfully!");
        }
    }
}
