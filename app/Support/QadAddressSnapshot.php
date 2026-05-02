<?php

namespace App\Support;

use App\Models\Address;

/**
 * Alamat pembeli untuk payload QAD / sync customer (satu sumber kebenaran).
 *
 * @return array{city: string, street1: string, street2: string, postal_code: string}
 */
final class QadAddressSnapshot
{
    public static function fromBuyerAddress(Address $address): array
    {
        $postal = '';
        foreach ([
            $address->postal_code,
            $address->district?->postal_code,
            $address->regency?->postal_code,
            $address->district?->city?->postal_code,
        ] as $p) {
            $p = trim((string) $p);
            if ($p !== '' && preg_match('/^\d{5}$/', $p)) {
                $postal = $p;
                break;
            }
        }
        if ($postal === '' && $address->address_detail && preg_match('/\b(\d{5})\b/', (string) $address->address_detail, $m)) {
            $postal = $m[1];
        }

        return [
            'city' => $address->regency?->name ?? 'Jakarta',
            'street1' => $address->address_detail ?? $address->full_address ?? '-',
            'street2' => trim((string) (($address->district?->name ?? '') . ' ' . ($address->regency?->name ?? ''))),
            'postal_code' => $postal,
        ];
    }
}
