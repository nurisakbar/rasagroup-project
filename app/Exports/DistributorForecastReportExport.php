<?php

namespace App\Exports;

use App\Models\DistributorForecastLine;
use App\Services\DistributorForecastService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Rekap forecast beberapa distributor: kolom Distributor + format forecast (Customer Name .. December).
 */
class DistributorForecastReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    /** @param Collection<int, DistributorForecastLine> $lines */
    public function __construct(private Collection $lines)
    {
    }

    public function headings(): array
    {
        return array_merge(['Distributor'], DistributorForecastService::headings(), ['Total']);
    }

    public function collection(): Collection
    {
        $salesNames = [];

        return $this->lines
            ->sortBy(fn ($line) => strtolower(($line->forecast->distributor->name ?? '') . '|' . ($line->outlet->name ?? '') . '|' . ($line->product->code ?? '')))
            ->map(function (DistributorForecastLine $line) use (&$salesNames) {
                $distributor = $line->forecast->distributor;
                $sales = $distributor ? ($salesNames[$distributor->id] ??= DistributorForecastService::salesName($distributor)) : '';

                return array_merge([
                    $distributor->name ?? '',
                    $line->outlet->name ?? '',
                    $line->outlet ? DistributorForecastService::billTo($line->outlet) : '',
                    $sales,
                    $line->product->code ?? '',
                    $line->product->name ?? '',
                    $line->product->brand->name ?? '',
                ], array_map(fn ($m) => $line->qty($m), range(1, 12)), [$line->total()]);
            })->values();
    }
}
