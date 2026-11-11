<?php

namespace Tests\Feature;

use App\Helpers\WACloudHelper;
use App\Jobs\SendWhatsAppNotification;
use App\Models\Expedition;
use App\Models\Order;
use App\Models\User;
use App\Notifications\Orders\OrderCompletedNotification;
use App\Notifications\Orders\OrderPickupReadyNotification;
use App\Notifications\Orders\OrderProcessingNotification;
use App\Notifications\Orders\OrderShippedNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Email order: Kurir Toko (diantar) tidak disebut "ambil di gudang", link detail mengarah ke halaman
 * pesanan pembeli. WA ke hub hanya untuk order yang sudah dibayar. Butuh MySQL yang sudah termigrasi.
 */
class OrderNotificationTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $buyer = User::factory()->create(['role' => User::ROLE_OUTLET, 'wa_verified_at' => now()]);
        $row = DB::table('orders')->whereNotNull('source_warehouse_id')->first();
        $this->assertNotNull($row, 'Test butuh minimal satu order.');
        $this->order = Order::findOrFail($row->id);
        $this->order->forceFill(['user_id' => $buyer->id, 'order_type' => Order::TYPE_REGULAR])->save();
    }

    private function useExpedition(string $code, string $service): void
    {
        $expedition = Expedition::where('code', $code)->firstOrFail();
        $this->order->forceFill(['expedition_id' => $expedition->id, 'expedition_service' => $service])->save();
        $this->order = $this->order->fresh(['expedition', 'user']);
    }

    private function mailText($notification): string
    {
        $mail = $notification->toMail($this->order->user);

        return implode("\n", array_merge($mail->introLines, $mail->outroLines)) . "\n" . $mail->actionUrl;
    }

    public function test_kurir_toko_email_says_delivered_not_pickup(): void
    {
        foreach (['Diantar Ketempat', 'Self Pickup'] as $service) {
            $this->useExpedition('kurir_toko', $service);
            $text = $this->mailText(new OrderProcessingNotification($this->order));

            $this->assertStringContainsString('Diantar ke tempat', $text, $service);
            $this->assertStringNotContainsString('Pengambilan Ditempat di gudang', $text, $service);
        }
    }

    public function test_self_pickup_email_still_says_pickup(): void
    {
        $this->useExpedition('self_pickup', 'Pengambilan Ditempat');

        $this->assertStringContainsString('Pengambilan Ditempat di gudang', $this->mailText(new OrderProcessingNotification($this->order)));
    }

    public function test_email_detail_links_open_buyer_order_page(): void
    {
        $this->useExpedition('kurir_toko', 'Diantar Ketempat');
        $expected = route('buyer.orders.show', $this->order);

        foreach ([OrderProcessingNotification::class, OrderShippedNotification::class, OrderPickupReadyNotification::class, OrderCompletedNotification::class] as $class) {
            $this->assertSame($expected, (new $class($this->order))->toMail($this->order->user)->actionUrl, $class);
        }

        $this->actingAs($this->order->user)->get($expected)->assertOk();
    }

    public function test_hub_whatsapp_skipped_until_order_is_paid(): void
    {
        Http::fake();

        $this->order->forceFill(['payment_status' => 'pending', 'payment_method' => 'faspay_bca_va', 'finance_approved' => false])->save();
        foreach (SendWhatsAppNotification::HUB_TYPES as $type) {
            (new SendWhatsAppNotification($this->order->fresh(), $type))->handle();
        }
        Http::assertNothingSent();

        // Setelah dibayar, job tidak lagi dilewati oleh penjaga status bayar.
        $this->order->forceFill(['payment_status' => 'paid'])->save();
        $this->assertTrue($this->order->fresh()->isPaidOrTopApproved());
    }
}
