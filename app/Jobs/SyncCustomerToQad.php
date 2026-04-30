<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\QadService;
use App\Support\QadBusinessRelationHeadOffice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SyncCustomerToQad implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;
    protected array $addressSnapshot;

    public function __construct(User $user, array $addressSnapshot = [])
    {
        $this->user = $user;
        $this->addressSnapshot = $addressSnapshot;
    }

    public function handle(QadService $qadService): void
    {
        $user = $this->user->fresh();
        if (!$user) {
            return;
        }

        if (!empty($user->qad_customer_code)) {
            Log::info('SyncCustomerToQad: Skip, already has customer code', [
                'user_id' => $user->id,
                'qad_customer_code' => $user->qad_customer_code,
            ]);
            return;
        }

        $customerCode = $this->generateCustomerCode($user);

        $payloadCandidates = [
            $this->buildCustomerPayload($user, $customerCode, [
                'use_minimal_strings' => false,
            ]),
            $this->buildCustomerPayload($user, $customerCode, [
                'use_minimal_strings' => true,
            ]),
        ];

        // Sama seperti QadTestingManual: banyak environment QID bisa create customer tanpa BR eksplisit.
        $result = $this->attemptCreateCustomer($qadService, $user->id, $payloadCandidates);
        $createdCode = $this->extractCreatedCustomerCode($result);

        if (!$createdCode) {
            Log::info('SyncCustomerToQad: Direct customer create did not succeed; trying business relation then customer', [
                'user_id' => $user->id,
                'customer_code' => $customerCode,
            ]);
            $this->ensureBusinessRelation($qadService, $user, $customerCode);
            $result = $this->attemptCreateCustomer($qadService, $user->id, $payloadCandidates);
            $createdCode = $this->extractCreatedCustomerCode($result);
        }

        if ($createdCode) {
            $user->update(['qad_customer_code' => $createdCode]);
            Log::info('SyncCustomerToQad: Customer created', [
                'user_id' => $user->id,
                'qad_customer_code' => $createdCode,
            ]);
            $this->ensureCustomerData($qadService, $createdCode);
            QadBusinessRelationHeadOffice::patch($qadService, $user->fresh(), $createdCode, $this->addressSnapshot);

            return;
        }

        $check = $qadService->getCustomer($customerCode, 'MCR-CUST');
        $existsCode = $this->extractCustomerCodeFromGet($check);
        if ($existsCode) {
            $user->update(['qad_customer_code' => $existsCode]);
            Log::info('SyncCustomerToQad: Customer exists, code saved', [
                'user_id' => $user->id,
                'qad_customer_code' => $existsCode,
            ]);
            $this->ensureCustomerData($qadService, $existsCode);
            QadBusinessRelationHeadOffice::patch($qadService, $user->fresh(), $existsCode, $this->addressSnapshot);

            return;
        }

        Log::error('SyncCustomerToQad: Failed to create customer', [
            'user_id' => $user->id,
            'customer_code' => $customerCode,
            'response' => $result,
        ]);
    }

    /**
     * @param  array<int, array<string, mixed>>  $payloadCandidates
     */
    private function attemptCreateCustomer(QadService $qadService, string $userId, array $payloadCandidates): ?array
    {
        $result = null;
        foreach ($payloadCandidates as $idx => $payload) {
            Log::info('SyncCustomerToQad: Creating customer in QID', [
                'user_id' => $userId,
                'candidate' => $idx + 1,
                'payload' => $payload,
            ]);
            $result = $qadService->createCustomer($payload);
            if ($this->extractCreatedCustomerCode($result) !== null) {
                break;
            }
        }

        return $result;
    }

    private function generateCustomerCode(User $user): string
    {
        for ($i = 0; $i < 40; $i++) {
            $code = 'ZH' . str_pad((string) random_int(0, 99999), 5, '0', STR_PAD_LEFT);
            if (! User::query()->where('qad_customer_code', $code)->exists()) {
                return $code;
            }
        }

        return 'ZH' . strtoupper(substr(str_replace('-', '', (string) $user->getKey()), 0, 8));
    }

    private function buildCustomerPayload(User $user, string $customerCode, array $opts = []): array
    {
        $useMinimal = (bool) ($opts['use_minimal_strings'] ?? false);

        $name = (string) ($user->name ?? 'Customer');
        $city = $this->normalizeCityForQad((string) ($this->addressSnapshot['city'] ?? ''));
        $street1 = $this->sanitizeStreet((string) ($this->addressSnapshot['street1'] ?? '-'));
        $street2 = $this->sanitizeStreet((string) ($this->addressSnapshot['street2'] ?? '-'));

        $nameShort = substr($name, 0, 20);
        $cityShort = substr($city, 0, 30);
        $street1Short = substr($street1, 0, 30);
        $street2Short = substr($street2, 0, 30);

        if ($useMinimal) {
            $name = $customerCode;
            $nameShort = $customerCode;
            $street2Short = '-';
        }

        return [
            'addressName' => $name,
            'addressSearchName' => $nameShort,
            'businessRelationCode' => $customerCode,
            'city' => $cityShort,
            'countryCode' => 'ID',
            'languageCode' => 'us',
            'street1' => $street1Short,
            'street2' => $street2Short,
            'isTaxInCity' => true,
            'taxZone' => 'IDN',
            'taxClass' => 'PPN',
            'reminderCountryCode' => 'ID',
            'reminderLanguageCode' => 'us',
            'reminderTaxZone' => 'IDN',
            'customerCode' => $customerCode,
            'isActive' => true,
            'isBusinessRelationActive' => true,
            'businessRelationName' => $name,
            'invoiceControlGLProfileCode' => '12101',
            'creditNoteControlGLProfileCode' => '12101',
            'prePaymentControlGLProfileCode' => '12101',
            'salesAccountGLProfileCode' => '41101',
            'currencyCode' => 'IDR',
            'customerTypeCode' => 'LOC',
            'creditTermsCode' => 'CIA',
            'creditTermsType' => 'NORMAL',
            'invoiceStatusCode' => 'APPROVED-AR',
            'isTaxable' => true,
            'sharedSetCode' => 'MCR-CUST',
            'vatDeliveryType' => 'SERVICE',
            'vatPercentageLevel' => 'NONE',
            'addressTypeCode' => 'HEADOFFICE',
            'isBusinessRelationFieldsEnabled' => true,
            'customerCurrencyCode' => 'IDR',
            'isOverruleAllowedSOCreditLimit' => true,
        ];
    }

    private function ensureBusinessRelation(QadService $qadService, User $user, string $code): void
    {
        $existing = $qadService->getBusinessRelation($code);
        if ($this->businessRelationExists($existing, $code)) {
            return;
        }

        $city = $this->normalizeCityForQad((string) ($this->addressSnapshot['city'] ?? ''));
        $payload = [
            'businessRelationCode' => $code,
            'businessRelationName1' => (string) ($user->name ?? 'Customer'),
            'businessRelationName2' => '',
            'businessRelationName3' => '',
            'businessRelationSearchName' => substr((string) ($user->name ?? 'Customer'), 0, 20),
            'corporateGroupCode' => '',
            'headOfficeAddressName' => (string) ($user->name ?? 'Customer'),
            'headOfficeAddressSearchName' => substr((string) ($user->name ?? 'Customer'), 0, 20),
            'headOfficeAddressTypeCode' => 'HEADOFFICE',
            'headOfficeBusinessRelationCode' => $code,
            'headOfficeCity' => substr($city, 0, 30),
            'headOfficeLanguageCode' => 'us',
            'headOfficeLatitude' => 0,
            'headOfficeLongitude' => 0,
            'headOfficeStreet1' => substr($this->sanitizeStreet((string) ($this->addressSnapshot['street1'] ?? '-')), 0, 30),
            'headOfficeStreet2' => substr($this->sanitizeStreet((string) ($this->addressSnapshot['street2'] ?? '-')), 0, 30),
            'headOfficeTaxClass' => 'PPN',
            'headOfficeTaxZone' => 'IDN',
            'headOfficeTelephone' => $this->normalizePhoneForQad((string) ($user->phone ?? '')),
            'headOfficeWebSite' => '',
            'headOfficeZipCode' => $this->resolvedPostalCode(),
            'isActive' => true,
        ];

        Log::info('SyncCustomerToQad: Creating business relation in QID', [
            'user_id' => $user->id,
            'payload' => $payload,
        ]);

        $res = $qadService->createBusinessRelation($payload);
        if (is_array($res) && ($res['error']['isError'] ?? false)) {
            Log::warning('SyncCustomerToQad: business-relation/create response has error flag', [
                'user_id' => $user->id,
                'response' => $res,
            ]);
        }
    }

    private function ensureCustomerData(QadService $qadService, string $customerCode): void
    {
        $payload = ['customerCode' => $customerCode];
        Log::info('SyncCustomerToQad: Ensuring customer create-data', [
            'customer_code' => $customerCode,
        ]);
        $qadService->createCustomerData($payload);
    }

    private function resolvedPostalCode(): string
    {
        $raw = trim((string) ($this->addressSnapshot['postal_code'] ?? ''));
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

    /**
     * QAD / QidApi umumnya memakai label kota singkat seperti di contoh internal ("Jakarta").
     */
    private function normalizeCityForQad(string $city): string
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

        // QAD/QidApi environment ini terlihat hanya menerima label kota tertentu.
        // Untuk menghindari BadRequest pada city yang tidak dikenali (mis. "Aceh Barat"),
        // fallback ke default city yang dipastikan ada di master QAD (config).
        return $default;
    }

    private function sanitizeStreet(string $line): string
    {
        $line = trim($line);

        return $line !== '' ? $line : '-';
    }

    private function normalizePhoneForQad(string $phone): string
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

    private function extractCreatedCustomerCode(?array $result): ?string
    {
        if (!$result || ($result['error']['isError'] ?? false)) {
            return null;
        }

        $c = $result['data']['customerCode']
            ?? $result['data']['businessRelationCode']
            ?? $result['customerCode']
            ?? null;

        return is_string($c) && $c !== '' ? $c : null;
    }

    private function extractCustomerCodeFromGet(?array $result): ?string
    {
        if (!$result || ($result['error']['isError'] ?? false)) {
            return null;
        }

        $c = $result['data']['customerCode'] ?? null;

        return is_string($c) && $c !== '' ? $c : null;
    }

    private function businessRelationExists(?array $res, string $code): bool
    {
        if (!$res || ($res['error']['isError'] ?? false)) {
            return false;
        }

        $got = $res['data']['businessRelationCode'] ?? null;

        return is_string($got) && $got !== '' && $got === $code;
    }
}
