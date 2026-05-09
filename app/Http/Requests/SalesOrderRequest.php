<?php

namespace App\Http\Requests;

use App\Models\SalesOrder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SalesOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'quotation_id' => ['nullable', 'integer', 'exists:quotations,id'],
            'number' => ['nullable', 'string', 'max:100'],
            'date' => ['required', 'date'],
            'delivery_date' => ['nullable', 'date'],
            'due_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(SalesOrder::STATUSES)],
            'notes' => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_rate' => ['nullable', 'numeric', 'min:0'],
            'subtotal' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'tax' => ['nullable', 'numeric', 'min:0'],
            'total' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
