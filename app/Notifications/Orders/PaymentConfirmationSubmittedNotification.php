<?php

namespace App\Notifications\Orders;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentConfirmationSubmittedNotification extends Notification implements ShouldQueue
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
            ->subject('Konfirmasi Pembayaran Baru - Order #' . $this->order->order_number . ' | Rasaconnect')
            ->greeting('Halo, Admin!')
            ->line('Pelanggan telah mengirimkan bukti konfirmasi pembayaran untuk pesanan (Order #' . $this->order->order_number . ').')
            ->line('Pelanggan: ' . ($this->order->user->name ?? 'Pelanggan'))
            ->line('Total Pesanan: Rp ' . number_format($this->order->total_amount, 0, ',', '.'))
            ->line('Catatan Pembayaran: ' . ($this->order->payment_submit_note ?: '-'))
            ->action('Verifikasi Pembayaran Sekarang', $url)
            ->line('Silakan login ke panel admin untuk memeriksa bukti transfer dan memverifikasi pembayaran ini.')
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
