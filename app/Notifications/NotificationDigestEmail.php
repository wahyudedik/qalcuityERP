<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NotificationDigestEmail extends Notification implements ShouldQueue
{
    use Queueable;

    protected array $groupedNotifications;

    protected string $frequency;

    protected $startDate;

    protected $endDate;

    /**
     * Create a new notification instance.
     */
    public function __construct(array $groupedNotifications, string $frequency, $startDate, $endDate = null)
    {
        $this->groupedNotifications = $groupedNotifications;
        $this->frequency = $frequency;
        $this->startDate = $startDate;
        $this->endDate = $endDate ?? $startDate;
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
        $subject = $this->frequency === 'daily'
            ? 'Ringkasan Notifikasi Harian'
            : 'Ringkasan Notifikasi Mingguan';

        $dateRange = $this->startDate->format('d M Y');
        if ($this->endDate && ! $this->startDate->eq($this->endDate)) {
            $dateRange .= ' - '.$this->endDate->format('d M Y');
        }

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting("Halo {$notifiable->name},")
            ->line("Berikut adalah ringkasan notifikasi Anda untuk periode: {$dateRange}")
            ->line("Total notifikasi: {$this->groupedNotifications['summary']['total']}")
            ->line("Belum dibaca: {$this->groupedNotifications['summary']['unread']}");

        // Add sections for each module
        foreach ($this->groupedNotifications['by_module'] as $module => $data) {
            $moduleLabel = $this->getModuleLabel($module);
            $mailMessage->line('─────────────────────');
            $mailMessage->line("📂 {$moduleLabel} ({$data['count']} notifikasi, {$data['unread']} belum dibaca)");

            // Show top 5 notifications per module
            $topNotifications = array_slice($data['notifications'], 0, 5);
            foreach ($topNotifications as $notif) {
                $status = $notif['read_at'] ? '✓' : '○';
                $mailMessage->line("{$status} {$notif['title']}");
            }

            if ($data['count'] > 5) {
                $remaining = $data['count'] - 5;
                $mailMessage->line("... dan {$remaining} notifikasi lainnya");
            }
        }

        $mailMessage->line('─────────────────────')
            ->action('Lihat Semua Notifikasi', url('/notifications'))
            ->line('Terima kasih telah menggunakan Qalcuity ERP!');

        return $mailMessage;
    }

    /**
     * Get module label for display.
     */
    protected function getModuleLabel(string $module): string
    {
        return match ($module) {
            'inventory' => '📦 Inventori',
            'finance' => '💰 Keuangan',
            'hrm' => '👥 SDM',
            'ai' => '🤖 AI',
            'system' => '⚙️ Sistem',
            'ecommerce' => '🛒 E-Commerce',
            'healthcare' => '🏥 Kesehatan',
            default => ucfirst($module),
        };
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'frequency' => $this->frequency,
            'total_notifications' => $this->groupedNotifications['summary']['total'],
            'unread_notifications' => $this->groupedNotifications['summary']['unread'],
            'modules' => $this->groupedNotifications['summary']['modules'],
        ];
    }
}
