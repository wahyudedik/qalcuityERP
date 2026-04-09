@extends('layouts.app')

@section('title', 'Pharmacy Reports')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-primary"></i> Pharmacy Reports
            </h1>
            <p class="text-muted mb-0">Pharmacy analytics and performance reports</p>
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
                    <h3 class="text-success">{{ $stats['total_prescriptions'] ?? 0 }}</h3>
                    <small class="text-muted">Prescriptions</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning">{{ $stats['low_stock_items'] ?? 0 }}</h3>
                    <small class="text-muted">Low Stock Items</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger">{{ $stats['expired_items'] ?? 0 }}</h3>
                    <small class="text-muted">Expired Items</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-pills"></i> Top 10 Medications
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Medication</th>
                                    <th>Quantity</th>
                                    <th>Revenue</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topMedications ?? [] as $index => $med)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $med['name'] }}</td>
                                        <td>{{ $med['quantity'] }}</td>
                                        <td>Rp {{ number_format($med['revenue'], 0, ',', '.') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
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
                        <i class="fas fa-exclamation-triangle"></i> Low Stock Alert
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current Stock</th>
                                    <th>Min. Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($lowStockItems ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><strong class="text-danger">{{ $item['stock'] }}</strong></td>
                                        <td>{{ $item['min_stock'] }}</td>
                                        <td>
                                            @if ($item['stock'] == 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @else
                                                <span class="badge bg-warning">Low Stock</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">All items well stocked</td>
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
                        <i class="fas fa-calendar-times"></i> Expiring Soon (30 days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Batch #</th>
                                    <th>Stock</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringItems ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><code>{{ $item['batch_number'] }}</code></td>
                                        <td>{{ $item['stock'] }}</td>
                                        <td>{{ $item['expiry_date'] }}</td>
                                        <td>
                                            @php
                                                $days = $item['days_left'];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $days <= 7 ? 'danger' : ($days <= 14 ? 'warning' : 'info') }}">
                                                {{ $days }} days
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No items expiring soon</td>
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
