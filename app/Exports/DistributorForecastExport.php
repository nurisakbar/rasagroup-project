<?php

namespace App\Exports;

use App\Models\DistributorForecast;
use App\Services\DistributorForecastService;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

/**
 * Forecast satu distributor/tahun dalam format Excel forecast (sekaligus template upload).
 */
class DistributorForecastExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private DistributorForecast $forecast)
    {
    }

    public function headings(): array
    {
        return DistributorForecastService::headings();
    }

    public function collection(): Collection
    {
        return app(DistributorForecastService::class)->exportRows($this->forecast);
    }
}
