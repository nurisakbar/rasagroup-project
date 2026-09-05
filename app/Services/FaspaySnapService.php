<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FaspaySnapService
{
    protected $company;
    protected $companyConfig;
    protected $baseUrl;
    protected $clientId;
    protected $partnerId;
    protected $privateKeyPath;
    
    public function __construct(?string $company = null)
    {
        $this->company = $company ? strtolower($company) : FaspayConfig::getDefaultCompany();
        $this->companyConfig = FaspayConfig::getCompanyConfig($this->company);

        $env = $this->companyConfig['env'] ?? config('services.faspay.env', 'dev');
        $isProduction = in_array(strtolower($env), ['production', 'prod']);
        
        $this->baseUrl = $this->companyConfig['snap_base_url'] ?? config('services.faspay.snap_base_url');
        // Auto-switch to production URL if env is production and URL is still pointing to sandbox
        if ($isProduction && str_contains($this->baseUrl, 'sandbox')) {
            $this->baseUrl = 'https://debit.faspay.co.id/v1.0';
        }
        
        $this->clientId = $this->companyConfig['snap_client_id'] ?: ($this->companyConfig['merchant_id'] ?? '37020');
        $this->partnerId = $this->clientId;

        $configPath = $isProduction 
            ? ($this->companyConfig['private_key_prod_path'] ?? null) 
            : ($this->companyConfig['private_key_dev_path'] ?? null);

        $resolvedPath = ($configPath && str_starts_with($configPath, '/')) ? $configPath : base_path($configPath ?? 'storage/app/faspay_private_key.pem');

        // Fallback to legacy path if company-specific key does not exist
        if (!file_exists($resolvedPath)) {
            $legacyDev = base_path('storage/app/faspay_private_key_dev.pem');
            $legacyProd = base_path('storage/app/faspay_private_key.pem');
            if (!$isProduction && file_exists($legacyDev)) {
                $resolvedPath = $legacyDev;
            } elseif (file_exists($legacyProd)) {
                $resolvedPath = $legacyProd;
            }
        }

        $this->privateKeyPath = $resolvedPath;
    }

    /**
     * Get the active company code.
     */
    public function getCompany(): string
    {
        return $this->company;
    }

    /**
     * Get private key contents.
     */
    public function getPrivateKey(): string
    {
        if (file_exists($this->privateKeyPath)) {
            return file_get_contents($this->privateKeyPath);
        }
        return '';
    }

    /**
     * Generate SNAP B2B Access Token using RSA-SHA256 (with 14-minute caching per company)
     */
    public function getB2bToken($forceRefresh = false)
    {
        $cacheKey = 'faspay_snap_b2b_token_' . $this->company;

        if (!$forceRefresh && Cache::has($cacheKey)) {
            $cached = Cache::get($cacheKey);
            if ($cached && isset($cached['accessToken'])) {
                return $cached;
            }
        }

        $url = rtrim($this->baseUrl, '/') . '/access-token/b2b';
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $stringToSign = $this->clientId . '|' . $timestamp;
        
        $privateKey = $this->getPrivateKey();
        
        Log::debug('Faspay B2B Token: Key Debug Info', [
            'company' => $this->company,
            'path' => $this->privateKeyPath,
            'exists' => file_exists($this->privateKeyPath),
            'header' => substr(trim($privateKey), 0, 30) // To check if it says PUBLIC or PRIVATE
        ]);

        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            $error = openssl_error_string();
            Log::error('Faspay B2B Token Error: Invalid private key at path ' . $this->privateKeyPath, [
                'company' => $this->company,
                'openssl_error' => $error
            ]);
            throw new \Exception("Faspay Private Key configuration is invalid or missing for [{$this->company}] at path: " . $this->privateKeyPath . ". OpenSSL Error: " . $error);
        }
        openssl_sign($stringToSign, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        $signatureBase64 = base64_encode($signature);

        $headers = [
            'X-TIMESTAMP' => $timestamp,
            'X-CLIENT-KEY' => $this->clientId,
            'X-SIGNATURE' => $signatureBase64,
            'Content-Type' => 'application/json',
        ];

        $payload = [
            'grantType' => 'client_credentials',
            'additionalInfo' => (object) [],
        ];

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

        if ($response->successful()) {
            $data = $response->json();
            if (isset($data['accessToken'])) {
                Cache::put($cacheKey, $data, 840); // 14 mins cache
            }
            return $data;
        }

        Log::error('Faspay SNAP B2B Token Error', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Generate SNAP Symmetric Signature (HMAC-SHA512)
     */
    public function generateSymmetricSignature($httpMethod, $endpointUrl, $accessToken, $requestBody, $timestamp, $clientSecret)
    {
        $bodyHash = hash('sha256', is_string($requestBody) ? $requestBody : json_encode($requestBody));
        $stringToSign = $httpMethod . ':' . $endpointUrl . ':' . $accessToken . ':' . strtolower($bodyHash) . ':' . $timestamp;
        
        return base64_encode(hash_hmac('sha512', $stringToSign, $clientSecret, true));
    }

    /**
     * Generate SNAP Asymmetric Signature (RSA-SHA256)
     */
    public function generateTransactionAsymmetricSignature($method, $endpoint, $payload, $timestamp, $privateKey = null)
    {
        $privateKey = $privateKey ?: $this->getPrivateKey();
        $minifyPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $hashPayload = strtolower(hash('sha256', $minifyPayload));
        $stringToSign = $method . ":" . $endpoint . ":" . $hashPayload . ":" . $timestamp;
        
        Log::debug('Faspay Transaction Signature: Key Debug Info', [
            'company' => $this->company,
            'header' => substr(trim($privateKey), 0, 30)
        ]);

        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            $error = openssl_error_string();
            Log::error('Faspay Signature Error: Supplied key is empty or cannot be coerced into a private key.', [
                'company' => $this->company,
                'openssl_error' => $error
            ]);
            throw new \Exception("Faspay Private Key configuration is invalid or missing for [{$this->company}]. Ensure FASPAY_SNAP_PRIVATE_KEY points to a valid private key. OpenSSL Error: " . $error);
        }
        
        openssl_sign($stringToSign, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Generate QRIS
     */
    public function generateQris($order, $amount)
    {
        // Re-align company if order specifies one
        if (!empty($order->company) && $order->company !== $this->company) {
            return (new self($order->company))->generateQris($order, $amount);
        }

        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/qr/qr-mpm-generate';
        $url = rtrim($this->baseUrl, '/') . '/qr/qr-mpm-generate';
        $partnerId = $this->partnerId;
        $privateKey = $this->getPrivateKey();
        
        Log::debug('Faspay QRIS Generation: Path Debug Info', [
            'company' => $this->company,
            'resolved_path' => $this->privateKeyPath,
            'file_exists' => file_exists($this->privateKeyPath)
        ]);

        $payload = [
            'partnerReferenceNo' => (string) $order->order_number,
            'amount' => [
                'value' => number_format($amount, 2, '.', ''),
                'currency' => 'IDR'
            ],
            'merchantId' => $partnerId,
            'validityPeriod' => now()->addHours(1)->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
            'additionalInfo' => [
                'billDate' => now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
                'billDescription' => 'Payment #' . $order->order_number,
                'channelCode' => '836',
                'phoneNo' => $order->user->phone ?? '081234567890'
            ]
        ];

        $signature = $this->generateTransactionAsymmetricSignature('POST', $endpoint, $payload, $timestamp, $privateKey);

        $b2bData = $this->getB2bToken();
        $b2bToken = $b2bData['accessToken'] ?? '';

        $headers = [
            'Authorization' => 'Bearer ' . $b2bToken,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $partnerId,
            'X-EXTERNAL-ID' => date('YmdHis') . rand(1000, 9999),
            'CHANNEL-ID' => '77001',
            'Content-Type' => 'application/json'
        ];

        Log::debug('Faspay SNAP QRIS Request', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

        Log::debug('Faspay SNAP QRIS Response', [
            'company' => $this->company,
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Faspay SNAP QRIS Error', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Direct Debit Payment (Host-to-Host)
     */
    public function directDebitPayment($order, $amount, $paymentChannelUid = '812')
    {
        // Re-align company if order specifies one
        if (!empty($order->company) && $order->company !== $this->company) {
            return (new self($order->company))->directDebitPayment($order, $amount, $paymentChannelUid);
        }

        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/debit/payment-host-to-host';
        $url = rtrim($this->baseUrl, '/') . '/debit/payment-host-to-host';
        $partnerId = $this->partnerId;
        $privateKey = $this->getPrivateKey();
        
        // Ensure amount is string with 2 decimal places
        $amountStr = number_format($amount, 2, '.', '');
        
        $payload = [
            'partnerReferenceNo' => (string) $order->order_number,
            'merchantId' => $partnerId,
            'amount' => [
                'value' => $amountStr,
                'currency' => 'IDR'
            ],
            'customerEmail' => $order->user->email ?? 'customer@rasagroup.co.id',
            'customerPhone' => $order->user->phone ?? '081234567890',
            'validUpTo' => now()->addHours(24)->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
            'additionalInfo' => [
                'billDate' => now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP'),
                'channelCode' => $paymentChannelUid,
                'paymentChannelUid' => $paymentChannelUid,
                'customerName' => $order->user->name ?? 'Customer Rasa Group',
                'billDescription' => 'Payment #' . $order->order_number
            ]
        ];

        $signature = $this->generateTransactionAsymmetricSignature('POST', $endpoint, $payload, $timestamp, $privateKey);

        $b2bData = $this->getB2bToken();
        $b2bToken = $b2bData['accessToken'] ?? '';

        $headers = [
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $partnerId,
            'X-EXTERNAL-ID' => date('YmdHis') . rand(1000, 9999),
            'CHANNEL-ID' => '77001',
            'Content-Type' => 'application/json'
        ];

        \Log::info('Faspay SNAP Direct Debit Request', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload
        ]);

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

        if ($response->successful()) {
            \Log::info('Faspay SNAP Direct Debit Response', [
                'company' => $this->company,
                'status' => $response->status(),
                'body' => $response->json()
            ]);
            return $response->json();
        }

        Log::error('Faspay SNAP Direct Debit Error', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }

    /**
     * Direct Debit Payment Status
     */
    public function directDebitPaymentStatus($order)
    {
        // Re-align company if order specifies one
        if (!empty($order->company) && $order->company !== $this->company) {
            return (new self($order->company))->directDebitPaymentStatus($order);
        }

        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/debit/status';
        $url = rtrim($this->baseUrl, '/') . '/debit/status';
        $partnerId = $this->partnerId;
        $privateKey = $this->getPrivateKey();
        
        $payload = [
            'originalPartnerReferenceNo' => (string) $order->order_number,
            'merchantId' => $partnerId,
            'serviceCode' => '55'
        ];

        $signature = $this->generateTransactionAsymmetricSignature('POST', $endpoint, $payload, $timestamp, $privateKey);

        $b2bData = $this->getB2bToken();
        $b2bToken = $b2bData['accessToken'] ?? '';

        $headers = [
            'Authorization' => 'Bearer ' . $b2bToken,
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $partnerId,
            'X-EXTERNAL-ID' => date('YmdHis') . rand(1000, 9999),
            'CHANNEL-ID' => '77001',
            'Content-Type' => 'application/json'
        ];

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

        Log::info("Faspay SNAP Direct Debit Status Response", [
            'company' => $this->company,
            'scenario' => 'Direct Debit Payment Status',
            'request_url' => $url,
            'http_code' => $response->status(),
            'response_body' => $response->json() ?? $response->body()
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Faspay SNAP Direct Debit Status Error', [
            'company' => $this->company,
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }
}
