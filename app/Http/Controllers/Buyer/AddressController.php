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
    /**
     * Display a listing of addresses.
     */
    public function index()
    {
        $addresses = Auth::user()->addresses()
            ->with(['province', 'regency', 'district', 'village'])
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
        $provinces = Province::orderBy('name')->get();
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
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'village_id' => ['required', 'exists:villages,id'],
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
            'village_id.required' => 'Kelurahan/Desa wajib dipilih.',
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
            'village_id' => $validated['village_id'],
            'address_detail' => $validated['address_detail'],
            'postal_code' => $validated['postal_code'],
            'notes' => $validated['notes'],
            'is_default' => $isDefault,
        ]);

        // If request expects JSON (AJAX), return JSON response
        if ($request->expectsJson() || $request->ajax()) {
            $address->load(['province', 'regency', 'district', 'village']);
            return response()->json([
                'success' => true,
                'message' => 'Alamat berhasil ditambahkan.',
                'address' => $address,
                'full_address' => $address->full_address,
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

        $provinces = Province::orderBy('name')->get();
        $regencies = Regency::where('province_id', $address->province_id)->orderBy('name')->get();
        $districts = District::where('regency_id', $address->regency_id)->orderBy('name')->get();
        $villages = Village::where('district_id', $address->district_id)->orderBy('name')->get();

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
            'province_id' => ['required', 'exists:provinces,id'],
            'regency_id' => ['required', 'exists:regencies,id'],
            'district_id' => ['required', 'exists:districts,id'],
            'village_id' => ['required', 'exists:villages,id'],
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
            'village_id' => $validated['village_id'],
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
        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($regencies);
    }

    /**
     * Get districts by regency (AJAX).
     */
    public function getDistricts(Request $request)
    {
        $districts = District::where('regency_id', $request->regency_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($districts);
    }

    /**
     * Get villages by district (AJAX).
     */
    public function getVillages(Request $request)
    {
        $villages = Village::where('district_id', $request->district_id)
            ->orderBy('name')
            ->get(['id', 'name']);
        
        return response()->json($villages);
    }
}

