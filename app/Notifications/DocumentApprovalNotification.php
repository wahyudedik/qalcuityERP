<?php

namespace App\Notifications;

use App\Models\Document;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DocumentApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected Document $document;
    protected string $action;
    protected string $comments;

    /**
     * Create a new notification instance.
     */
    public function __construct(Document $document, string $action, string $comments = '')
    {
        $this->document = $document;
        $this->action = $action;
        $this->comments = $comments;
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
        $subject = match ($this->action) {
            'pending_approval' => "Document Requires Your Approval: {$this->document->title}",
            'approved' => "Document Approved: {$this->document->title}",
            'rejected' => "Document Rejected: {$this->document->title}",
            default => "Document Update: {$this->document->title}",
        };

        $greeting = match ($this->action) {
            'pending_approval' => 'A document requires your approval',
            'approved' => 'Your document has been approved',
            'rejected' => 'Your document has been rejected',
            default => 'Document status update',
        };

        $category = $this->document->category ?? 'General';
        $status = ucfirst(str_replace('_', ' ', $this->document->status));
        $uploaderName = $this->document->uploader?->name ?? 'Unknown';

        $mailMessage = (new MailMessage)
            ->subject($subject)
            ->greeting($greeting)
            ->line("Document: {$this->document->title}")
            ->line("Category: {$category}")
            ->line("Status: {$status}");

        if ($this->comments) {
            $mailMessage->line("Comments: {$this->comments}");
        }

        if ($this->action === 'pending_approval') {
            $mailMessage->action('Review Document', url("/documents/{$this->document->id}/approval"))
                ->line('Please review and approve or reject this document at your earliest convenience.');
        } elseif ($this->action === 'approved') {
            $mailMessage->success()
                ->line('The document has completed the approval process.');
        } elseif ($this->action === 'rejected') {
            $mailMessage->error()
                ->line('Please review the comments and make necessary changes.')
                ->action('View Document', url("/documents/{$this->document->id}"));
        }

        $mailMessage->line("Uploaded by: {$uploaderName}")
            ->line("Version: {$this->document->version}")
            ->line('Thank you for using our document management system!');

        return $mailMessage;
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
            'action' => $this->action,
            'status' => $this->document->status,
            'comments' => $this->comments,
            'version' => $this->document->version,
            'url' => url("/documents/{$this->document->id}/approval"),
            'icon' => match ($this->action) {
                'pending_approval' => '⏳',
                'approved' => '✅',
                'rejected' => '❌',
                default => '📄',
            },
        ];
    }

    /**
     * Get the database representation of the notification.
     */
    public function toDatabase(object $notifiable): array
    {
        return $this->toArray($notifiable);
    }
}
