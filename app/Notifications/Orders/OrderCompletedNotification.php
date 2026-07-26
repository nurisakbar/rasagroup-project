<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class OrderCompletedNotification extends Notification implements ShouldQueue
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
        $url = url('/orders/' . $this->order->id);
        $timeStr = $this->order->received_at ? Carbon::parse($this->order->received_at)->format('d M Y, H:i') . ' WIB' : now()->format('d M Y, H:i') . ' WIB';

        $mail = (new MailMessage)
            ->subject('Pesanan Selesai! #' . $this->order->order_number . ' | Rasa Group')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!')
            ->line('Terima kasih! Konfirmasi penerimaan pesanan Anda #' . $this->order->order_number . ' telah kami terima pada ' . $timeStr . '.')
            ->line('Pesanan ini sekarang resmi berstatus SELESAI.');

        if ($this->order->points_earned > 0) {
            $mail->line('🎉 Selamat! Anda memperoleh poin reward sebesar ' . number_format($this->order->points_earned, 0, ',', '.') . ' poin dari transaksi ini.');
        }

        return $mail->action('Lihat Detail Pesanan', $url)
                    ->line('Kami sangat menghargai kepercayaan Anda berbelanja bersama Rasa Group. Sampai jumpa di pesanan berikutnya!')
                    ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
