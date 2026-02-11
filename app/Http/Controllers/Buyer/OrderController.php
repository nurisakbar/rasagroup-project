<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderController extends Controller
{
    public function index()
    {
        $orders = Order::where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

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

        $rajaOngkir = new \App\Services\RajaOngkirService();
        $code = strtolower($order->expedition->code);
        
        $result = $rajaOngkir->trackWaybill($order->tracking_number, $code);

        if ($result && isset($result['data']) && !is_null($result['data'])) {
            return response()->json(['success' => true, 'data' => $result['data']]);
        }
        
        // Check for specific error message from RajaOngkir/Komerce
        $errorMessage = 'Gagal melacak resi via RajaOngkir';
        if (isset($result['meta']['message'])) {
            $errorMessage .= ': ' . $result['meta']['message'];
        } elseif (isset($result['status']['description'])) {
            $errorMessage .= ': ' . $result['status']['description'];
        }

        return response()->json(['success' => false, 'message' => $errorMessage], 400);
    }
}
