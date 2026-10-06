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

        $faspayChannels = ['bca_va', 'mandiri_va', 'bri_va', 'bni_va', 'cimb_va', 'permata_va', 'sinarmas_va', 'maybank_va', 'danamon_va', 'bsi_va', 'qris'];
        $faspayFees = [];
        $faspayActive = [];

        foreach ($faspayChannels as $channel) {
            $faspayFees['fee_faspay_' . $channel] = Setting::get('fee_faspay_' . $channel, 0);
            $faspayActive['active_faspay_' . $channel] = Setting::get('active_faspay_' . $channel, 1);
        }

        return view('admin.payment_fees.index', compact('faspayFees', 'faspayActive', 'faspayChannels'));
    }

    public function update(Request $request)
    {
        if (!in_array(auth()->user()->role, ['super_admin', 'finance'])) {
            abort(403);
        }

        $rules = [];
        $faspayChannels = ['bca_va', 'mandiri_va', 'bri_va', 'bni_va', 'cimb_va', 'permata_va', 'sinarmas_va', 'maybank_va', 'danamon_va', 'bsi_va', 'qris'];
        foreach ($faspayChannels as $channel) {
            $rules['fee_faspay_' . $channel] = 'nullable|numeric|min:0';
        }

        $request->validate($rules);

        foreach ($faspayChannels as $channel) {
            $feeKey = 'fee_faspay_' . $channel;
            $activeKey = 'active_faspay_' . $channel;
            
            Setting::set($feeKey, $request->input($feeKey, 0), 'Biaya tambahan untuk pembayaran Faspay ' . strtoupper(str_replace('_', ' ', $channel)));
            Setting::set($activeKey, $request->has($activeKey) ? 1 : 0, 'Status aktif pembayaran Faspay ' . strtoupper(str_replace('_', ' ', $channel)));
        }

        return back()->with('success', 'Pengaturan pembayaran berhasil diperbarui.');
    }
}
