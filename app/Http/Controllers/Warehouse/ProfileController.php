<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;

class ProfileController extends Controller
{
    /**
     * Show the edit form for warehouse profile and hub info.
     */
    public function edit()
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return back()->with('error', 'Anda tidak terasosiasi dengan Warehouse/Hub manapun.');
        }

        return view('warehouse.profile.edit', compact('user', 'warehouse'));
    }

    /**
     * Update the warehouse/hub information.
     */
    public function update(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return back()->with('error', 'Anda tidak memiliki otoritas untuk memperbarui Hub.');
        }

        $request->validate([
            'hub_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'required|string',
            'description' => 'nullable|string',
        ]);

        $warehouse->update([
            'name' => $request->hub_name,
            'phone' => $request->phone,
            'address' => $request->address,
            'description' => $request->description,
        ]);

        return back()->with('success', 'Informasi Hub berhasil diperbarui.');
    }

    public function operationalHours()
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return back()->with('error', 'Anda tidak terasosiasi dengan Warehouse/Hub manapun.');
        }

        if ($warehouse->operationalHours()->count() === 0) {
            $warehouse->generateDefaultOperationalHours();
        }

        $operationalHours = $warehouse->operationalHours;

        return view('warehouse.profile.operational-hours', compact('warehouse', 'operationalHours'));
    }

    public function updateOperationalHours(Request $request)
    {
        $user = auth()->user();
        $warehouse = $user->warehouse;

        if (!$warehouse) {
            return back()->with('error', 'Anda tidak memiliki otoritas untuk memperbarui Hub.');
        }

        $request->validate([
            'hours' => 'required|array|size:7',
            'hours.*.is_open' => 'required|boolean',
            'hours.*.open_time' => 'required|date_format:H:i',
            'hours.*.close_time' => 'required|date_format:H:i|after:hours.*.open_time',
        ]);

        foreach ($request->hours as $day => $data) {
            $warehouse->operationalHours()->updateOrCreate(
                ['day' => $day],
                [
                    'is_open' => $data['is_open'],
                    'open_time' => $data['open_time'],
                    'close_time' => $data['close_time'],
                ]
            );
        }

        return back()->with('success', 'Jadwal operasional Hub berhasil diperbarui.');
    }
}
