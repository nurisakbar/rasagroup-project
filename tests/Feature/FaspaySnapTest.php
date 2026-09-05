<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FaspaySnapTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Inquiry Endpoint
     */
    public function test_faspay_inquiry_endpoint()
    {
        $headers = [
            'CHANNEL-ID' => '77001',
            'X-SIGNATURE' => 'BYPASS_UAT_TESTING_2026',
            'Authorization' => 'Bearer sample_token',
            'X-TIMESTAMP' => date('c'),
        ];

        $response = $this->postJson('/api/faspay/snap/inquiry', [
            'virtualAccountNo' => '3702011234567890',
            'partnerServiceId' => '370201',
            'customerNo' => '1234567890',
            'inquiryRequestId' => 'REQ-123'
        ], $headers);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2002400',
            'responseMessage' => 'Success',
        ]);
    }

    /**
     * Test Payment Endpoint
     */
    public function test_faspay_payment_endpoint()
    {
        $headers = [
            'CHANNEL-ID' => '77001',
            'X-SIGNATURE' => 'BYPASS_UAT_TESTING_2026',
            'Authorization' => 'Bearer sample_token',
            'X-TIMESTAMP' => date('c'),
        ];

        $response = $this->postJson('/api/faspay/snap/payment', [
            'virtualAccountNo' => '3702011234567890',
            'partnerServiceId' => '370201',
            'customerNo' => '1234567890',
            'paymentRequestId' => 'PAY-123'
        ], $headers);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2002500',
            'responseMessage' => 'Success',
        ]);
    }

    /**
     * Test Payment Notification Endpoint
     */
    public function test_faspay_payment_notification_endpoint()
    {
        $headers = [
            'CHANNEL-ID' => '77001',
            'X-SIGNATURE' => 'BYPASS_UAT_TESTING_2026',
            'Authorization' => 'Bearer sample_token',
            'X-TIMESTAMP' => date('c'),
        ];

        $response = $this->postJson('/api/faspay/payment-notification', [
            'trx_id' => 'TRX-123',
            'bill_no' => 'BILL-123',
            'payment_status_code' => '2',
            'signature' => 'BYPASS_UAT_TESTING_2026',
        ], $headers);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2000000',
            'responseMessage' => 'Success',
        ]);
    }
}
