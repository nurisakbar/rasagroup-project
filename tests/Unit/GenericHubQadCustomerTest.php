<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

/**
 * Order dari Hub Bekasi (HUB00002) dan Hub Maggiore (HUB00001) dikirim ke QAD dengan customer generik.
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

    public function test_bekasi_uses_hub00002_and_maggiore_uses_hub00001(): void
    {
        $cases = [
            ['Hub Bekasi', 'FG008', 'HUB00002'],
            ['Hub Maggiore', 'FG007', 'HUB00001'],
            // Dikenali dari kode lokasi bila nama berubah, dan dari nama bila kode lokasi belum diisi.
            ['Gudang Serpong', 'FG007', 'HUB00001'],
            ['Hub Maggiore', null, 'HUB00001'],
            ['HUB BEKASI 2', null, 'HUB00002'],
        ];

        foreach ($cases as [$name, $location, $expected]) {
            $hub = new Warehouse(['name' => $name, 'qad_location_code' => $location]);
            $this->assertTrue($hub->usesGenericQadCustomer(), $name);
            $this->assertSame($expected, $hub->genericQadCustomerCode(), $name);
            $this->assertSame($expected, $this->order($hub)->resolveQadCustomerCode(), $name);
        }

        $this->assertTrue(Warehouse::isGenericQadCustomerCode('HUB00001'));
        $this->assertTrue(Warehouse::isGenericQadCustomerCode('HUB00002'));
        $this->assertFalse(Warehouse::isGenericQadCustomerCode('CS92913'));
    }

    public function test_other_hubs_use_outlet_customer_code(): void
    {
        $hub = new Warehouse(['name' => 'Hub Surabaya Tidar', 'qad_location_code' => 'FG011']);

        $this->assertFalse($hub->usesGenericQadCustomer());
        $this->assertSame('CS92913', $this->order($hub)->resolveQadCustomerCode());
    }
}
