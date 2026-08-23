<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use App\Models\Warehouse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    protected $ekspedisiku;

    public function __construct(\App\Services\EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }

    /**
     * Display a listing of addresses.
     */
    public function index()
    {
        $user = Auth::user();
        $addresses = $user->addresses()
            ->with(['wilayah'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $selectedShoppingAddressId = session('selected_shipping_address_id');
        $selectedShoppingAddress = $selectedShoppingAddressId
            ? $addresses->firstWhere('id', $selectedShoppingAddressId)
            : null;

        if ($selectedShoppingAddressId && !$selectedShoppingAddress) {
            session()->forget('selected_shipping_address_id');
            $selectedShoppingAddressId = null;
        }

        $autoAppliedShopping = false;

        if ($addresses->count() === 1) {
            $onlyAddress = $addresses->first();
            if (!$selectedShoppingAddress || $selectedShoppingAddress->id !== $onlyAddress->id) {
                $this->applyAddressForShopping($onlyAddress);
                $selectedShoppingAddress = $onlyAddress;
                $selectedShoppingAddressId = $onlyAddress->id;
                $autoAppliedShopping = true;
            }
        }

        return view('buyer.addresses.index', [
            'addresses' => $addresses,
            'selectedShoppingAddressId' => $selectedShoppingAddressId,
            'selectedHubName' => session('selected_hub_name'),
            'autoAppliedShopping' => $autoAppliedShopping,
        ]);
    }

    /**
     * Show the form for creating a new address.
     */
    public function create(Request $request)
    {
        $result = $this->ekspedisiku->getProvinces();
        $provinces = isset($result['data']) ? $result['data'] : [];

        $isFirstAddressFlow = Auth::user()->addresses()->count() === 0;

        if (in_array($request->query('origin'), ['checkout', 'distributor_checkout']) || $isFirstAddressFlow) {
            $origin = $request->query('origin');
            if ($isFirstAddressFlow && !$origin) {
                $origin = 'checkout'; // default fallback for first address
            }
            session(['checkout_return_origin' => $origin]);
        }

        $redirectToCheckout = session()->has('checkout_return_origin') || $request->query('origin') === 'checkout' || $request->query('origin') === 'distributor_checkout' || $isFirstAddressFlow;

        return view('buyer.addresses.create', compact('provinces', 'redirectToCheckout'));
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'store_name' => ['nullable', 'string', 'max:150', 'required_if:label,Toko'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required'],
            'regency_id' => ['required'],
            'district_id' => ['required'],
            'village_id' => ['nullable'],
            'address_detail' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
        ], [
            'label.required' => 'Label alamat wajib diisi.',
            'recipient_name.required' => 'Nama penerima wajib diisi.',
            'phone.required' => 'Nomor HP wajib diisi.',
            'province_id.required' => 'Provinsi wajib dipilih.',
            'regency_id.required' => 'Kabupaten/Kota wajib dipilih.',
            'district_id.required' => 'Kecamatan wajib dipilih.',
            'address_detail.required' => 'Alamat lengkap wajib diisi.',
        ]);

        $user = Auth::user();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default')) {
            $user->addresses()->update(['is_default' => false]);
        }

        // If this is the first address, make it default
        $isDefault = $request->boolean('is_default') || $user->addresses()->count() === 0;

        $address = $user->addresses()->create([
            'label' => $validated['label'],
            'store_name' => $validated['store_name'] ?? null,
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'village_id' => $request->village_id,
            'address_detail' => $validated['address_detail'],
            'postal_code' => $validated['postal_code'],
            'notes' => $validated['notes'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $isDefault,
        ]);

        // If request expects JSON (AJAX), return JSON response
        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan.',
                'address' => $address,
            ]);
        }

        $checkoutOrigin = session()->pull('checkout_return_origin');
        
        $redirectToCheckout = $request->has('redirect_to_checkout')
            || $checkoutOrigin
            || $user->addresses()->count() === 1;

        if ($redirectToCheckout) {
            $this->applyAddressForShopping($address);

            $routeName = 'checkout.index';
            if ($checkoutOrigin === 'distributor_checkout' || $request->input('origin') === 'distributor_checkout') {
                $routeName = 'distributor.orders.checkout';
            }

            return redirect()->route($routeName, ['address_id' => $address->id])
                ->with('success', 'Alamat berhasil ditambahkan. Silakan lanjutkan checkout.');
        }

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Alamat berhasil ditambahkan.');
    }

    /**
     * Show the form for editing an address.
     */
    public function edit(Address $address)
    {
        // Ensure user can only edit their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $provinceRes = $this->ekspedisiku->getProvinces();
        $provinces = isset($provinceRes['data']) ? $provinceRes['data'] : [];
        
        $regencyRes = $this->ekspedisiku->getRegencies($address->province_id);
        $regencies = isset($regencyRes['data']) ? $regencyRes['data'] : [];
        
        $districtRes = $this->ekspedisiku->getDistricts($address->regency_id);
        $districts = isset($districtRes['data']) ? $districtRes['data'] : [];

        $villageRes = $this->ekspedisiku->getVillages($address->district_id);
        $villages = isset($villageRes['data']) ? $villageRes['data'] : [];

        return view('buyer.addresses.edit', compact('address', 'provinces', 'regencies', 'districts', 'villages'));
    }

    /**
     * Update the specified address.
     */
    public function update(Request $request, Address $address)
    {
        // Ensure user can only update their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'store_name' => ['nullable', 'string', 'max:150', 'required_if:label,Toko'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required'],
            'regency_id' => ['required'],
            'district_id' => ['required'],
            'village_id' => ['nullable'],
            'address_detail' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default') && !$address->is_default) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $validated['label'],
            'store_name' => $validated['store_name'] ?? null,
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'village_id' => $request->village_id,
            'address_detail' => $validated['address_detail'],
            'postal_code' => $validated['postal_code'],
            'notes' => $validated['notes'],
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'is_default' => $request->boolean('is_default'),
        ]);

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Alamat berhasil diperbarui.');
    }

    /**
     * Remove the specified address.
     */
    public function destroy(Address $address)
    {
        // Ensure user can only delete their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        $wasDefault = $address->is_default;
        $wasSelectedForShopping = session('selected_shipping_address_id') === $address->id;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = Auth::user()->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        if ($wasSelectedForShopping) {
            $this->clearShoppingHubSession();
            $this->syncShoppingAddressAfterChange();
        }

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Alamat berhasil dihapus.');
    }

    /**
     * Set an address as default.
     */
    public function setDefault(Address $address)
    {
        // Ensure user can only modify their own addresses
        if ($address->user_id !== Auth::id()) {
            abort(403);
        }

        // Unset all other defaults
        Auth::user()->addresses()->update(['is_default' => false]);
        
        // Set this as default
        $address->update(['is_default' => true]);

        return redirect()->route('buyer.addresses.index')
            ->with('success', 'Alamat utama berhasil diubah.');
    }

    /**
     * Get regencies by province (AJAX).
     */
    public function getRegencies(Request $request)
    {
        $result = $this->ekspedisiku->getRegencies($request->province_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Get districts by regency (AJAX).
     */
    public function getDistricts(Request $request)
    {
        $result = $this->ekspedisiku->getDistricts($request->regency_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    public function getVillages(Request $request)
    {
        $result = $this->ekspedisiku->getVillages($request->district_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Match location components to WilayahAdministratif.
     */
    public function matchLocation(Request $request)
    {
        $province = $request->input('province', '');
        $regency = $request->input('regency', '');
        $district = $request->input('district', '');
        $village = $request->input('village', '');

        $districtClean = trim(str_replace(['Kecamatan ', 'District'], '', $district));
        $villageClean = trim(str_replace(['Kelurahan ', 'Desa ', 'Village'], '', $village));
        
        $regencyClean = trim(str_replace(['Kota ', 'Kabupaten ', 'Regency', 'City'], '', $regency));
        // Remove directional words to get core name (e.g. "West Bandung" -> "Bandung")
        $regencyCore = trim(str_replace(['West', 'East', 'North', 'South', 'Central', 'Barat', 'Timur', 'Utara', 'Selatan', 'Pusat'], '', $regencyClean));

        $matched = null;

        // Strategy 1: Village and District (Most accurate, avoids translation issues)
        if ($villageClean && $districtClean) {
            $matched = \App\Models\WilayahAdministratif::where('district_name', 'like', '%' . $districtClean . '%')
                ->where('village_name', 'like', '%' . $villageClean . '%')
                ->first();
        }

        // Strategy 2: District and Core Regency
        if (!$matched && $districtClean && $regencyCore) {
            $matched = \App\Models\WilayahAdministratif::where('regency_name', 'like', '%' . $regencyCore . '%')
                ->where('district_name', 'like', '%' . $districtClean . '%')
                ->first();
        }

        // Strategy 3: Core Regency and Province Core
        if (!$matched && $regencyCore) {
            $provinceCore = trim(str_replace(['West', 'East', 'North', 'South', 'Central', 'Java', 'Kalimantan', 'Sumatera', 'Sulawesi'], '', $province));
            $query = \App\Models\WilayahAdministratif::where('regency_name', 'like', '%' . $regencyCore . '%');
            if ($provinceCore) {
                $query->where('province_name', 'like', '%' . $provinceCore . '%');
            }
            $matched = $query->first();
        }

        if ($matched) {
            return response()->json([
                'success' => true,
                'data' => [
                    'province_id' => $matched->province_id,
                    'province_name' => $matched->province_name,
                    'regency_id' => $matched->regency_id,
                    'regency_name' => $matched->regency_name,
                    'district_id' => $matched->district_id,
                    'district_name' => $matched->district_name,
                    'village_id' => $matched->village_id,
                    'village_name' => $matched->village_name,
                ]
            ]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Select address for shopping session (AJAX).
     */
    public function selectForShopping(Request $request)
    {
        $request->validate([
            'address_id' => 'required|string',
        ]);

        $address = Address::where('id', $request->address_id)
            ->where('user_id', Auth::id())
            ->firstOrFail();

        $hub = $this->applyAddressForShopping($address);

        if (!$hub) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada hub yang aktif saat ini.'
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat pengiriman dan hub terdekat berhasil dipilih.',
            'hub' => $hub->name,
            'address_id' => $address->id,
        ]);
    }

    /**
     * Helper to apply an address to the current shopping session and find the nearest hub.
     */
    public function applyAddressForShopping(Address $address)
    {
        session(['selected_shipping_address_id' => $address->id]);

        $excludeHubId = Auth::user()?->distributorShoppingExcludedWarehouseId();
        $hub = Warehouse::findBestHubForAddress($address, $excludeHubId);

        if ($hub) {
            session([
                'selected_hub_id' => $hub->id,
                'selected_hub_name' => $hub->name,
                'selected_hub_slug' => $hub->slug,
            ]);

            cookie()->queue('selected_hub_id', $hub->id, 60 * 24 * 30);
            cookie()->queue('selected_hub_name', $hub->name, 60 * 24 * 30);
            cookie()->queue('selected_hub_slug', $hub->slug, 60 * 24 * 30);
        }

        return $hub;
    }

    private function clearShoppingHubSession(): void
    {
        session()->forget([
            'selected_shipping_address_id',
            'selected_hub_id',
            'selected_hub_name',
            'selected_hub_slug',
        ]);
    }

    private function syncShoppingAddressAfterChange(): void
    {
        $addresses = Auth::user()->addresses()->get();

        if ($addresses->count() === 1) {
            $this->applyAddressForShopping($addresses->first());
        }
    }
}

