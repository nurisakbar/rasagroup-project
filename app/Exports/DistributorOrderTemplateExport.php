<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DistributorOrderTemplateExport implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function collection()
    {
        return Product::where('status', 'active')->orderBy('name')->get();
    }

    public function headings(): array
    {
        return [
            'Kode Produk',
            'Nama Produk',
            'Jumlah',
        ];
    }

    public function map($product): array
    {
        return [
            $product->code,
            $product->display_name,
            '', // Empty for user to fill
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20, // Kode Produk
            'B' => 45, // Nama Produk
            'C' => 15, // Jumlah
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1    => ['font' => ['bold' => true]],
        ];
    }
}
