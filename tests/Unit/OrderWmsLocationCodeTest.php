<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\Warehouse;
use Tests\TestCase;

class OrderWmsLocationCodeTest extends TestCase
{
    private function order(?string $snapshot, ?string $qadLocation, ?string $kodeHub = 'WHMPRG003'): Order
    {
        $order = new Order();
        $order->source_qad_location_code = $snapshot;
        $order->setRelation('sourceWarehouse', $kodeHub === null && $qadLocation === null
            ? null
            : new Warehouse(['kode_hub' => $kodeHub, 'qad_location_code' => $qadLocation]));

        return $order;
    }

    public function test_current_warehouse_location_wins_over_stale_snapshot(): void
    {
        // Order dibuat saat warehouse belum punya Kode Lokasi QAD -> snapshot = kode hub.
        $this->assertSame('FG007', $this->order('WHMPRG003', 'FG007')->wmsLocationCode());
    }

    public function test_snapshot_used_when_warehouse_has_no_qad_location(): void
    {
        $this->assertSame('FG001', $this->order('FG001', null)->wmsLocationCode());
        $this->assertSame('FG001', $this->order('FG001', '')->wmsLocationCode());
    }

    public function test_falls_back_to_hub_code_then_null(): void
    {
        $this->assertSame('WHMPRG003', $this->order(null, null)->wmsLocationCode());
        $this->assertNull($this->order(null, null, null)->wmsLocationCode());
    }
}
