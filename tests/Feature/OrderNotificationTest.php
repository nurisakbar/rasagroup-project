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
            $this->assertStringNotContainsString('Pengambilan Ditempat', $text, $service);
        }
    }

    public function test_self_pickup_email_still_says_pickup(): void
    {
        $this->useExpedition('self_pickup', 'Pengambilan Ditempat');

        $text = $this->mailText(new OrderProcessingNotification($this->order));
        $this->assertStringContainsString('Metode: Pengambilan Ditempat', $text);
        $this->assertStringContainsString('Lokasi pengambilan: ' . $this->order->sourceWarehouse->name, $text);
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

    public function test_paid_order_email_attaches_invoice_pdf(): void
    {
        $this->useExpedition('kurir_toko', 'Diantar Ketempat');
        $this->order->forceFill(['payment_status' => 'paid'])->save();
        $order = $this->order->fresh(['expedition', 'user']);

        $mail = (new OrderProcessingNotification($order))->toMail($order->user);

        $this->assertCount(1, $mail->rawAttachments);
        $attachment = $mail->rawAttachments[0];
        $this->assertSame('invoice-' . $order->order_number . '.pdf', $attachment['name']);
        $this->assertSame('application/pdf', $attachment['options']['mime']);
        $this->assertStringStartsWith('%PDF', $attachment['data']);
        $this->assertContains('Invoice pesanan Anda terlampir pada email ini.', $mail->introLines);

        // Email benar-benar bisa dirender dan dikirim (driver array).
        config(['mail.default' => 'array']);
        $order->user->notifyNow(new OrderProcessingNotification($order));
        $sent = app('mailer')->getSymfonyTransport()->messages();
        $this->assertCount(1, $sent);
        $this->assertCount(1, $sent[0]->getOriginalMessage()->getAttachments());
    }

    public function test_thank_you_and_hub_whatsapp_sent_only_once_per_order(): void
    {
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);
        \Illuminate\Support\Facades\Cache::forget(SendWhatsAppNotification::sentKey($this->order->id, 'thank_you'));
        $this->order->user->forceFill(['phone' => '081234567890'])->save();
        $this->order->forceFill(['payment_status' => 'paid'])->save();

        (new SendWhatsAppNotification($this->order->fresh(), 'thank_you'))->handle();
        $afterFirst = count(Http::recorded());
        $this->assertGreaterThan(0, $afterFirst);

        // Dipicu lagi (mis. halaman sukses dibuka ulang): tidak mengirim lagi.
        (new SendWhatsAppNotification($this->order->fresh(), 'thank_you'))->handle();
        $this->assertCount($afterFirst, Http::recorded());
    }

    public function test_order_created_email_sent_once_even_if_success_page_reopened(): void
    {
        \Illuminate\Support\Facades\Mail::fake();
        \Illuminate\Support\Facades\Queue::fake();
        \Illuminate\Support\Facades\Cache::forget('order_created_mail_sent:' . $this->order->id);

        (new \App\Jobs\ProcessCheckoutSuccessJob((string) $this->order->id))->handle();
        (new \App\Jobs\ProcessCheckoutSuccessJob((string) $this->order->id))->handle();

        // OrderCreatedMail memakai antrean (ShouldQueue).
        \Illuminate\Support\Facades\Mail::assertQueued(\App\Mail\OrderCreatedMail::class, 1);
    }

    public function test_saving_pickup_ready_time_notifies_buyer_by_email_and_whatsapp(): void
    {
        \Illuminate\Support\Facades\Notification::fake();
        \Illuminate\Support\Facades\Queue::fake();
        $this->useExpedition('self_pickup', 'Pengambilan Ditempat');
        $this->order->forceFill(['payment_status' => 'paid', 'order_status' => 'processing', 'pickup_ready_at' => null])->save();
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.orders.update', $this->order), [
                'pickup_ready_at' => now()->addDay()->format('Y-m-d\\TH:i'),
                'pickup_note' => 'Ambil di loket 2',
            ])->assertRedirect();

        \Illuminate\Support\Facades\Notification::assertSentTo($this->order->user, OrderPickupReadyNotification::class);
        \Illuminate\Support\Facades\Queue::assertPushed(SendWhatsAppNotification::class, function ($job) {
            return (new \ReflectionProperty($job, 'type'))->getValue($job) === 'pickup_ready';
        });
    }

    public function test_pickup_ready_whatsapp_uses_meta_template_when_configured(): void
    {
        config(['services.meta_wa.pickup_ready_template' => 'rasaconnect_pesanan_siap_diambil', 'services.meta_wa.provider' => 'meta']);
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);
        $this->useExpedition('self_pickup', 'Pengambilan Ditempat');
        $this->order->user->forceFill(['phone' => '081234567890'])->save();
        $this->order->forceFill(['pickup_ready_at' => now()->addDay(), 'pickup_note' => 'Loket 2'])->save();

        WACloudHelper::sendPickupReadyNotification($this->order->fresh(['user', 'address', 'sourceWarehouse']));

        $templateRequest = collect(Http::recorded())->map(fn ($pair) => $pair[0])
            ->first(fn ($request) => ($request->data()['type'] ?? null) === 'template');
        $this->assertNotNull($templateRequest, 'WA siap diambil harus dikirim sebagai template Meta.');
        $this->assertSame('rasaconnect_pesanan_siap_diambil', $templateRequest->data()['template']['name']);
        $params = $templateRequest->data()['template']['components'][0]['parameters'];
        $this->assertCount(6, $params);
        $this->assertSame('Loket 2', $params[4]['text']);
    }

    public function test_all_whatsapp_goes_through_meta_even_if_provider_is_wacloud(): void
    {
        config([
            'services.meta_wa.provider' => 'wacloud',
            'services.meta_wa.token' => 'token-uji',
            'services.meta_wa.phone_number_id' => '123',
            'services.meta_wa.tracking_template' => 'rasaconnect_resi_pengiriman',
        ]);
        Http::fake(['*' => Http::response(['messages' => [['id' => 'wamid.test']]], 200)]);

        WACloudHelper::sendText('081234567890', 'Halo');
        WACloudHelper::sendDocument('081234567890', 'https://contoh.test/invoice.pdf', 'invoice.pdf');

        $this->useExpedition('kurir_toko', 'Diantar Ketempat');
        $this->order->user->forceFill(['phone' => '081234567890'])->save();
        $this->order->forceFill(['tracking_number' => 'RESI123'])->save();
        WACloudHelper::sendTrackingNotification($this->order->fresh(['user', 'address', 'expedition']));

        $requests = collect(Http::recorded())->map(fn ($pair) => $pair[0]);
        $this->assertGreaterThanOrEqual(3, $requests->count());
        $requests->each(fn ($request) => $this->assertStringStartsWith('https://graph.facebook.com/', $request->url()));

        $this->assertSame(['text', 'document', 'template'], $requests->take(3)->map(fn ($r) => $r->data()['type'])->all());
        $this->assertSame('invoice.pdf', $requests[1]->data()['document']['filename']);
        $tracking = $requests[2]->data()['template'];
        $this->assertSame('rasaconnect_resi_pengiriman', $tracking['name']);
        $this->assertSame('RESI123', $tracking['components'][0]['parameters'][3]['text']);
    }
}
