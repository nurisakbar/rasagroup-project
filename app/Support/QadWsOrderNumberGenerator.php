<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Nomor pesanan website = pola nomor SO QAD: WS + 6 digit (WS000001 … WS999999).
 * Urutan global memperhatikan order_number dan qid_sales_order_number agar tidak bentrok.
 */
final class QadWsOrderNumberGenerator
{
    public const LOCK_KEY = 'sequence:qad_ws_order_number';

    /**
     * Nilai urutan tertinggi yang sudah terpakai (tanpa mengalokasikan nomor baru).
     */
    public static function currentMaxSequence(): int
    {
        return self::maxSequence();
    }

    public static function generate(): string
    {
        $seconds = max(5, (int) config('qidapi.ws_order_number_lock_seconds', 15));

        return Cache::lock(self::LOCK_KEY, $seconds)->block($seconds, function (): string {
            $last = self::maxSequence();
            $next = $last + 1;
            if ($next > 999999) {
                $next = 1;
            }

            return 'WS' . str_pad((string) $next, 6, '0', STR_PAD_LEFT);
        });
    }

    private static function maxSequence(): int
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? self::maxSequenceMysql()
            : self::maxSequencePortable();
    }

    private static function maxSequenceMysql(): int
    {
        $pattern = '^WS[0-9]{6}$';

        $fromOrder = (int) (Order::query()
            ->whereRaw('order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(order_number, 3) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        $fromQid = (int) (Order::query()
            ->whereRaw('qid_sales_order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(qid_sales_order_number, 3) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        return max($fromOrder, $fromQid, 0);
    }

    private static function maxSequencePortable(): int
    {
        $max = 0;
        Order::query()
            ->where(function ($q) {
                $q->where('order_number', 'like', 'WS%')
                    ->orWhere('qid_sales_order_number', 'like', 'WS%');
            })
            ->select(['order_number', 'qid_sales_order_number'])
            ->lazyById()
            ->each(function (Order $o) use (&$max) {
                foreach ([$o->order_number, $o->qid_sales_order_number] as $code) {
                    if (is_string($code) && preg_match('/^WS(\d{6})$/', $code, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                }
            });

        return $max;
    }
}
