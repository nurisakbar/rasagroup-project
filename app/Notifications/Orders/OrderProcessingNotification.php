<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderProcessingNotification extends Notification implements ShouldQueue
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
        $isSelfPickup = $this->order->expedition && (
            $this->order->expedition->code === 'self_pickup' || 
            str_contains(strtolower($this->order->expedition->name), 'pickup')
        );

        $url = url('/orders/' . $this->order->id);

        $mail = (new MailMessage)
            ->subject('Pesanan Diproses - #' . $this->order->order_number . ' | Rasa Group')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!')
            ->line('Kabar gembira! Pembayaran pesanan Anda #' . $this->order->order_number . ' telah berhasil diverifikasi dan saat ini sedang disiapkan oleh tim gudang kami.')
            ->line('Total Pesanan: Rp ' . number_format($this->order->total_amount, 0, ',', '.'));

        if ($isSelfPickup) {
            $mail->line('Metode Pengambilan: Ambil Sendiri di Gudang (Self Pickup).')
                 ->line('Anda akan menerima notifikasi email berikutnya apabila barang telah selesai disiapkan dan siap untuk Anda ambil di gudang.');
        } else {
            $mail->line('Pesanan Anda akan segera diserahkan kepada pihak kurir/ekspedisi (' . ($this->order->expedition->name ?? 'Reguler') . ').')
                 ->line('Anda akan menerima notifikasi email berikutnya beserta nomor resi apabila pesanan telah dalam pengiriman.');
        }

        return $mail->action('Lihat Detail Pesanan', $url)
                    ->line('Terima kasih telah berbelanja di Rasa Group!')
                    ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
