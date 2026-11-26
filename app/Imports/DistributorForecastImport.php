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
 * Upload forecast dari Excel (format template: Customer Name, Item Number, Description, Brand, January..December;
 * format lama dengan kolom Bill-To dan NewSalesName tetap diterima).
 *
 * - Subdist dicocokkan lewat Customer Name (nama persis, harus unik di antara subdist rekrutan); bila file memuat
 *   Bill-To (kode customer QAD atau email), itu diutamakan. Hanya subdist rekrutan.
 * - Produk dicocokkan lewat Item Number (kode produk).
 * - Baris yang ada di file dan berisi angka ditambahkan/diperbarui; baris tanpa angka dilewati; baris lain di forecast tidak dihapus.
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

            // Baris tanpa angka dilewati: baris bantuan outlet, dan produk di template yang belum diisi
            // (tidak membuat baris forecast kosong). Isi 0 bila ingin mencatat nol.
            if (! $hasQty) {
                continue;
            }

            $billTo = strtolower(trim((string) ($row['bill_to'] ?? '')));
            $outlet = $billTo !== '' ? ($byCode[$billTo] ?? null) : null;
            if (! $outlet) {
                $name = strtolower(trim((string) ($row['customer_name'] ?? '')));
                $candidates = $byName[$name] ?? [];
                $outlet = count($candidates) === 1 ? $candidates[0] : null;

                if (! $outlet && count($candidates) > 1) {
                    $this->errors[] = "Baris {$line}: ada lebih dari satu subdist bernama \"" . trim((string) $row['customer_name']) . "\". Ubah salah satu namanya atau isi kolom Bill-To dengan email subdist.";
                    continue;
                }
            }
            if (! $outlet) {
                $this->errors[] = "Baris {$line}: subdist tidak ditemukan di daftar subdist rekrutan Anda (cek Customer Name; jangan ubah kolom itu dari template).";
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
