<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-file-invoice-dollar text-blue-600"></i> My Billing History
            </h1>
            <p class="text-gray-500">View your invoices and payment history</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-400">
                <div class="p-5 text-center">
                    <h3 class="text-blue-600">
                        Rp {{ number_format(($stats['total_billed'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-gray-500">Total Billed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">
                        Rp {{ number_format(($stats['total_paid'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-gray-500">Total Paid</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">
                        Rp {{ number_format(($stats['outstanding'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-gray-500">Outstanding</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">
                        Rp {{ number_format(($stats['overdue'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-gray-500">Overdue</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Invoices & Payments</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Date</th>
                                    <th>Description</th>
                                    <th>Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td>
                                            <strong>{{ $invoice->invoice_number ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <small>{{ $invoice->invoice_date->format('d/m/Y') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ Str::limit($invoice->description ?? '-', 50) }}</small>
                                        </td>
                                        <td>
                                            <strong>Rp
                                                {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="text-emerald-600">
                                                Rp {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $balance = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                                            @endphp
                                            <strong class="{{ $balance > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                                Rp {{ number_format($balance, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if ($invoice->status == 'paid')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Paid
                                                </span>
                                            @elseif($invoice->status == 'partial')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    <i class="fas fa-clock"></i> Partial
                                                </span>
                                            @elseif($invoice->status == 'unpaid')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times-circle"></i> Unpaid
                                                </span>
                                            @elseif($invoice->status == 'overdue')
                                                <span class="badge bg-gray-900">
                                                    <i class="fas fa-exclamation-triangle"></i> Overdue
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($invoice->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                    data-bs-target="#invoiceDetailModal{{ $invoice->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Download"
                                                    onclick="window.print()">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                @if ($balance > 0)
                                                    <button class="px-3 py-1.5 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-xs transition" title="Pay Now">
                                                        <i class="fas fa-credit-bg-white rounded-2xl border border-gray-200"></i> Pay
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- Invoice Detail Modal -->
                                    <div class="modal fade" id="invoiceDetailModal{{ $invoice->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Invoice Details</h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Invoice Number:</strong>
                                                            <p>{{ $invoice->invoice_number ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Invoice Date:</strong>
                                                            <p>{{ $invoice->invoice_date->format('d/m/Y') ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                @if ($invoice->status == 'paid')
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Paid</span>
                                                                @elseif($invoice->status == 'partial')
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Partial</span>
                                                                @elseif($invoice->status == 'overdue')
                                                                    <span class="badge bg-gray-900">Overdue</span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Unpaid</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Due Date:</strong>
                                                            <p>{{ $invoice->due_date->format('d/m/Y') ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Invoice Items:</strong>
                                                        <table class="w-full text-sm text-left mt-2">
                                                            <thead>
                                                                <tr>
                                                                    <th>Item</th>
                                                                    <th>Qty</th>
                                                                    <th>Price</th>
                                                                    <th>Total</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @forelse($invoice->items ?? [] as $item)
                                                                    <tr>
                                                                        <td>{{ $item['name'] ?? '-' }}</td>
                                                                        <td>{{ $item['quantity'] ?? 1 }}</td>
                                                                        <td>Rp
                                                                            {{ number_format($item['price'] ?? 0, 0, ',', '.') }}
                                                                        </td>
                                                                        <td>Rp
                                                                            {{ number_format($item['total'] ?? 0, 0, ',', '.') }}
                                                                        </td>
                                                                    </tr>
                                                                @empty
                                                                    <tr>
                                                                        <td colspan="4" class="text-center text-gray-400">No
                                                                            items</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Subtotal:</strong>
                                                            <p>Rp {{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}
                                                            </p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Tax:</strong>
                                                            <p>Rp {{ number_format($invoice->tax ?? 0, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Total Amount:</strong>
                                                            <p class="text-blue-600">
                                                                <strong>Rp
                                                                    {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Paid Amount:</strong>
                                                            <p class="text-emerald-600">
                                                                <strong>Rp
                                                                    {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No billing history available</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($invoices) && $invoices->hasPages())
                        <div class="mt-3">
                            {{ $invoices->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
