<?php

namespace App\Imports;

use App\Models\DistributorForecast;
use App\Models\Product;
use App\Models\User;
use App\Services\DistributorForecastService;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Upload forecast dari Excel (format: Customer Name, Bill-To, NewSalesName, Item Number, Description, Brand, January..December).
 *
 * - Outlet dicocokkan lewat Bill-To (kode customer QAD atau email outlet), lalu nama persis; hanya outlet rekrutan.
 * - Produk dicocokkan lewat Item Number (kode produk).
 * - Baris yang ada di file ditambahkan/diperbarui; baris lain di forecast tidak dihapus.
 * - Bulan terkunci diabaikan. Satu baris salah membatalkan seluruh file.
 */
class DistributorForecastImport implements ToCollection, WithHeadingRow
{
    /** @var list<string> */
    public array $errors = [];

    public int $saved = 0;

    public int $changedCells = 0;

    public int $ignoredLocked = 0;

    public function __construct(private DistributorForecast $forecast, private User $distributor, private ?string $actorId)
    {
    }

    public function collection(Collection $rows): void
    {
        $service = app(DistributorForecastService::class);
        $outlets = $service->outlets($this->distributor);
        $byCode = [];
        $byName = [];
        foreach ($outlets as $outlet) {
            foreach (array_filter([$outlet->qad_customer_code, $outlet->email]) as $code) {
                $byCode[strtolower(trim($code))] = $outlet;
            }
            $byName[strtolower(trim($outlet->name))][] = $outlet;
        }
        $products = Product::withoutGlobalScopes()->whereNotNull('code')->get(['id', 'code', 'alternate_codes'])
            ->keyBy(fn ($p) => strtoupper(trim($p->code)));

        $pending = [];
        $seen = [];
        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $itemNumber = strtoupper(trim((string) ($row['item_number'] ?? '')));
            $quantities = [];
            $hasQty = false;
            foreach (DistributorForecast::MONTHS as $month => $name) {
                $raw = $row[strtolower($name)] ?? null;
                if ($raw === null || trim((string) $raw) === '') {
                    continue;
                }
                $value = is_numeric($raw) ? (float) $raw : (float) preg_replace('/[^\d]/', '', (string) $raw);
                if ($value < 0 || floor($value) != $value) {
                    $this->errors[] = "Baris {$line}: jumlah {$name} harus bilangan bulat tidak negatif.";
                    continue 2;
                }
                $quantities[$month] = (int) $value;
                $hasQty = true;
            }

            // Baris bantuan outlet tanpa produk & tanpa angka dilewati.
            if ($itemNumber === '' && ! $hasQty) {
                continue;
            }

            $billTo = strtolower(trim((string) ($row['bill_to'] ?? '')));
            $outlet = $billTo !== '' ? ($byCode[$billTo] ?? null) : null;
            if (! $outlet) {
                $candidates = $byName[strtolower(trim((string) ($row['customer_name'] ?? '')))] ?? [];
                $outlet = count($candidates) === 1 ? $candidates[0] : null;
            }
            if (! $outlet) {
                $this->errors[] = "Baris {$line}: outlet tidak ditemukan di daftar outlet rekrutan Anda (cek Bill-To / Customer Name).";
                continue;
            }

            $product = $products[$itemNumber] ?? null;
            if (! $product) {
                $this->errors[] = "Baris {$line}: Item Number \"{$itemNumber}\" tidak dikenal.";
                continue;
            }

            $key = $outlet->id . '|' . $product->id;
            if (isset($seen[$key])) {
                $this->errors[] = "Baris {$line}: outlet & Item Number sama dengan baris {$seen[$key]}.";
                continue;
            }
            $seen[$key] = $line;
            $pending[] = [$outlet, $product, $quantities];
        }

        if ($this->errors) {
            return;
        }

        DB::transaction(function () use ($pending, $service) {
            foreach ($pending as [$outlet, $product, $quantities]) {
                $result = $service->saveLine($this->forecast, $outlet, $product, $quantities, $this->actorId, 'excel');
                $this->saved++;
                $this->changedCells += $result['changed'];
                $this->ignoredLocked += count($result['ignored_locked']);
            }
        });
    }
}
