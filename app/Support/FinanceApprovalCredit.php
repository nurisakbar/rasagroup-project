<?php

namespace App\Support;

use App\Models\Order;

/**
 * Data kredit pembeli yang dikirim bersama permintaan approval finance (EDMS):
 * AR outstanding dan credit limit, beserta rinciannya.
 */
class FinanceApprovalCredit
{
    /**
     * @return array{
     *     credit_limit: ?float,
     *     ar_outstanding: float,
     *     existing_top_transactions: float,
     *     accumulated_debt: float,
     *     remaining_credit_limit: ?float
     * }
     */
    public static function forOrder(Order $order): array
    {
        $order->loadMissing('user');
        $user = $order->user;

        $creditLimit = $user?->credit_limit !== null ? (float) $user->credit_limit : null;
        $arOutstanding = $user ? $user->getArOutstandingAmount() : 0.0;
        $existingTop = $user ? $user->getExistingTopTransactionTotal($order->id) : 0.0;
        // Total utang bila order ini disetujui: AR outstanding + order TOP berjalan + order ini.
        $accumulatedDebt = $arOutstanding + $existingTop + (float) $order->total_amount;

        return [
            'credit_limit' => $creditLimit !== null ? round($creditLimit, 2) : null,
            'ar_outstanding' => round($arOutstanding, 2),
            'existing_top_transactions' => round($existingTop, 2),
            'accumulated_debt' => round($accumulatedDebt, 2),
            'remaining_credit_limit' => $creditLimit !== null ? round($creditLimit - $accumulatedDebt, 2) : null,
        ];
    }
}
