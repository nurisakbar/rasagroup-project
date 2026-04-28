<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\QadService;
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
        // avoid serializing heavy relations; store only needed address fields
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

        // Some QID setups require a Business Relation to exist before creating a Customer.
        $this->ensureBusinessRelation($qadService, $user, $customerCode);

        $payloadCandidates = [
            // Candidate 1: full-ish payload (but trimmed)
            $this->buildCustomerPayload($user, $customerCode, [
                'use_minimal_strings' => false,
            ]),
            // Candidate 2: minimal/short strings to avoid MFG/PRO field-size issues
            $this->buildCustomerPayload($user, $customerCode, [
                'use_minimal_strings' => true,
            ]),
        ];

        $result = null;
        foreach ($payloadCandidates as $idx => $payload) {
            Log::info('SyncCustomerToQad: Creating customer in QID', [
                'user_id' => $user->id,
                'candidate' => $idx + 1,
                'payload' => $payload,
            ]);
            $result = $qadService->createCustomer($payload);
            if (!empty($result)) {
                break;
            }
        }

        $createdCode =
            $result['data']['customerCode'] ??
            $result['data']['businessRelationCode'] ??
            $result['customerCode'] ??
            null;

        if ($result && $createdCode) {
            $user->update(['qad_customer_code' => $createdCode]);
            Log::info('SyncCustomerToQad: Customer created', [
                'user_id' => $user->id,
                'qad_customer_code' => $createdCode,
            ]);

            // In some implementations, this is required to finalize customer data creation.
            $this->ensureCustomerData($qadService, $createdCode);
            return;
        }

        // fallback: try get customer (QID sometimes creates but returns error/empty)
        $check = $qadService->getCustomer($customerCode, 'MCR-CUST');
        $existsCode = $check['data']['customerCode'] ?? null;
        if ($check && $existsCode) {
            $user->update(['qad_customer_code' => $existsCode]);
            Log::info('SyncCustomerToQad: Customer exists, code saved', [
                'user_id' => $user->id,
                'qad_customer_code' => $existsCode,
            ]);
            $this->ensureCustomerData($qadService, $existsCode);
            return;
        }

        Log::error('SyncCustomerToQad: Failed to create customer', [
            'user_id' => $user->id,
            'customer_code' => $customerCode,
            'response' => $result,
        ]);
    }

    private function generateCustomerCode(User $user): string
    {
        // match working pattern like ZH00002 (no dash)
        $hash = hexdec(substr(md5((string) $user->id), 0, 8));
        $num = (int) ($hash % 100000);
        return 'ZH' . str_pad((string) $num, 5, '0', STR_PAD_LEFT);
    }

    private function buildCustomerPayload(User $user, string $customerCode, array $opts = []): array
    {
        $useMinimal = (bool) ($opts['use_minimal_strings'] ?? false);

        $name = (string) ($user->name ?? 'Customer');
        $city = (string) ($this->addressSnapshot['city'] ?? 'Jakarta');
        $street1 = (string) ($this->addressSnapshot['street1'] ?? '-');
        $street2 = (string) ($this->addressSnapshot['street2'] ?? '-');

        // Conservative trimming: MFG/PRO fields are often short.
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
            "addressName" => $name,
            "addressSearchName" => $nameShort,
            "businessRelationCode" => $customerCode,
            "city" => $cityShort,
            "countryCode" => "ID",
            "languageCode" => "us",
            "street1" => $street1Short,
            "street2" => $street2Short,
            "isTaxInCity" => true,
            "taxZone" => "IDN",
            "taxClass" => "PPN",
            "reminderCountryCode" => "ID",
            "reminderLanguageCode" => "us",
            "reminderTaxZone" => "IDN",
            "customerCode" => $customerCode,
            "isActive" => true,
            "isBusinessRelationActive" => true,
            "businessRelationName" => $name,
            "invoiceControlGLProfileCode" => "12101",
            "creditNoteControlGLProfileCode" => "12101",
            "prePaymentControlGLProfileCode" => "12101",
            "salesAccountGLProfileCode" => "41101",
            "currencyCode" => "IDR",
            "customerTypeCode" => "LOC",
            "creditTermsCode" => "CIA",
            "creditTermsType" => "NORMAL",
            "invoiceStatusCode" => "APPROVED-AR",
            "isTaxable" => true,
            "sharedSetCode" => "MCR-CUST",
            "vatDeliveryType" => "SERVICE",
            "vatPercentageLevel" => "NONE",
            "addressTypeCode" => "HEADOFFICE",
            "isBusinessRelationFieldsEnabled" => true,
            "customerCurrencyCode" => "IDR",
            "isOverruleAllowedSOCreditLimit" => true,
        ];
    }

    private function ensureBusinessRelation(QadService $qadService, User $user, string $code): void
    {
        // Try "get" first to avoid unnecessary create attempts
        $existing = $qadService->getBusinessRelation($code);
        $existingCode = $existing['data']['businessRelationCode'] ?? null;
        if ($existing && $existingCode) {
            return;
        }

        $payload = [
            "businessRelationCode" => $code,
            "businessRelationName1" => (string) ($user->name ?? 'Customer'),
            "businessRelationName2" => "",
            "businessRelationName3" => "",
            "businessRelationSearchName" => substr((string) ($user->name ?? 'Customer'), 0, 20),
            "corporateGroupCode" => "",
            "headOfficeAddressName" => (string) ($user->name ?? 'Customer'),
            "headOfficeAddressSearchName" => substr((string) ($user->name ?? 'Customer'), 0, 20),
            "headOfficeAddressTypeCode" => "HEADOFFICE",
            "headOfficeBusinessRelationCode" => $code,
            "headOfficeCity" => (string) ($this->addressSnapshot['city'] ?? 'Jakarta'),
            "headOfficeLanguageCode" => "us",
            "headOfficeLatitude" => 0,
            "headOfficeLongitude" => 0,
            "headOfficeStreet1" => (string) ($this->addressSnapshot['street1'] ?? '-'),
            "headOfficeStreet2" => (string) ($this->addressSnapshot['street2'] ?? '-'),
            "headOfficeTaxClass" => "PPN",
            "headOfficeTaxZone" => "IDN",
            "headOfficeTelephone" => (string) ($user->phone ?? ''),
            "headOfficeWebSite" => "",
            "headOfficeZipCode" => "",
            "isActive" => true,
        ];

        Log::info('SyncCustomerToQad: Creating business relation in QID', [
            'user_id' => $user->id,
            'payload' => $payload,
        ]);

        $qadService->createBusinessRelation($payload);
    }

    private function ensureCustomerData(QadService $qadService, string $customerCode): void
    {
        $payload = ["customerCode" => $customerCode];
        Log::info('SyncCustomerToQad: Ensuring customer create-data', [
            'customer_code' => $customerCode,
        ]);
        $qadService->createCustomerData($payload);
    }
}

