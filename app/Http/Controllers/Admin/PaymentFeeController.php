<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;

class PaymentFeeController extends Controller
{
    public function index()
    {
        // For finance and super_admin only
        if (!in_array(auth()->user()->role, ['super_admin', 'finance'])) {
            abort(403);
        }

        $faspayFees = [
            'fee_faspay_bca_va' => Setting::get('fee_faspay_bca_va', 0),
            'fee_faspay_mandiri_va' => Setting::get('fee_faspay_mandiri_va', 0),
            'fee_faspay_bri_va' => Setting::get('fee_faspay_bri_va', 0),
            'fee_faspay_bni_va' => Setting::get('fee_faspay_bni_va', 0),
            'fee_faspay_cimb_va' => Setting::get('fee_faspay_cimb_va', 0),
            'fee_faspay_permata_va' => Setting::get('fee_faspay_permata_va', 0),
            'fee_faspay_sinarmas_va' => Setting::get('fee_faspay_sinarmas_va', 0),
            'fee_faspay_maybank_va' => Setting::get('fee_faspay_maybank_va', 0),
            'fee_faspay_danamon_va' => Setting::get('fee_faspay_danamon_va', 0),
            'fee_faspay_bsi_va' => Setting::get('fee_faspay_bsi_va', 0),
            'fee_faspay_qris' => Setting::get('fee_faspay_qris', 0),
        ];

        return view('admin.payment_fees.index', compact('faspayFees'));
    }

    public function update(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'finance'])) {
            abort(403);
        }

        $request->validate([
            'fee_faspay_bca_va' => 'nullable|numeric|min:0',
            'fee_faspay_mandiri_va' => 'nullable|numeric|min:0',
            'fee_faspay_bri_va' => 'nullable|numeric|min:0',
            'fee_faspay_bni_va' => 'nullable|numeric|min:0',
            'fee_faspay_cimb_va' => 'nullable|numeric|min:0',
            'fee_faspay_permata_va' => 'nullable|numeric|min:0',
            'fee_faspay_sinarmas_va' => 'nullable|numeric|min:0',
            'fee_faspay_maybank_va' => 'nullable|numeric|min:0',
            'fee_faspay_danamon_va' => 'nullable|numeric|min:0',
            'fee_faspay_bsi_va' => 'nullable|numeric|min:0',
            'fee_faspay_qris' => 'nullable|numeric|min:0',
        ]);

        $faspayChannels = ['bca_va', 'mandiri_va', 'bri_va', 'bni_va', 'cimb_va', 'permata_va', 'sinarmas_va', 'maybank_va', 'danamon_va', 'bsi_va', 'qris'];
        foreach ($faspayChannels as $channel) {
            $key = 'fee_faspay_' . $channel;
            Setting::set($key, $request->input($key, 0), 'Biaya tambahan untuk pembayaran Faspay ' . strtoupper(str_replace('_', ' ', $channel)));
        }

        return back()->with('success', 'Pengaturan biaya layanan berhasil diperbarui.');
    }
}
