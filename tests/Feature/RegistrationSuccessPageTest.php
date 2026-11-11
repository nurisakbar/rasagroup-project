<?php

namespace Tests\Feature;

use App\Jobs\SendWhatsAppVerificationJob;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

/**
 * Setelah daftar, pengguna diarahkan ke halaman "Pendaftaran berhasil" berisi instruksi aktivasi email.
 * Butuh MySQL yang sudah termigrasi.
 */
class RegistrationSuccessPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }
    }

    public function test_register_redirects_to_success_page_with_activation_instructions(): void
    {
        Notification::fake();
        Queue::fake();
        $email = 'daftar' . uniqid() . '@example.com';

        $this->post(route('register'), [
            'name' => 'Pendaftar Uji',
            'email' => $email,
            'password' => 'RahasiaBaru123',
            'password_confirmation' => 'RahasiaBaru123',
            'phone' => '08' . random_int(1000000000, 1999999999),
        ])->assertRedirect(route('register.success'));

        $this->assertNull(User::where('email', $email)->value('email_verified_at'));
        // OTP WhatsApp tidak dikirim saat registrasi.
        Queue::assertNotPushed(SendWhatsAppVerificationJob::class);

        $this->get(route('register.success'))
            ->assertOk()
            ->assertSee('Pendaftaran berhasil')
            ->assertSee($email)
            ->assertSee(route('verification.resend_form', ['email' => $email]), false);
    }

    public function test_success_page_without_registration_redirects_to_login(): void
    {
        $this->get(route('register.success'))->assertRedirect(route('login'));
    }
}
