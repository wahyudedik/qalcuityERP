<?php

namespace App\Http\Requests;

use App\Models\Employee;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EmployeeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'           => ['required', 'string', 'max:255'],
            'employee_id'    => ['nullable', 'string', 'max:50'],
            'email'          => ['nullable', 'email', 'max:255'],
            'phone'          => ['nullable', 'string', 'max:20'],
            'department_id'  => ['nullable', 'integer'],
            'position_id'    => ['nullable', 'integer'],
            'join_date'      => ['nullable', 'date'],
            'status'         => ['required', Rule::in(Employee::STATUSES)],
            'notes'          => ['nullable', 'string', 'max:1000'],
        ];
    }
}
