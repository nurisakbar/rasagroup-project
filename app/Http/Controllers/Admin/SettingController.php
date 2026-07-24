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
        $apiCourierCodes = ['self_pickup'];

        \App\Models\Expedition::updateOrCreate(
            ['code' => 'self_pickup'],
            [
                'name' => 'Ambil Sendiri (Self Pickup)',
            ]
        );
        
        if (isset($courierRes['data']) && is_array($courierRes['data'])) {
            foreach ($courierRes['data'] as $courier) {
                $apiCourierCodes[] = $courier['id'];
                
                $expedition = \App\Models\Expedition::firstOrNew(['code' => $courier['id']]);
                $expedition->name = $courier['name'];
                if (empty($expedition->logo) && !empty($courier['image'])) {
                    $expedition->logo = $courier['image'];
                }
                $expedition->save();
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
