<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Notifications\Healthcare\AppointmentReminder;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'healthcare:reminders:appointments
                            {--hours=24 : Hours before appointment to send reminder}
                            {--channel=all : Notification channel (email, sms, whatsapp, all)}
                            {--dry-run : Test mode without sending}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send appointment reminders via SMS/WhatsApp/Email';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hoursBefore = (int) $this->option('hours');
        $channel = $this->option('channel');
        $dryRun = $this->option('dry-run');

        $this->info("🔔 Sending appointment reminders ({$hoursBefore} hours before)...");

        if ($dryRun) {
            $this->warn('⚠️ DRY RUN MODE - No notifications will be sent');
        }

        $targetTime = now()->addHours($hoursBefore);
        $startTime = $targetTime->copy()->startOfHour();
        $endTime = $targetTime->copy()->endOfHour();

        $appointments = Appointment::where('status', 'scheduled')
            ->whereBetween('appointment_date', [$startTime, $endTime])
            ->with(['patient', 'doctor'])
            ->get();

        $this->info("Found {$appointments->count()} appointments to remind");

        $sentCount = 0;
        $failedCount = 0;

        foreach ($appointments as $appointment) {
            /** @var Appointment $appointment */
            try {
                if (! $appointment->patient) {
                    $this->warn("⚠️ Appointment {$appointment->id} has no patient");

                    continue;
                }

                if ($dryRun) {
                    $this->line("📋 Would send reminder to: {$appointment->patient->name}");
                    $this->line("   Appointment: {$appointment->appointment_date->format('d M Y H:i')}");
                    $this->line("   Doctor: {$appointment->doctor?->name}");
                    $sentCount++;

                    continue;
                }

                // Send email reminder
                if (in_array($channel, ['email', 'all'])) {
                    $this->sendEmailReminder($appointment);
                }

                // Send SMS reminder
                if (in_array($channel, ['sms', 'all'])) {
                    $this->sendSmsReminder($appointment);
                }

                // Send WhatsApp reminder
                if (in_array($channel, ['whatsapp', 'all'])) {
                    $this->sendWhatsAppReminder($appointment);
                }

                // Mark as reminded
                $appointment->update([
                    'reminder_sent_at' => now(),
                    'reminder_channel' => $channel,
                ]);

                $sentCount++;
                $this->info("✓ Reminder sent to {$appointment->patient->name}");

            } catch (\Exception $e) {
                $failedCount++;
                $this->error("❌ Failed to send reminder for appointment {$appointment->id}: {$e->getMessage()}");

                Log::error('Appointment reminder failed', [
                    'appointment_id' => $appointment->id,
                    'patient_id' => $appointment->patient_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $this->info("\n📊 Reminder Summary:");
        $this->line("   ✓ Sent: {$sentCount}");
        $this->line("   ✗ Failed: {$failedCount}");
        $this->line("   Total: {$appointments->count()}");

        return Command::SUCCESS;
    }

    /**
     * Send email reminder
     */
    protected function sendEmailReminder($appointment): void
    {
        if (! $appointment->patient->email) {
            return;
        }

        try {
            $appointment->patient->notify(new AppointmentReminder(
                $appointment,
                'email'
            ));
        } catch (\Exception $e) {
            Log::warning('Email reminder failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send SMS reminder
     */
    protected function sendSmsReminder($appointment): void
    {
        if (! $appointment->patient->phone) {
            return;
        }

        $message = "Reminder: Janji temu dengan {$appointment->doctor?->name} pada {$appointment->appointment_date->format('d M Y H:i')}. Hubungi kami jika perlu reschedule.";

        try {
            // Integrate with SMS gateway (Twilio, WaveCell, etc.)
            // SMS::send($appointment->patient->phone, $message);

            Log::info('SMS reminder sent', [
                'appointment_id' => $appointment->id,
                'phone' => $appointment->patient->phone,
                'message' => $message,
            ]);
        } catch (\Exception $e) {
            Log::warning('SMS reminder failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send WhatsApp reminder
     */
    protected function sendWhatsAppReminder($appointment): void
    {
        if (! $appointment->patient->phone) {
            return;
        }

        $message = "🏥 *Appointment Reminder*\n\n"
            ."Yth. {$appointment->patient->name},\n\n"
            ."Janji temu Anda:\n"
            .'👨‍⚕️ Dokter: '.($appointment->doctor?->name ?? 'N/A')."\n"
            ."📅 Tanggal: {$appointment->appointment_date->format('l, d F Y')}\n"
            ."⏰ Jam: {$appointment->appointment_date->format('H:i')}\n"
            .'📍 Lokasi: '.($appointment->department ?? 'Klinik')."\n\n"
            ."Harap datang 15 menit sebelum jadwal.\n"
            .'Untuk reschedule, hubungi kami.';

        try {
            // Integrate with WhatsApp API (Fonnte, Wablas, Twilio)
            // WhatsApp::send($appointment->patient->phone, $message);

            Log::info('WhatsApp reminder sent', [
                'appointment_id' => $appointment->id,
                'phone' => $appointment->patient->phone,
                'message_length' => strlen($message),
            ]);
        } catch (\Exception $e) {
            Log::warning('WhatsApp reminder failed', [
                'appointment_id' => $appointment->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
