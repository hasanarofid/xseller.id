<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WelcomeRegisterNotification extends Notification
{
    use Queueable;

    public $user;
    public $passwordText;

    /**
     * Create a new notification instance.
     */
    public function __construct($user, string $passwordText = null)
    {
        $this->user = $user;
        $this->passwordText = $passwordText;
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
        $loginUrl = route('login');
        $packageName = $this->user->package_name ?: 'Basic';

        $mail = (new MailMessage)
            ->subject('Selamat Datang di XSELLER.ID - Akun Anda Berhasil Terdaftar!')
            ->greeting('Halo ' . $this->user->name . ',')
            ->line('Selamat datang di **XSELLER.ID** (E-Commerce Trade Promotion Program & Affiliasi MLM Binary).')
            ->line('Akun Anda telah berhasil terdaftar dan diaktifkan dengan rincian sebagai berikut:')
            ->line('• **Nama:** ' . $this->user->name)
            ->line('• **Username:** @' . ($this->user->username ?: strtolower(explode(' ', $this->user->name)[0])))
            ->line('• **Email:** ' . $this->user->email)
            ->line('• **Paket Membership:** ' . $packageName);

        if ($this->passwordText) {
            $mail->line('• **Password:** ' . $this->passwordText);
        }

        return $mail
            ->action('Masuk ke Member Area', $loginUrl)
            ->line('Silakan jaga kerahasiaan informasi akun dan PIN keamanan Anda.')
            ->line('Terima kasih telah bergabung bersama XSELLER.ID!');
    }
}
