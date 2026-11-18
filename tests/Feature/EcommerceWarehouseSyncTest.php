<?php

namespace Tests\Feature;

use App\Jobs\SendSalesOrderToWmsJob;
use App\Jobs\SyncOrderToZohoJob;
use App\Models\Order;
use App\Models\Warehouse;
use App\Services\WmsService;
use App\Services\ZohoService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Order dari gudang yang ditandai ecom tidak dikirim ke WMS dan Zoho. Butuh MySQL yang sudah termigrasi.
 */
class EcommerceWarehouseSyncTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        Http::fake();
        config([
            'services.wms.api_url' => 'https://wms.invalid', 'services.wms.api_key' => 'k',
            'services.zoho.enabled' => true, 'services.zoho.client_id' => 'id', 'services.zoho.client_secret' => 's',
            'services.zoho.refresh_token' => 'r', 'services.zoho.org_id' => 'o',
        ]);
    }

    private function ecomOrder(): Order
    {
        $order = Order::firstOrFail();
        $order->setRelation('sourceWarehouse', new Warehouse(['name' => 'Ecommerce Tomang', 'qad_location_code' => 'WHECOMM1']));

        return $order;
    }

    public function test_ecom_detection(): void
    {
        $this->assertTrue((new Warehouse(['name' => 'Ecommerce Tomang']))->isEcommerce());
        $this->assertTrue((new Warehouse(['name' => 'X', 'qad_location_code' => 'WHECOMM1']))->isEcommerce());
        $this->assertTrue((new Warehouse(['name' => 'Warehouse X', 'kode_hub' => 'WHECOMM3']))->isEcommerce());
        $this->assertFalse((new Warehouse(['name' => 'Hub Surabaya Tidar', 'kode_hub' => 'WHMPSBY', 'target_role' => ['ecommerce']]))->isEcommerce());
        $this->assertFalse((new Warehouse(['name' => 'DC MM2100', 'qad_location_code' => 'FG001']))->isEcommerce());
    }

    public function test_scope_ecommerce_matches_only_ecom_warehouses(): void
    {
        $names = Warehouse::ecommerce()->pluck('name');
        $this->assertNotContains('DC MM2100', $names->all());
        foreach (Warehouse::ecommerce()->get() as $w) {
            $this->assertTrue($w->isEcommerce());
        }
    }

    public function test_ecom_order_is_not_sent_to_wms_or_zoho(): void
    {
        $order = $this->ecomOrder();
        $this->assertFalse($order->shouldSyncToWms());
        $this->assertFalse($order->shouldSyncToZoho());

        app(ZohoService::class)->createOrderToPaymentFlow($order);
        Http::assertNothingSent();
    }

    public function test_wms_job_skips_ecom_order(): void
    {
        $order = Order::with('sourceWarehouse')->whereHas('sourceWarehouse', fn ($w) => $w->ecommerce())->first();
        if (! $order) {
            $this->markTestSkipped('Tidak ada order dari gudang ecom di database.');
        }

        $before = $order->wms_so_status;
        (new SendSalesOrderToWmsJob($order))->handle(app(WmsService::class));

        Http::assertNothingSent();
        $this->assertSame($before, $order->fresh()->wms_so_status);
    }
}
