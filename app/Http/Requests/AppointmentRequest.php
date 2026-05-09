<?php

namespace App\Http\Requests;

use App\Models\Appointment;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['nullable', 'integer'],
            'doctor_id' => ['nullable', 'integer'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['nullable', 'string', 'max:10'],
            'type' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', Rule::in(Appointment::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'special_requests' => ['nullable', 'string', 'max:500'],
        ];
    }
}
