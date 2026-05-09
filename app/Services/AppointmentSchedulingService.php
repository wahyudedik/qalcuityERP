<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\MedicalStaffSchedule;
use App\Models\Patient;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AppointmentSchedulingService
{
    /**
     * Book new appointment with conflict detection
     */
    public function bookAppointment(array $data): Appointment
    {
        return DB::transaction(function () use ($data) {
            $patient = Patient::findOrFail($data['patient_id']);
            $doctor = Doctor::findOrFail($data['doctor_id']);
            $appointmentDate = Carbon::parse($data['appointment_date']);
            $appointmentTime = $data['appointment_time'];

            // Validate doctor availability
            $this->validateDoctorAvailability($doctor, $appointmentDate, $appointmentTime);

            // Check for conflicts
            $this->checkForConflicts($doctor, $appointmentDate, $appointmentTime, $data['appointment_type'] ?? 'consultation');

            // Get or create schedule
            $schedule = $this->getOrCreateSchedule($doctor, $appointmentDate, $data);

            // Create appointment
            $appointment = Appointment::create([
                'patient_id' => $patient->id,
                'doctor_id' => $doctor->id,
                'department_id' => $data['department_id'] ?? null,
                'schedule_id' => $schedule?->id,
                'created_by' => $data['created_by'] ?? null,
                'appointment_type' => $data['appointment_type'] ?? 'consultation',
                'visit_type' => $data['visit_type'] ?? 'return_patient',
                'appointment_date' => $appointmentDate,
                'appointment_time' => $appointmentTime,
                'estimated_duration' => $data['estimated_duration'] ?? 30,
                'reason_for_visit' => $data['reason_for_visit'] ?? null,
                'symptoms' => $data['symptoms'] ?? null,
                'special_requests' => $data['special_requests'] ?? null,
                'is_urgent' => $data['is_urgent'] ?? false,
                'notification_method' => $data['notification_method'] ?? 'sms',
                'notes' => $data['notes'] ?? null,
            ]);

            // Update schedule if exists
            if ($schedule) {
                $schedule->bookSlot();
            }

            // Send notification
            $this->sendAppointmentNotification($appointment, 'created');

            return $appointment;
        });
    }

    /**
     * Validate doctor availability
     */
    protected function validateDoctorAvailability(Doctor $doctor, Carbon $date, string $time): void
    {
        // Check doctor status
        if ($doctor->status !== 'active') {
            throw new Exception('Doctor is not active');
        }

        if (! $doctor->accepting_patients) {
            throw new Exception('Doctor is not accepting patients');
        }

        // Check if date is in the past
        if ($date->lt(Carbon::today())) {
            throw new Exception('Cannot book appointment in the past');
        }

        // Check practice days
        if ($doctor->practice_days) {
            $dayOfWeek = strtolower($date->format('l'));
            if (! in_array($dayOfWeek, $doctor->practice_days)) {
                throw new Exception("Doctor does not practice on {$dayOfWeek}");
            }
        }

        // Check practice hours
        if ($doctor->practice_start_time && $doctor->practice_end_time) {
            $appointmentTime = Carbon::parse($time);
            $startTime = Carbon::parse($doctor->practice_start_time);
            $endTime = Carbon::parse($doctor->practice_end_time);

            if ($appointmentTime->lt($startTime) || $appointmentTime->gt($endTime)) {
                throw new Exception('Appointment time is outside doctor\'s practice hours');
            }
        }
    }

    /**
     * Check for scheduling conflicts
     */
    protected function checkForConflicts(Doctor $doctor, Carbon $date, string $time, string $type): void
    {
        $appointmentTime = Carbon::parse($time);

        // Check existing appointments at the same time
        $conflictingAppointment = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress'])
            ->first();

        if ($conflictingAppointment) {
            throw new Exception('Time slot already booked. Please choose another time.');
        }

        // Check if doctor has another appointment within the duration window
        $existingAppointments = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress'])
            ->get();

        foreach ($existingAppointments as $existing) {
            $existingTime = Carbon::parse($existing->appointment_time);
            $existingEnd = $existingTime->copy()->addMinutes($existing->estimated_duration ?? 30);
            $newEnd = $appointmentTime->copy()->addMinutes(30); // Default 30 minutes

            // Check overlap
            if (
                $appointmentTime->between($existingTime, $existingEnd) ||
                $newEnd->between($existingTime, $existingEnd) ||
                ($appointmentTime->lte($existingTime) && $newEnd->gte($existingEnd))
            ) {
                throw new Exception('Time conflict with existing appointment');
            }
        }
    }

    /**
     * Get or create schedule for the date
     */
    protected function getOrCreateSchedule(Doctor $doctor, Carbon $date, array $data): ?MedicalStaffSchedule
    {
        $schedule = MedicalStaffSchedule::where('doctor_id', $doctor->id)
            ->whereDate('schedule_date', $date)
            ->first();

        if (! $schedule) {
            // Auto-create schedule based on doctor's practice settings
            if ($doctor->practice_start_time && $doctor->practice_end_time) {
                $schedule = MedicalStaffSchedule::create([
                    'doctor_id' => $doctor->id,
                    'schedule_date' => $date,
                    'start_time' => $doctor->practice_start_time,
                    'end_time' => $doctor->practice_end_time,
                    'slot_duration' => 30,
                    'max_appointments' => 0, // Unlimited by default
                    'location' => $data['location'] ?? null,
                    'location_details' => $data['location_details'] ?? null,
                    'schedule_type' => $data['schedule_type'] ?? 'regular',
                ]);
            }
        }

        return $schedule;
    }

    /**
     * Cancel appointment
     */
    public function cancelAppointment(Appointment $appointment, string $reason, $cancelledBy = null): Appointment
    {
        return DB::transaction(function () use ($appointment, $reason, $cancelledBy) {
            $appointment->cancel($reason, $cancelledBy);

            // Free up schedule slot
            if ($appointment->schedule_id) {
                $schedule = MedicalStaffSchedule::find($appointment->schedule_id);
                if ($schedule) {
                    $schedule->cancelBooking();
                }
            }

            // Send cancellation notification
            $this->sendAppointmentNotification($appointment, 'cancelled');

            return $appointment;
        });
    }

    /**
     * Reschedule appointment
     */
    public function rescheduleAppointment(Appointment $appointment, array $newData): Appointment
    {
        return DB::transaction(function () use ($appointment, $newData) {
            $doctor = $appointment->doctor;
            $newDate = Carbon::parse($newData['appointment_date']);
            $newTime = $newData['appointment_time'];

            // Validate new time
            $this->validateDoctorAvailability($doctor, $newDate, $newTime);

            // Check conflicts (exclude current appointment)
            $this->checkForConflicts($doctor, $newDate, $newTime, $appointment->appointment_type);

            // Free old schedule slot
            if ($appointment->schedule_id) {
                $oldSchedule = MedicalStaffSchedule::find($appointment->schedule_id);
                if ($oldSchedule) {
                    $oldSchedule->cancelBooking();
                }
            }

            // Get or create new schedule
            $newSchedule = $this->getOrCreateSchedule($doctor, $newDate, $newData);

            // Update appointment
            $appointment->update([
                'appointment_date' => $newDate,
                'appointment_time' => $newTime,
                'schedule_id' => $newSchedule?->id,
                'estimated_duration' => $newData['estimated_duration'] ?? $appointment->estimated_duration,
                'status' => 'rescheduled',
            ]);

            // Book new schedule slot
            if ($newSchedule) {
                $newSchedule->bookSlot();
            }

            // Create new appointment for the rescheduled date
            $newAppointment = $appointment->replicate();
            $newAppointment->appointment_date = $newDate;
            $newAppointment->appointment_time = $newTime;
            $newAppointment->schedule_id = $newSchedule?->id;
            $newAppointment->status = 'scheduled';
            $newAppointment->rescheduled_to_id = null;
            $newAppointment->save();

            // Link old appointment to new one
            $appointment->update(['rescheduled_to_id' => $newAppointment->id]);

            // Send notification
            $this->sendAppointmentNotification($newAppointment, 'rescheduled');

            return $newAppointment;
        });
    }

    /**
     * Get available time slots for doctor on specific date
     */
    public function getAvailableSlots(Doctor $doctor, string $date): array
    {
        $date = Carbon::parse($date);
        $slots = [];

        // Get doctor's practice hours
        $startTime = Carbon::parse($doctor->practice_start_time ?? '09:00');
        $endTime = Carbon::parse($doctor->practice_end_time ?? '17:00');
        $slotDuration = 30; // minutes

        // Get existing appointments
        $bookedTimes = Appointment::where('doctor_id', $doctor->id)
            ->whereDate('appointment_date', $date)
            ->whereIn('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress'])
            ->pluck('appointment_time')
            ->toArray();

        // Generate available slots
        $currentTime = $startTime->copy();
        while ($currentTime < $endTime) {
            $timeString = $currentTime->format('H:i');

            if (! in_array($timeString, $bookedTimes)) {
                $slots[] = [
                    'time' => $timeString,
                    'available' => true,
                ];
            } else {
                $slots[] = [
                    'time' => $timeString,
                    'available' => false,
                ];
            }

            $currentTime->addMinutes($slotDuration);
        }

        return $slots;
    }

    /**
     * Get doctor's schedule for date range
     */
    public function getDoctorSchedule(Doctor $doctor, string $startDate, string $endDate)
    {
        return MedicalStaffSchedule::where('doctor_id', $doctor->id)
            ->whereBetween('schedule_date', [$startDate, $endDate])
            ->with('appointments')
            ->orderBy('schedule_date')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Send appointment notification
     */
    protected function sendAppointmentNotification(Appointment $appointment, string $type): void
    {
        // This will be implemented with actual SMS/WhatsApp/Email service
        // For now, just log the notification requirement

        $patient = $appointment->patient;
        $doctor = $appointment->doctor;

        $notificationData = [
            'type' => $type,
            'patient_name' => $patient->full_name,
            'patient_phone' => $patient->phone_primary,
            'patient_email' => $patient->email,
            'doctor_name' => $doctor->full_name,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => $appointment->appointment_time,
            'notification_method' => $appointment->notification_method,
        ];

        // Log notification (implement actual sending later)
        Log::info('Appointment notification required', $notificationData);

        // TODO: Implement actual notification sending
        // - SMS notification
        // - WhatsApp notification
        // - Email notification
    }

    /**
     * Send reminder for upcoming appointments
     */
    public function sendRemindersForToday(): int
    {
        $sent = 0;

        // Get appointments needing 24-hour reminder
        $appointments24h = Appointment::needs24HourReminder()->get();
        foreach ($appointments24h as $appointment) {
            $this->sendAppointmentNotification($appointment, 'reminder_24h');
            $appointment->sendReminder(24);
            $sent++;
        }

        // Get appointments needing 1-hour reminder
        $appointments1h = Appointment::needs1HourReminder()->get();
        foreach ($appointments1h as $appointment) {
            $this->sendAppointmentNotification($appointment, 'reminder_1h');
            $appointment->sendReminder(1);
            $sent++;
        }

        return $sent;
    }

    /**
     * Check if patient has conflict (double booking)
     */
    public function checkPatientConflict(Patient $patient, Carbon $date, string $time): bool
    {
        $conflict = Appointment::where('patient_id', $patient->id)
            ->whereDate('appointment_date', $date)
            ->where('appointment_time', $time)
            ->whereIn('status', ['scheduled', 'confirmed', 'checked_in', 'in_progress'])
            ->exists();

        return $conflict;
    }

    /**
     * Get appointment statistics for doctor
     */
    public function getDoctorStatistics(Doctor $doctor, string $startDate, string $endDate): array
    {
        $appointments = Appointment::where('doctor_id', $doctor->id)
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->get();

        return [
            'total_appointments' => $appointments->count(),
            'completed' => $appointments->where('status', 'completed')->count(),
            'cancelled' => $appointments->where('status', 'cancelled')->count(),
            'no_show' => $appointments->where('status', 'no_show')->count(),
            'upcoming' => $appointments->where('status', 'scheduled')->count(),
            'confirmed' => $appointments->where('status', 'confirmed')->count(),
            'completion_rate' => $appointments->count() > 0
                ? round(($appointments->where('status', 'completed')->count() / $appointments->count()) * 100, 2)
                : 0,
        ];
    }
}
