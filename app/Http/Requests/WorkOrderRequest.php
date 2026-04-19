<?php

namespace App\Http\Requests;

use App\Models\WorkOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class WorkOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'number'          => ['nullable', 'string', 'max:100'],
            'product_id'      => ['nullable', 'integer', 'exists:products,id'],
            'quantity'        => ['required', 'numeric', 'min:0'],
            'planned_start'   => ['nullable', 'date'],
            'planned_end'     => ['nullable', 'date'],
            'actual_start'    => ['nullable', 'date'],
            'actual_end'      => ['nullable', 'date'],
            'status'          => ['required', Rule::in(WorkOrder::STATUSES)],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }
}
