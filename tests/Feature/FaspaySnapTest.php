<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class FaspaySnapTest extends TestCase
{
    /**
     * Test Inquiry Endpoint
     */
    public function test_faspay_inquiry_endpoint()
    {
        $response = $this->postJson('/api/faspay/snap/inquiry', [
            'virtualAccountNo' => '3685011234567890',
            'partnerServiceId' => '368501',
            'customerNo' => '1234567890',
            'inquiryRequestId' => 'REQ-123'
        ]);

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
        $response = $this->postJson('/api/faspay/snap/payment', [
            'virtualAccountNo' => '3685011234567890',
            'partnerServiceId' => '368501',
            'customerNo' => '1234567890',
            'paymentRequestId' => 'PAY-123'
        ]);

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
        $response = $this->postJson('/api/faspay/payment-notification', [
            'trx_id' => 'TRX-123',
            'bill_no' => 'BILL-123',
            'payment_status_code' => '2'
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'responseCode' => '2000000',
            'responseMessage' => 'Success',
        ]);
    }
}
