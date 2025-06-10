<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResetPasswordNotification extends Notification
{
    protected $token;

    public function __construct($token)
    {
        $this->token = $token;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        $url = url('/reset-password/' . $this->token);

        return (new MailMessage)
            ->subject('Reset Password - Eye Care')
            ->line('Kamu menerima email ini karena ada permintaan reset password.')
            ->action('Reset Password', $url)
            ->line('Jika kamu tidak meminta reset, abaikan email ini.');
    }
}
