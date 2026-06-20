<?php

namespace App\Support;

final class QadIntegration
{
    /**
     * QAD/QID aktif jika kredensial API sudah lengkap (tanpa flag env).
     */
    public static function enabled(): bool
    {
        return self::isConfigured();
    }

    public static function isConfigured(): bool
    {
        return filled(config('qidapi.base_url'))
            && filled(config('qidapi.username'))
            && filled(config('qidapi.password'))
            && filled(config('qidapi.apps_id'));
    }
}
