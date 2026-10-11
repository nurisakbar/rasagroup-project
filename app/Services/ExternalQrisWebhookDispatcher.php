<?php

namespace App\Services;

use App\Models\ExternalQrisTransaction;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ExternalQrisWebhookDispatcher
{
    /**
     * Dispatch webhook notification to the 3rd party endpoint.
     *
     * @param ExternalQrisTransaction $transaction
     * @param array|null $rawNotification
     * @return array
     */
    public function dispatch(ExternalQrisTransaction $transaction, ?array $rawNotification = null): array
    {
        $callbackUrl = $transaction->callback_url;

        // Fallback to configured default callback URL if available
        if (empty($callbackUrl)) {
            $callbackUrl = config('services.faspay.external_qris.default_callback_url');
        }

        if (empty($callbackUrl)) {
            Log::info('External QRIS Webhook: No callback URL configured', [
                'reference_no' => $transaction->reference_no,
            ]);

            $transaction->callback_status = 'none';
            $transaction->save();

            return [
                'delivered' => false,
                'status_code' => null,
                'reason' => 'No callback URL provided by 3rd party',
            ];
        }

        if ($rawNotification) {
            $transaction->raw_notification_payload = $rawNotification;
        }

        $payload = $transaction->toWebhookPayload();
        $jsonPayload = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $secret = $transaction->callback_token ?: config('services.faspay.external_qris.api_key', '');
        $timestamp = (string) now()->timestamp;
        $signature = hash_hmac('sha256', $timestamp . '.' . $jsonPayload, $secret);

        $headers = [
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
            'User-Agent' => 'RasaGroup-Webhook-Relay/1.0',
            'X-Event' => 'payment.verified',
            'X-Timestamp' => $timestamp,
            'X-Signature' => $signature,
        ];

        if (!empty($transaction->callback_token)) {
            $headers['X-Callback-Token'] = $transaction->callback_token;
        }

        Log::info('External QRIS Webhook: Dispatching notification to 3rd party', [
            'reference_no' => $transaction->reference_no,
            'callback_url' => $callbackUrl,
            'attempt' => ($transaction->callback_attempts ?? 0) + 1,
        ]);

        try {
            $response = Http::withoutVerifying()
                ->timeout(15)
                ->withHeaders($headers)
                ->post($callbackUrl, $payload);

            $statusCode = $response->status();
            $body = $response->body();
            $isSuccess = $response->successful();

            $transaction->recordCallbackResult($isSuccess, $statusCode, $body);

            Log::info('External QRIS Webhook: Dispatch result', [
                'reference_no' => $transaction->reference_no,
                'callback_url' => $callbackUrl,
                'status_code' => $statusCode,
                'delivered' => $isSuccess,
            ]);

            return [
                'delivered' => $isSuccess,
                'status_code' => $statusCode,
                'response_body' => $body,
            ];

        } catch (\Throwable $e) {
            Log::error('External QRIS Webhook: Dispatch failed with exception', [
                'reference_no' => $transaction->reference_no,
                'callback_url' => $callbackUrl,
                'error' => $e->getMessage(),
            ]);

            $transaction->recordCallbackResult(false, 0, $e->getMessage());

            return [
                'delivered' => false,
                'status_code' => 0,
                'response_body' => $e->getMessage(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
