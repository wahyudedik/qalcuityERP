<?php

namespace App\Notifications\Construction;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportSubmittedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $report;
    protected $approver;

    public function __construct($report, $approver)
    {
        $this->report = $report;
        $this->approver = $approver;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Daily Site Report Submitted for Approval')
            ->greeting("Hello {$this->approver->name},")
            ->line("A new daily site report has been submitted for your approval.")
            ->line("**Project:** {$this->report->project->name}")
            ->line("**Date:** {$this->report->report_date->format('d F Y')}")
            ->line("**Progress:** {$this->report->progress_percentage}%")
            ->line("**Reported by:** {$this->report->reportedBy->name}")
            ->action('Review Report', url("/construction/reports/{$this->report->id}"))
            ->line('Please review and approve or reject this report.')
            ->salutation('Regards, QalcuityERP Construction Module');
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'project_name' => $this->report->project->name,
            'report_date' => $this->report->report_date->format('Y-m-d'),
            'submitted_by' => $this->report->reportedBy->name,
            'message' => 'Daily site report submitted for approval',
        ];
    }
}
