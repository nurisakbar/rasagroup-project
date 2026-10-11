<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyExternalApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $expectedKey = config('services.external_qris.api_key');
        
        // Ensure an API key is configured
        if (empty($expectedKey)) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '5000000',
                'responseMessage' => 'Server misconfiguration: EXTERNAL_QRIS_API_KEY is not configured.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $providedKey = $request->header('X-API-KEY') ?: $request->header('x-api-key');

        if (empty($providedKey)) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '4010000',
                'responseMessage' => 'Unauthorized: Header X-API-KEY is required.'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if (!hash_equals($expectedKey, (string) $providedKey)) {
            return response()->json([
                'status' => 'error',
                'responseCode' => '4030000',
                'responseMessage' => 'Forbidden: Invalid X-API-KEY provided.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}
