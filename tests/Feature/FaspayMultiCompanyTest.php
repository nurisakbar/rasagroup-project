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
    public function test_default_company_is_rdi()
    {
        $this->assertEquals('rdi', FaspayConfig::getDefaultCompany());
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
        // RDI Dev prefixes
        $permataRdi = FaspayConfig::getVaPrefix('faspay_permata_va', 'rdi', 'dev');
        $mandiriRdi = FaspayConfig::getVaPrefix('faspay_mandiri_va', 'rdi', 'dev');
        $this->assertEquals('370201', $permataRdi);
        $this->assertEquals('37020002', $mandiriRdi);

        // RDI Production prefixes
        $mandiriProd = FaspayConfig::getVaPrefix('faspay_mandiri_va', 'rdi', 'production');
        $this->assertEquals('88558010', $mandiriProd);
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
        $this->assertEquals('rdi', FaspayConfig::resolveCompanyFromRequest($emptyRequest));
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
}
