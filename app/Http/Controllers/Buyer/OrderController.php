<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->query('status');

        $counts = Order::where('user_id', Auth::id())
            ->selectRaw('order_status, count(*) as count')
            ->groupBy('order_status')
            ->pluck('count', 'order_status')
            ->all();

        $totalCount = array_sum($counts);

        $query = Order::with(['items.product'])
            ->where('user_id', Auth::id());

        if (in_array($status, ['pending', 'processing', 'shipped', 'delivered', 'cancelled'], true)) {
            $query->where('order_status', $status);
        }

        $orders = $query->latest()->paginate(5)->withQueryString();

        if ($request->ajax()) {
            return response()->json([
                'html' => view('buyer.orders.partials.list', compact('orders'))->render(),
                'total_count' => $totalCount,
                'counts' => $counts
            ]);
        }

        return view('buyer.orders.index', compact('orders', 'status', 'counts', 'totalCount'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'sourceWarehouse.province', 'sourceWarehouse.regency', 'expedition']);
        return view('buyer.orders.show', compact('order'));
    }
    
    public function downloadInvoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'user', 'sourceWarehouse.province', 'sourceWarehouse.regency', 'expedition']);
        
        $pdf = Pdf::loadView('buyer.orders.invoice', compact('order'));
        
        return $pdf->download('invoice-' . $order->order_number . '.pdf');
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
    public function confirmPaymentForm(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('buyer.orders.show', $order)->with('info', 'Pesanan ini sudah dibayar.');
        }

        return view('buyer.orders.confirm-payment', compact('order'));
    }

    public function storePaymentConfirmation(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $request->validate([
            'payment_proof' => 'required|image|max:2048',
            'payment_submit_note' => 'nullable|string|max:1000',
        ]);

        try {
            if ($request->hasFile('payment_proof')) {
                $path = $request->file('payment_proof')->store('payment_proofs', 'public');
                $order->update([
                    'payment_proof' => $path,
                    'payment_submit_note' => $request->payment_submit_note,
                    'payment_submitted_at' => now(),
                ]);
            }

            return redirect()->route('buyer.orders.show', $order)->with('success', 'Konfirmasi pembayaran berhasil dikirim. Tunggu verifikasi dari pusat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses konfirmasi: ' . $e->getMessage());
        }
    }
}
