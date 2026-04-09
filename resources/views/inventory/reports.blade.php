@extends('layouts.app')

@section('title', 'Inventory Reports')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar text-primary"></i> Inventory Reports
            </h1>
            <p class="text-muted mb-0">Medical inventory analytics and insights</p>
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
                        <div class="col-md-3">
                            <h3 class="text-primary">{{ $stats['total_items'] ?? 0 }}</h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success">Rp {{ number_format($stats['total_value'] ?? 0, 0, ',', '.') }}</h3>
                            <small class="text-muted">Total Value</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ $stats['low_stock'] ?? 0 }}</h3>
                            <small class="text-muted">Low Stock Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger">{{ $stats['expiring_soon'] ?? 0 }}</h3>
                            <small class="text-muted">Expiring Soon</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-boxes"></i> Inventory by Category
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Value</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categoryStats ?? [] as $cat)
                                    <tr>
                                        <td>{{ $cat['name'] }}</td>
                                        <td>{{ $cat['items'] }}</td>
                                        <td>Rp {{ number_format($cat['value'], 0, ',', '.') }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary"
                                                    style="width: {{ $cat['percentage'] }}%">
                                                    {{ $cat['percentage'] }}%
                                                </div>
                                            </div>
                                        </td>
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
                        <i class="fas fa-exclamation-triangle"></i> Critical Stock Alerts
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current</th>
                                    <th>Min. Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($criticalStock ?? [] as $item)
                                    <tr>
                                        <td><strong>{{ $item['name'] }}</strong></td>
                                        <td class="text-danger fw-bold">{{ $item['stock'] }}</td>
                                        <td>{{ $item['min_stock'] }}</td>
                                        <td>
                                            @if ($item['stock'] == 0)
                                                <span class="badge bg-danger">Out of Stock</span>
                                            @else
                                                <span class="badge bg-warning">Critical</span>
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
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Stock Movement (Last 7 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Received</th>
                                    <th>Used</th>
                                    <th>Net Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stockMovement ?? [] as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td class="text-success">+{{ $day['received'] }}</td>
                                        <td class="text-danger">-{{ $day['used'] }}</td>
                                        <td>
                                            @php
                                                $net = $day['received'] - $day['used'];
                                            @endphp
                                            <span class="{{ $net >= 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                                {{ $net >= 0 ? '+' : '' }}{{ $net }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No movement data</td>
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
                        <i class="fas fa-calendar-times"></i> Expiration Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Batch</th>
                                    <th>Qty</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($expiringItems ?? [] as $item)
                                    <tr>
                                        <td>{{ $item['name'] }}</td>
                                        <td><code>{{ $item['batch'] }}</code></td>
                                        <td>{{ $item['quantity'] }}</td>
                                        <td>{{ $item['expiry_date'] }}</td>
                                        <td>
                                            @php
                                                $days = $item['days_left'];
                                            @endphp
                                            <span
                                                class="badge bg-{{ $days <= 7 ? 'danger' : ($days <= 30 ? 'warning' : 'info') }}">
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
