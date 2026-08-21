<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BonusReceivedNotification extends Notification
{
    use Queueable;

    public $category;
    public $amount;
    public $description;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $category, float $amount, string $description = '')
    {
        $this->category = $category;
        $this->amount = $amount;
        $this->description = $description;
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
        $categoryName = ucfirst($this->category);
        $formattedAmount = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $dashboardUrl = route('admin.finance.index');

        return (new MailMessage)
            ->subject('Selamat! Bonus ' . $categoryName . ' ' . $formattedAmount . ' Berhasil Diterima - XSELLER.ID')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Selamat! Anda baru saja mendapatkan alokasi bonus baru ke dompet akun Anda.')
            ->line('• **Kategori Bonus:** Bonus ' . $categoryName)
            ->line('• **Nominal Bonus:** ' . $formattedAmount)
            ->line('• **Keterangan:** ' . ($this->description ?: 'Distribusi alokasi bonus sistem XSELLER'))
            ->action('Cek Saldo & Dompet Saya', $dashboardUrl)
            ->line('Bonus ini dapat dicairkan langsung ke E-Wallet atau direkeningkan sesuai ketentuan sistem.')
            ->line('Terima kasih atas kerja keras dan perkembangan jaringan Anda di XSELLER.ID!');
    }
}
