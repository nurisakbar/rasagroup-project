<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

/**
 * Halaman atur ulang kata sandi harus menampilkan token & email dari link reset, dan bisa disubmit.
 * Butuh database MySQL yang sudah termigrasi.
 */
class PasswordResetPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }
    }

    public function test_reset_page_renders_token_and_email(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OUTLET, 'wa_verified_at' => now()]);
        $token = Password::broker()->createToken($user);

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()
            ->assertSee('name="token" value="' . $token . '"', false)
            ->assertSee('value="' . $user->email . '"', false);
    }

    public function test_reset_page_without_email_query_still_renders(): void
    {
        $this->get(route('password.reset', ['token' => 'abc123']))
            ->assertOk()
            ->assertSee('name="token" value="abc123"', false);
    }

    public function test_user_can_reset_password_and_invalid_submit_returns_to_form(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_OUTLET, 'wa_verified_at' => now()]);
        $token = Password::broker()->createToken($user);
        $resetUrl = route('password.reset', ['token' => $token, 'email' => $user->email]);

        // Konfirmasi salah: kembali ke form tanpa error variabel.
        $this->from($resetUrl)->post(route('password.store'), [
            'token' => $token, 'email' => $user->email, 'password' => 'RahasiaBaru123', 'password_confirmation' => 'beda',
        ])->assertRedirect($resetUrl)->assertSessionHasErrors('password');
        $this->get($resetUrl)->assertOk();

        $this->post(route('password.store'), [
            'token' => $token, 'email' => $user->email, 'password' => 'RahasiaBaru123', 'password_confirmation' => 'RahasiaBaru123',
        ])->assertRedirect(route('login'));

        $this->assertTrue(Hash::check('RahasiaBaru123', $user->fresh()->password));
    }
}
