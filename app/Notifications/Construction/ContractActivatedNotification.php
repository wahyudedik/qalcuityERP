<?php

namespace App\Notifications\Construction;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ContractActivatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $contract;

    public function __construct($contract)
    {
        $this->contract = $contract;
    }

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Subcontractor Contract Activated')
            ->greeting("Hello {$this->contract->subcontractor->contact_person},")
            ->line('Your contract has been activated.')
            ->line("**Contract Number:** {$this->contract->contract_number}")
            ->line("**Project:** {$this->contract->project->name}")
            ->line('**Contract Value:** Rp '.number_format($this->contract->contract_value, 0, ',', '.'))
            ->line("**Period:** {$this->contract->start_date->format('d M Y')} - {$this->contract->end_date->format('d M Y')}")
            ->action('View Contract', url("/construction/subcontractors/{$this->contract->subcontractor_id}"))
            ->salutation('Regards, QalcuityERP Construction Module');
    }

    public function toArray($notifiable): array
    {
        return [
            'contract_id' => $this->contract->id,
            'contract_number' => $this->contract->contract_number,
            'project_name' => $this->contract->project->name,
            'contract_value' => $this->contract->contract_value,
            'message' => 'Subcontractor contract activated',
        ];
    }
}
