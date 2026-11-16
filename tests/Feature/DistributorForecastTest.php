<?php

namespace Tests\Feature;

use App\Models\DistributorForecast;
use App\Models\DistributorForecastLine;
use App\Models\DistributorForecastRevision;
use App\Models\Product;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Forecast distributor: hanya outlet rekrutan, satuan pcs, bulan berjalan dikunci, upload Excel + tabel web,
 * audit perubahan, dan laporan admin. Butuh MySQL yang sudah termigrasi.
 */
class DistributorForecastTest extends TestCase
{
    use DatabaseTransactions;

    private User $distributor;

    private User $outlet;

    private User $otherOutlet;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        if (DB::getDriverName() !== 'mysql') {
            $this->markTestSkipped('Butuh database MySQL yang sudah termigrasi.');
        }

        // 15 Juni 2026 (WIB): Jan–Jun terkunci, Jul–Des terbuka.
        Carbon::setTestNow(Carbon::create(2026, 6, 15, 10, 0, 0, 'Asia/Jakarta'));

        $this->distributor = User::factory()->create(['role' => User::ROLE_DISTRIBUTOR, 'wa_verified_at' => now(), 'is_active' => true]);
        $this->distributor->refresh();
        $this->outlet = User::factory()->create([
            'role' => User::ROLE_OUTLET, 'name' => 'Outlet Forecast Uji', 'email' => 'outlet.forecast.' . uniqid() . '@contoh.test',
            'sales_code' => $this->distributor->distributor_sales_code, 'wa_verified_at' => now(),
        ]);
        $this->otherOutlet = User::factory()->create(['role' => User::ROLE_OUTLET, 'name' => 'Outlet Bukan Rekrutan', 'wa_verified_at' => now()]);
        $this->product = Product::withoutGlobalScopes()->whereNotNull('code')->where('code', '!=', '')->firstOrFail();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function line(): ?DistributorForecastLine
    {
        return DistributorForecastLine::whereHas('forecast', fn ($q) => $q->where('distributor_id', $this->distributor->id)->where('year', 2026))
            ->where('outlet_id', $this->outlet->id)->where('product_id', $this->product->id)->first();
    }

    public function test_page_is_for_distributors_only(): void
    {
        $this->actingAs($this->distributor)->get(route('buyer.forecast.index'))
            ->assertOk()->assertSee('Forecast 2026')->assertSee('Outlet Forecast Uji')->assertDontSee('Outlet Bukan Rekrutan');

        $this->actingAs($this->outlet)->get(route('buyer.forecast.index'))->assertForbidden();
    }

    public function test_add_line_only_for_referred_outlet_and_locked_months_ignored(): void
    {
        $this->actingAs($this->distributor)->post(route('buyer.forecast.store'), [
            'year' => 2026, 'outlet_id' => $this->otherOutlet->id, 'product_id' => $this->product->id,
        ])->assertSessionHasErrors('outlet_id');

        $this->actingAs($this->distributor)->post(route('buyer.forecast.store'), [
            'year' => 2026, 'outlet_id' => $this->outlet->id, 'product_id' => $this->product->id,
            'qty' => [6 => 100, 7 => 200, 12 => 300],
        ])->assertRedirect(route('buyer.forecast.index', ['year' => 2026]));

        $line = $this->line();
        $this->assertSame([0, 200, 300], [$line->qty(6), $line->qty(7), $line->qty(12)]);
        $this->assertSame(2, DistributorForecastRevision::where('forecast_id', $line->forecast_id)->count());
    }

    public function test_grid_update_saves_open_months_and_records_revisions(): void
    {
        $forecast = DistributorForecast::create(['distributor_id' => $this->distributor->id, 'year' => 2026]);
        $line = DistributorForecastLine::create(['forecast_id' => $forecast->id, 'outlet_id' => $this->outlet->id, 'product_id' => $this->product->id, 'qty_3' => 50, 'qty_8' => 10]);

        $this->actingAs($this->distributor)->put(route('buyer.forecast.update'), [
            'year' => 2026, 'lines' => [$line->id => [3 => 999, 8 => 15, 9 => 20]],
        ])->assertRedirect();

        $line->refresh();
        $this->assertSame([50, 15, 20], [$line->qty(3), $line->qty(8), $line->qty(9)]);
        $revision = DistributorForecastRevision::where('forecast_id', $forecast->id)->where('month', 8)->first();
        $this->assertSame([10, 15, 'web', (string) $this->distributor->id], [$revision->old_qty, $revision->new_qty, $revision->source, (string) $revision->changed_by]);

        // Baris dengan angka di bulan terkunci tidak bisa dihapus.
        $this->actingAs($this->distributor)->delete(route('buyer.forecast.destroy', $line))->assertSessionHas('error');
        $this->assertNotNull($line->fresh());
    }

    public function test_excel_upload_matches_bill_to_and_item_number(): void
    {
        $header = 'Customer Name,Bill-To,NewSalesName,Item Number,Description,Brand,January,February,March,April,May,June,July,August,September,October,November,December';
        $row = "Outlet Forecast Uji,{$this->outlet->email},,{$this->product->code},,," . '5,5,5,5,5,5,"21,408",7,8,9,10,11';
        $file = UploadedFile::fake()->createWithContent('forecast.csv', $header . "\n" . $row . "\n");

        $this->actingAs($this->distributor)->post(route('buyer.forecast.import'), ['year' => 2026, 'file' => $file])
            ->assertRedirect()->assertSessionHas('success');

        $line = $this->line();
        $this->assertSame([0, 21408, 11], [$line->qty(6), $line->qty(7), $line->qty(12)]);
        $this->assertSame('excel', DistributorForecastRevision::where('forecast_id', $line->forecast_id)->value('source'));

        // Satu baris salah membatalkan seluruh file.
        $bad = UploadedFile::fake()->createWithContent('forecast.csv', $header . "\n"
            . "Outlet Forecast Uji,{$this->outlet->email},,{$this->product->code},,,0,0,0,0,0,0,1,1,1,1,1,1\n"
            . "Outlet Bukan Rekrutan,{$this->otherOutlet->email},,{$this->product->code},,,0,0,0,0,0,0,1,1,1,1,1,1\n");
        $this->actingAs($this->distributor)->post(route('buyer.forecast.import'), ['year' => 2026, 'file' => $bad])
            ->assertSessionHas('import_errors');
        $this->assertSame(21408, $this->line()->qty(7));
    }

    public function test_export_and_admin_report(): void
    {
        $forecast = DistributorForecast::create(['distributor_id' => $this->distributor->id, 'year' => 2026]);
        DistributorForecastLine::create(['forecast_id' => $forecast->id, 'outlet_id' => $this->outlet->id, 'product_id' => $this->product->id, 'qty_2' => 21408]);

        $this->actingAs($this->distributor)->get(route('buyer.forecast.export', ['year' => 2026]))
            ->assertOk()->assertDownload();

        $admin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'wa_verified_at' => now()]);
        $this->actingAs($admin, 'admin')->get(route('admin.reports.distributor-forecast.index', ['year' => 2026]))
            ->assertOk()->assertSee('21.408');
        $rows = $this->actingAs($admin, 'admin')
            ->getJson(route('admin.reports.distributor-forecast.index', ['year' => 2026, 'distributor_id' => $this->distributor->id]), ['X-Requested-With' => 'XMLHttpRequest'])
            ->assertOk()->json('data');
        $this->assertSame(['Outlet Forecast Uji', $this->outlet->email, $this->product->code, '21.408'], [$rows[0]['customer'], $rows[0]['bill_to'], $rows[0]['item_number'], $rows[0]['qty_2']]);

        $this->actingAs($admin, 'admin')->get(route('admin.reports.distributor-forecast.export', ['year' => 2026]))->assertOk()->assertDownload();
    }
}
