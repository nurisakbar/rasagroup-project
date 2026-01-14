<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Address;
use App\Models\District;
use App\Models\Province;
use App\Models\Regency;
use App\Models\Village;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class AddressApiController extends Controller
{
    /**
     * Get list of user's addresses
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        $addresses = Address::with(['province', 'regency', 'district', 'village'])
            ->where('user_id', $user->id)
            ->orderByDesc('is_default')
            ->orderByDesc('created_at')
            ->get();

        $data = $addresses->map(function ($address) {
            return [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address_detail' => $address->address_detail,
                'postal_code' => $address->postal_code,
                'notes' => $address->notes,
                'is_default' => $address->is_default,
                'province' => $address->province ? [
                    'id' => $address->province->id,
                    'name' => $address->province->name,
                ] : null,
                'regency' => $address->regency ? [
                    'id' => $address->regency->id,
                    'name' => $address->regency->name,
                ] : null,
                'district' => $address->district ? [
                    'id' => $address->district->id,
                    'name' => $address->district->name,
                ] : null,
                'village' => $address->village ? [
                    'id' => $address->village->id,
                    'name' => $address->village->name,
                ] : null,
                'full_address' => $address->full_address,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    /**
     * Create new address
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|exists:provinces,id',
            'regency_id' => 'required|exists:regencies,id',
            'district_id' => 'required|exists:districts,id',
            'village_id' => 'required|exists:villages,id',
            'address_detail' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
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

        $address->load(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil ditambahkan.',
            'data' => [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address_detail' => $address->address_detail,
                'postal_code' => $address->postal_code,
                'notes' => $address->notes,
                'is_default' => $address->is_default,
                'province' => $address->province ? [
                    'id' => $address->province->id,
                    'name' => $address->province->name,
                ] : null,
                'regency' => $address->regency ? [
                    'id' => $address->regency->id,
                    'name' => $address->regency->name,
                ] : null,
                'district' => $address->district ? [
                    'id' => $address->district->id,
                    'name' => $address->district->name,
                ] : null,
                'village' => $address->village ? [
                    'id' => $address->village->id,
                    'name' => $address->village->name,
                ] : null,
                'full_address' => $address->full_address,
            ],
        ], 201);
    }

    /**
     * Update address
     * 
     * @param Request $request
     * @param string $id
     * @return JsonResponse
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $validated = $request->validate([
            'label' => 'required|string|max:50',
            'recipient_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
            'province_id' => 'required|exists:provinces,id',
            'regency_id' => 'required|exists:regencies,id',
            'district_id' => 'required|exists:districts,id',
            'village_id' => 'required|exists:villages,id',
            'address_detail' => 'required|string',
            'postal_code' => 'nullable|string|max:10',
            'notes' => 'nullable|string|max:500',
            'is_default' => 'nullable|boolean',
        ]);

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

        $address->load(['province', 'regency', 'district', 'village']);

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil diperbarui.',
            'data' => [
                'id' => $address->id,
                'label' => $address->label,
                'recipient_name' => $address->recipient_name,
                'phone' => $address->phone,
                'address_detail' => $address->address_detail,
                'postal_code' => $address->postal_code,
                'notes' => $address->notes,
                'is_default' => $address->is_default,
                'province' => $address->province ? [
                    'id' => $address->province->id,
                    'name' => $address->province->name,
                ] : null,
                'regency' => $address->regency ? [
                    'id' => $address->regency->id,
                    'name' => $address->regency->name,
                ] : null,
                'district' => $address->district ? [
                    'id' => $address->district->id,
                    'name' => $address->district->name,
                ] : null,
                'village' => $address->village ? [
                    'id' => $address->village->id,
                    'name' => $address->village->name,
                ] : null,
                'full_address' => $address->full_address,
            ],
        ]);
    }

    /**
     * Delete address
     * 
     * @param string $id
     * @return JsonResponse
     */
    public function destroy(string $id): JsonResponse
    {
        $user = Auth::user();
        $address = Address::where('user_id', $user->id)
            ->where('id', $id)
            ->firstOrFail();

        $wasDefault = $address->is_default;
        $address->delete();

        // If deleted address was default, set another as default
        if ($wasDefault) {
            $newDefault = $user->addresses()->first();
            if ($newDefault) {
                $newDefault->update(['is_default' => true]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Alamat berhasil dihapus.',
        ]);
    }

    /**
     * Get provinces list
     * 
     * @return JsonResponse
     */
    public function getProvinces(): JsonResponse
    {
        $provinces = Province::orderBy('name')->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $provinces,
        ]);
    }

    /**
     * Get regencies by province
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getRegencies(Request $request): JsonResponse
    {
        $request->validate([
            'province_id' => 'required|exists:provinces,id',
        ]);

        $regencies = Regency::where('province_id', $request->province_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $regencies,
        ]);
    }

    /**
     * Get districts by regency
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getDistricts(Request $request): JsonResponse
    {
        $request->validate([
            'regency_id' => 'required|exists:regencies,id',
        ]);

        $districts = District::where('regency_id', $request->regency_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $districts,
        ]);
    }

    /**
     * Get villages by district
     * 
     * @param Request $request
     * @return JsonResponse
     */
    public function getVillages(Request $request): JsonResponse
    {
        $request->validate([
            'district_id' => 'required|exists:districts,id',
        ]);

        $villages = Village::where('district_id', $request->district_id)
            ->orderBy('name')
            ->get(['id', 'name']);

        return response()->json([
            'success' => true,
            'data' => $villages,
        ]);
    }
}

