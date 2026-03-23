<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class BudgetExceededNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param array $budgets  [['name', 'department', 'amount', 'realized', 'usage_percent', 'period'], ...]
     */
    public function __construct(
        public readonly array  $budgets,
        public readonly string $tenantName,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $exceeded = array_filter($this->budgets, fn($b) => $b['usage_percent'] >= 100);
        $warning  = array_filter($this->budgets, fn($b) => $b['usage_percent'] >= 80 && $b['usage_percent'] < 100);

        $mail = (new MailMessage)
            ->subject("💰 Alert Anggaran — {$this->tenantName}")
            ->greeting("Halo, {$notifiable->name}!");

        if (!empty($exceeded)) {
            $mail->line('**Anggaran Terlampaui (≥ 100%):**');
            foreach ($exceeded as $b) {
                $over = $b['realized'] - $b['amount'];
                $mail->line("• **{$b['name']}** ({$b['department']}) — {$b['usage_percent']}% | Kelebihan Rp " .
                    number_format($over, 0, ',', '.'));
            }
        }

        if (!empty($warning)) {
            $mail->line('**Anggaran Hampir Habis (≥ 80%):**');
            foreach ($warning as $b) {
                $sisa = $b['amount'] - $b['realized'];
                $mail->line("• **{$b['name']}** ({$b['department']}) — {$b['usage_percent']}% | Sisa Rp " .
                    number_format($sisa, 0, ',', '.'));
            }
        }

        return $mail
            ->action('Lihat Anggaran', url('/budget'))
            ->line('Tinjau pengeluaran Anda untuk menjaga anggaran tetap terkendali.')
            ->salutation('Salam, Qalcuity ERP');
    }
}
