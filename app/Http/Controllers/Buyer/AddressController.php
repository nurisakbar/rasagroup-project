<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AddressController extends Controller
{
    protected $rajaOngkir;

    public function __construct(\App\Services\RajaOngkirService $rajaOngkir)
    {
        $this->rajaOngkir = $rajaOngkir;
    }

    /**
     * Display a listing of addresses.
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district'])
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        return view('buyer.addresses.index', compact('addresses'));
    }

    /**
     * Show the form for creating a new address.
     */
    public function create()
    {
        $result = $this->rajaOngkir->getProvinces();
        $provinces = isset($result['data']) ? $result['data'] : [];
        return view('buyer.addresses.create', compact('provinces'));
    }

    /**
     * Store a newly created address.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'label' => ['required', 'string', 'max:50'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'exists:raja_ongkir_provinces,id'],
            'regency_id' => ['required', 'exists:raja_ongkir_cities,id'],
            'district_id' => ['required', 'exists:raja_ongkir_districts,id'],
            'address_detail' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
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
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'address_detail' => $validated['address_detail'],
            'postal_code' => $validated['postal_code'],
            'notes' => $validated['notes'],
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

        $provinceRes = $this->rajaOngkir->getProvinces();
        $provinces = isset($provinceRes['data']) ? $provinceRes['data'] : [];
        
        $regencyRes = $this->rajaOngkir->getCities($address->province_id);
        $regencies = isset($regencyRes['data']) ? $regencyRes['data'] : [];
        
        $districtRes = $this->rajaOngkir->getDistricts($address->regency_id);
        $districts = isset($districtRes['data']) ? $districtRes['data'] : [];

        // Fetch villages from local table by mapping district name
        $villages = [];
        // Village logic removed/commented out previously caused errors, explicitly removed now.

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
            'recipient_name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:20'],
            'province_id' => ['required', 'exists:raja_ongkir_provinces,id'],
            'regency_id' => ['required', 'exists:raja_ongkir_cities,id'],
            'district_id' => ['required', 'exists:raja_ongkir_districts,id'],
            'address_detail' => ['required', 'string'],
            'postal_code' => ['nullable', 'string', 'max:10'],
            'notes' => ['nullable', 'string', 'max:500'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        $user = Auth::user();

        // If this is set as default, unset other defaults
        if ($request->boolean('is_default') && !$address->is_default) {
            $user->addresses()->where('id', '!=', $address->id)->update(['is_default' => false]);
        }

        $address->update([
            'label' => $validated['label'],
            'recipient_name' => $validated['recipient_name'],
            'phone' => $validated['phone'],
            'province_id' => $validated['province_id'],
            'regency_id' => $validated['regency_id'],
            'district_id' => $validated['district_id'],
            'address_detail' => $validated['address_detail'],
            'postal_code' => $validated['postal_code'],
            'notes' => $validated['notes'],
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
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = Auth::user()->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
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
        $result = $this->rajaOngkir->getCities($request->province_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    /**
     * Get districts by regency (AJAX).
     */
    public function getDistricts(Request $request)
    {
        $result = $this->rajaOngkir->getDistricts($request->regency_id);
        return response()->json(isset($result['data']) ? $result['data'] : []);
    }

    public function getVillages(Request $request)
    {
        $districtId = $request->district_id;
        if (!$districtId) {
            return response()->json([]);
        }

        // 1. Get district name from RajaOngkirDistrict
        $roDistrict = \App\Models\RajaOngkirDistrict::find($districtId);
        if (!$roDistrict) {
            return response()->json([]);
        }

        // 2. Find matching local district by name
        // Use where like for flexibility, or exact name
        $localDistrict = \App\Models\District::where('name', $roDistrict->name)->first();
        
        // If not found exactly, try case-insensitive or partial
        if (!$localDistrict) {
            $localDistrict = \App\Models\District::where('name', 'like', '%' . $roDistrict->name . '%')->first();
        }

        if ($localDistrict) {
             // Village logic removed
             return response()->json([]);
        }

        return response()->json([]);
    }
}

