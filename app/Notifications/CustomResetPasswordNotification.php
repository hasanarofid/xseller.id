<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CustomResetPasswordNotification extends Notification
{
    use Queueable;

    public $token;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $token)
    {
        $this->token = $token;
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
        $resetUrl = url(route('password.reset', [
            'token' => $this->token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Reset Password Akun XSELLER.ID')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Anda menerima email ini karena kami menerima permintaan reset password untuk akun XSELLER.ID Anda.')
            ->action('Reset Password Sekarang', $resetUrl)
            ->line('Tautan reset password ini akan kedaluwarsa dalam waktu 60 menit.')
            ->line('Jika Anda tidak merasa melakukan permintaan reset password, tidak ada tindakan lanjutan yang perlu dilakukan.')
            ->line('Terima kasih, Salam sukses XSELLER.ID!');
    }
}
