@extends('layouts.app')

@section('title', 'Billing History')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> My Billing History
            </h1>
            <p class="text-muted mb-0">View your invoices and payment history</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary">
                        Rp {{ number_format(($stats['total_billed'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-muted">Total Billed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">
                        Rp {{ number_format(($stats['total_paid'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-muted">Total Paid</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">
                        Rp {{ number_format(($stats['outstanding'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-muted">Outstanding</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">
                        Rp {{ number_format(($stats['overdue'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-muted">Overdue</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Invoices & Payments</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
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
                                            <span class="text-success">
                                                Rp {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $balance = ($invoice->total_amount ?? 0) - ($invoice->paid_amount ?? 0);
                                            @endphp
                                            <strong class="{{ $balance > 0 ? 'text-danger' : 'text-success' }}">
                                                Rp {{ number_format($balance, 0, ',', '.') }}
                                            </strong>
                                        </td>
                                        <td>
                                            @if ($invoice->status == 'paid')
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Paid
                                                </span>
                                            @elseif($invoice->status == 'partial')
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Partial
                                                </span>
                                            @elseif($invoice->status == 'unpaid')
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Unpaid
                                                </span>
                                            @elseif($invoice->status == 'overdue')
                                                <span class="badge bg-dark">
                                                    <i class="fas fa-exclamation-triangle"></i> Overdue
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">{{ ucfirst($invoice->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#invoiceDetailModal{{ $invoice->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-sm btn-success" title="Download"
                                                    onclick="window.print()">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                                @if ($balance > 0)
                                                    <button class="btn btn-sm btn-primary" title="Pay Now">
                                                        <i class="fas fa-credit-card"></i> Pay
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
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Invoice Number:</strong>
                                                            <p>{{ $invoice->invoice_number ?? '-' }}</p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Invoice Date:</strong>
                                                            <p>{{ $invoice->invoice_date->format('d/m/Y') ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                @if ($invoice->status == 'paid')
                                                                    <span class="badge bg-success">Paid</span>
                                                                @elseif($invoice->status == 'partial')
                                                                    <span class="badge bg-warning">Partial</span>
                                                                @elseif($invoice->status == 'overdue')
                                                                    <span class="badge bg-dark">Overdue</span>
                                                                @else
                                                                    <span class="badge bg-danger">Unpaid</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Due Date:</strong>
                                                            <p>{{ $invoice->due_date->format('d/m/Y') ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Invoice Items:</strong>
                                                        <table class="table table-sm mt-2">
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
                                                                        <td colspan="4" class="text-center text-muted">No
                                                                            items</td>
                                                                    </tr>
                                                                @endforelse
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Subtotal:</strong>
                                                            <p>Rp {{ number_format($invoice->subtotal ?? 0, 0, ',', '.') }}
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Tax:</strong>
                                                            <p>Rp {{ number_format($invoice->tax ?? 0, 0, ',', '.') }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <strong>Total Amount:</strong>
                                                            <p class="text-primary">
                                                                <strong>Rp
                                                                    {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Paid Amount:</strong>
                                                            <p class="text-success">
                                                                <strong>Rp
                                                                    {{ number_format($invoice->paid_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No billing history available</p>
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
@endsection
