<?php

namespace App\Exports;

use App\Models\Order;
use App\Support\Wib;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AdminOrdersExport implements FromQuery, WithHeadings, WithMapping, WithColumnWidths, WithStyles
{
    public function __construct(private Builder $orders)
    {
    }

    public function query(): Builder
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'No. Pesanan',
            'No. SO Jubelio',
            'No. SO QAD',
            'Tanggal',
            'Tipe Order',
            'Pembeli',
            'Email',
            'Ekspedisi',
            'No. Resi',
            'Sumber Pengiriman',
            'Total',
            'Status Pesanan',
            'Pembayaran',
            'Metode Pembayaran',
        ];
    }

    /**
     * @param  Order  $order
     */
    public function map($order): array
    {
        $type = match ($order->order_type) {
            'pos' => 'Offline (POS)',
            'distributor' => 'Distributor',
            default => 'Online (Regular)',
        };

        $payment = $order->payment_method === 'term_of_payment'
            ? 'Term Of Payment'
            : ucwords(str_replace('_', ' ', (string) $order->payment_status));

        $jubelioSo = $order->jubelio_salesorder_no ?: ($order->jubelio_salesorder_id ? 'ID '.$order->jubelio_salesorder_id : '-');
        $qadSo = $order->qad_so_number ?: ($order->qid_sales_order_number ?: '-');

        return [
            $order->order_number,
            $jubelioSo,
            $qadSo,
            Wib::format($order->created_at, 'd/m/Y H:i'),
            $type,
            $order->user->name ?? '-',
            $order->user->email ?? '-',
            $order->expedition->name ?? '-',
            $order->tracking_number ?: '-',
            $order->sourceWarehouse->name ?? '-',
            (float) $order->total_amount,
            ucfirst((string) $order->order_status),
            $payment,
            $order->payment_method ? ucwords(str_replace('_', ' ', (string) $order->payment_method)) : '-',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 22,
            'B' => 22,
            'C' => 22,
            'D' => 20,
            'E' => 20,
            'F' => 28,
            'G' => 32,
            'H' => 22,
            'I' => 22,
            'J' => 28,
            'K' => 16,
            'L' => 18,
            'M' => 22,
            'N' => 22,
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
