<?php

namespace App\Http\Controllers\Warehouse;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Warehouse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

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
     * Update the warehouse user account profile and security (password).
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:20',
            'current_password' => 'nullable|required_with:new_password|string',
            'new_password' => 'nullable|string|min:8|confirmed',
        ]);

        if ($request->filled('new_password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
            }
            $user->password = Hash::make($request->new_password);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->save();

        return back()->with('success', 'Profil akun dan password berhasil diperbarui.');
    }

    /**
     * Alias for account update.
     */
    public function updateAccount(Request $request)
    {
        return $this->update($request);
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
            'hours.*.is_open' => 'required|in:0,1',
            'hours.*.open_time' => 'nullable|date_format:H:i',
            'hours.*.close_time' => 'nullable|date_format:H:i',
        ]);

        foreach ($request->hours as $day => $data) {
            $isOpen = (bool) $data['is_open'];

            if ($isOpen) {
                if (empty($data['open_time']) || empty($data['close_time'])) {
                    return back()->withInput()->with('error', 'Jam buka dan tutup wajib diisi untuk hari yang berstatus Buka.');
                }
                if ($data['open_time'] >= $data['close_time']) {
                    return back()->withInput()->with('error', 'Jam tutup harus lebih besar dari jam buka untuk hari yang berstatus Buka.');
                }
            }

            $existing = $warehouse->operationalHours()->where('day', $day)->first();
            $openTime = !empty($data['open_time']) ? $data['open_time'] : ($existing?->open_time?->format('H:i') ?? '08:00');
            $closeTime = !empty($data['close_time']) ? $data['close_time'] : ($existing?->close_time?->format('H:i') ?? '20:00');

            $warehouse->operationalHours()->updateOrCreate(
                ['day' => $day],
                [
                    'is_open' => $isOpen,
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                ]
            );
        }

        return back()->with('success', 'Jadwal operasional Hub berhasil diperbarui.');
    }
}
