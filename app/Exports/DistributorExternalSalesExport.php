<?php

namespace App\Exports;

use App\Models\DistributorExternalSale;
use App\Support\Wib;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class DistributorExternalSalesExport implements FromQuery, WithHeadings, WithMapping
{
    public function __construct(private Builder $sales)
    {
    }

    public function query(): Builder
    {
        return $this->sales->with('distributor')->withCount('items')->orderBy('sold_at');
    }

    public function headings(): array
    {
        return ['Tanggal Jual', 'Distributor', 'Kode Distributor', 'No. Order Distributor', 'Pelanggan', 'Kode Pelanggan', 'Jumlah Item', 'Subtotal', 'Diskon', 'Pajak', 'Total', 'Status'];
    }

    /**
     * @param  DistributorExternalSale  $sale
     */
    public function map($sale): array
    {
        return [
            Wib::format($sale->sold_at),
            $sale->distributor?->name,
            $sale->distributor?->distributor_sales_code,
            $sale->external_order_number,
            $sale->customer_name,
            $sale->customer_code,
            $sale->items_count,
            (float) $sale->subtotal,
            (float) $sale->discount,
            (float) $sale->tax,
            (float) $sale->total,
            $sale->status === DistributorExternalSale::STATUS_CANCELLED ? 'Dibatalkan' : 'Terkonfirmasi',
        ];
    }
}
