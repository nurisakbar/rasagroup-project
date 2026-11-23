<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyGmvApiKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = (string) config('services.rasaconnect.api_key');

        if ($expectedKey === '') {
            return response()->json(['message' => 'RASACONNECT_API_KEY belum dikonfigurasi di server.'], Response::HTTP_SERVICE_UNAVAILABLE);
        }

        $providedKey = (string) $request->header('X-API-KEY', '');

        if ($providedKey === '') {
            return response()->json(['message' => 'Header X-API-KEY wajib diisi.'], Response::HTTP_UNAUTHORIZED);
        }

        if (! hash_equals($expectedKey, $providedKey)) {
            return response()->json(['message' => 'X-API-KEY tidak valid.'], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
