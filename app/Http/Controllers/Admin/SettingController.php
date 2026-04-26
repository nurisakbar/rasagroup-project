<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    protected $ekspedisiku;

    public function __construct(\App\Services\EkspedisiKuService $ekspedisiku)
    {
        $this->ekspedisiku = $ekspedisiku;
    }

    public function index()
    {
        // Fetch couriers from EkspedisiKu API
        $courierRes = $this->ekspedisiku->getCouriers();
        $apiCourierCodes = [];
        
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
                
                \App\Models\Expedition::updateOrCreate(
                    ['code' => $courier['id']], // Use id (e.g. lion_parcel) as code
                    [
                        'name' => $courier['name'],
                        'logo' => $courier['image'] ?? null,
                    ]
                );
            }
        }

        // Only show expeditions that are in the API response
        $expeditions = Expedition::whereIn('code', $apiCourierCodes)->get();
        
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
