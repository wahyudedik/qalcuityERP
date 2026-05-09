<?php

namespace App\Console\Commands;

use App\Models\Teleconsultation;
use App\Services\TelemedicineReminderService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendTelemedicineReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'telemedicine:send-reminders {--minutes=30 : Minutes before consultation to send reminder}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send telemedicine consultation reminders to doctors and patients';

    protected $reminderService;

    /**
     * Create a new command instance.
     */
    public function __construct(TelemedicineReminderService $reminderService)
    {
        parent::__construct();
        $this->reminderService = $reminderService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $minutesBefore = $this->option('minutes');

        $this->info("Sending telemedicine reminders for consultations in the next {$minutesBefore} minutes...");

        $consultations = Teleconsultation::with(['patient', 'doctor'])
            ->where('status', 'scheduled')
            ->where('scheduled_time', '>=', now())
            ->where('scheduled_time', '<=', now()->addMinutes($minutesBefore))
            ->get();

        if ($consultations->isEmpty()) {
            $this->info('No consultations found that need reminders.');

            return 0;
        }

        $this->info("Found {$consultations->count()} consultation(s) to remind.");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($consultations as $consultation) {
            try {
                $this->line("Sending reminder for consultation #{$consultation->consultation_number}...");

                $success = $this->reminderService->sendReminder($consultation);

                if ($success) {
                    $sentCount++;
                    $this->info('✓ Reminder sent successfully');
                } else {
                    $failedCount++;
                    $this->warn('✗ Failed to send reminder');
                }
            } catch (\Exception $e) {
                $failedCount++;
                $this->error('✗ Error: '.$e->getMessage());
                Log::error('Telemedicine reminder failed', [
                    'consultation_id' => $consultation->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->newLine();
        $this->info('Reminder Summary:');
        $this->line("✓ Sent: {$sentCount}");
        $this->line("✗ Failed: {$failedCount}");
        $this->line("Total: {$consultations->count()}");

        return 0;
    }
}
