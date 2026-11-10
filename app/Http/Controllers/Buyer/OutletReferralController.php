<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OutletReferralController extends Controller
{
    /**
     * Kode sales dan link pendaftaran outlet milik distributor, beserta outlet hasil rekrutnya.
     */
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $registerLink = route('register', ['distributor' => $user->distributor_sales_code]);
        $outlets = $user->referredOutlets()->latest()->paginate(10);

        return view('buyer.outlet-referral.index', compact('user', 'registerLink', 'outlets'));
    }
}
