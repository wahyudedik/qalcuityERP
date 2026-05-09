<?php

namespace App\Notifications\Construction;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class DailyReportApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $report;

    public function __construct($report)
    {
        $this->report = $report;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Daily Site Report Approved')
            ->greeting("Hello {$this->report->reportedBy->name},")
            ->line('Your daily site report has been approved.')
            ->line("**Project:** {$this->report->project->name}")
            ->line("**Date:** {$this->report->report_date->format('d F Y')}")
            ->line("**Progress:** {$this->report->progress_percentage}%")
            ->line("**Approved by:** {$this->report->approvedBy->name}")
            ->action('View Report', url("/construction/reports/{$this->report->id}"))
            ->salutation('Regards, QalcuityERP Construction Module');
    }

    public function toArray($notifiable): array
    {
        return [
            'report_id' => $this->report->id,
            'project_name' => $this->report->project->name,
            'report_date' => $this->report->report_date->format('Y-m-d'),
            'approved_by' => $this->report->approvedBy->name,
            'message' => 'Daily site report approved',
        ];
    }
}
