<?php

namespace Tests\Feature;

use App\Jobs\SyncOrderToQad;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use ReflectionMethod;
use Tests\TestCase;

/**
 * Butuh skema MySQL: sebagian migrasi memakai MODIFY ENUM yang tidak jalan di SQLite.
 * Jalankan dengan DB_CONNECTION=mysql dan DB_DATABASE mengarah ke database uji yang sudah termigrasi.
 */
class DistributorOutletReferralTest extends TestCase
{
    use DatabaseTransactions;

    private User $distributor;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        Queue::fake();
        Notification::fake();

        $this->distributor = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'name' => 'PT Mitra Rasa', 'is_active' => true]);
    }

    private function registrationPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Outlet Baru',
            'email' => 'outlet-baru@example.test',
            'phone' => '081299990001',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ], $overrides);
    }

    private function outlet(?string $salesCode): User
    {
        return User::factory()->create(['role' => User::ROLE_OUTLET, 'sales_code' => $salesCode, 'wa_verified_at' => now()]);
    }

    private function qadSalespersonFor(?string $orderSalesCode, ?User $customer = null): ?string
    {
        // Konstruktor job memanggil load('user'), jadi customer harus lewat user_id, bukan setRelation.
        $order = new Order(['sales_code' => $orderSalesCode]);
        $order->user_id = $customer?->id;

        $method = new ReflectionMethod(SyncOrderToQad::class, 'salespersonCodeForQad');

        return $method->invoke(new SyncOrderToQad($order));
    }

    public function test_distributor_gets_unique_sequential_code_on_create(): void
    {
        $next = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR]);

        $this->assertMatchesRegularExpression('/^DST\d{5,}$/', $this->distributor->distributor_sales_code);
        $this->assertSame(
            (int) substr($this->distributor->distributor_sales_code, 3) + 1,
            (int) substr($next->distributor_sales_code, 3)
        );
    }

    public function test_buyer_promoted_to_distributor_gets_code_and_existing_code_is_stable(): void
    {
        $buyer = User::factory()->create(['role' => User::ROLE_BUYER]);
        $this->assertNull($buyer->distributor_sales_code);

        $buyer->update(['role' => User::ROLE_DISTRIBUTOR]);
        $code = $buyer->fresh()->distributor_sales_code;
        $this->assertNotNull($code);

        $buyer->fresh()->update(['name' => 'Nama Baru']);
        $this->assertSame($code, $buyer->fresh()->distributor_sales_code);
    }

    public function test_register_page_shows_distributor_and_hides_sales_picker(): void
    {
        $code = $this->distributor->distributor_sales_code;

        $this->get(route('register', ['distributor' => $code]))
            ->assertOk()
            ->assertSee('PT Mitra Rasa')
            ->assertSee('name="distributor_code" value="' . $code . '"', false)
            ->assertDontSee('name="sales_code"', false);
    }

    public function test_register_page_warns_on_unknown_code_and_keeps_normal_form(): void
    {
        $this->get(route('register', ['distributor' => 'DST99999']))
            ->assertOk()
            ->assertSee('tidak valid')
            ->assertSee('name="sales_code"', false);
    }

    public function test_registering_via_link_creates_outlet_under_distributor(): void
    {
        $code = $this->distributor->distributor_sales_code;

        $this->post('/register', $this->registrationPayload(['distributor_code' => $code]))
            ->assertRedirect(route('login'));

        $outlet = User::where('email', 'outlet-baru@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_OUTLET, $outlet->role);
        $this->assertSame($code, $outlet->sales_code);
        $this->assertTrue($outlet->referringDistributor->is($this->distributor));
        $this->assertCount(1, $this->distributor->referredOutlets);
    }

    public function test_distributor_code_wins_over_tampered_sales_code(): void
    {
        $code = $this->distributor->distributor_sales_code;

        $this->post('/register', $this->registrationPayload(['distributor_code' => $code, 'sales_code' => 'MCR001']));

        $this->assertSame($code, User::where('email', 'outlet-baru@example.test')->value('sales_code'));
    }

    public function test_registration_rejects_unknown_or_inactive_distributor(): void
    {
        $this->post('/register', $this->registrationPayload(['distributor_code' => 'DST99999']))
            ->assertSessionHasErrors('distributor_code');

        $this->distributor->update(['is_active' => false]);
        $this->post('/register', $this->registrationPayload(['distributor_code' => $this->distributor->distributor_sales_code]))
            ->assertSessionHasErrors('distributor_code');

        $this->assertDatabaseMissing('users', ['email' => 'outlet-baru@example.test']);
    }

    public function test_existing_registration_flows_are_unchanged(): void
    {
        $sales = User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TST001', 'name' => 'Sales Uji']);

        $this->post('/register', $this->registrationPayload(['sales_code' => 'TST001 - Sales Uji']));
        $viaSales = User::where('email', 'outlet-baru@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_OUTLET, $viaSales->role);
        $this->assertSame('TST001', $viaSales->sales_code);
        $this->assertTrue($viaSales->salesPerson->is($sales));

        $this->post('/logout');
        $this->post('/register', $this->registrationPayload(['email' => 'pembeli@example.test', 'phone' => '081299990002']));
        $buyer = User::where('email', 'pembeli@example.test')->firstOrFail();
        $this->assertSame(User::ROLE_BUYER, $buyer->role);
        $this->assertNull($buyer->sales_code);
    }

    public function test_profile_update_cannot_change_distributor_attribution(): void
    {
        $code = $this->distributor->distributor_sales_code;
        $outlet = $this->outlet($code);

        $this->actingAs($outlet)->put(route('buyer.profile.update'), [
            'name' => 'Nama Diubah',
            'email' => $outlet->email,
            'sales_code' => '',
        ]);

        $outlet->refresh();
        $this->assertSame('Nama Diubah', $outlet->name);
        $this->assertSame($code, $outlet->sales_code);
    }

    public function test_profile_update_still_lets_sales_outlet_clear_sales_code(): void
    {
        User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TST002']);
        $outlet = $this->outlet('TST002');

        $this->actingAs($outlet)->put(route('buyer.profile.update'), [
            'name' => $outlet->name,
            'email' => $outlet->email,
            'sales_code' => '',
        ]);

        $this->assertNull($outlet->fresh()->sales_code);
    }

    public function test_qad_never_receives_distributor_code(): void
    {
        $code = $this->distributor->distributor_sales_code;

        $this->assertNull($this->qadSalespersonFor($code));
        $this->assertNull($this->qadSalespersonFor(null, $this->outlet($code)));

        $this->distributor->update(['sales_code' => 'SLS00049']);
        $this->assertSame('SLS00049', $this->qadSalespersonFor($code));

        $this->distributor->update(['is_active' => false]);
        $this->assertSame('SLS00049', $this->qadSalespersonFor($code));

        $this->distributor->delete();
        $this->assertSame('SLS00049', $this->qadSalespersonFor($code));
    }

    public function test_qad_keeps_regular_sales_codes(): void
    {
        $this->assertSame('MCR001', $this->qadSalespersonFor('MCR001'));
        $this->assertSame('MCR002', $this->qadSalespersonFor('null', $this->outlet('MCR002')));
        $this->assertNull($this->qadSalespersonFor(null));
    }

    public function test_known_sales_code_covers_sales_and_distributor_only(): void
    {
        User::factory()->create(['role' => User::ROLE_SALES, 'sales_code' => 'TST003']);
        $outletWithCode = $this->outlet('TST004');

        $this->assertTrue(User::isKnownSalesCode('TST003'));
        $this->assertTrue(User::isKnownSalesCode($this->distributor->distributor_sales_code));
        $this->assertFalse(User::isKnownSalesCode($outletWithCode->sales_code));
        $this->assertFalse(User::isKnownSalesCode(''));
    }

    public function test_distributor_page_shows_link_and_own_outlets_only(): void
    {
        $code = $this->distributor->distributor_sales_code;
        $own = $this->outlet($code);
        $other = $this->outlet('MCR001');
        $this->distributor->forceFill(['wa_verified_at' => now()])->save();

        $this->actingAs($this->distributor)->get(route('buyer.outlet-referral.index'))
            ->assertOk()
            ->assertSee(route('register', ['distributor' => $code]))
            ->assertSee($own->name)
            ->assertDontSee($other->name);
    }

    public function test_distributor_page_is_forbidden_for_non_distributor(): void
    {
        $buyer = User::factory()->create(['role' => User::ROLE_BUYER, 'wa_verified_at' => now()]);

        $this->actingAs($buyer)->get(route('buyer.outlet-referral.index'))->assertForbidden();
    }

    public function test_admin_can_update_outlet_keeping_distributor_code(): void
    {
        $code = $this->distributor->distributor_sales_code;
        $outlet = $this->outlet($code);
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.outlets.update', $outlet), [
                'name' => 'Outlet Edit',
                'email' => $outlet->email,
                'sales_code' => $code,
            ])
            ->assertRedirect(route('admin.outlets.index'));

        $this->assertSame($code, $outlet->fresh()->sales_code);
        $this->assertSame('Outlet Edit', $outlet->fresh()->name);
    }

    public function test_admin_outlet_rejects_unknown_sales_code(): void
    {
        $outlet = $this->outlet(null);
        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);

        $this->actingAs($admin, 'admin')
            ->put(route('admin.outlets.update', $outlet), [
                'name' => $outlet->name,
                'email' => $outlet->email,
                'sales_code' => 'TIDAKADA',
            ])
            ->assertSessionHasErrors('sales_code');
    }
}
