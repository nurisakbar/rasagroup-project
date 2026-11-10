<?php

namespace App\Exports;

use App\Models\Product;
use App\Models\Warehouse;
use App\Support\Pricing\WarehousePriceResolver;
use App\Support\Wib;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Sekaligus template import: isi kolom harga_baru (dan opsional tanggal), lalu unggah kembali.
 */
class WarehousePricesExport implements FromCollection, WithHeadings
{
    public function __construct(private Warehouse $warehouse)
    {
    }

    public function headings(): array
    {
        return ['warehouse_id', 'nama_warehouse', 'kode_produk', 'nama_produk', 'harga_global', 'harga_berlaku', 'harga_baru', 'berlaku_mulai', 'berlaku_sampai', 'catatan'];
    }

    public function collection(): Collection
    {
        $products = Product::where('status', 'active')->orderBy('name')->get(['id', 'code', 'name', 'price']);
        $masters = app(WarehousePriceResolver::class)->masterPricesFor($this->warehouse->id, $products->pluck('id')->all());

        return $products->map(fn (Product $product) => [
            $this->warehouse->id,
            $this->warehouse->name,
            $product->code,
            $product->name,
            (float) $product->price,
            (float) ($masters->get($product->id)?->price ?? $product->price),
            null,
            null,
            null,
            null,
        ]);
    }
}
