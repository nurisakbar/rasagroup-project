<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Helpers\WACloudHelper;
use App\Models\Order;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Send order notification via WhatsApp
     */
    public function sendOrderNotification(Order $order)
    {
        if (!WACloudHelper::isConfigured()) {
            return back()->with('error', 'WACloud belum dikonfigurasi. Silakan konfigurasi di Settings.');
        }

        if (!$order->address || !$order->address->phone) {
            return back()->with('error', 'Nomor telepon tidak tersedia untuk pesanan ini.');
        }

        $phone = $order->address->phone;
        $message = "📦 *Pesanan #{$order->order_number}*\n\n";
        $message .= "Status: {$order->order_status}\n";
        $message .= "Total: Rp " . number_format($order->total_amount, 0, ',', '.') . "\n";
        $message .= "Metode Pembayaran: " . ucfirst($order->payment_method) . "\n\n";
        $message .= "Terima kasih atas pesanan Anda!";

        $result = WACloudHelper::sendText($phone, $message);

        if ($result) {
            return back()->with('success', 'Notifikasi WhatsApp berhasil dikirim ke ' . $phone);
        }

        return back()->with('error', 'Gagal mengirim notifikasi WhatsApp. Silakan cek log untuk detail.');
    }

    /**
     * Send invoice document via WhatsApp
     */
    public function sendInvoice(Order $order)
    {
        if (!WACloudHelper::isConfigured()) {
            return back()->with('error', 'WACloud belum dikonfigurasi. Silakan konfigurasi di Settings.');
        }

        if (!$order->address || !$order->address->phone) {
            return back()->with('error', 'Nomor telepon tidak tersedia untuk pesanan ini.');
        }

        // Generate invoice URL (adjust sesuai kebutuhan)
        $invoiceUrl = route('orders.invoice', $order->id);
        
        // Pastikan URL bisa diakses secara publik
        $fullInvoiceUrl = url($invoiceUrl);
        
        $filename = "Invoice-{$order->order_number}.pdf";

        $result = WACloudHelper::sendDocument($order->address->phone, $fullInvoiceUrl, $filename);

        if ($result) {
            return back()->with('success', 'Invoice berhasil dikirim via WhatsApp ke ' . $order->address->phone);
        }

        return back()->with('error', 'Gagal mengirim invoice via WhatsApp. Pastikan URL invoice bisa diakses.');
    }

    /**
     * Test send WhatsApp message
     */
    public function testSend(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'required|string',
        ]);

        if (!WACloudHelper::isConfigured()) {
            return response()->json([
                'success' => false,
                'message' => 'WACloud belum dikonfigurasi'
            ], 400);
        }

        $result = WACloudHelper::sendText($request->phone, $request->message);

        if ($result) {
            return response()->json([
                'success' => true,
                'message' => 'Pesan berhasil dikirim',
                'data' => $result
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Gagal mengirim pesan. Silakan cek log untuk detail.'
        ], 500);
    }
}

