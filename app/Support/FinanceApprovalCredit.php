<?php

namespace App\Support;

use App\Models\Order;

/**
 * Data kredit pembeli yang dikirim bersama permintaan approval finance (EDMS):
 * AR outstanding, credit limit, dan pending invoice (order tempo yang belum lunas).
 */
class FinanceApprovalCredit
{
    /** Batas jumlah pending invoice yang dirinci di payload (total tetap menghitung semuanya). */
    private const MAX_PENDING_INVOICES = 100;

    /**
     * @return array{
     *     credit_limit: ?float,
     *     ar_outstanding: float,
     *     existing_top_transactions: float,
     *     accumulated_debt: float,
     *     remaining_credit_limit: ?float,
     *     pending_invoice_count: int,
     *     pending_invoice_total: float,
     *     pending_invoice_overdue_count: int,
     *     pending_invoice_overdue_total: float,
     *     pending_invoices: list<array<string, mixed>>
     * }
     */
    public static function forOrder(Order $order): array
    {
        $order->loadMissing('user');
        $user = $order->user;

        $creditLimit = $user?->credit_limit !== null ? (float) $user->credit_limit : null;
        $arOutstanding = $user ? $user->getArOutstandingAmount() : 0.0;
        $pending = self::pendingInvoices($order);
        $existingTop = $pending['total'];
        // Total utang bila order ini disetujui: AR outstanding + order TOP berjalan + order ini.
        $accumulatedDebt = $arOutstanding + $existingTop + (float) $order->total_amount;

        return [
            'credit_limit' => $creditLimit !== null ? round($creditLimit, 2) : null,
            'ar_outstanding' => round($arOutstanding, 2),
            'existing_top_transactions' => round($existingTop, 2),
            'accumulated_debt' => round($accumulatedDebt, 2),
            'remaining_credit_limit' => $creditLimit !== null ? round($creditLimit - $accumulatedDebt, 2) : null,
            'pending_invoice_count' => $pending['count'],
            'pending_invoice_total' => round($existingTop, 2),
            'pending_invoice_overdue_count' => $pending['overdue_count'],
            'pending_invoice_overdue_total' => round($pending['overdue_total'], 2),
            'pending_invoices' => $pending['items'],
        ];
    }

    /**
     * Order tempo (TOP) pembeli yang belum lunas dan tidak dibatalkan, selain order yang sedang diajukan.
     * Jatuh tempo = tanggal order + tempo pembayaran pelanggan (perkiraan; tidak ada kolom jatuh tempo).
     *
     * @return array{count: int, total: float, overdue_count: int, overdue_total: float, items: list<array<string, mixed>>}
     */
    private static function pendingInvoices(Order $order): array
    {
        $user = $order->user;
        if (! $user) {
            return ['count' => 0, 'total' => 0.0, 'overdue_count' => 0, 'overdue_total' => 0.0, 'items' => []];
        }

        $termDays = max(0, (int) ($user->term_of_payment ?? 0));
        $today = now(Wib::TZ)->startOfDay();

        $orders = $user->orders()
            ->where('payment_method', 'term_of_payment')
            ->where('payment_status', '!=', 'paid')
            ->whereNotIn('order_status', ['cancelled'])
            ->where('id', '!=', $order->id)
            ->orderBy('created_at')
            ->get(['id', 'order_number', 'total_amount', 'created_at', 'finance_approved', 'order_status', 'payment_status']);

        $items = [];
        $overdueCount = 0;
        $overdueTotal = 0.0;
        foreach ($orders as $pending) {
            $orderDate = $pending->created_at?->copy()->timezone(Wib::TZ);
            $dueDate = $orderDate ? $orderDate->copy()->startOfDay()->addDays($termDays) : null;
            $daysOverdue = $dueDate && $dueDate->lt($today) ? (int) $dueDate->diffInDays($today) : 0;

            if ($daysOverdue > 0) {
                $overdueCount++;
                $overdueTotal += (float) $pending->total_amount;
            }

            $items[] = [
                'order_number' => $pending->order_number,
                'invoice_number' => ProformaInvoice::invoiceNumber($pending),
                'amount' => round((float) $pending->total_amount, 2),
                'order_date' => $orderDate?->toDateString(),
                'due_date' => $dueDate?->toDateString(),
                'days_overdue' => $daysOverdue,
                'status' => $pending->finance_approved ? 'unpaid' : 'awaiting_approval',
            ];
        }

        return [
            'count' => count($items),
            'total' => (float) $orders->sum('total_amount'),
            'overdue_count' => $overdueCount,
            'overdue_total' => $overdueTotal,
            'items' => array_slice($items, 0, self::MAX_PENDING_INVOICES),
        ];
    }
}
