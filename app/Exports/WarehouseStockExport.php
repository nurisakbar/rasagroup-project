<?php

namespace App\Exports;

use App\Exports\Sheets\WarehouseStockBatchDetailSheet;
use App\Exports\Sheets\WarehouseStockSummarySheet;
use App\Models\Warehouse;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class WarehouseStockExport implements WithMultipleSheets
{
    public function __construct(
        protected Warehouse $warehouse,
        protected Collection $stocks,
        protected array $wmsBatches = []
    ) {}

    public function sheets(): array
    {
        return [
            new WarehouseStockSummarySheet($this->warehouse, $this->stocks, $this->wmsBatches),
            new WarehouseStockBatchDetailSheet($this->warehouse, $this->stocks, $this->wmsBatches),
        ];
    }
}
