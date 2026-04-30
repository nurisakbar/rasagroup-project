<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QidSalesOrderFormatTest extends TestCase
{
    public function test_qid_test_so_format_hits_expected_endpoint_shape(): void
    {
        Http::fake([
            '*/authorization/login' => Http::response([
                'data' => [
                    'token' => 'fake-jwt',
                    'username' => 'tester',
                    'expiresIn' => 3600,
                ],
            ], 200),
            '*/api/transaction/sales-orders/create' => Http::response([
                'error' => [
                    'isError' => false,
                    'errorMessages' => [],
                ],
                'data' => [
                    'salesOrderNumber' => 'WS961111',
                    'domainCode' => 'MCR',
                ],
            ], 200),
        ]);

        $code = Artisan::call('qid:test-so-format', [
            '--ws' => 'WS961111',
            '--customer' => 'ZH78584',
            '--item' => 'FMB010-MD03',
            '--remarks' => 'ORD-20260428-0002',
            '--po' => '2604280002',
            '--date' => '2026-04-28',
        ]);

        $this->assertSame(0, $code);

        Http::assertSent(function (\Illuminate\Http\Client\Request $request) {
            if (! str_contains($request->url(), 'sales-orders/create')) {
                return false;
            }
            $body = $request->data();

            return ($body['domainCode'] ?? null) === 'MCR'
                && ($body['salesOrderNumber'] ?? null) === 'WS961111'
                && ($body['billToCustomerCode'] ?? null) === 'ZH78584'
                && ($body['purchaseOrderNumber'] ?? null) === '2604280002'
                && ($body['salesOrderLines'][0]['unitOfMeasure'] ?? null) === 'PK'
                && ($body['salesOrderLines'][0]['itemCode'] ?? null) === 'FMB010-MD03'
                && ! isset($body['currencyCode'])
                && ! isset($body['siteCode']);
        });
    }
}
