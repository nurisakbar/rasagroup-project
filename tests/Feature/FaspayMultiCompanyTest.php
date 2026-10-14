<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\FaspayConfig;
use App\Services\FaspayService;
use App\Services\FaspaySnapService;
use Illuminate\Http\Request;
use Tests\TestCase;

class FaspayMultiCompanyTest extends TestCase
{
    public function test_default_company_is_mcr()
    {
        $this->assertEquals('mcr', FaspayConfig::getDefaultCompany());
    }

    public function test_get_company_config_rdi()
    {
        $config = FaspayConfig::getCompanyConfig('rdi');
        $this->assertEquals('rdi', $config['code']);
        $this->assertEquals('PT Rasa Distribusi Indonesia', $config['name']);
        $this->assertEquals('37020', $config['merchant_id']);
    }

    public function test_get_company_config_mcr()
    {
        $config = FaspayConfig::getCompanyConfig('mcr');
        $this->assertEquals('mcr', $config['code']);
        $this->assertEquals('PT Multi Citra Rasa', $config['name']);
    }

    public function test_get_company_display_name()
    {
        $this->assertEquals('PT Rasa Distribusi Indonesia', FaspayConfig::getCompanyName('rdi'));
        $this->assertEquals('PT Multi Citra Rasa', FaspayConfig::getCompanyName('mcr'));
    }

    public function test_va_prefix_resolution()
    {
        // 8 Faspay Virtual Account Channels
        $expectedChannels = [
            'faspay_permata_va'  => '735136',     // 402 - Permata
            'faspay_bsi_va'      => '12601038',   // 723 - BSI Virtual Account
            'faspay_cimb_va'     => '222555',     // 825 - CIMB VA
            'faspay_maybank_va'  => '78218638',   // 408 - Maybank Virtual Account
            'faspay_danamon_va'  => '797055',     // 708 - Danamon VA
            'faspay_mandiri_va'  => '88558038',   // 802 - Mandiri Virtual Account
            'faspay_sinarmas_va' => '885655',     // 818 - Sinarmas Virtual Account
            'faspay_bni_va'      => '9881236387', // 801 - BNI Virtual Account
        ];

        foreach ($expectedChannels as $method => $prefix) {
            $this->assertEquals($prefix, FaspayConfig::getVaPrefix($method, 'mcr'));
        }

        // Test getAllVaPrefixes contains all 8 prefixes
        $allPrefixes = FaspayConfig::getAllVaPrefixes();
        foreach ($expectedChannels as $prefix) {
            $this->assertContains($prefix, $allPrefixes);
        }
    }

    public function test_resolve_company_by_merchant_id()
    {
        $resolvedRdi = FaspayConfig::resolveCompanyByMerchantId('37020');
        $this->assertEquals('rdi', $resolvedRdi);
        $this->assertNull(FaspayConfig::resolveCompanyByMerchantId('non_existent_id'));
    }

    public function test_resolve_company_from_order()
    {
        $order = new Order();
        $order->company = 'mcr';

        $request = new Request();
        $company = FaspayConfig::resolveCompanyFromRequest($request, $order);
        $this->assertEquals('mcr', $company);

        $orderRdi = new Order();
        $orderRdi->company = 'rdi';
        $this->assertEquals('rdi', FaspayConfig::resolveCompanyFromRequest($request, $orderRdi));
    }

    public function test_resolve_company_from_request_headers_and_body()
    {
        // Header X-PARTNER-ID
        $requestWithHeader = Request::create('/api/faspay/inquiry', 'POST', [], [], [], [
            'HTTP_X-PARTNER-ID' => '37020'
        ]);
        $this->assertEquals('rdi', FaspayConfig::resolveCompanyFromRequest($requestWithHeader));

        // Body merchantId
        $requestWithBody = Request::create('/api/faspay/inquiry', 'POST', ['merchantId' => '37020']);
        $this->assertEquals('rdi', FaspayConfig::resolveCompanyFromRequest($requestWithBody));

        // Fallback default
        $emptyRequest = Request::create('/api/faspay/inquiry', 'POST');
        $this->assertEquals('mcr', FaspayConfig::resolveCompanyFromRequest($emptyRequest));
    }

    public function test_order_model_fillable_has_company()
    {
        $order = new Order(['company' => 'mcr']);
        $this->assertEquals('mcr', $order->company);
        $this->assertContains('company', $order->getFillable());
    }

    public function test_services_support_multi_company()
    {
        $snapRdi = new FaspaySnapService('rdi');
        $this->assertEquals('rdi', $snapRdi->getCompany());

        $snapMcr = new FaspaySnapService('mcr');
        $this->assertEquals('mcr', $snapMcr->getCompany());

        $legacyRdi = new FaspayService('rdi');
        $this->assertEquals('rdi', $legacyRdi->getCompany());

        $legacyMcr = new FaspayService('mcr');
        $this->assertEquals('mcr', $legacyMcr->getCompany());
    }

    public function test_legacy_service_callback_signature()
    {
        $legacyRdi = new FaspayService('rdi');
        $sig = $legacyRdi->generateCallbackSignature('BILL123', '2');
        $this->assertNotEmpty($sig);
        $this->assertEquals(40, strlen($sig)); // sha1 is 40 hex chars
    }

    public function test_va_number_generation_for_all_8_channels()
    {
        $channels = [
            '402' => ['method' => 'faspay_permata_va',  'name' => 'Permata',  'expected_prefix' => '735136'],
            '723' => ['method' => 'faspay_bsi_va',      'name' => 'BSI',      'expected_prefix' => '12601038'],
            '825' => ['method' => 'faspay_cimb_va',     'name' => 'CIMB',     'expected_prefix' => '222555'],
            '408' => ['method' => 'faspay_maybank_va',  'name' => 'Maybank',  'expected_prefix' => '78218638'],
            '708' => ['method' => 'faspay_danamon_va',  'name' => 'Danamon',  'expected_prefix' => '797055'],
            '802' => ['method' => 'faspay_mandiri_va',  'name' => 'Mandiri',  'expected_prefix' => '88558038'],
            '818' => ['method' => 'faspay_sinarmas_va', 'name' => 'Sinarmas', 'expected_prefix' => '885655'],
            '801' => ['method' => 'faspay_bni_va',      'name' => 'BNI',      'expected_prefix' => '9881236387'],
        ];

        $orderNumber = 'W2610001';
        $numericOrder = preg_replace('/[^0-9]/', '', $orderNumber);

        foreach ($channels as $channelId => $data) {
            $prefix = FaspayConfig::getVaPrefix($data['method'], 'mcr');
            $this->assertEquals($data['expected_prefix'], $prefix, "Prefix mismatch for {$data['name']}");

            $targetLength = 16;
            $prefixLength = strlen($prefix);
            $freeDigitsLength = $targetLength - $prefixLength;

            if (strlen($numericOrder) > $freeDigitsLength) {
                $freeDigits = substr($numericOrder, -$freeDigitsLength);
            } else {
                $freeDigits = str_pad($numericOrder, $freeDigitsLength, '0', STR_PAD_LEFT);
            }

            $vaNumber = $prefix . $freeDigits;

            // Assert exactly 16 digits
            $this->assertEquals(16, strlen($vaNumber), "VA length not 16 for {$data['name']}");
            $this->assertStringStartsWith($prefix, $vaNumber, "VA does not start with prefix for {$data['name']}");

            // Verify extraction
            $allPrefixes = FaspayConfig::getAllVaPrefixes();
            $extractedPrefix = null;
            $extractedCustomerNo = null;
            foreach ($allPrefixes as $p) {
                if (str_starts_with($vaNumber, $p)) {
                    $extractedPrefix = $p;
                    $extractedCustomerNo = substr($vaNumber, strlen($p));
                    break;
                }
            }

            $this->assertEquals($prefix, $extractedPrefix, "Extracted prefix mismatch for {$data['name']}");
            $this->assertEquals($freeDigits, $extractedCustomerNo, "Extracted customerNo mismatch for {$data['name']}");
        }
    }

    public function test_all_8_channels_allowed_in_snap_controller()
    {
        $controller = new \App\Http\Controllers\Api\FaspaySnapController();
        $reflector = new \ReflectionClass($controller);
        $method = $reflector->getMethod('validateSnapHeaders');
        $method->setAccessible(true);

        $channels = ['402', '723', '825', '408', '708', '802', '818', '801'];

        foreach ($channels as $channelId) {
            $request = Request::create('/api/faspay/snap/inquiry', 'POST', [], [], [], [
                'HTTP_CHANNEL-ID' => $channelId,
                'HTTP_AUTHORIZATION' => 'Bearer sample_token',
                'HTTP_X-SIGNATURE' => 'valid_mock',
            ]);

            $res = $method->invoke($controller, $request, '24', null);
            // It should NOT return 401 with 'Unauthorized. Invalid Channel ID'
            if ($res instanceof \Illuminate\Http\JsonResponse) {
                $data = $res->getData(true);
                $this->assertNotEquals('Unauthorized. Invalid Channel ID', $data['responseMessage'] ?? '', "Channel {$channelId} was rejected as invalid channel ID");
            }
        }
    }
}


