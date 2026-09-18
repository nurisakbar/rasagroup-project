<?php

namespace App\Support;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * Nomor pesanan website = pola W + YYMM + 3 digit urutan bulanan (contoh: W2609001).
 * Urutan global memperhatikan order_number dan qid_sales_order_number agar tidak bentrok, dan reset setiap bulan.
 */
final class QadWsOrderNumberGenerator
{
    public const LOCK_KEY = 'sequence:qad_ws_order_number_monthly';

    /**
     * Nilai urutan tertinggi yang sudah terpakai untuk bulan ini.
     */
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
            
            // Format: W + YYMM + 3 digit
            $datePrefix = date('ym');
            
            // Jika lebih dari 999 urutan dalam sebulan, akan otomatis menjadi 4 digit (misal: 1000)
            return 'W' . $datePrefix . str_pad((string) $next, 3, '0', STR_PAD_LEFT);
        });
    }

    private static function maxSequenceForThisMonth(): int
    {
        return DB::connection()->getDriverName() === 'mysql'
            ? self::maxSequenceMysqlForThisMonth()
            : self::maxSequencePortableForThisMonth();
    }

    private static function maxSequenceMysqlForThisMonth(): int
    {
        $datePrefix = date('ym');
        // Pencarian pola: W + ym + angka minimal 3 digit
        $pattern = '^W' . $datePrefix . '[0-9]{3,}$';

        $fromOrder = (int) (Order::query()
            ->whereRaw('order_number REGEXP ?', [$pattern])
            // SUBSTRING 6 karena 'W' (1) + 'ym' (4) = 5 karakter. Digit mulai dari karakter ke-6.
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(order_number, 6) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        $fromQid = (int) (Order::query()
            ->whereRaw('qid_sales_order_number REGEXP ?', [$pattern])
            ->selectRaw('COALESCE(MAX(CAST(SUBSTRING(qid_sales_order_number, 6) AS UNSIGNED)), 0) AS m')
            ->value('m'));

        return max($fromOrder, $fromQid, 0);
    }

    private static function maxSequencePortableForThisMonth(): int
    {
        $datePrefix = date('ym');
        $max = 0;
        Order::query()
            ->where(function ($q) use ($datePrefix) {
                $q->where('order_number', 'like', 'W' . $datePrefix . '%')
                    ->orWhere('qid_sales_order_number', 'like', 'W' . $datePrefix . '%');
            })
            ->select(['order_number', 'qid_sales_order_number'])
            ->lazyById()
            ->each(function (Order $o) use (&$max, $datePrefix) {
                foreach ([$o->order_number, $o->qid_sales_order_number] as $code) {
                    if (is_string($code) && preg_match('/^W' . $datePrefix . '(\d{3,})$/', $code, $m)) {
                        $max = max($max, (int) $m[1]);
                    }
                }
            });

        return $max;
    }
}
