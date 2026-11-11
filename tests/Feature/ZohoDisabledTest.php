<?php

namespace Tests\Feature;

use App\Jobs\SyncOrderToZohoJob;
use App\Models\Order;
use App\Services\ZohoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ZOHO_ENABLED=false (server dev): tidak ada request apa pun ke Zoho.
 * Butuh MySQL yang sudah termigrasi.
 */
class ZohoDisabledTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        config([
            'services.zoho.client_id' => 'id', 'services.zoho.client_secret' => 'secret',
            'services.zoho.refresh_token' => 'refresh', 'services.zoho.org_id' => 'org',
        ]);
        Http::fake();
    }

    public function test_disabled_zoho_sends_nothing(): void
    {
        config(['services.zoho.enabled' => false]);
        $order = Order::whereNull('zoho_salesorder_id')->firstOrFail();

        $this->assertFalse(ZohoService::isConfigured());
        (new SyncOrderToZohoJob($order->id))->handle(app(ZohoService::class));
        app(ZohoService::class)->createOrderToPaymentFlow($order);
        $this->assertSame([], app(ZohoService::class)->getBranches());

        Http::assertNothingSent();
        $this->assertNull($order->fresh()->zoho_salesorder_id);
    }

    public function test_disabled_zoho_blocks_direct_api_calls(): void
    {
        config(['services.zoho.enabled' => false]);

        $this->expectException(\RuntimeException::class);
        app(ZohoService::class)->getAccessToken();
    }

    public function test_enabled_by_default(): void
    {
        config(['services.zoho.enabled' => true]);

        $this->assertTrue(ZohoService::isEnabled());
        $this->assertTrue(ZohoService::isConfigured());
    }
}
