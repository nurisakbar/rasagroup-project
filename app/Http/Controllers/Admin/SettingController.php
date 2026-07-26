<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Expedition;
use App\Models\Setting;
use App\Models\Warehouse;
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
        
        $hubs = Warehouse::where('is_active', true)->get();
        $payment_confirmation_email = Setting::get('payment_confirmation_email');
        $distributor_default_hub = Setting::get('distributor_default_hub');

        return view('admin.settings.index', compact('expeditions', 'hubs', 'payment_confirmation_email', 'distributor_default_hub'));
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

    public function updateGeneral(Request $request)
    {
        $request->validate([
            'payment_confirmation_email' => 'nullable|email',
            'distributor_default_hub' => 'nullable|exists:warehouses,id',
        ]);

        Setting::set('payment_confirmation_email', $request->payment_confirmation_email, 'Email untuk menerima konfirmasi pembayaran');
        Setting::set('distributor_default_hub', $request->distributor_default_hub, 'Hub default untuk pengiriman order distributor');

        return back()->with('success', 'Pengaturan umum berhasil diperbarui.');
    }
}
