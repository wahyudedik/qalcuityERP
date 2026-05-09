<?php

namespace App\Http\Controllers\Api;

use App\Models\Invoice;
use Illuminate\Http\Request;

class ApiInvoiceController extends ApiBaseController
{
    public function index(Request $request)
    {
        $invoices = Invoice::where('tenant_id', $this->tenantId())
            ->with('customer')
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->overdue, fn ($q) => $q->where('due_date', '<', today())->whereIn('status', ['unpaid', 'partial']))
            ->latest()
            ->paginate(50);

        return $this->ok($invoices);
    }

    public function show(int $id)
    {
        $invoice = Invoice::where('tenant_id', $this->tenantId())
            ->with(['customer', 'installments'])
            ->findOrFail($id);

        return $this->ok($invoice);
    }
}
