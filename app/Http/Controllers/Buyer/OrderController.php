<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::with(['items.product'])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(5);

        return view('buyer.orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'sourceWarehouse.province', 'sourceWarehouse.regency', 'expedition']);
        return view('buyer.orders.show', compact('order'));
    }

    public function trackOrder(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }
        
        if (!$order->tracking_number || !$order->expedition) {
            return response()->json(['success' => false, 'message' => 'Resi atau ekspedisi belum tersedia'], 400);
        }

        $code = strtolower($order->expedition->code);
        Log::info('Buyer OrderController: trackOrder requested', [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'courier' => $code,
            'tracking_number' => $order->tracking_number,
        ]);

        if ($code === 'lion_parcel') {
            $ekspedisiku = app(\App\Services\EkspedisiKuService::class);
            $result = $ekspedisiku->track($order->tracking_number, $code);

            if ($result && isset($result['success']) && $result['success']) {
                return response()->json(['success' => true, 'data' => $result['data'] ?? $result]);
            }
        } else {
            $rajaOngkir = new \App\Services\RajaOngkirService();
            $result = $rajaOngkir->trackWaybill($order->tracking_number, $code);

            if ($result && isset($result['data']) && !is_null($result['data'])) {
                return response()->json(['success' => true, 'data' => $result['data']]);
            }
        }

        // Check for specific error message
        $errorMessage = 'Gagal melacak resi';
        if (isset($result['meta']['message'])) {
            $errorMessage .= ': ' . $result['meta']['message'];
        } elseif (isset($result['status']['description'])) {
            $errorMessage .= ': ' . $result['status']['description'];
        } elseif (isset($result['message'])) {
            $errorMessage .= ': ' . $result['message'];
        }

        return response()->json(['success' => false, 'message' => $errorMessage], 400);
    }
}
