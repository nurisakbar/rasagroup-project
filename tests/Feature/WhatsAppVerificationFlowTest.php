<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppVerificationJob;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Pengguna yang belum verifikasi WhatsApp dialihkan ke halaman verifikasi (bukan error), dan OTP
 * dikirim saat halaman verifikasi dibuka. Butuh MySQL yang sudah termigrasi.
 */
class WhatsAppVerificationFlowTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        // Pastikan verifikasi WA aktif selama test.
        putenv('BYPASS_WA_VERIFICATION=false');
        $_ENV['BYPASS_WA_VERIFICATION'] = $_SERVER['BYPASS_WA_VERIFICATION'] = 'false';

        Queue::fake();
        $this->user = User::factory()->create([
            'role' => 'buyer',
            'phone' => '08' . random_int(1000000000, 1999999999),
            'email_verified_at' => now(),
            'wa_verified_at' => null,
            'wa_verification_code' => null,
        ]);
        Cache::forget('wa_otp_auto_sent:' . $this->user->id);
    }

    public function test_add_to_cart_redirects_to_wa_verification_instead_of_error(): void
    {
        $product = Product::where('status', 'active')->where('price', '>', 0)->firstOrFail();

        $this->actingAs($this->user)
            ->from(route('products.show', $product->slug))
            ->postJson(route('cart.store', $product), ['quantity' => 1])
            ->assertForbidden()
            ->assertJson(['redirect' => route('wa.verify')])
            ->assertJsonPath('message', 'Silakan verifikasi nomor WhatsApp Anda terlebih dahulu.');

        // Halaman produk disimpan untuk tombol "Lanjut belanja" setelah verifikasi.
        $this->assertSame(route('products.show', $product->slug), session('url.intended'));
    }

    public function test_otp_is_sent_when_verification_page_opens_once_per_cooldown(): void
    {
        $this->actingAs($this->user)->get(route('wa.verify'))->assertOk();
        Queue::assertPushed(SendWhatsAppVerificationJob::class, 1);
        $this->assertNotNull($this->user->fresh()->wa_verification_code);

        // Refresh halaman dalam jeda: tidak mengirim ulang.
        $this->actingAs($this->user)->get(route('wa.verify'))->assertOk();
        Queue::assertPushed(SendWhatsAppVerificationJob::class, 1);

        // Tombol kirim ulang tetap bisa dipakai.
        $this->actingAs($this->user)->post(route('wa.resend'))->assertRedirect();
        Queue::assertPushed(SendWhatsAppVerificationJob::class, 2);
    }

    public function test_no_otp_sent_without_phone(): void
    {
        $this->user->forceFill(['phone' => null])->save();

        $this->actingAs($this->user)->get(route('wa.verify'))->assertOk();
        Queue::assertNotPushed(SendWhatsAppVerificationJob::class);
    }

    public function test_after_verification_user_can_continue_shopping(): void
    {
        $this->actingAs($this->user)->get(route('wa.verify'));
        $code = $this->user->fresh()->wa_verification_code;
        $productUrl = url('/products/contoh');

        $this->actingAs($this->user)
            ->withSession(['url.intended' => $productUrl])
            ->post(route('wa.verify.post'), ['code' => $code])
            ->assertRedirect(route('wa.verify.success'));

        $this->actingAs($this->user)
            ->withSession(['url.intended' => $productUrl])
            ->get(route('wa.verify.success'))
            ->assertOk()
            ->assertSee('Lanjut belanja')
            ->assertSee($productUrl, false);

        $this->assertNotNull($this->user->fresh()->wa_verified_at);
    }
}
