<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ActiveProductsExport implements FromCollection, WithHeadings, WithMapping
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Product::with(['brand', 'category'])
            ->where('status', 'active')
            ->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Product ID',
            'Kode Produk (SKU)',
            'Brand',
            'Kategori',
            'Status',
            'Nama Komersial',
            'Harga Jual',
            'Poin per Unit',
            'Berat (gram)',
            'Satuan (UoM)',
            'Ukuran Sizing',
            'Satuan Besar',
            'Isi per Satuan Besar',
            'Deskripsi',
        ];
    }

    /**
     * @param mixed $product
     *
     * @return array
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->code,
            $product->brand ? $product->brand->name : '',
            $product->category ? $product->category->name : '',
            $product->status,
            $product->commercial_name ?: $product->name,
            $product->price,
            $product->reseller_point,
            $product->weight,
            $product->unit,
            $product->size,
            $product->large_unit,
            $product->units_per_large,
            $product->description,
        ];
    }
}
