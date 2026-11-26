<?php

namespace App\Exports;

use App\Models\DistributorForecast;
use App\Services\DistributorForecastService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\Cell\DefaultValueBinder;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Template + data forecast satu distributor/tahun. Berisi semua produk master aktif untuk subdist
 * terpilih (atau semua subdist), jadi distributor tinggal mengisi jumlah per bulan lalu upload kembali.
 * Lembar kedua berisi petunjuk; upload hanya membaca lembar pertama.
 */
class DistributorForecastExport implements WithMultipleSheets
{
    public function __construct(private DistributorForecast $forecast, private ?string $outletId = null)
    {
    }

    public function sheets(): array
    {
        return [
            new DistributorForecastTemplateSheet($this->forecast, $this->outletId),
            new DistributorForecastInstructionSheet($this->forecast),
        ];
    }
}

/**
 * Kolom teks (A-D) selalu disimpan sebagai teks: kode produk/pelanggan berupa angka panjang
 * (mis. 1731355444026967091) akan dibulatkan Excel bila disimpan sebagai angka.
 */
class DistributorForecastTemplateSheet extends DefaultValueBinder implements FromCollection, WithHeadings, WithColumnWidths, WithColumnFormatting, WithCustomValueBinder, WithStrictNullComparison, WithStyles, WithEvents, WithTitle
{
    public function bindValue(Cell $cell, $value): bool
    {
        if (is_scalar($value) && $value !== '' && in_array($cell->getColumn(), ['A', 'B', 'C', 'D'], true)) {
            $cell->setValueExplicit((string) $value, DataType::TYPE_STRING);

            return true;
        }

        return parent::bindValue($cell, $value);
    }

    public function columnFormats(): array
    {
        return ['B' => NumberFormat::FORMAT_TEXT];
    }

    private int $rowCount = 0;

    public function __construct(private DistributorForecast $forecast, private ?string $outletId)
    {
    }

    public function title(): string
    {
        return 'Forecast ' . $this->forecast->year;
    }

    public function headings(): array
    {
        return DistributorForecastService::templateHeadings();
    }

    public function collection(): Collection
    {
        $rows = app(DistributorForecastService::class)->templateRows($this->forecast, $this->outletId);
        $this->rowCount = $rows->count();

        return $rows;
    }

    public function columnWidths(): array
    {
        $widths = ['A' => 28, 'B' => 16, 'C' => 50, 'D' => 16];
        foreach (range('E', 'P') as $column) {
            $widths[$column] = 11;
        }

        return $widths;
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '6A1B1B']],
            ],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $last = max(2, $this->rowCount + 1);

                // Kolom referensi (A-D) abu-abu: tidak perlu diubah.
                $sheet->getStyle("A2:D{$last}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F1EDE8');

                // Bulan terbuka (isi di sini) kuning muda; bulan berjalan/terkunci abu-abu (angkanya diabaikan saat upload).
                foreach ($this->forecast->openMonths() as $month) {
                    $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $month);
                    $sheet->getStyle("{$column}2:{$column}{$last}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFF8E6');
                }
                foreach (range(1, 12) as $month) {
                    if ($this->forecast->monthLocked($month)) {
                        $column = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(4 + $month);
                        $sheet->getStyle("{$column}2:{$column}{$last}")->getFont()->getColor()->setRGB('9A9A9A');
                        $sheet->getStyle("{$column}2:{$column}{$last}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E8E8E8');
                    }
                }

                $sheet->freezePane('E2');
                $sheet->setAutoFilter("A1:P{$last}");

                // Hanya bilangan bulat >= 0 di kolom jumlah.
                $validation = new DataValidation();
                $validation->setType(DataValidation::TYPE_WHOLE)
                    ->setOperator(DataValidation::OPERATOR_GREATERTHANOREQUAL)
                    ->setFormula1('0')
                    ->setAllowBlank(true)
                    ->setShowErrorMessage(true)
                    ->setErrorTitle('Jumlah tidak valid')
                    ->setError('Isi bilangan bulat 0 atau lebih (satuan botol/pcs).');
                $sheet->setDataValidation("E2:P{$last}", $validation);
            },
        ];
    }
}

class DistributorForecastInstructionSheet implements FromArray, WithTitle, WithColumnWidths
{
    public function __construct(private DistributorForecast $forecast)
    {
    }

    public function title(): string
    {
        return 'Petunjuk';
    }

    public function columnWidths(): array
    {
        return ['A' => 110];
    }

    public function array(): array
    {
        $open = collect($this->forecast->openMonths())->map(fn ($m) => DistributorForecast::MONTHS[$m])->implode(', ');

        return [
            ["Petunjuk pengisian forecast {$this->forecast->year}"],
            [''],
            ['1. Buka lembar "Forecast ' . $this->forecast->year . '". Setiap baris adalah satu produk untuk satu subdist; kolom abu-abu (subdist, kode, nama, merek) sudah terisi dari master produk, jangan diubah.'],
            ['2. Isi jumlah per bulan di kolom kuning, dalam satuan botol/pcs (bilangan bulat, 0 atau lebih).'],
            ['3. Baris yang jumlahnya dibiarkan kosong diabaikan. Isi 0 bila ingin memastikan produk itu tercatat nol.'],
            ['4. Bulan yang sedang berjalan dan sebelumnya dikunci (kolom abu-abu pada bulan); angkanya diabaikan saat upload.'],
            ['   Bulan yang masih bisa diisi: ' . ($open !== '' ? $open : '-') . '.'],
            ['5. Jangan mengubah kolom Customer Name dan Item Number karena dipakai untuk mencocokkan subdist dan produk saat upload.'],
            ['6. Simpan file, lalu upload di halaman Forecast. Bila ada baris yang salah, seluruh file dibatalkan dan alasannya ditampilkan.'],
        ];
    }
}
