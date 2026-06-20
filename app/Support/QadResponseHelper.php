<?php

namespace App\Support;

final class QadResponseHelper
{
    public static function isError(?array $response): bool
    {
        return ! is_array($response) || ($response['error']['isError'] ?? false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function list(?array $response): array
    {
        if (self::isError($response)) {
            return [];
        }

        $data = $response['data'] ?? $response;

        if (! is_array($data)) {
            return [];
        }

        return array_is_list($data) ? $data : [$data];
    }
}
