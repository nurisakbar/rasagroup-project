<?php

namespace Tests\Feature;

use App\Jobs\NotifyExternalFinanceApprovalNeeded;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Permintaan approval finance ke EDMS menyertakan AR outstanding dan credit limit pembeli.
 * Butuh MySQL yang sudah termigrasi.
 */
class FinanceApprovalPayloadTest extends TestCase
{
    use DatabaseTransactions;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        $buyer = User::factory()->create([
            'role' => User::ROLE_OUTLET,
            'wa_verified_at' => now(),
            'credit_limit' => 10000000,
            'ar_outstanding' => 2500000,
            'term_of_payment' => 30,
        ]);

        $this->order = Order::findOrFail(DB::table('orders')->value('id'));
        $this->order->forceFill([
            'user_id' => $buyer->id,
            'payment_method' => 'term_of_payment',
            'payment_status' => 'pending',
            'finance_approved' => false,
            'total_amount' => 1500000,
        ])->saveQuietly();
    }

    public function test_finance_approval_webhook_sends_ar_outstanding_and_credit_limit(): void
    {
        config(['services.finance_approval.webhook_url' => 'https://edms.test/approval']);
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        (new NotifyExternalFinanceApprovalNeeded($this->order->fresh(), force: true))->handle();

        Http::assertSent(function (Request $request) {
            $data = $request->data();

            return $request->url() === 'https://edms.test/approval'
                && $data['payment_method'] === 'term_of_payment'
                && filled($data['payment_method_label'])
                && $data['ar_outstanding'] === 2500000.0
                && $data['credit_limit'] === 10000000.0
                && $data['existing_top_transactions'] === 0.0
                && $data['accumulated_debt'] === 4000000.0
                && $data['remaining_credit_limit'] === 6000000.0
                && $data['customer']['ar_outstanding'] === 2500000.0
                && $data['customer']['credit_limit'] === 10000000.0
                // 10 jt - (2,5 jt AR + 1,5 jt order ini)
                && $data['customer']['remaining_credit_limit'] === 6000000.0;
        });
    }

    public function test_edms_approval_api_sends_ar_outstanding_and_credit_limit(): void
    {
        Http::fake(['*' => Http::response(['ok' => true], 200)]);

        $this->postJson(route('api.orders.edms-approval', $this->order->id))->assertOk()
            ->assertJsonPath('data.payload.ar_outstanding', 2500000)
            ->assertJsonPath('data.payload.credit_limit', 10000000)
            ->assertJsonPath('data.payload.accumulated_debt', 4000000)
            ->assertJsonPath('data.payload.remaining_credit_limit', 6000000);

        Http::assertSent(fn (Request $request) => str_contains($request->url(), 'edms.rasagroupoffice.com')
            && $request->data()['ar_outstanding'] === 2500000.0
            && $request->data()['credit_limit'] === 10000000.0);
    }

    public function test_buyer_without_credit_limit_sends_null_limit(): void
    {
        $this->order->user->forceFill(['credit_limit' => null, 'ar_outstanding' => null])->save();
        $credit = \App\Support\FinanceApprovalCredit::forOrder($this->order->fresh());

        $this->assertNull($credit['credit_limit']);
        $this->assertSame(0.0, $credit['ar_outstanding']);
        $this->assertNull($credit['remaining_credit_limit']);
    }
}
