<?php

namespace App\Http\Controllers;

use App\Models\Promo;
use Illuminate\Http\Request;

class PromoController extends Controller
{
    public function index()
    {
        $query = Promo::query()
            ->where('awal', '<=', now())
            ->where('akhir', '>=', now());

        $user = auth()->user();
        if (!$user) {
            $query->whereJsonContains('target_audience', 'umum');
        } else {
            $allowedAudiences = ['umum'];
            if ($user->isDistributor()) {
                $allowedAudiences[] = 'distributor';
            }
            if ($user->isDriippreneur() || $user->hasAffiliateBankDetails()) {
                $allowedAudiences[] = 'affiliator';
            }
            // Super admin or agent can see all
            if ($user->isSuperAdmin() || $user->isAgent()) {
                $allowedAudiences = ['umum', 'affiliator', 'distributor'];
            }
            $query->where(function($q) use ($allowedAudiences) {
                foreach ($allowedAudiences as $aud) {
                    $q->orWhereJsonContains('target_audience', $aud);
                }
            });
        }

        $promos = $query->orderBy('akhir')->get();

        return view('themes.nest.promo.index', compact('promos'));
    }

    public function show($slug)
    {
        $promo = Promo::where('slug', $slug)->firstOrFail();

        return view('themes.nest.promo.show', compact('promo'));
    }
}
