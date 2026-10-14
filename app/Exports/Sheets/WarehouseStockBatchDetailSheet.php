<?php

namespace App\Exports\Sheets;

use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WarehouseStockBatchDetailSheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithTitle
{
    protected int $rowNumber = 1;

    public function __construct(
        protected Warehouse $warehouse,
        protected Collection $stocks,
        protected array $wmsBatches = []
    ) {}

    public function collection()
    {
        $rows = collect();

        foreach ($this->stocks as $stock) {
            if (!$stock->product || $stock->product->status !== 'active') {
                continue;
            }

            $productBatches = [];
            foreach ($stock->product->getAllCodes() as $code) {
                if (isset($this->wmsBatches[$code])) {
                    foreach ($this->wmsBatches[$code] as $b) {
                        $b['_item_code'] = $code;
                        $productBatches[] = $b;
                    }
                } else {
                    $ucCode = strtoupper(trim((string) $code));
                    if (isset($this->wmsBatches[$ucCode])) {
                        foreach ($this->wmsBatches[$ucCode] as $b) {
                            $b['_item_code'] = $ucCode;
                            $productBatches[] = $b;
                        }
                    }
                }
            }

            $wmsTotal = 0;
            foreach ($productBatches as $b) {
                $wmsTotal += (int) ($b['qty'] ?? 0);
            }

            if (count($productBatches) > 0) {
                foreach ($productBatches as $batch) {
                    $rows->push([
                        'product_name' => $stock->product->name,
                        'product_code' => $stock->product->code,
                        'price' => (float) $stock->product->price,
                        'stock_jubelio' => (int) $stock->stock,
                        'wms_total' => $wmsTotal,
                        'wms_item_code' => $batch['_item_code'] ?? '-',
                        'lot_serial' => $batch['lot_serial'] ?? '-',
                        'expired' => $batch['expired'] ?? '-',
                        'batch_qty' => (int) ($batch['qty'] ?? 0),
                    ]);
                }
            } else {
                $rows->push([
                    'product_name' => $stock->product->name,
                    'product_code' => $stock->product->code,
                    'price' => (float) $stock->product->price,
                    'stock_jubelio' => (int) $stock->stock,
                    'wms_total' => 0,
                    'wms_item_code' => '-',
                    'lot_serial' => '-',
                    'expired' => '-',
                    'batch_qty' => 0,
                ]);
            }
        }

        return $rows;
    }

    public function title(): string
    {
        return 'Detail Per Batch';
    }

    public function headings(): array
    {
        return [
            'No',
            'Kode Produk (SKU)',
            'Nama Produk',
            'Kode Item WMS',
            'Nomor Batch / Lot',
            'Tanggal Expired',
            'Qty Batch (WMS)',
            'Total Fisik (WMS)',
            'Stok (Jubelio)',
            'Harga (Rp)',
        ];
    }

    public function map($row): array
    {
        return [
            $this->rowNumber++,
            $row['product_code'],
            $row['product_name'],
            $row['wms_item_code'],
            $row['lot_serial'],
            $row['expired'],
            $row['batch_qty'],
            $row['wms_total'],
            $row['stock_jubelio'],
            $row['price'],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 38,
            'D' => 18,
            'E' => 20,
            'F' => 18,
            'G' => 16,
            'H' => 18,
            'I' => 18,
            'J' => 16,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A:J')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('B')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G:I')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('G:I')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('J')->getNumberFormat()->setFormatCode('#,##0');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '385723'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
