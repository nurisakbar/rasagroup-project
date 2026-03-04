<?php

namespace App\Imports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class DistributorOrderImport implements ToCollection, WithHeadingRow
{
    public $rows = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            if (!isset($row['kode_produk']) || empty($row['jumlah'])) {
                continue;
            }

            $product = Product::where('code', $row['kode_produk'])->first();
            if ($product) {
                $this->rows[] = [
                    'product_id' => $product->id,
                    'quantity' => (int) $row['jumlah'],
                    'product_name' => $product->display_name,
                    'code' => $product->code
                ];
            }
        }
    }
}
