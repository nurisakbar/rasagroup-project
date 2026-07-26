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
        $isDistributor = $this->order->order_type === Order::TYPE_DISTRIBUTOR;
        $url = $isDistributor
            ? url('/distributor/orders/' . $this->order->id)
            : url('/orders/' . $this->order->id);

        $readyTime = $this->order->pickup_ready_at ? Carbon::parse($this->order->pickup_ready_at)->format('d M Y, H:i') . ' WIB' : 'Segera';
        $note = $this->order->pickup_note ?: 'Harap menunjukkan nomor pesanan / invoice ini saat menemui petugas gudang.';

        $subject = 'Pesanan Siap Diambil (Ready) - Order #' . $this->order->order_number . ' | Rasa Group';

        $mail = (new MailMessage)
            ->subject($subject)
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!');

        if ($isDistributor) {
            $mail->line('Pesanan Anda #' . $this->order->order_number . ' telah selesai disiapkan oleh gudang pusat dan saat ini berstatus SIAP DIKIRIM / DIAMBIL (Ready).')
                 ->line('Jadwal Siap Diambil (Ready): ' . $readyTime)
                 ->line('Catatan / Instruksi Gudang: ' . $note)
                 ->line('Silakan ikuti instruksi di atas untuk pengambilan atau penerimaan stok barang pesanan Anda.');
        } else {
            $mail->line('Pesanan Anda #' . $this->order->order_number . ' telah selesai disiapkan oleh gudang dan sekarang berstatus SIAP UNTUK DIAMBIL (Ready / Self Pickup).')
                 ->line('Jadwal Siap Diambil: ' . $readyTime)
                 ->line('Catatan / Instruksi Gudang: ' . $note)
                 ->line('Silakan datang ke lokasi gudang kami sesuai jadwal dan instruksi di atas untuk pengambilan barang pesanan Anda.');
        }

        return $mail->action('Lihat Detail & Instruksi Pengambilan', $url)
                    ->line('Terima kasih telah berbelanja di Rasa Group!')
                    ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
