<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function show()
    {
        return view('buyer.profile.show');
    }

    public function edit()
    {
        $salesList = \App\Models\User::where('role', 'sales')->get();
        return view('buyer.profile.edit', compact('salesList'));
    }

    public function update(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->getKey()),
            ],
            'phone' => 'nullable|string|max:20',
            'date_of_birth' => 'nullable|date|before_or_equal:today',
            'occupation' => 'nullable|string|max:120',
            'sales_code' => 'nullable|string|max:255',
        ]);

        if (array_key_exists('occupation', $validated) && is_string($validated['occupation'])) {
            $trimmed = trim($validated['occupation']);
            $validated['occupation'] = $trimmed === '' ? null : $trimmed;
        }

        if (array_key_exists('sales_code', $validated)) {
            $trimmed = is_string($validated['sales_code']) ? trim($validated['sales_code']) : null;
            $validated['sales_code'] = ($trimmed === '' || $trimmed === null) ? null : $trimmed;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return redirect()->route('buyer.profile')->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if (!Hash::check($request->current_password, Auth::user()->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        Auth::user()->update([
            'password' => Hash::make($request->password),
        ]);

        return redirect()->route('buyer.profile')->with('success', 'Password berhasil diubah.');
    }

    public function operationalHours()
    {
        $user = Auth::user();
        
        if (!$user->isDistributor()) {
            abort(403, 'Akses khusus Distributor.');
        }

        if ($user->operationalHours()->count() === 0) {
            $user->generateDefaultOperationalHours();
        }

        $operationalHours = $user->operationalHours;

        return view('buyer.profile.operational-hours', compact('operationalHours'));
    }

    public function updateOperationalHours(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->isDistributor()) {
            abort(403, 'Akses khusus Distributor.');
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

            $existing = $user->operationalHours()->where('day', $day)->first();
            $openTime = !empty($data['open_time']) ? $data['open_time'] : ($existing?->open_time?->format('H:i') ?? '08:00');
            $closeTime = !empty($data['close_time']) ? $data['close_time'] : ($existing?->close_time?->format('H:i') ?? '20:00');

            $user->operationalHours()->updateOrCreate(
                ['day' => $day],
                [
                    'is_open' => $isOpen,
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                ]
            );
        }

        return back()->with('success', 'Jadwal operasional berhasil diperbarui.');
    }

    public function mobileMenu()
    {
        return view('buyer.profile.mobile-menu');
    }

    public function editPassword()
    {
        return view('buyer.profile.password-edit');
    }
}
