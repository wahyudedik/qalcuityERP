@extends('layouts.app')

@section('title', 'Aging Report')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clock text-primary"></i> Accounts Receivable Aging
            </h1>
            <p class="text-muted mb-0">Outstanding invoices by age category</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-2">
                            <h4 class="text-primary">Rp {{ number_format($stats['total_outstanding'] ?? 0, 0, ',', '.') }}
                            </h4>
                            <small class="text-muted">Total Outstanding</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-success">Rp {{ number_format($stats['current'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">Current</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-info">Rp {{ number_format($stats['days_30'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">1-30 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-warning">Rp {{ number_format($stats['days_60'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">31-60 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-danger">Rp {{ number_format($stats['days_90'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">61-90 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-dark">Rp {{ number_format($stats['over_90'] ?? 0, 0, ',', '.') }}</h4>
                            <small class="text-muted">90+ Days</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Invoice #</th>
                                    <th>Patient</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Days Overdue</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($invoices as $invoice)
                                    <tr>
                                        <td><code>{{ $invoice->invoice_number }}</code></td>
                                        <td>{{ $invoice->patient?->name ?? '-' }}</td>
                                        <td>{{ $invoice->invoice_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>{{ $invoice->due_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td><strong>Rp
                                                {{ number_format($invoice->outstanding_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $daysOverdue = $invoice->due_date?->diffInDays(now()) ?? 0;
                                            @endphp
                                            @if ($daysOverdue > 0)
                                                <span class="text-danger fw-bold">{{ $daysOverdue }} days</span>
                                            @else
                                                <span class="text-success">Not due</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($daysOverdue <= 0)
                                                <span class="badge bg-success">Current</span>
                                            @elseif($daysOverdue <= 30)
                                                <span class="badge bg-info">1-30 Days</span>
                                            @elseif($daysOverdue <= 60)
                                                <span class="badge bg-warning">31-60 Days</span>
                                            @elseif($daysOverdue <= 90)
                                                <span class="badge bg-danger">61-90 Days</span>
                                            @else
                                                <span class="badge bg-dark">90+ Days</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-bell"></i> Remind
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No outstanding invoices</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $invoices->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
