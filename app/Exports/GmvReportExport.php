<?php

namespace App\Exports;

use App\Models\Order;
use App\Services\GmvAnalyticsService;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GmvReportExport implements FromCollection, WithHeadings, WithMapping, WithStyles, ShouldAutoSize
{
    protected array $filters;
    protected GmvAnalyticsService $service;
    protected int $rowNumber = 0;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
        $this->service = app(GmvAnalyticsService::class);
    }

    public function collection()
    {
        $parsed = $this->service->parseFilters($this->filters);
        return $this->service->getOrdersBaseQuery($parsed)
            ->with(['items', 'user', 'sourceWarehouse'])
            ->latest()
            ->get();
    }

    public function headings(): array
    {
        return [
            'No',
            'No. Pesanan',
            'Tanggal',
            'Pelanggan',
            'Tipe Pelanggan',
            'Kode Sales',
            'Hub / Gudang',
            'Total Unit',
            'GMV (Gross DPP)',
            'Diskon',
            'Net DPP',
            'PPN',
            'Ongkir',
            'Total Pesanan',
            'Status Pesanan',
            'Status Pembayaran',
        ];
    }

    /**
     * @param Order $order
     */
    public function map($order): array
    {
        $this->rowNumber++;

        $grossDpp = (float) $order->items->sum(fn ($it) => $it->price * $it->quantity);
        $totalQty = (int) $order->items->sum('quantity');
        $shipping = (float) ($order->shipping_cost ?? 0);
        $totalAmount = (float) $order->total_amount;
        $goodsTotal = max(0.0, $totalAmount - $shipping - (float) ($order->payment_fee ?? 0));
        $usesPpn = (bool) ($order->pakai_ppn || ($order->user && $order->user->usesPpn()));
        $netDpp = $usesPpn ? round($goodsTotal / 1.11, 2) : $goodsTotal;

        $discount = 0.0;
        if ((float) ($order->discount_amount ?? 0) > 0) {
            $discount = (float) $order->discount_amount;
        } elseif ($grossDpp > $netDpp) {
            $discount = round($grossDpp - $netDpp, 2);
        }

        $ppn = max(0.0, round($goodsTotal - $netDpp, 2));

        return [
            $this->rowNumber,
            $order->order_number,
            $order->created_at->format('d/m/Y H:i'),
            $order->user?->name ?? 'Tamu',
            strtoupper((string) ($order->user?->role ?? 'buyer')),
            $order->sales_code ?: '-',
            $order->sourceWarehouse?->name ?? '-',
            $totalQty,
            $grossDpp,
            $discount,
            $netDpp,
            $ppn,
            $shipping,
            $totalAmount,
            strtoupper((string) $order->order_status),
            strtoupper((string) $order->payment_status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF1A2226'],
                ],
            ],
        ];
    }
}
