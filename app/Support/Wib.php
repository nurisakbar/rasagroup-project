<?php

namespace App\Support;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final class Wib
{
    public const TZ = 'Asia/Jakarta';

    public static function format(mixed $value, string $format = 'd M Y H:i'): string
    {
        if ($value === null || $value === '') {
            return '';
        }

        try {
            $date = $value instanceof CarbonInterface
                ? $value->copy()
                : Carbon::parse($value);
        } catch (\Throwable) {
            return '';
        }

        return $date->timezone(self::TZ)->format($format);
    }
}
