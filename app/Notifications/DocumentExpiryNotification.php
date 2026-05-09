<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentExpiryNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;

    protected int $daysUntilExpiry;

    protected bool $isExpired;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, int $daysUntilExpiry, bool $isExpired = false)
    {
        $this->document = $document;
        $this->daysUntilExpiry = $daysUntilExpiry;
        $this->isExpired = $isExpired;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        if ($this->isExpired) {
            return $this->buildExpiredMail();
        }

        return $this->buildExpiringSoonMail();
    }

    /**
     * Build mail for expired document.
     */
    protected function buildExpiredMail(): MailMessage
    {
        $category = $this->document->category ?? 'General';

        return (new MailMessage)
            ->subject("⚠️ Document Expired: {$this->document->title}")
            ->error()
            ->greeting('Document Expired!')
            ->line('The following document has expired:')
            ->line("**Document:** {$this->document->title}")
            ->line("**Category:** {$category}")
            ->line("**Expired on:** {$this->document->expires_at->format('d M Y H:i')}")
            ->line("**Status:** {$this->document->status}")
            ->action('View Document', url("/documents/{$this->document->id}"))
            ->line('Please take necessary action to renew or archive this document.')
            ->line('Thank you for using our document management system!');
    }

    /**
     * Build mail for document expiring soon.
     */
    protected function buildExpiringSoonMail(): MailMessage
    {
        $urgency = $this->getUrgencyLevel();
        $category = $this->document->category ?? 'General';

        return (new MailMessage)
            ->subject("{$urgency} Document Expiring Soon: {$this->document->title}")
            ->greeting('Document Expiry Reminder')
            ->line("The following document will expire in **{$this->daysUntilExpiry} days**:")
            ->line("**Document:** {$this->document->title}")
            ->line("**Category:** {$category}")
            ->line("**Expires on:** {$this->document->expires_at->format('d M Y H:i')}")
            ->line("**Current Status:** {$this->document->status}")
            ->action('View Document', url("/documents/{$this->document->id}"))
            ->line('Please review and renew this document before it expires.')
            ->line('Thank you for using our document management system!');
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'document_id' => $this->document->id,
            'document_title' => $this->document->title,
            'document_category' => $this->document->category,
            'days_until_expiry' => $this->daysUntilExpiry,
            'is_expired' => $this->isExpired,
            'expires_at' => $this->document->expires_at->toISOString(),
            'status' => $this->document->status,
            'url' => url("/documents/{$this->document->id}"),
            'urgency' => $this->getUrgencyLevel(),
            'icon' => $this->isExpired ? '⚠️' : '⏰',
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }

    /**
     * Get urgency level based on days until expiry.
     */
    protected function getUrgencyLevel(): string
    {
        if ($this->isExpired) {
            return '🔴';
        }

        if ($this->daysUntilExpiry <= 3) {
            return '🔴 URGENT:';
        }

        if ($this->daysUntilExpiry <= 7) {
            return '🟡 WARNING:';
        }

        if ($this->daysUntilExpiry <= 30) {
            return '🔔 REMINDER:';
        }

        return '📌 INFO:';
    }
}
