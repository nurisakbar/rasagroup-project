<?php

namespace Tests\Feature;

use App\Jobs\SyncOrderToJubelio;
use App\Models\Order;
use App\Services\JubelioService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Order yang sedang disinkronkan (lock dipegang job lain) tidak boleh membuat SO Jubelio kedua.
 * Butuh MySQL yang sudah termigrasi.
 */
class SyncOrderToJubelioLockTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }
    }

    public function test_second_sync_is_skipped_when_first_has_already_created_the_so(): void
    {
        $order = Order::whereNull('jubelio_salesorder_id')->firstOrFail();
        $job = new SyncOrderToJubelio($order);

        // Simulasi: job lain sudah membuat SO tepat sebelum job ini mendapat lock.
        Order::whereKey($order->id)->update(['jubelio_salesorder_id' => 999001]);

        $jubelio = \Mockery::mock(JubelioService::class);
        $jubelio->shouldNotReceive('createSalesOrder');
        $jubelio->shouldNotReceive('token');

        $job->handle($jubelio);

        $this->assertSame(999001, (int) $order->fresh()->jubelio_salesorder_id);
    }

    public function test_lock_is_released_after_handle(): void
    {
        $order = Order::whereNull('jubelio_salesorder_id')->firstOrFail();
        Order::whereKey($order->id)->update(['jubelio_salesorder_id' => 999002]);

        (new SyncOrderToJubelio($order))->handle(\Mockery::mock(JubelioService::class));

        $lock = Cache::lock('jubelio-sync-order-'.$order->id, 5);
        $this->assertTrue($lock->get());
        $lock->release();
    }
}
