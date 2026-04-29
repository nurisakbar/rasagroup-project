<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Log;

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

        Log::info('Building VerifyEmailNotification mail', [
            'user_id' => method_exists($notifiable, 'getKey') ? $notifiable->getKey() : null,
            'email' => $notifiable->email ?? null,
            'verification_url_host' => parse_url($verificationUrl, PHP_URL_HOST),
            'verification_url_path' => parse_url($verificationUrl, PHP_URL_PATH),
        ]);

        return (new MailMessage)
            ->subject('Verifikasi Alamat Email - Rasa Group')
            ->greeting('Halo, ' . $notifiable->name . '!')
            ->line('Terima kasih telah bergabung dengan Rasa Group. Langkah terakhir untuk mengaktifkan akun Anda adalah dengan melakukan verifikasi alamat email.')
            ->action('Verifikasi Email Sekarang', $verificationUrl)
            ->line('Jika Anda tidak merasa melakukan pendaftaran akun, abaikan saja email ini.')
            ->salutation("Salam Hangat,\nTim Rasa Group");
    }
}
