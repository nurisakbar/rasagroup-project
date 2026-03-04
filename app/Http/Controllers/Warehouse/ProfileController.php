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
}
