<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class OutletReferralController extends Controller
{
    /**
     * Link pendaftaran distributor (umum dan per sales, untuk outlet atau subdist), beserta outlet/subdist hasil rekrutnya.
     */
    public function index(): View
    {
        $user = Auth::user();
        abort_unless($user->isDistributor(), 403);

        $registerLink = $user->registrationLink(User::ROLE_SUBDIST);
        $salespeople = $user->distributorSales()->where('is_active', true)->whereNotNull('sales_code')->orderBy('sales_code')->get();
        $salesCodes = $salespeople->pluck('sales_code')->all();

        // Subdist: tercatat ke kode distributor. Outlet: tercatat ke kode sales milik distributor ini.
        $outlets = User::where(function ($q) use ($user, $salesCodes) {
            $q->where(fn ($s) => $s->where('role', User::ROLE_SUBDIST)->where('sales_code', $user->distributor_sales_code))
                ->orWhere(fn ($o) => $o->where('role', User::ROLE_OUTLET)->whereIn('sales_code', $salesCodes ?: ['__none__']));
        })
            ->with(['recruitedBySales:id,name,sales_code', 'salesPerson:id,name,sales_code'])
            ->latest()
            ->paginate(10);

        return view('buyer.outlet-referral.index', compact('user', 'registerLink', 'salespeople', 'outlets'));
    }
}
