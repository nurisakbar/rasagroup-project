<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $driippreneurPointRate = Setting::get('driippreneur_point_rate', 1000);
        $expeditions = Expedition::all();
        
        return view('admin.settings.index', compact('driippreneurPointRate', 'expeditions'));
    }

    public function updateExpeditions(Request $request)
    {
        // Set all to inactive initially
        Expedition::query()->update(['is_active' => false]);

        // Set selected to active
        if ($request->has('expeditions')) {
            Expedition::whereIn('id', $request->expeditions)->update(['is_active' => true]);
        }

        return back()->with('success', 'Pengaturan ekspedisi berhasil diperbarui.');
    }

    public function updateDriippreneurPointRate(Request $request)
    {
        $request->validate([
            'point_rate' => 'required|integer|min:0',
        ], [
            'point_rate.required' => 'Point rate wajib diisi.',
            'point_rate.integer' => 'Point rate harus berupa angka.',
            'point_rate.min' => 'Point rate minimal 0.',
        ]);

        Setting::set(
            'driippreneur_point_rate',
            $request->point_rate,
            'Point yang diberikan per item untuk DRiiPPreneur saat belanja'
        );

        return back()->with('success', 'Point rate DRiiPPreneur berhasil diperbarui.');
    }
}
