<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification
{
    use Queueable;

    public $withdrawal;

    /**
     * Create a new notification instance.
     */
    public function __construct($withdrawal)
    {
        $this->withdrawal = $withdrawal;
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
        $status = strtolower($this->withdrawal->status);
        $amountFormatted = 'Rp ' . number_format($this->withdrawal->amount, 0, ',', '.');
        $wdUrl = route('admin.withdrawals.index');

        $mail = new MailMessage();

        if ($status === 'approved') {
            $mail->subject('Penarikan Saldo ' . $amountFormatted . ' Berhasil Disetujui (WD #' . $this->withdrawal->id . ')')
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Permohonan penarikan saldo (WD) Anda telah **DISETUJUI** dan ditransfer ke rekening bank Anda.')
                ->line('• **Nominal WD:** ' . $amountFormatted)
                ->line('• **Bank Tujuan:** ' . $this->withdrawal->bank_name)
                ->line('• **Nomor Rekening:** ' . $this->withdrawal->bank_account_number)
                ->line('• **Atas Nama:** ' . $this->withdrawal->bank_account_name);
        } elseif ($status === 'rejected') {
            $mail->subject('Pemberitahuan Penarikan Saldo WD #' . $this->withdrawal->id . ' Ditolak')
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Permohonan penarikan saldo (WD) Anda sebesar ' . $amountFormatted . ' **DITOLAK**.')
                ->line('• **Alasan:** ' . ($this->withdrawal->admin_notes ?: 'Persyaratan tidak terpenuhi.'))
                ->line('• **Status Dana:** Saldo sebesar ' . $amountFormatted . ' telah dikembalikan secara utuh ke saldo E-Wallet Anda.');
        } else {
            $mail->subject('Permohonan Penarikan Saldo ' . $amountFormatted . ' Diterima (WD #' . $this->withdrawal->id . ')')
                ->greeting('Halo ' . $notifiable->name . ',')
                ->line('Permohonan penarikan saldo (WD) Anda sebesar ' . $amountFormatted . ' telah berhasil terkirim dan sedang diproses oleh Tim Keuangan XSELLER.ID.')
                ->line('• **Bank Tujuan:** ' . $this->withdrawal->bank_name . ' (' . $this->withdrawal->bank_account_number . ')');
        }

        return $mail
            ->action('Lihat Riwayat Penarikan', $wdUrl)
            ->line('Terima kasih atas kepercayaan Anda menggunakan XSELLER.ID.');
    }
}
