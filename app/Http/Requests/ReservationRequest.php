<?php

namespace App\Http\Requests;

use App\Models\Reservation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReservationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'guest_id'      => ['nullable', 'integer'],
            'room_id'       => ['required', 'integer', 'exists:rooms,id'],
            'check_in'      => ['required', 'date'],
            'check_out'     => ['required', 'date', 'after:check_in'],
            'adults'        => ['nullable', 'integer', 'min:1'],
            'children'      => ['nullable', 'integer', 'min:0'],
            'status'        => ['required', Rule::in(Reservation::STATUSES)],
            'notes'         => ['nullable', 'string', 'max:1000'],
            'special_requests' => ['nullable', 'string', 'max:500'],
            'total_amount'  => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
