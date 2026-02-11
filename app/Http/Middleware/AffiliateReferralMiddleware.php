<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AffiliateReferralMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $referralCode = $request->query('ref');
            
            // Validate if user exists with this code
            $affiliate = \App\Models\User::where('referral_code', $referralCode)->first();
            
            if ($affiliate) {
                session(['affiliate_id' => $affiliate->id]);
            }
        }

        return $next($request);
    }
}
