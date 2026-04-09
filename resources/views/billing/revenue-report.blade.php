@extends('layouts.app')

@section('title', 'Revenue Report')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-primary"></i> Revenue Report
            </h1>
            <p class="text-muted mb-0">Healthcare revenue analytics and trends</p>
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
                    <form method="GET" class="row g-2">
                        <div class="col-md-3">
                            <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary">Rp {{ number_format($stats['total_revenue'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-muted">Total Revenue</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success">Rp {{ number_format($stats['collected'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-muted">Collected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">Rp {{ number_format($stats['pending'] ?? 0, 0, ',', '.') }}</h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">{{ $stats['total_patients'] ?? 0 }}</h3>
                    <small class="text-muted">Patients Served</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-hospital"></i> Revenue by Department
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Revenue</th>
                                    <th>%</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departmentRevenue ?? [] as $dept)
                                    <tr>
                                        <td>{{ $dept['name'] }}</td>
                                        <td><strong>Rp {{ number_format($dept['revenue'], 0, ',', '.') }}</strong></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary"
                                                    style="width: {{ $dept['percentage'] }}%">
                                                    {{ $dept['percentage'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-credit-card"></i> Revenue by Payment Method
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Payment Method</th>
                                    <th>Amount</th>
                                    <th>Transactions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentMethods ?? [] as $method)
                                    <tr>
                                        <td>{{ $method['name'] }}</td>
                                        <td><strong>Rp {{ number_format($method['amount'], 0, ',', '.') }}</strong></td>
                                        <td>{{ $method['count'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar"></i> Daily Revenue Trend
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patients</th>
                                    <th>Revenue</th>
                                    <th>Avg. per Patient</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dailyTrend ?? [] as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td>{{ $day['patients'] }}</td>
                                        <td><strong>Rp {{ number_format($day['revenue'], 0, ',', '.') }}</strong></td>
                                        <td>Rp {{ number_format($day['avg_per_patient'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No trend data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
