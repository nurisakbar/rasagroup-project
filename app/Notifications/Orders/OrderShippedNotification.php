<?php

namespace App\Notifications\Orders;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Carbon;

class OrderShippedNotification extends Notification implements ShouldQueue
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

        $isDistributor = $this->order->order_type === Order::TYPE_DISTRIBUTOR;
        $url = $isDistributor 
            ? url('/distributor/orders/' . $this->order->id) 
            : url('/orders/' . $this->order->id);

        $timeStr = $this->order->shipped_at ? Carbon::parse($this->order->shipped_at)->format('d M Y, H:i') . ' WIB' : now()->format('d M Y, H:i') . ' WIB';

        $mail = (new MailMessage)
            ->greeting('Halo, ' . ($notifiable->name ?? 'Pelanggan') . '!');

        if ($isSelfPickup) {
            $mail->subject('Pesanan Diserahkan / Diambil (Handover) - Order #' . $this->order->order_number . ' | Rasa Group')
                 ->line('Barang pesanan Anda #' . $this->order->order_number . ' telah resmi diserahkan / diambil dari gudang pada ' . $timeStr . ' (Status: Handover).');
            
            if ($isDistributor) {
                $mail->line('Jika Anda telah memeriksa barang dan menerimanya dengan baik, silakan login dan klik tombol "Masukkan ke Stock" pada halaman pesanan Anda untuk mengonversinya menjadi stok gudang Anda.');
            } else {
                $mail->line('Jika Anda telah memeriksa barang dan menerimanya dengan baik, silakan login dan klik tombol "Konfirmasi Sudah Diterima" pada halaman pesanan Anda.');
            }
        } else {
            $expName = $this->order->expedition->name ?? 'Kurir';
            $resi = $this->order->tracking_number ? 'Nomor Resi: ' . $this->order->tracking_number : 'Kurir Sedang Mengirimkan Paket';

            $mail->subject('Pesanan Dikirim / Diserahkan (Handover) - Order #' . $this->order->order_number . ' | Rasa Group')
                 ->line('Pesanan Anda #' . $this->order->order_number . ' telah diserahkan / dikirimkan menggunakan layanan ekspedisi ' . $expName . '.')
                 ->line($resi)
                 ->line('Waktu Penyerahan / Pengiriman: ' . $timeStr);

            if ($isDistributor) {
                $mail->line('Jika paket sudah tiba dan Anda terima dengan baik, silakan login dan klik tombol "Masukkan ke Stock" pada halaman pesanan Anda untuk memasukkan item ke dalam stok gudang.');
            } else {
                $mail->line('Jika paket sudah tiba dan Anda terima dengan baik, silakan login dan klik tombol konfirmasi penerimaan pada halaman pesanan.');
            }
        }

        $actionText = $isDistributor ? 'Lihat Pesanan & Masukkan ke Stock' : 'Lihat Pesanan & Konfirmasi Terima';

        return $mail->action($actionText, $url)
                    ->line('Terima kasih telah berbelanja di Rasa Group!')
                    ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
