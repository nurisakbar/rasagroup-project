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
        $result = $this->ekspedisiku->getProvinces();
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
}

