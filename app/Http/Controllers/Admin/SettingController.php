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
        $expeditions = Expedition::all();
        
        return view('admin.settings.index', compact('expeditions'));
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
}
