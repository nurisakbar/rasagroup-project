<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\QadService;
use Illuminate\Console\Command;

class CreateQadCustomer extends Command
{
    protected $signature = 'qad:create-customer
        {email? : Email user lokal. Nama, kota, jalan, dan telepon diambil dari akun ini.}
        {--name= : Nama customer jika tidak memakai email}
        {--city=Jakarta : Kota, maksimal 20 karakter}
        {--street= : Jalan, maksimal 20 karakter}
        {--phone= : Telepon}
        {--zip=10110 : Kode pos}
        {--code= : Kode customer CS#####. Kosong berarti dibuat otomatis}
        {--save : Simpan kode QAD ke user jika email diberikan}';

    protected $description = 'Buat customer di QAD lalu kirim create-data';

    public function handle(QadService $qad): int
    {
        $user = $this->resolveUser();
        $profile = $this->profile($user);

        $code = strtoupper(trim((string) ($this->option('code') ?: $this->nextCode())));
        if (! preg_match('/^CS\d{5}$/', $code)) {
            $this->error('Kode customer harus berbentuk CS diikuti 5 digit, contoh CS91030.');

            return self::FAILURE;
        }

        $payload = $this->customerPayload($code, $profile);

        $this->info("Membuat customer {$code} ({$payload['addressName']})...");
        $this->line(json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $created = $qad->createCustomer($payload);
        $this->comment('Respons create:');
        $this->line(json_encode($created, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        if ($this->isError($created)) {
            $this->warn('Create customer gagal. Mencoba buat business relation lalu create ulang.');
            $relation = $qad->createBusinessRelation($this->businessRelationPayload($code, $profile));
            $this->line(json_encode($relation, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $created = $qad->createCustomer($payload);
            $this->comment('Respons create kedua:');
            $this->line(json_encode($created, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }

        if ($this->isError($created) && ! $this->customerExists($qad, $code)) {
            $this->error($this->messages($created) ?: 'Customer tidak berhasil dibuat.');

            return self::FAILURE;
        }

        $data = $qad->createCustomerData(['customerCode' => $code]);
        $this->comment('Respons create-data:');
        $this->line(json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

        $dataMessage = (string) ($data['data']['message'] ?? '');
        if (str_contains(strtolower($dataMessage), 'too long')) {
            $this->warn('QAD membuat customer, tetapi create-data melaporkan field terlalu panjang. Sales order customer baru ini bisa gagal dengan pesan customer data is not complete / invalid site.');
        }

        if ($user && $this->option('save')) {
            $user->update(['qad_customer_code' => $code]);
            $this->info("Kode {$code} disimpan ke user {$user->email}.");
        }

        $this->info("Selesai. Kode customer: {$code}");

        return self::SUCCESS;
    }

    private function resolveUser(): ?User
    {
        $email = trim((string) $this->argument('email'));
        if ($email === '') {
            return null;
        }

        $user = User::query()->where('email', $email)->first();
        if (! $user) {
            $this->warn("User {$email} tidak ditemukan. Memakai opsi --name, --city, --street, dan --phone.");
        }

        return $user;
    }

    /**
     * @return array{name: string, city: string, street: string, phone: string, zip: string}
     */
    private function profile(?User $user): array
    {
        $address = $user?->addresses()->orderByDesc('is_default')->first();
        $city = (string) ($this->option('city') ?: ($address?->regency?->name ?? 'Jakarta'));
        $street = (string) ($this->option('street') ?: ($address?->address_detail ?? 'Jl Pasar Pagi'));
        $phone = (string) ($this->option('phone') ?: ($user?->phone ?? ''));
        $name = (string) ($this->option('name') ?: ($user?->name ?? 'Customer'));

        return [
            'name' => $this->clip($name, 20),
            'city' => $this->clip($this->shortCity($city), 20),
            'street' => $this->clip($this->plainStreet($street), 20),
            'phone' => $this->phone($phone),
            'zip' => $this->zip((string) ($address?->postal_code ?? $this->option('zip'))),
        ];
    }

    /**
     * @param  array{name: string, city: string, street: string, phone: string, zip: string}  $profile
     * @return array<string, mixed>
     */
    private function customerPayload(string $code, array $profile): array
    {
        return [
            'addressName' => $profile['name'],
            'addressSearchName' => $profile['name'],
            'businessRelationCode' => $code,
            'city' => $profile['city'],
            'countryCode' => 'ID',
            'languageCode' => 'us',
            'street1' => $profile['street'],
            'street2' => '',
            'isTaxInCity' => true,
            'taxZone' => 'IDN',
            'taxClass' => 'PPN',
            'reminderCountryCode' => 'ID',
            'reminderLanguageCode' => 'us',
            'reminderTaxZone' => 'IDN',
            'customerCode' => $code,
            'isActive' => true,
            'isBusinessRelationActive' => true,
            'businessRelationName' => $profile['name'],
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
            'corporateGroupCode' => 'Customer',
            'isOverruleAllowedSOCreditLimit' => true,
        ];
    }

    /**
     * @param  array{name: string, city: string, street: string, phone: string, zip: string}  $profile
     * @return array<string, mixed>
     */
    private function businessRelationPayload(string $code, array $profile): array
    {
        return [
            'businessRelationCode' => $code,
            'businessRelationName1' => $profile['name'],
            'businessRelationSearchName' => $profile['name'],
            'headOfficeAddressName' => $profile['name'],
            'headOfficeAddressSearchName' => $profile['name'],
            'headOfficeAddressTypeCode' => 'HEADOFFICE',
            'headOfficeBusinessRelationCode' => $code,
            'headOfficeCity' => $profile['city'],
            'headOfficeLanguageCode' => 'us',
            'headOfficeStreet1' => $profile['street'],
            'headOfficeStreet2' => '',
            'headOfficeTaxClass' => 'PPN',
            'headOfficeTaxZone' => 'IDN',
            'headOfficeTelephone' => $profile['phone'],
            'headOfficeZipCode' => $profile['zip'],
            'isActive' => true,
        ];
    }

    private function nextCode(): string
    {
        for ($i = 0; $i < 40; $i++) {
            $code = 'CS'.str_pad((string) random_int(80000, 99999), 5, '0', STR_PAD_LEFT);
            $taken = User::query()->where('qad_customer_code', $code)->exists();
            if (! $taken) {
                return $code;
            }
        }

        return 'CS'.random_int(80000, 99999);
    }

    private function customerExists(QadService $qad, string $code): bool
    {
        $found = $qad->getCustomer($code, 'MCR-CUST');
        $data = $found['data'] ?? null;

        return is_array($data) && ($data !== [] || isset($data['customerCode']));
    }

    private function isError(?array $result): bool
    {
        return ! is_array($result) || (bool) ($result['error']['isError'] ?? false);
    }

    private function messages(?array $result): string
    {
        $messages = $result['error']['errorMessages'] ?? [];

        return is_array($messages) ? implode(' ', array_filter($messages)) : '';
    }

    private function shortCity(string $city): string
    {
        $city = trim($city);
        $upper = mb_strtoupper($city, 'UTF-8');
        foreach (['JAKARTA' => 'Jakarta', 'BEKASI' => 'Bekasi', 'TANGERANG' => 'Tangerang', 'BANDUNG' => 'Bandung', 'SURABAYA' => 'Surabaya'] as $needle => $label) {
            if (str_contains($upper, $needle)) {
                return $label;
            }
        }

        return $city !== '' ? $city : 'Jakarta';
    }

    private function plainStreet(string $street): string
    {
        $street = trim(preg_replace('/\s+/', ' ', $street) ?? '');
        $street = str_replace(['+', '/', '\\'], ' ', $street);

        return trim(preg_replace('/\s+/', ' ', $street) ?? '') ?: 'Jl Pasar Pagi';
    }

    private function phone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '6210000000';
        }
        if (str_starts_with($digits, '0')) {
            $digits = '62'.substr($digits, 1);
        }
        if (! str_starts_with($digits, '62')) {
            $digits = '62'.$digits;
        }

        return substr($digits, 0, 16);
    }

    private function zip(string $zip): string
    {
        $digits = preg_replace('/\D+/', '', $zip) ?? '';

        return strlen($digits) >= 5 ? substr($digits, 0, 5) : '10110';
    }

    private function clip(string $value, int $length): string
    {
        $value = trim($value);

        return mb_substr($value !== '' ? $value : '-', 0, $length, 'UTF-8');
    }
}
