<?php

namespace App\Notifications;

use App\Models\SharedReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportSharedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected SharedReport $sharedReport;
    protected string $senderName;
    protected string $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(SharedReport $sharedReport, string $senderName, string $message = '')
    {
        $this->sharedReport = $sharedReport;
        $this->senderName = $senderName;
        $this->message = $message;
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
        $url = $this->sharedReport->share_url;
        $expiresAt = $this->sharedReport->expires_at?->format('d M Y H:i') ?? 'No expiration';
        $accessLevel = ucfirst($this->sharedReport->access_level);

        return (new MailMessage)
            ->subject("📊 Report Shared: {$this->sharedReport->name}")
            ->greeting("Hello!")
            ->line("{$this->senderName} has shared a report with you.")
            ->line("**Report Name:** {$this->sharedReport->name}")
            ->line("**Type:** " . ucfirst(str_replace('_', ' ', $this->sharedReport->type)))
            ->line("**Access Level:** {$accessLevel}")
            ->line("**Expires:** {$expiresAt}")
            ->when($this->message, function ($mail) {
                return $mail->line("**Message:** {$this->message}");
            })
            ->action('View Report', $url)
            ->line('This link will expire on ' . $expiresAt . '.')
            ->line('If you did not expect to receive this report, please ignore this email.')
            ->salutation('Best regards, ' . config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'report_id' => $this->sharedReport->report_id,
            'report_name' => $this->sharedReport->name,
            'sender_name' => $this->senderName,
            'message' => $this->message,
            'url' => $this->sharedReport->share_url,
        ];
    }
}
