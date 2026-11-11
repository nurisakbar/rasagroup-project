<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Pesanan sudah sampai di alamat pembeli (status "delivered", mis. dari webhook kurir):
 * minta pembeli mengonfirmasi penerimaan.
 */
class OrderDeliveredNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Order $order)
    {
    }

    public function via($notifiable): array
    {
        return ['mail'];
    }

    public function toMail($notifiable): MailMessage
    {
        $order = $this->order;
        $isDistributor = $order->order_type === Order::TYPE_DISTRIBUTOR;
        $receivedAt = $order->received_at
            ? \App\Support\Wib::format($order->received_at, 'd M Y, H:i') . ' WIB'
            : now(\App\Support\Wib::TZ)->format('d M Y, H:i') . ' WIB';

        $mail = (new MailMessage)
            ->subject('Pesanan Sudah Sampai - #' . $order->order_number . ' | Rasaconnect')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!')
            ->line('Pesanan Anda #' . $order->order_number . ' telah sampai di alamat tujuan pada ' . $receivedAt . '.');

        if ($order->expedition?->name || $order->tracking_number) {
            $mail->line(trim(($order->expedition?->name ? 'Ekspedisi: ' . $order->expedition->name . '. ' : '') . ($order->tracking_number ? 'No. resi: ' . $order->tracking_number . '.' : '')));
        }

        $mail->line($isDistributor
            ? 'Silakan periksa barang, lalu klik "Masukkan ke Stock" pada halaman pesanan Anda.'
            : 'Silakan periksa barang, lalu klik "Konfirmasi Sudah Diterima" pada halaman pesanan Anda.');

        return $mail->action($isDistributor ? 'Lihat Pesanan & Masukkan ke Stock' : 'Lihat Pesanan & Konfirmasi Terima', $order->customerOrderUrl())
            ->line('Jika ada kendala dengan pesanan Anda, silakan hubungi customer service kami.')
            ->salutation("Salam Hangat,\nTim Rasaconnect");
    }
}
