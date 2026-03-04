<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DistributorOrderTemplateExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::where('status', 'active')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Nama Produk',
            'Kode Produk',
            'Jumlah',
        ];
    }

    public function map($product): array
    {
        return [
            $product->display_name,
            $product->code,
            '', // Empty for user to fill
        ];
    }
}
