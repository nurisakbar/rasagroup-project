<?php

namespace App\Http\Controllers\Buyer;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Notifications\Orders\OrderCompletedNotification;

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

        $order->load(['items.product', 'sourceWarehouse.province', 'sourceWarehouse.regency', 'expedition', 'sales']);
        return view('buyer.orders.show', compact('order'));
    }
    
    public function downloadInvoice(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        $order->load(['items.product', 'user', 'sourceWarehouse.province', 'sourceWarehouse.regency', 'expedition', 'sales']);
        
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

        $ekspedisiku = app(\App\Services\EkspedisiKuService::class);
        $result = $ekspedisiku->track($order->tracking_number, $code);

        if ($result && isset($result['success']) && $result['success']) {
            return response()->json(['success' => true, 'data' => $result['data'] ?? $result]);
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
    public function changePaymentMethod(Request $request, Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if ($order->payment_status !== 'pending') {
            return redirect()->back()->with('error', 'Hanya pesanan pending yang bisa diganti metode pembayarannya.');
        }

        $request->validate([
            'payment_method' => 'required|string'
        ]);

        $activeGateway = config('services.active_payment_gateway');
        $user = $order->user;
        $total = $order->total_amount;

        try {
            \DB::beginTransaction();

            // Clear old payment URLs if any
            $order->faspay_redirect_url = null;
            $order->faspay_bill_no = null;
            $order->virtual_account_no = null;

            if (str_starts_with($request->payment_method, 'faspay_')) {
                $orderCompany = $order->company ?: \App\Services\FaspayConfig::getDefaultCompany();

                if ($request->payment_method === 'faspay_qris') {
                    $snapService = new \App\Services\FaspaySnapService($orderCompany);
                    $qrisData = $snapService->generateQris($order, $total);
                    if ($qrisData && (isset($qrisData['qrUrl']) || isset($qrisData['additionalInfo']['qrImageUrl']))) {
                        $order->virtual_account_no = $qrisData['qrUrl'] ?? $qrisData['additionalInfo']['qrImageUrl'];
                        $order->payment_method = 'faspay_qris';
                    } else {
                        throw new \Exception('Failed to generate Faspay QRIS');
                    }
                } elseif ($request->payment_method === 'faspay_bca_va') {
                    $faspayService = new \App\Services\FaspayService($orderCompany);
                    $invoice = $faspayService->createBill($order, $user, '702');
                    if ($invoice && isset($invoice['bill_no'])) {
                        $order->faspay_bill_no = $invoice['bill_no'] ?? $order->order_number;
                        $order->faspay_redirect_url = $invoice['redirect_url'];
                        $order->payment_method = 'faspay_bca_va';
                        if (empty($order->virtual_account_no)) {
                            // Generate a mock VA number for BCA so it displays on the frontend
                            $order->virtual_account_no = '0712' . substr(preg_replace('/[^0-9]/', '', $order->order_number), -11);
                        }
                    } else {
                        throw new \Exception('Failed to generate Faspay BCA VA');
                    }
                } else {
                    $prefix = \App\Services\FaspayConfig::getVaPrefix($request->payment_method, $orderCompany);
                    $targetLength = 16;
                    $prefixLength = strlen($prefix);
                    $freeDigitsLength = $targetLength - $prefixLength;
                    
                    $numericOrder = preg_replace('/[^0-9]/', '', $order->order_number);
                    if (strlen($numericOrder) > $freeDigitsLength) {
                        $freeDigits = substr($numericOrder, -$freeDigitsLength);
                    } else {
                        $freeDigits = str_pad($numericOrder, $freeDigitsLength, '0', STR_PAD_LEFT);
                    }
                    
                    $order->virtual_account_no = $prefix . $freeDigits;
                    $order->payment_method = $request->payment_method;
                }
            } else {
                $order->payment_method = $request->payment_method;
            }

            $order->save();
            \DB::commit();

            return redirect()->route('checkout.success', $order)->with('success', 'Metode pembayaran berhasil diubah.');
        } catch (\Exception $e) {
            \DB::rollBack();
            \Log::error('Change Payment Method Error', ['error' => $e->getMessage(), 'order_id' => $order->id]);
            return redirect()->back()->with('error', 'Gagal mengubah metode pembayaran: ' . $e->getMessage());
        }
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

                if ($email = \App\Models\Setting::get('payment_confirmation_email')) {
                    try {
                        \Illuminate\Support\Facades\Notification::route('mail', $email)
                            ->notify(new \App\Notifications\Orders\PaymentConfirmationSubmittedNotification($order));
                    } catch (\Exception $e) {
                        \Illuminate\Support\Facades\Log::error('Failed sending payment confirmation email: ' . $e->getMessage());
                    }
                }
            }

            return redirect()->route('buyer.orders.show', $order)->with('success', 'Konfirmasi pembayaran berhasil dikirim. Tunggu verifikasi dari pusat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses konfirmasi: ' . $e->getMessage());
        }
    }

    public function confirmReceipt(Order $order)
    {
        if ($order->user_id !== Auth::id()) {
            abort(403);
        }

        if (!in_array($order->order_status, ['shipped', 'delivered']) && empty($order->shipped_at)) {
            return redirect()->route('buyer.orders.show', $order)->with('error', 'Pesanan belum dapat dikonfirmasi karena belum dikirim/diserahkan.');
        }

        $order->update([
            'order_status' => 'completed',
            'received_at' => now(),
        ]);
        $order->creditPoints();

        if ($order->user) {
            $order->user->notify(new OrderCompletedNotification($order));
        }

        return redirect()->route('buyer.orders.show', $order)->with('success', 'Terima kasih! Pesanan telah dikonfirmasi selesai dan diterima dengan baik.');
    }
}
