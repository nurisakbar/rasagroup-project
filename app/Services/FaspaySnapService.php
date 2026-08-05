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
        $this->baseUrl = config('services.faspay.snap_base_url');
        $this->clientId = config('services.faspay.snap_client_id');
        $this->privateKeyPath = base_path(config('services.faspay.private_key_path'));
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
        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
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
        
        openssl_sign($stringToSign, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        return base64_encode($signature);
    }

    /**
     * Generate QRIS
     */
    public function generateQris($order, $amount)
    {
        $timestamp = now()->timezone('Asia/Jakarta')->format('Y-m-d\TH:i:sP');
        $endpoint = '/v1.0/qr/qr-mpm-generate';
        $url = env('FASPAY_SNAP_URL', 'https://debit-sandbox.faspay.co.id') . $endpoint;
        $partnerId = env('FASPAY_SNAP_CLIENT_ID', env('FASPAY_MERCHANT_ID'));
        $privateKeyPath = env('FASPAY_PRIVATE_KEY_PATH', storage_path('app/faspay_private_key.pem'));
        $privateKey = file_exists($privateKeyPath) ? file_get_contents($privateKeyPath) : '';

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
                'channelCode' => '711',
                'phoneNo' => $order->user->phone ?? '081234567890'
            ]
        ];

        $signature = $this->generateTransactionAsymmetricSignature('POST', $endpoint, $payload, $timestamp, $privateKey);

        $headers = [
            'X-TIMESTAMP' => $timestamp,
            'X-SIGNATURE' => $signature,
            'X-PARTNER-ID' => $partnerId,
            'X-EXTERNAL-ID' => (string) $order->order_number,
            'CHANNEL-ID' => '711',
            'Content-Type' => 'application/json'
        ];

        $response = Http::withoutVerifying()
            ->withHeaders($headers)
            ->post($url, $payload);

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
}
