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
        return config('services.faspay.default', self::COMPANY_RDI);
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
            'private_key_dev_path' => config('services.faspay.private_key_dev_path'),
            'private_key_prod_path' => config('services.faspay.private_key_prod_path'),
            'public_key_dev_path' => config('services.faspay.public_key_dev_path'),
            'public_key_prod_path' => config('services.faspay.public_key_prod_path'),
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
        $env = $forcedEnv ?: ($config['env'] ?? config('services.faspay.env', 'dev'));
        $isProd = in_array(strtolower($env), ['prod', 'production']);
        $envKey = $isProd ? 'production' : 'dev';

        $prefixes = $config['va_prefixes'][$envKey] ?? [];

        if (isset($prefixes[$paymentMethod])) {
            return $prefixes[$paymentMethod];
        }

        // Fallback default prefixes for sandbox / dev
        if (!$isProd) {
            $defaultDevPrefixes = [
                'faspay_permata_va'  => '370201',
                'faspay_mandiri_va'  => '37020002',
                'faspay_bri_va'      => '370202',
                'faspay_cimb_va'     => '370204',
                'faspay_bni_va'      => '9881236387',
            ];
            return $defaultDevPrefixes[$paymentMethod] ?? '370200';
        }

        // Fallback default prefixes for production
        $defaultProdPrefixes = [
            'faspay_mandiri_va'  => '88558010',
            'faspay_sinarmas_va' => '885648',
            'faspay_permata_va'  => '735161',
            'faspay_maybank_va'  => '78218052',
            'faspay_danamon_va'  => '797039',
            'faspay_bsi_va'      => '12601021',
            'faspay_cimb_va'     => '222550',
            'faspay_bri_va'      => '121568',
            'faspay_bni_va'      => '8583',
        ];

        return $defaultProdPrefixes[$paymentMethod] ?? '370200';
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
