<?php

namespace App\Notifications\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewTopOrderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(public \App\Models\Order $order)
    {
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $url = url('/admin/orders/' . $this->order->id);

        return (new MailMessage)
            ->subject('Order Baru (TOP) - #' . $this->order->order_number . ' | Rasaconnect')
            ->greeting('Halo, ' . ($notifiable->name ?? 'Staff') . '!')
            ->line('Terdapat pesanan baru (Order #' . $this->order->order_number . ') yang menggunakan metode pembayaran TOP (Term of Payment) yang dialokasikan ke Hub/Gudang Anda.')
            ->line('Pelanggan: ' . ($this->order->user->name ?? 'Pelanggan'))
            ->line('Total Pesanan: Rp ' . number_format($this->order->total_amount, 0, ',', '.'))
            ->action('Lihat Detail Pesanan', $url)
            ->line('Silakan login ke panel untuk memproses pesanan ini.')
            ->salutation("Salam,\nSistem Rasaconnect");
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'order_id' => $this->order->id,
            'order_number' => $this->order->order_number,
        ];
    }
}
