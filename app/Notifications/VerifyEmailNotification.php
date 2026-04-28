<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Lang;

class VerifyEmailNotification extends VerifyEmail
{
    /**
     * Get the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $verificationUrl = $this->verificationUrl($notifiable);

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email - Rasa Group')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Terima kasih telah bergabung dengan Rasa Group. Langkah terakhir untuk mengaktifkan akun Anda adalah dengan melakukan verifikasi alamat email.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Jika Anda tidak merasa melakukan pendaftaran akun, abaikan saja email ini.')
            ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
