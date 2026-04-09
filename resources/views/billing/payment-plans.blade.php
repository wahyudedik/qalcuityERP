@extends('layouts.app')

@section('title', 'Payment Plans')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-alt text-primary"></i> Payment Plans
            </h1>
            <p class="text-muted mb-0">Patient payment installment plans</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Plan #</th>
                                    <th>Patient</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Installments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentPlans as $plan)
                                    <tr>
                                        <td><code>{{ $plan->plan_number }}</code></td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $plan->patient) }}">
                                                {{ $plan->patient->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td><strong>Rp {{ number_format($plan->total_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-success">Rp
                                            {{ number_format($plan->paid_amount ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-danger">Rp {{ number_format($plan->balance ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            {{ $plan->paid_installments ?? 0 }}/{{ $plan->total_installments ?? 0 }}
                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: {{ $plan->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'completed' => 'secondary',
                                                    'defaulted' => 'danger',
                                                    'cancelled' => 'warning',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary' }}">
                                                {{ ucfirst($plan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($plan->status == 'active')
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-money-bill"></i> Payment
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No payment plans found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $paymentPlans->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
