<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Nomor pesanan website = pola W + YYMM + 3 digit urutan bulanan (contoh: W2609001).
 * Di environment non-production, prefix menjadi D agar tidak bentrok, tapi tetap 8 karakter.
 */
final class QadWsOrderNumberGenerator
{
    public const LOCK_KEY = 'sequence:qad_ws_order_number_monthly';

    public static function currentMaxSequence(): int
    {
        return self::maxSequenceForThisMonth();
    }

    public static function generate(): string
    {
        $seconds = max(5, (int) config('qidapi.ws_order_number_lock_seconds', 15));

        return Cache::lock(self::LOCK_KEY, $seconds)->block($seconds, function (): string {
            $last = self::maxSequenceForThisMonth();
            $next = $last + 1;
            
            $prefix = self::getPrefix();
            $datePrefix = date('ym');
            
            return $prefix . $datePrefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    private static function getPrefix(): string
    {
        return config('app.env') === 'production' ? 'W' : 'D';
    }

    private static function maxSequenceForThisMonth(): int
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? self::maxSequenceMysqlForThisMonth()
            : self::maxSequencePortableForThisMonth();
    }

    private static function maxSequenceMysqlForThisMonth(): int
    {
        $prefix = self::getPrefix();
        $datePrefix = date('ym');
        $pattern = '^' . $prefix . $datePrefix . '[0-9]{3,}$';
        $prefixLength = strlen($prefix . $datePrefix) + 1;

        $fromOrder = (int) (Order::query()
            ->whereRaw('order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(order_number, ?) AS UNSIGNED)), 0) AS m', [$prefixLength])
            ->value('m'));

        $fromQid = (int) (Order::query()
            ->whereRaw('qid_sales_order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(qid_sales_order_number, ?) AS UNSIGNED)), 0) AS m', [$prefixLength])
            ->value('m'));

        return max($fromOrder, $fromQid, 0);
    }

    private static function maxSequencePortableForThisMonth(): int
    {
        $prefix = self::getPrefix();
        $datePrefix = date('ym');
        $max = 0;
        Order::query()
            ->where(function ($q) use ($prefix, $datePrefix) {
                $q->where('order_number', 'like', $prefix . $datePrefix . '%')
                    ->orWhere('qid_sales_order_number', 'like', $prefix . $datePrefix . '%');
            })
            ->select(['order_number', 'qid_sales_order_number'])
            ->lazyById()
            ->each(function (Order $o) use (&$max, $prefix, $datePrefix) {
                foreach ([$o->order_number, $o->qid_sales_order_number] as $code) {
                    if (is_string($code) && preg_match('/^' . $prefix . $datePrefix . '(\d{3,})$/', $code, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                }
            });

        return $max;
    }
}
