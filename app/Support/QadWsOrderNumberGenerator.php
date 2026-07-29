<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Nomor pesanan website = pola WS + YYMMDD + 3 digit urutan (contoh: WS260729001).
 * Urutan global memperhatikan order_number dan qid_sales_order_number agar tidak bentrok, dan reset setiap hari.
 */
final class QadWsOrderNumberGenerator
{
    public const LOCK_KEY = 'sequence:qad_ws_order_number_daily';

    /**
     * Nilai urutan tertinggi yang sudah terpakai untuk hari ini.
     */
    public static function currentMaxSequence(): int
    {
        return self::maxSequenceForToday();
    }

    public static function generate(): string
    {
        $seconds = max(5, (int) config('qidapi.ws_order_number_lock_seconds', 15));

        return Cache::lock(self::LOCK_KEY, $seconds)->block($seconds, function (): string {
            $last = self::maxSequenceForToday();
            $next = $last + 1;
            
            // Format: WS + YYMMDD + 3 digit
            $datePrefix = date('ymd');
            
            // Jika lebih dari 999 urutan dalam sehari, akan otomatis menjadi 4 digit (misal: 1000)
            return 'WS' . $datePrefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    private static function maxSequenceForToday(): int
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? self::maxSequenceMysqlForToday()
            : self::maxSequencePortableForToday();
    }

    private static function maxSequenceMysqlForToday(): int
    {
        $datePrefix = date('ymd');
        // Pencarian pola: WS + ymd + angka minimal 3 digit
        $pattern = '^WS' . $datePrefix . '[0-9]{3,}$';

        $fromOrder = (int) (Order::query()
            ->whereRaw('order_number REGEXP ?', [$pattern])
            // SUBSTRING 9 karena 'WS' (2) + 'ymd' (6) = 8 karakter. Digit mulai dari karakter ke-9.
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(order_number, 9) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        $fromQid = (int) (Order::query()
            ->whereRaw('qid_sales_order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(qid_sales_order_number, 9) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        return max($fromOrder, $fromQid, 0);
    }

    private static function maxSequencePortableForToday(): int
    {
        $datePrefix = date('ymd');
        $max = 0;
        Order::query()
            ->where(function ($q) use ($datePrefix) {
                $q->where('order_number', 'like', 'WS' . $datePrefix . '%')
                    ->orWhere('qid_sales_order_number', 'like', 'WS' . $datePrefix . '%');
            })
            ->select(['order_number', 'qid_sales_order_number'])
            ->lazyById()
            ->each(function (Order $o) use (&$max, $datePrefix) {
                foreach ([$o->order_number, $o->qid_sales_order_number] as $code) {
                    if (is_string($code) && preg_match('/^WS' . $datePrefix . '(\d{3,})$/', $code, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                }
            });

        return $max;
    }
}
