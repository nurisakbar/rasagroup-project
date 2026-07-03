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
        return view('buyer.profile.edit');
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
        ]);

        if (array_key_exists('occupation', $validated) && is_string($validated['occupation'])) {
            $trimmed = trim($validated['occupation']);
            $validated['occupation'] = $trimmed === '' ? null : $trimmed;
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
            'hours.*.is_open' => 'required|boolean',
            'hours.*.open_time' => 'required|date_format:H:i',
            'hours.*.close_time' => 'required|date_format:H:i|after:hours.*.open_time',
        ]);

        foreach ($request->hours as $day => $data) {
            $user->operationalHours()->updateOrCreate(
                ['day' => $day],
                [
                    'is_open' => $data['is_open'],
                    'open_time' => $data['open_time'],
                    'close_time' => $data['close_time'],
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
