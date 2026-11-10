<?php

namespace App\Http\Middleware;

use App\Models\DistributorApiClient;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyDistributorApiKey
{
    public const CLIENT_ATTRIBUTE = 'distributorApiClient';

    /**
     * Distributor ditentukan dari API key, tidak pernah dari isi request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Sistem eksternal sering tidak mengirim Accept; tanpa ini error validasi menjadi redirect HTML.
        $request->headers->set('Accept', 'application/json');

        $providedKey = (string) $request->header('X-API-KEY', '');

        if ($providedKey === '') {
            return $this->reject('Header X-API-KEY wajib diisi.', Response::HTTP_UNAUTHORIZED);
        }

        $client = DistributorApiClient::with('distributor')
            ->where('key_hash', DistributorApiClient::hashKey($providedKey))
            ->first();

        $distributor = $client?->distributor;

        if (! $client || ! $client->is_active || ! $distributor?->isDistributor() || ! $distributor->is_active) {
            return $this->reject('API key tidak valid atau sudah nonaktif.', Response::HTTP_FORBIDDEN);
        }

        if (! $client->last_used_at || $client->last_used_at->lt(now()->subMinute())) {
            $client->forceFill(['last_used_at' => now()])->saveQuietly();
        }

        $request->attributes->set(self::CLIENT_ATTRIBUTE, $client);

        return $next($request);
    }

    private function reject(string $message, int $status): Response
    {
        return response()->json(['success' => false, 'message' => $message], $status);
    }
}
