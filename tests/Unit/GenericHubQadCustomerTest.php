<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

/**
 * Order dari Hub Bekasi dan Hub Maggiore dikirim ke QAD dengan customer generik HUB00002.
 */
class GenericHubQadCustomerTest extends TestCase
{
    private function order(Warehouse $warehouse): Order
    {
        $order = new Order();
        $order->setRelation('sourceWarehouse', $warehouse);
        $order->setRelation('user', new User(['qad_customer_code' => 'CS92913']));

        return $order;
    }

    public function test_bekasi_and_maggiore_use_generic_customer(): void
    {
        $hubs = [
            new Warehouse(['name' => 'Hub Bekasi', 'qad_location_code' => 'FG008']),
            new Warehouse(['name' => 'Hub Maggiore', 'qad_location_code' => 'FG007']),
            // Dikenali juga dari kode lokasi bila nama berubah.
            new Warehouse(['name' => 'Gudang Serpong', 'qad_location_code' => 'FG007']),
        ];

        foreach ($hubs as $hub) {
            $this->assertTrue($hub->usesGenericQadCustomer(), $hub->name);
            $this->assertSame('HUB00002', $this->order($hub)->resolveQadCustomerCode(), $hub->name);
        }
    }

    public function test_other_hubs_use_outlet_customer_code(): void
    {
        $hub = new Warehouse(['name' => 'Hub Surabaya Tidar', 'qad_location_code' => 'FG011']);

        $this->assertFalse($hub->usesGenericQadCustomer());
        $this->assertSame('CS92913', $this->order($hub)->resolveQadCustomerCode());
    }
}
