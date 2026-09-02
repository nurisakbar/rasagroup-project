<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FaspaySnapService
{
    protected $baseUrl;
    protected $clientId;
    protected $privateKeyPath;
    
    public function __construct()
    {
        $isProduction = config('services.faspay.env', 'dev') === 'production' || config('services.faspay.env', 'dev') === 'prod';
        
        $this->baseUrl = config('services.faspay.snap_base_url');
        // Auto-switch to production URL if env is production and URL is still pointing to sandbox
        if ($isProduction && str_contains($this->baseUrl, 'sandbox')) {
            $this->baseUrl = 'https://debit.faspay.co.id/v1.0';
        }
        
        $this->clientId = config('services.faspay.snap_client_id') ?: config('services.faspay.merchant_id');
        $configPath = $isProduction ? config('services.faspay.private_key_prod_path') : config('services.faspay.private_key_dev_path');
        $this->privateKeyPath = ($configPath && str_starts_with($configPath, '/')) ? $configPath : base_path($configPath ?? 'storage/app/faspay_private_key.pem');
    }

    /**
     * Generate SNAP B2B Access Token using RSA-SHA256 (with 14-minute caching)
     */
    public function getB2bToken($forceRefresh = false)
    {
        if (!$forceRefresh && Cache::has('faspay_snap_b2b_token')) {
            $cached = Cache::get('faspay_snap_b2b_token');
            if ($cached && isset($cached['accessToken'])) {
                return $cached;
            }
        }

        $url = rtrim($this->baseUrl, '/') . '/access-token/b2b';
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $stringToSign = $this->clientId . '|' . $timestamp;
        
        $privateKey = file_get_contents($this->privateKeyPath);
        
        Log::debug('Faspay B2B Token: Key Debug Info', [
            'path' => $this->privateKeyPath,
            'exists' => file_exists($this->privateKeyPath),
            'header' => substr(trim($privateKey), 0, 30) // To check if it says PUBLIC or PRIVATE
        ]);

        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            $error = openssl_error_string();
            Log::error('Faspay B2B Token Error: Invalid private key at path ' . $this->privateKeyPath, [
                'openssl_error' => $error
            ]);
            throw new \Exception("Faspay Private Key configuration is invalid or missing at path: " . $this->privateKeyPath . ". OpenSSL Error: " . $error);
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
                Cache::put('faspay_snap_b2b_token', $data, 840); // 14 mins cache
            }
            return $data;
        }

        Log::error('Faspay SNAP B2B Token Error', [
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
    public function generateTransactionAsymmetricSignature($method, $endpoint, $payload, $timestamp, $privateKey)
    {
        $minifyPayload = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $hashPayload = strtolower(hash('sha256', $minifyPayload));
        $stringToSign = $method . ":" . $endpoint . ":" . $hashPayload . ":" . $timestamp;
        
        Log::debug('Faspay Transaction Signature: Key Debug Info', [
            'header' => substr(trim($privateKey), 0, 30) // To check if it says PUBLIC or PRIVATE
        ]);

        $keyResource = openssl_pkey_get_private($privateKey);
        if (!$keyResource) {
            $error = openssl_error_string();
            Log::error('Faspay Signature Error: Supplied key is empty or cannot be coerced into a private key.', [
                'openssl_error' => $error
            ]);
            throw new \Exception("Faspay Private Key configuration is invalid or missing. Ensure FASPAY_SNAP_PRIVATE_KEY_DEV_PATH (or PROD_PATH) points to a valid private key. OpenSSL Error: " . $error);
        }
        
        openssl_sign($stringToSign, $signature, $keyResource, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Generate QRIS
     */
    public function generateQris($order, $amount)
    {
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/qr/qr-mpm-generate';
        $url = config('services.faspay.snap_base_url', 'https://debit-sandbox.faspay.co.id/v1.0') . '/qr/qr-mpm-generate';
        $partnerId = config('services.faspay.snap_client_id') ?: config('services.faspay.merchant_id');
        $isProduction = config('services.faspay.env', 'dev') === 'production';
        $configPath = $isProduction ? config('services.faspay.private_key_prod_path') : config('services.faspay.private_key_dev_path');
        $privateKeyPath = ($configPath && str_starts_with($configPath, '/')) ? $configPath : base_path($configPath ?? 'storage/app/faspay_private_key.pem');
        $privateKey = file_exists($privateKeyPath) ? file_get_contents($privateKeyPath) : '';
        
        Log::debug('Faspay QRIS Generation: Path Debug Info', [
            'env_config' => $configPath,
            'resolved_path' => $privateKeyPath,
            'file_exists' => file_exists($privateKeyPath)
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
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
        ]);

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

        Log::debug('Faspay SNAP QRIS Response', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Faspay SNAP QRIS Error', [
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
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/debit/payment-host-to-host';
        $url = config('services.faspay.snap_base_url', 'https://debit-sandbox.faspay.co.id/v1.0') . '/debit/payment-host-to-host';
        $partnerId = config('services.faspay.snap_client_id') ?: config('services.faspay.merchant_id');
        $isProduction = config('services.faspay.env', 'dev') === 'production';
        $configPath = $isProduction ? config('services.faspay.private_key_prod_path') : config('services.faspay.private_key_dev_path');
        $privateKeyPath = ($configPath && str_starts_with($configPath, '/')) ? $configPath : base_path($configPath ?? 'storage/app/faspay_private_key.pem');
        $privateKey = file_exists($privateKeyPath) ? file_get_contents($privateKeyPath) : '';
        
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

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Faspay SNAP Direct Debit Error', [
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
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/debit/status';
        $url = config('services.faspay.snap_base_url', 'https://debit-sandbox.faspay.co.id/v1.0') . '/debit/status';
        $partnerId = config('services.faspay.snap_client_id') ?: config('services.faspay.merchant_id');
        $isProduction = config('services.faspay.env', 'dev') === 'production';
        $configPath = $isProduction ? config('services.faspay.private_key_prod_path') : config('services.faspay.private_key_dev_path');
        $privateKeyPath = ($configPath && str_starts_with($configPath, '/')) ? $configPath : base_path($configPath ?? 'storage/app/faspay_private_key.pem');
        $privateKey = file_exists($privateKeyPath) ? file_get_contents($privateKeyPath) : '';
        
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

        // UAT Logging for Scenario 19.14
        Log::info("Faspay UAT Simulation Result - Scenario 19.14", [
            'scenario' => 'Direct Debit Payment Status',
            'request_url' => $url,
            'request_headers' => $headers,
            'request_payload' => $payload,
            'http_code' => $response->status(),
            'response_body' => $response->json() ?? $response->body()
        ]);

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Faspay SNAP Direct Debit Status Error', [
            'url' => $url,
            'headers' => $headers,
            'payload' => $payload,
            'status' => $response->status(),
            'body' => $response->body()
        ]);

        return null;
    }
}
