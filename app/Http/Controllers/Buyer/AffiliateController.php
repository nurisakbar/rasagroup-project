<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Get orders that used this user's affiliate code
        $referralOrders = Order::where('affiliate_id', $user->id)
            ->where('payment_status', 'paid')
            ->with('user')
            ->latest()
            ->paginate(10);
            
        // Calculate total referral points
        $totalReferralPoints = Order::where('affiliate_id', $user->id)
            ->where('payment_status', 'paid')
            ->sum('affiliate_points');

        return view('buyer.affiliate.index', compact('user', 'referralOrders', 'totalReferralPoints'));
    }
}
