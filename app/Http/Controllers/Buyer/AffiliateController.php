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
        
        // Ensure user has referral code
        if (empty($user->referral_code)) {
            $user->update([
                'referral_code' => \App\Models\User::generateUniqueReferralCode($user->name)
            ]);
        }
        
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

    /**
     * Simpan data rekening untuk penarikan poin afiliasi (bukan distributor).
     */
    public function updateBank(Request $request)
    {
        $user = Auth::user();
        if ($user->isDistributor()) {
            return redirect()->route('buyer.affiliate.index')->with('error', 'Akun distributor tidak mengelola rekening di halaman ini.');
        }

        $validated = $request->validate([
            'bank_name' => 'nullable|string|max:100',
            'bank_account_number' => 'nullable|string|max:50',
            'bank_account_name' => 'nullable|string|max:100',
        ]);

        $user->update($validated);

        return redirect()->route('buyer.affiliate.index')->with('success', 'Data rekening berhasil diperbarui.');
    }
}
