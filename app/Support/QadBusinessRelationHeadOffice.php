<?php

namespace App\Support;

use App\Models\User;
use App\Services\QadService;
use Illuminate\Support\Facades\Log;

/**
 * Menyamakan head office Business Relation dengan alamat/telepon snapshot.
 * PATCH parsial di QID tanpa isActive=true dapat menonaktifkan BR; selalu kirim isActive true.
 */
final class QadBusinessRelationHeadOffice
{
    public static function patch(QadService $qadService, User $user, string $businessRelationCode, array $addressSnapshot): void
    {
        $city = self::normalizeCityForQad((string) ($addressSnapshot['city'] ?? ''));
        $street1 = self::sanitizeStreet((string) ($addressSnapshot['street1'] ?? '-'));
        $street2 = self::sanitizeStreet((string) ($addressSnapshot['street2'] ?? '-'));

        $payload = [
            'businessRelationCode' => $businessRelationCode,
            'headOfficeBusinessRelationCode' => $businessRelationCode,
            'isActive' => true,
            'headOfficeCity' => substr($city, 0, 30),
            'headOfficeStreet1' => substr($street1, 0, 30),
            'headOfficeStreet2' => substr($street2, 0, 30),
            'headOfficeZipCode' => self::resolvedPostalCode($addressSnapshot),
            'headOfficeTelephone' => self::normalizePhoneForQad((string) ($user->phone ?? '')),
            'headOfficeTaxClass' => 'PPN',
            'headOfficeTaxZone' => 'IDN',
            'headOfficeLanguageCode' => 'us',
        ];

        $res = $qadService->updateBusinessRelation($payload);
        if (is_array($res) && ($res['error']['isError'] ?? false)) {
            Log::warning('QadBusinessRelationHeadOffice: business-relation/update failed', [
                'user_id' => $user->id,
                'business_relation_code' => $businessRelationCode,
                'response' => $res,
            ]);
        }
    }

    private static function resolvedPostalCode(array $addressSnapshot): string
    {
        $raw = trim((string) ($addressSnapshot['postal_code'] ?? ''));
        $digits = preg_replace('/\D+/', '', $raw) ?? '';
        if ($digits !== '') {
            return substr($digits, 0, 10);
        }

        $default = trim((string) config('qidapi.default_customer_zip', '10110'));
        if ($default !== '' && preg_match('/^\d{5,10}$/', $default)) {
            return $default;
        }

        return '10110';
    }

    private static function normalizeCityForQad(string $city): string
    {
        $city = trim($city);
        $default = (string) config('qidapi.default_customer_city', 'Jakarta');

        if ($city === '') {
            return $default;
        }

        $u = mb_strtoupper($city, 'UTF-8');

        if (str_contains($u, 'JAKARTA')) {
            return 'Jakarta';
        }

        if (str_contains($u, 'TANGERANG') || str_contains($u, 'BANTEN')) {
            return 'Tangerang';
        }

        if (str_contains($u, 'BANDUNG')) {
            return 'Bandung';
        }

        if (str_contains($u, 'SURABAYA')) {
            return 'Surabaya';
        }

        // Konsisten dengan SyncCustomerToQad: fallback agar tidak memicu BadRequest pada master kota QAD.
        return $default;
    }

    private static function sanitizeStreet(string $line): string
    {
        $line = trim($line);

        return $line !== '' ? $line : '-';
    }

    private static function normalizePhoneForQad(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '6210000000';
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62' . substr($digits, 1);
        }
        if (! str_starts_with($digits, '62')) {
            $digits = '62' . $digits;
        }

        return substr($digits, 0, 20);
    }
}
