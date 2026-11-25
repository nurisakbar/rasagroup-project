<?php

namespace App\Services;

use App\Models\DistributorForecast;
use App\Models\DistributorForecastLine;
use App\Models\DistributorForecastRevision;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Forecast kebutuhan distributor per outlet rekrutan × produk × bulan.
 *
 * - Pelanggan hanya outlet rekrutan distributor (mendaftar lewat link/kode distributor).
 * - Satuan kecil (botol/pcs).
 * - Bulan berjalan dan sebelumnya dikunci: nilai yang dikirim untuk bulan terkunci diabaikan.
 * - Setiap perubahan angka dicatat di distributor_forecast_revisions.
 */
class DistributorForecastService
{
    public function forecastFor(User $distributor, int $year): DistributorForecast
    {
        return DistributorForecast::firstOrCreate(['distributor_id' => $distributor->id, 'year' => $year]);
    }

    /** Outlet rekrutan distributor (pelanggan yang boleh di-forecast). */
    public function outlets(User $distributor): Collection
    {
        if (! $distributor->distributor_sales_code) {
            return collect();
        }

        return $distributor->referredSubdists()->orderBy('name')->get(['id', 'name', 'email', 'qad_customer_code', 'sales_code']);
    }

    /** Kode pelanggan untuk kolom Bill-To: kode customer QAD outlet, atau email bila belum ada. */
    public static function billTo(User $outlet): string
    {
        return $outlet->qad_customer_code ?: (string) $outlet->email;
    }

    /** Nama sales Rasa yang menangani distributor (kolom NewSalesName). */
    public static function salesName(User $distributor): string
    {
        if (! $distributor->sales_code) {
            return '';
        }

        return (string) (User::where('role', User::ROLE_SALES)->where('sales_code', $distributor->sales_code)->value('name') ?? $distributor->sales_code);
    }

    /**
     * Simpan jumlah per bulan untuk satu outlet × produk. Bulan terkunci tidak diubah.
     *
     * @param array<int, int|null> $quantities bulan (1-12) => jumlah; bulan yang tidak dikirim tidak diubah
     * @return array{line: DistributorForecastLine, changed: int, ignored_locked: list<int>}
     */
    public function saveLine(DistributorForecast $forecast, User $outlet, Product $product, array $quantities, ?string $actorId, string $source): array
    {
        return DB::transaction(function () use ($forecast, $outlet, $product, $quantities, $actorId, $source) {
            $line = DistributorForecastLine::firstOrNew([
                'forecast_id' => $forecast->id,
                'outlet_id' => $outlet->id,
                'product_id' => $product->id,
            ]);

            $changed = 0;
            $ignored = [];
            $revisions = [];
            foreach ($quantities as $month => $qty) {
                $month = (int) $month;
                if ($month < 1 || $month > 12 || $qty === null) {
                    continue;
                }
                $qty = max(0, (int) $qty);
                $old = (int) ($line->{'qty_' . $month} ?? 0);
                if ($qty === $old) {
                    continue;
                }
                if ($forecast->monthLocked($month)) {
                    $ignored[] = $month;
                    continue;
                }

                $line->{'qty_' . $month} = $qty;
                $changed++;
                $revisions[] = [
                    'forecast_id' => $forecast->id,
                    'outlet_id' => $outlet->id,
                    'product_id' => $product->id,
                    'month' => $month,
                    'old_qty' => $old,
                    'new_qty' => $qty,
                    'changed_by' => $actorId,
                    'source' => $source,
                    'created_at' => now(),
                ];
            }

            if (! $line->exists || $changed > 0) {
                $line->save();
            }
            if ($revisions) {
                DistributorForecastRevision::insert($revisions);
            }

            return ['line' => $line, 'changed' => $changed, 'ignored_locked' => $ignored];
        });
    }

    /** Baris boleh dihapus bila bulan terkunci tidak berisi angka (riwayat yang sudah berjalan tetap tersimpan). */
    public function canDelete(DistributorForecastLine $line): bool
    {
        foreach (range(1, 12) as $month) {
            if ($line->forecast->monthLocked($month) && $line->qty($month) > 0) {
                return false;
            }
        }

        return true;
    }

    public function deleteLine(DistributorForecastLine $line, ?string $actorId): void
    {
        DB::transaction(function () use ($line, $actorId) {
            $revisions = [];
            foreach (range(1, 12) as $month) {
                if ($line->qty($month) > 0) {
                    $revisions[] = [
                        'forecast_id' => $line->forecast_id, 'outlet_id' => $line->outlet_id, 'product_id' => $line->product_id,
                        'month' => $month, 'old_qty' => $line->qty($month), 'new_qty' => 0,
                        'changed_by' => $actorId, 'source' => 'web', 'created_at' => now(),
                    ];
                }
            }
            if ($revisions) {
                DistributorForecastRevision::insert($revisions);
            }
            $line->delete();
        });
    }

    /**
     * Baris untuk Excel/laporan: Customer Name, Bill-To, NewSalesName, Item Number, Description, Brand, Jan..Des.
     *
     * @return Collection<int, array<int, mixed>>
     */
    public function exportRows(DistributorForecast $forecast): Collection
    {
        $distributor = $forecast->distributor;
        $salesName = self::salesName($distributor);
        $lines = $forecast->lines()->with(['outlet', 'product.brand'])->get()
            ->sortBy(fn ($line) => strtolower(($line->outlet->name ?? '') . '|' . ($line->product->code ?? '')));

        $rows = $lines->map(fn (DistributorForecastLine $line) => array_merge([
            $line->outlet->name ?? '',
            $line->outlet ? self::billTo($line->outlet) : '',
            $salesName,
            $line->product->code ?? '',
            $line->product->name ?? '',
            $line->product->brand->name ?? '',
        ], array_map(fn ($m) => $line->qty($m), range(1, 12))));

        // Outlet yang belum punya baris tetap dicantumkan (tanpa produk) sebagai bantuan isi template.
        $withLines = $lines->pluck('outlet_id')->map(fn ($id) => (string) $id)->flip();
        foreach ($this->outlets($distributor) as $outlet) {
            if (! isset($withLines[(string) $outlet->id])) {
                $rows->push(array_merge([$outlet->name, self::billTo($outlet), $salesName, '', '', ''], array_fill(0, 12, null)));
            }
        }

        return $rows->values();
    }

    public static function headings(): array
    {
        return array_merge(['Customer Name', 'Bill-To', 'NewSalesName', 'Item Number', 'Description', 'Brand'], array_values(DistributorForecast::MONTHS));
    }
}
