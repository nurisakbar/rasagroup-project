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

class WarehouseStockSummarySheet implements FromCollection, WithHeadings, WithMapping, WithColumnWidths, WithStyles, WithTitle
{
    protected int $rowNumber = 1;

    public function __construct(
        protected Warehouse $warehouse,
        protected Collection $stocks,
        protected array $wmsBatches = []
    ) {}

    public function collection()
    {
        return $this->stocks->filter(function ($stock) {
            return $stock->product && $stock->product->status === 'active';
        });
    }

    public function title(): string
    {
        return 'Stok & Batch Produk';
    }

    public function headings(): array
    {
        return [
            'No',
            'Produk',
            'Kode Produk',
            'Harga (Rp)',
            'Stok (Jubelio)',
            'Fisik (WMS)',
            'Detail Batch WMS',
        ];
    }

    public function map($stock): array
    {
        if (!$stock->product || $stock->product->status !== 'active') {
            return [];
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
        $batchLines = [];
        foreach ($productBatches as $batch) {
            $qty = (int) ($batch['qty'] ?? 0);
            $wmsTotal += $qty;
            $line = '[' . ($batch['_item_code'] ?? '-') . '] Batch: ' . ($batch['lot_serial'] ?? '-');
            if (!empty($batch['expired'])) {
                $line .= ' - Expired: ' . $batch['expired'];
            }
            $line .= ' - Jumlah: ' . number_format($qty);
            $batchLines[] = $line;
        }

        return [
            $this->rowNumber++,
            $stock->product->name,
            $stock->product->code,
            (float) $stock->product->price,
            (int) $stock->stock,
            $wmsTotal,
            count($batchLines) > 0 ? implode("\n", $batchLines) : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 38,
            'C' => 18,
            'D' => 16,
            'E' => 16,
            'F' => 16,
            'G' => 50,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->getStyle('A:G')->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
        $sheet->getStyle('G')->getAlignment()->setWrapText(true);
        $sheet->getStyle('A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('C')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('E:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle('D')->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle('E:F')->getNumberFormat()->setFormatCode('#,##0');

        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1F4E79'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }
}
