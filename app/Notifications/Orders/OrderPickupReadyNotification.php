<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class OrderPickupReadyNotification extends Notification implements ShouldQueue
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
        $readyTime = $this->order->pickup_ready_at ? Carbon::parse($this->order->pickup_ready_at)->format('d M Y, H:i') . ' WIB' : 'Segera';
        $note = $this->order->pickup_note ?: 'Harap menunjukkan nomor pesanan / invoice ini saat menemui petugas gudang.';

        return (new MailMessage)
            ->subject('Siap Diambil! Pesanan #' . $this->order->order_number . ' | Rasa Group')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!')
            ->line('Pesanan Anda #' . $this->order->order_number . ' telah selesai disiapkan oleh gudang dan sekarang SIAP UNTUK DIAMBIL (Self Pickup).')
            ->line('Jadwal Siap Diambil: ' . $readyTime)
            ->line('Catatan / Instruksi Gudang: ' . $note)
            ->line('Silakan datang ke lokasi gudang kami sesuai jadwal dan instruksi di atas untuk pengambilan barang pesanan Anda.')
            ->action('Lihat Detail & Instruksi Pengambilan', $url)
            ->line('Terima kasih telah berbelanja di Rasa Group!')
            ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
