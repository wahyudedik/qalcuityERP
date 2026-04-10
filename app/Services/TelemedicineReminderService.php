<?php

namespace App\Services;

use App\Models\Teleconsultation;
use App\Models\TelemedicineSetting;
use App\Notifications\TelemedicineReminderNotification;
use Illuminate\Support\Facades\Log;

class TelemedicineReminderService
{
    /**
     * Send reminder for consultation.
     */
    public function sendReminder(Teleconsultation $consultation): bool
    {
        $settings = TelemedicineSetting::getForTenant($consultation->patient?->tenant_id ?? 1);

        if (!$settings->reminder_enabled) {
            Log::info('Reminders disabled for tenant', ['tenant_id' => $consultation->patient?->tenant_id]);
            return false;
        }

        $sentDoctor = false;
        $sentPatient = false;

        // Send to doctor
        if ($settings->send_email_reminder) {
            $sentDoctor = $this->sendDoctorReminder($consultation);
        }

        // Send to patient
        if ($settings->send_email_reminder) {
            $sentPatient = $this->sendPatientReminder($consultation);
        }

        Log::info('Reminders sent', [
            'consultation_id' => $consultation->id,
            'doctor_reminded' => $sentDoctor,
            'patient_reminded' => $sentPatient,
        ]);

        return $sentDoctor || $sentPatient;
    }

    /**
     * Send reminder to doctor.
     */
    public function sendDoctorReminder(Teleconsultation $consultation): bool
    {
        try {
            $doctor = $consultation->doctor;
            if (!$doctor || !$doctor->user) {
                Log::warning('Doctor or user not found', ['consultation_id' => $consultation->id]);
                return false;
            }

            $doctor->user->notify(new TelemedicineReminderNotification($consultation, 'doctor'));

            Log::info('Doctor reminder sent', [
                'consultation_id' => $consultation->id,
                'doctor_id' => $consultation->doctor_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send doctor reminder', [
                'error' => $e->getMessage(),
                'consultation_id' => $consultation->id,
            ]);
            return false;
        }
    }

    /**
     * Send reminder to patient.
     */
    public function sendPatientReminder(Teleconsultation $consultation): bool
    {
        try {
            $patient = $consultation->patient;
            if (!$patient || !$patient->user) {
                Log::warning('Patient or user not found', ['consultation_id' => $consultation->id]);
                return false;
            }

            $patient->user->notify(new TelemedicineReminderNotification($consultation, 'patient'));

            Log::info('Patient reminder sent', [
                'consultation_id' => $consultation->id,
                'patient_id' => $consultation->patient_id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send patient reminder', [
                'error' => $e->getMessage(),
                'consultation_id' => $consultation->id,
            ]);
            return false;
        }
    }

    /**
     * Schedule reminders for consultation.
     * This would typically be called when consultation is booked.
     */
    public function scheduleReminders(Teleconsultation $consultation): void
    {
        $settings = TelemedicineSetting::getForTenant($consultation->patient?->tenant_id ?? 1);

        if (!$settings->reminder_enabled) {
            return;
        }

        // Reminders are handled by the scheduled console command
        // This method is just for logging/setup
        Log::info('Reminders scheduled for consultation', [
            'consultation_id' => $consultation->id,
            'reminder_minutes_before' => $settings->reminder_minutes_before,
        ]);
    }

    /**
     * Cancel reminders for consultation.
     */
    public function cancelReminders(Teleconsultation $consultation): void
    {
        // Cancel any pending notifications
        Log::info('Reminders cancelled for consultation', [
            'consultation_id' => $consultation->id,
        ]);
    }
}
