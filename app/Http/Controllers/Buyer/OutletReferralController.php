<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\OutletCategoryDiscount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class OutletReferralController extends Controller
{
    /**
     * Kode sales dan link pendaftaran outlet milik distributor, beserta outlet hasil rekrutnya.
     */
    public function index(): View
    {
        $user = $this->distributor();

        $registerLink = route('register', ['distributor' => $user->distributor_sales_code]);
        $outlets = $user->referredOutlets()
            ->withCount(['outletCategoryDiscounts as discount_count' => fn ($q) => $q->where('distributor_id', $user->id)])
            ->latest()
            ->paginate(10);
        $discountMax = $user->outletDiscountMax();

        return view('buyer.outlet-referral.index', compact('user', 'registerLink', 'outlets', 'discountMax'));
    }

    /**
     * Form diskon per brand & kategori untuk satu outlet rekrutan.
     */
    public function editDiscounts(User $outlet): View
    {
        $user = $this->distributor();
        $this->ensureReferredOutlet($user, $outlet);

        $brands = Brand::with(['categories' => fn ($q) => $q->orderBy('name')])->active()->orderBy('name')->get();
        $discounts = $outlet->outletCategoryDiscounts()
            ->where('distributor_id', $user->id)
            ->get()
            ->mapWithKeys(fn (OutletCategoryDiscount $row) => [$row->brand_id . '|' . $row->category_id => (float) $row->discount_percentage]);
        $discountMax = $user->outletDiscountMax();

        return view('buyer.outlet-referral.discounts', compact('user', 'outlet', 'brands', 'discounts', 'discountMax'));
    }

    public function updateDiscounts(Request $request, User $outlet): RedirectResponse
    {
        $user = $this->distributor();
        $this->ensureReferredOutlet($user, $outlet);

        $max = $user->outletDiscountMax();
        if ($max <= 0) {
            return back()->with('error', 'Admin belum mengizinkan Anda memberi diskon ke outlet.');
        }

        $validated = $request->validate([
            'discounts' => ['nullable', 'array'],
            'discounts.*' => ['nullable', 'array'],
            'discounts.*.*' => ['nullable', 'numeric', 'min:0', 'max:' . $max],
        ], [
            'discounts.*.*.max' => 'Diskon maksimal yang boleh Anda berikan adalah ' . rtrim(rtrim(number_format($max, 2, ',', '.'), '0'), ',') . '%.',
            'discounts.*.*.numeric' => 'Diskon harus berupa angka.',
            'discounts.*.*.min' => 'Diskon tidak boleh negatif.',
        ]);

        // Hanya pasangan brand-kategori yang memang terdaftar yang disimpan.
        $validPairs = DB::table('brand_category')
            ->get(['brand_id', 'category_id'])
            ->map(fn ($row) => $row->brand_id . '|' . $row->category_id)
            ->flip();

        DB::transaction(function () use ($validated, $validPairs, $user, $outlet) {
            foreach ($validated['discounts'] ?? [] as $brandId => $categories) {
                foreach ($categories ?? [] as $categoryId => $percentage) {
                    if (! isset($validPairs[$brandId . '|' . $categoryId])) {
                        continue;
                    }

                    $keys = ['outlet_id' => $outlet->id, 'brand_id' => $brandId, 'category_id' => $categoryId];
                    if ((float) $percentage > 0) {
                        OutletCategoryDiscount::updateOrCreate($keys, [
                            'distributor_id' => $user->id,
                            'discount_percentage' => round((float) $percentage, 2),
                        ]);
                    } else {
                        OutletCategoryDiscount::where($keys)->delete();
                    }
                }
            }
        });

        return redirect()->route('buyer.outlet-referral.discounts.edit', $outlet)
            ->with('success', 'Diskon untuk ' . $outlet->name . ' berhasil disimpan.');
    }

    private function distributor(): User
    {
        $user = Auth::user();
        abort_unless($user && $user->isDistributor(), 403);

        return $user;
    }

    private function ensureReferredOutlet(User $distributor, User $outlet): void
    {
        abort_unless(
            $outlet->role === User::ROLE_OUTLET && $outlet->sales_code === $distributor->distributor_sales_code,
            404
        );
    }
}
