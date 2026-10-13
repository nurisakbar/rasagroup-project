<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class FaspayConfig
{
    const COMPANY_RDI = 'rdi';
    const COMPANY_MCR = 'mcr';

    /**
     * Get list of supported companies.
     */
    public static function getCompanies(): array
    {
        return [self::COMPANY_RDI, self::COMPANY_MCR];
    }

    /**
     * Get the default company code.
     */
    public static function getDefaultCompany(): string
    {
        return config('services.faspay.default', self::COMPANY_MCR);
    }

    /**
     * Get company configuration array.
     */
    public static function getCompanyConfig(?string $company = null): array
    {
        $company = strtolower($company ?: self::getDefaultCompany());
        $companies = config('services.faspay.companies', []);

        if (isset($companies[$company])) {
            return $companies[$company];
        }

        // Fallback to default company
        $default = self::getDefaultCompany();
        if (isset($companies[$default])) {
            return $companies[$default];
        }

        // Legacy fallback from flat config if companies array is not set
        return [
            'code' => $company,
            'name' => $company === self::COMPANY_MCR ? 'PT Multi Citra Rasa' : 'PT Rasa Distribusi Indonesia',
            'merchant_id' => config('services.faspay.merchant_id'),
            'user_id' => config('services.faspay.user_id'),
            'password' => config('services.faspay.password'),
            'env' => config('services.faspay.env', 'dev'),
            'snap_base_url' => config('services.faspay.snap_base_url'),
            'snap_client_id' => config('services.faspay.snap_client_id'),
            'private_key_path' => config('services.faspay.private_key_path'),
            'public_key_path' => config('services.faspay.public_key_path'),
            'va_partner_id' => config('services.faspay.va_partner_id', '37020'),
            'qris_partner_id' => config('services.faspay.qris_partner_id', '37020'),
            'va_prefixes' => [],
        ];
    }

    /**
     * Get company display name.
     */
    public static function getCompanyName(?string $company = null): string
    {
        $config = self::getCompanyConfig($company);
        return $config['name'] ?? ($company === self::COMPANY_MCR ? 'PT Multi Citra Rasa' : 'PT Rasa Distribusi Indonesia');
    }

    /**
     * Get VA prefix for specific payment method and company.
     */
    public static function getVaPrefix(string $paymentMethod, ?string $company = null, ?string $forcedEnv = null): string
    {
        $config = self::getCompanyConfig($company);
        $prefixes = $config['va_prefixes'] ?? [];

        if (isset($prefixes[$paymentMethod])) {
            return $prefixes[$paymentMethod];
        }

        // Fallback default prefix
        return '370200';
    }

    /**
     * Get all known VA prefixes across production and dev, sorted by length descending.
     */
    public static function getAllVaPrefixes(): array
    {
        $prefixes = [
            // Production MCR
            '9881236387', // BNI MCR (10 digits)
            '12601038',   // BSI MCR (8 digits)
            '78218638',   // Maybank MCR (8 digits)
            '88558038',   // Mandiri MCR (8 digits)
            '735136',     // Permata MCR (6 digits)
            '222555',     // CIMB MCR (6 digits)
            '797055',     // Danamon MCR (6 digits)
            '885655',     // Sinarmas MCR (6 digits)

            // Production RDI
            '12601021',   // BSI RDI (8 digits)
            '78218052',   // Maybank RDI (8 digits)
            '88558010',   // Mandiri RDI (8 digits)
            '735161',     // Permata RDI (6 digits)
            '222550',     // CIMB RDI (6 digits)
            '797039',     // Danamon RDI (6 digits)
            '885648',     // Sinarmas RDI (6 digits)

            // Dev / Sandbox & UAT
            '37116001', '37116002', '37116003',
            '371161', '371162', '371164', '371165',
            '37020002',
            '370201', '370202', '370203', '370204', '370205', '370206', '370207', '370208', '370209',
        ];

        $companies = config('services.faspay.companies', []);
        foreach ($companies as $comp) {
            if (!empty($comp['va_prefixes']) && is_array($comp['va_prefixes'])) {
                foreach ($comp['va_prefixes'] as $p) {
                    if (!empty($p) && is_string($p)) {
                        $prefixes[] = trim($p);
                    }
                }
            }
        }

        $prefixes = array_values(array_unique(array_filter($prefixes)));
        usort($prefixes, fn($a, $b) => strlen($b) <=> strlen($a));

        return $prefixes;
    }

    /**
     * Resolve company by merchant ID or client ID.
     */
    public static function resolveCompanyByMerchantId(?string $id): ?string
    {
        if (empty($id)) {
            return null;
        }

        $cleanId = trim((string) $id);
        $companies = config('services.faspay.companies', []);

        foreach ($companies as $code => $cfg) {
            $merchantId = trim((string) ($cfg['merchant_id'] ?? ''));
            $clientId = trim((string) ($cfg['snap_client_id'] ?? ''));
            $vaPartnerId = trim((string) ($cfg['va_partner_id'] ?? ''));

            if (($merchantId !== '' && $merchantId === $cleanId) ||
                ($clientId !== '' && $clientId === $cleanId) ||
                ($vaPartnerId !== '' && $vaPartnerId === $cleanId)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Resolve company from an incoming request or order.
     */
    public static function resolveCompanyFromRequest(Request $request, ?Order $order = null): string
    {
        // 1. Check order's company if available
        if ($order && !empty($order->company)) {
            return strtolower($order->company);
        }

        // 2. Check X-PARTNER-ID or X-CLIENT-KEY headers
        $partnerId = $request->header('X-PARTNER-ID') ?? $request->header('X-CLIENT-KEY');
        if ($company = self::resolveCompanyByMerchantId($partnerId)) {
            return $company;
        }

        // 3. Check payload for merchantId or partnerServiceId
        $body = $request->all();
        $merchantId = $body['merchantId'] ?? $body['merchant_id'] ?? null;
        if ($company = self::resolveCompanyByMerchantId($merchantId)) {
            return $company;
        }

        $partnerServiceId = isset($body['partnerServiceId']) ? trim((string) $body['partnerServiceId']) : null;
        if ($company = self::resolveCompanyByMerchantId($partnerServiceId)) {
            return $company;
        }

        // 4. Default fallback
        return self::getDefaultCompany();
    }
}
