<?php

namespace Tests\Feature;

use App\Jobs\SendSalesOrderToWmsJob;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\WmsService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Payload SO ke WMS: is_update hanya dikirim saat kirim ulang, dan dibuang bila API WMS menolaknya.
 * Butuh database MySQL yang sudah termigrasi.
 */
class SendSalesOrderToWmsJobTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        config(['services.wms.api_url' => 'https://wms.test/api', 'services.wms.api_key' => 'key']);
        Queue::fake();

        $orderRow = DB::table('orders')->whereNotNull('source_warehouse_id')->first();
        $this->assertNotNull($orderRow, 'Test butuh minimal satu order dengan warehouse.');
        $this->order = Order::findOrFail($orderRow->id);
        $this->order->items()->delete();

        $productId = DB::table('products')->whereNotNull('code')->value('id');
        $item = OrderItem::create([
            'order_id' => $this->order->id,
            'product_id' => $productId,
            'quantity' => 2,
            'price' => 1000,
            'subtotal' => 2000,
            'allocated_batches' => [['lot_serial' => 'LOT1', 'qty' => 2, 'item_code' => null]],
        ]);
        $code = $item->product->code;
        $item->update(['allocated_batches' => [['lot_serial' => 'LOT1', 'qty' => 2, 'item_code' => $code]]]);
        $this->code = $code;
    }

    private string $code;

    /** @var list<array<string, mixed>> payload POST /sales-orders yang terkirim */
    private array $sent = [];

    private function fakeWms(array $salesOrderResponses): void
    {
        $this->sent = [];
        $responses = $salesOrderResponses;
        Http::fake(function (Request $request) use (&$responses) {
            if (str_contains($request->url(), '/batch-items')) {
                return Http::response(['data' => [[
                    'location' => ['item' => ['code' => $this->code, 'batches' => [['code' => 'LOT1', 'quantity' => 10]]]],
                ]], 'meta' => ['pagination' => ['last_page' => 1]]]);
            }

            $this->sent[] = $request->data();

            return array_shift($responses);
        });
    }

    public function test_first_send_has_no_is_update_field(): void
    {
        $this->order->forceFill(['wms_so_status' => null])->save();
        $this->fakeWms([Http::response(['data' => ['status' => 'QUEUED']], 201)]);

        (new SendSalesOrderToWmsJob($this->order))->handle(app(WmsService::class));

        $this->assertCount(1, $this->sent);
        $this->assertArrayNotHasKey('is_update', $this->sent[0]);
        $this->assertSame('QUEUED', $this->order->fresh()->wms_so_status);
    }

    public function test_resend_retries_without_is_update_when_wms_rejects_it(): void
    {
        $this->order->forceFill(['wms_so_status' => 'FAILED'])->save();
        $this->fakeWms([
            Http::response(['message' => ['property is_update should not exist'], 'statusCode' => 400], 400),
            Http::response(['data' => ['status' => 'QUEUED']], 201),
        ]);

        (new SendSalesOrderToWmsJob($this->order))->handle(app(WmsService::class));

        $this->assertCount(2, $this->sent);
        $this->assertTrue($this->sent[0]['is_update']);
        $this->assertArrayNotHasKey('is_update', $this->sent[1]);
        $this->assertSame('QUEUED', $this->order->fresh()->wms_so_status);
    }
}
