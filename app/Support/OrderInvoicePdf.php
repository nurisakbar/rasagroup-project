<?php

namespace App\Support;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;

/**
 * PDF invoice pesanan (sama dengan tombol unduh invoice di halaman pesanan).
 * Order yang sudah lunas / TOP disetujui menjadi "invoice", selain itu "proforma-invoice".
 */
class OrderInvoicePdf
{
    public static function filename(Order $order): string
    {
        return ($order->isPaidOrTopApproved() ? 'invoice-' : 'proforma-invoice-') . $order->order_number . '.pdf';
    }

    public static function render(Order $order): string
    {
        $order->load(ProformaInvoice::relations());
        $invoice = ProformaInvoice::fromOrder($order);

        return Pdf::loadView('buyer.orders.invoice', compact('order', 'invoice'))
            ->setPaper('a4', 'portrait')
            ->output();
    }
}
