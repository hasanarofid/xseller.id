<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WalletTransferNotification extends Notification
{
    use Queueable;

    public $type; // 'sender' or 'recipient'
    public $otherUser;
    public $amount;
    public $description;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, $otherUser, float $amount, string $description = '')
    {
        $this->type = $type;
        $this->otherUser = $otherUser;
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
        $amountFormatted = 'Rp ' . number_format($this->amount, 0, ',', '.');
        $financeUrl = route('admin.finance.index');

        if ($this->type === 'recipient') {
            return (new MailMessage)
                ->subject('Anda Menerima Transfer Saldo ' . $amountFormatted . ' dari @' . ($this->otherUser->username ?? 'member'))
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Anda telah menerima transfer saldo E-Wallet sebesar **' . $amountFormatted . '** dari **' . ($this->otherUser->name ?? 'Member') . '** (@' . ($this->otherUser->username ?? 'member') . ').')
                ->line('• **Jumlah:** ' . $amountFormatted)
                ->line('• **Pengirim:** ' . ($this->otherUser->name ?? 'Member') . ' (@' . ($this->otherUser->username ?? 'member') . ')')
                ->action('Cek Saldo Dompet', $financeUrl)
                ->line('Saldo ini sudah aktif dan dapat digunakan untuk transaksi atau ditarik.');
        } else {
            return (new MailMessage)
                ->subject('Konfirmasi Transfer Saldo ' . $amountFormatted . ' ke @' . ($this->otherUser->username ?? 'member'))
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Transaksi transfer saldo E-Wallet sebesar **' . $amountFormatted . '** ke **' . ($this->otherUser->name ?? 'Member') . '** (@' . ($this->otherUser->username ?? 'member') . ') telah **BERHASIL**.')
                ->line('• **Jumlah Transfer:** ' . $amountFormatted)
                ->line('• **Penerima:** ' . ($this->otherUser->name ?? 'Member') . ' (@' . ($this->otherUser->username ?? 'member') . ')')
                ->action('Lihat Mutasi Rekening', $financeUrl)
                ->line('Terima kasih telah bertransaksi di XSELLER.ID.');
        }
    }
}
