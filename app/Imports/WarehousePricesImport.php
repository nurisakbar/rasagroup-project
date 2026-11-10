<?php

namespace App\Imports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Models\WarehouseProductPrice;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

/**
 * Semua atau tidak sama sekali: satu baris salah membatalkan seluruh file, supaya master harga
 * tidak pernah terisi setengah. Harga baru langsung berlaku tanpa batas waktu.
 */
class WarehousePricesImport implements ToCollection, WithHeadingRow
{
    /** @var list<string> */
    public array $errors = [];

    public int $imported = 0;

    public function __construct(private ?string $userId)
    {
    }

    public function collection(Collection $rows): void
    {
        $warehouseIds = Warehouse::pluck('id')->flip();
        $productIds = Product::pluck('id', 'code')->mapWithKeys(fn ($id, $code) => [strtoupper(trim($code)) => $id]);
        $now = now();
        $records = [];
        $seen = [];

        foreach ($rows as $index => $row) {
            $line = $index + 2;
            $price = $row['harga_baru'] ?? null;

            if ($price === null || trim((string) $price) === '') {
                continue;
            }

            $warehouseId = trim((string) ($row['warehouse_id'] ?? ''));
            $productId = $productIds[strtoupper(trim((string) ($row['kode_produk'] ?? '')))] ?? null;

            if (! isset($warehouseIds[$warehouseId])) {
                $this->errors[] = "Baris {$line}: warehouse_id tidak dikenal.";
                continue;
            }
            if (! $productId) {
                $this->errors[] = "Baris {$line}: kode_produk tidak dikenal.";
                continue;
            }
            if (! is_numeric($price) || (float) $price <= 0) {
                $this->errors[] = "Baris {$line}: harga_baru harus angka lebih dari 0.";
                continue;
            }

            $key = $warehouseId . '|' . $productId;
            if (isset($seen[$key])) {
                $this->errors[] = "Baris {$line}: duplikat dengan baris {$seen[$key]} (warehouse dan produk sama).";
                continue;
            }
            $seen[$key] = $line;

            $records[] = [
                'warehouse_id' => $warehouseId,
                'product_id' => $productId,
                'price' => (float) $price,
                'source' => WarehouseProductPrice::SOURCE_IMPORT,
                'note' => filled($row['catatan'] ?? null) ? mb_substr(trim((string) $row['catatan']), 0, 255) : null,
                'created_by' => $this->userId,
            ];
        }

        if ($this->errors !== []) {
            return;
        }

        DB::transaction(function () use ($records, $now) {
            foreach ($records as $record) {
                WarehouseProductPrice::replaceCurrent($record, $now);
            }
        });

        $this->imported = count($records);
    }
}
