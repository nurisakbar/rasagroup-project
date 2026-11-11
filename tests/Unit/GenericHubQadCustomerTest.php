<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\User;
use App\Models\Warehouse;
use Tests\TestCase;

/**
 * Hub dengan Kode Customer QAD mengirim sales order ke QAD memakai customer tsb; hub lain memakai customer outlet.
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

    public function test_hub_with_qad_customer_code_uses_it(): void
    {
        foreach ([['Hub Bekasi', 'HUB00002'], ['Hub Maggiore', 'HUB00001'], ['Hub Baru', ' HUB00009 ']] as [$name, $code]) {
            $hub = new Warehouse(['name' => $name, 'qad_customer_code' => $code]);
            $this->assertTrue($hub->usesGenericQadCustomer(), $name);
            $this->assertSame(trim($code), $this->order($hub)->resolveQadCustomerCode(), $name);
        }
    }

    public function test_other_hubs_use_outlet_customer_code(): void
    {
        // Tanpa Kode Customer QAD (termasuk hub bernama Bekasi/Maggiore yang dikosongkan admin).
        foreach ([['Hub Surabaya Tidar', null], ['Hub Maggiore', '']] as [$name, $code]) {
            $hub = new Warehouse(['name' => $name, 'qad_location_code' => 'FG011', 'qad_customer_code' => $code]);
            $this->assertFalse($hub->usesGenericQadCustomer(), $name);
            $this->assertSame('CS92913', $this->order($hub)->resolveQadCustomerCode(), $name);
        }

        $hub = new Warehouse(['name' => 'Hub Surabaya Tidar', 'qad_location_code' => 'FG011']);

        $this->assertFalse($hub->usesGenericQadCustomer());
        $this->assertSame('CS92913', $this->order($hub)->resolveQadCustomerCode());
    }

    public function test_jubelio_sync_follows_hub_checkbox(): void
    {
        $this->assertTrue($this->order(new Warehouse(['name' => 'Hub Baru', 'send_to_jubelio' => true]))->shouldSyncToJubelio());
        // Nama hub tidak lagi menentukan: Bekasi tanpa centang tidak dikirim.
        $this->assertFalse($this->order(new Warehouse(['name' => 'Hub Bekasi', 'qad_location_code' => 'FG008', 'send_to_jubelio' => false]))->shouldSyncToJubelio());
        $this->assertFalse($this->order(new Warehouse(['name' => 'Hub Surabaya Tidar']))->shouldSyncToJubelio());
    }
}
