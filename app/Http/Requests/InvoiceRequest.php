<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'customer_id'   => ['required', 'integer', 'exists:customers,id'],
            'sales_order_id' => ['nullable', 'integer', 'exists:sales_orders,id'],
            'number'        => ['nullable', 'string', 'max:100'],
            'due_date'      => ['required', 'date'],
            'total_amount'  => ['required', 'numeric', 'min:0'],
            'status'        => ['required', Rule::in(Invoice::STATUSES)],
            'notes'         => ['nullable', 'string', 'max:1000'],
            'currency_code' => ['nullable', 'string', 'max:10'],
            'currency_rate' => ['nullable', 'numeric', 'min:0'],
        ];
    }
}
