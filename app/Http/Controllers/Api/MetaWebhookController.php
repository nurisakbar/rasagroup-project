<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MetaWebhookController extends Controller
{
    /**
     * Verifikasi Webhook dari Meta (GET).
     */
    public function verify(Request $request)
    {
        // Token verifikasi yang diatur di Meta App Dashboard
        $verifyToken = env('META_WEBHOOK_VERIFY_TOKEN', 'my_custom_verify_token');

        $mode = $request->query('hub_mode');
        $token = $request->query('hub_verify_token');
        $challenge = $request->query('hub_challenge');

        // Cek apakah ada mode dan token yang dikirim
        if ($mode && $token) {
            // Cek apakah mode dan token sesuai
            if ($mode === 'subscribe' && $token === $verifyToken) {
                Log::info('Meta Webhook Verified Successfully.');
                return response((string) $challenge, 200)->header('Content-Type', 'text/plain');
            } else {
                Log::warning('Meta Webhook Verification Failed: Token mismatch.', ['received' => $token, 'expected' => $verifyToken]);
                return response('Forbidden', 403);
            }
        }

        return response('Bad Request', 400);
    }

    /**
     * Menerima event Webhook dari Meta (POST).
     */
    public function handle(Request $request)
    {
        $body = $request->all();

        Log::info('Meta Webhook Event Received:', $body);

        // Pastikan event memiliki object (misalnya 'whatsapp_business_account' atau 'page')
        if (isset($body['object'])) {
            // Lakukan pemrosesan pesan/event di sini...
            
            // Meta membutuhkan response 200 OK untuk mengetahui webhook berhasil diterima
            return response('EVENT_RECEIVED', 200);
        }

        return response('Not Found', 404);
    }
}
