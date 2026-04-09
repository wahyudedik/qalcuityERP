@extends('layouts.app')

@section('title', 'Bed Occupancy Rate (BOR)')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bed text-primary"></i> Bed Occupancy Rate (BOR)
            </h1>
            <p class="text-muted mb-0">Hospital bed utilization metrics</p>
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
                            <h2 class="text-primary">{{ $stats['current_bor'] ?? 0 }}%</h2>
                            <small class="text-muted">Current BOR</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['avg_bor_month'] ?? 0 }}%</h2>
                            <small class="text-muted">Monthly Average</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['total_beds'] ?? 0 }}</h2>
                            <small class="text-muted">Total Beds</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning">{{ $stats['occupied_beds'] ?? 0 }}</h2>
                            <small class="text-muted">Occupied</small>
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
                    <h5 class="mb-0">BOR by Ward</h5>
                </div>
                <div class="card-body">
                    @forelse($wardBOR ?? [] as $ward)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ $ward['name'] }}</strong>
                                <span>{{ $ward['bor'] }}%</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $ward['bor'] > 85 ? 'danger' : ($ward['bor'] > 60 ? 'warning' : 'success') }}"
                                    style="width: {{ $ward['bor'] }}%">
                                    {{ $ward['bor'] }}%
                                </div>
                            </div>
                            <small class="text-muted">{{ $ward['occupied'] }}/{{ $ward['total'] }} beds occupied</small>
                        </div>
                    @empty
                        <p class="text-muted text-center">No ward data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">BOR Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>BOR</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($borTrend ?? [] as $day)
                                    <tr>
                                        <td>{{ $day['date'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $day['bor'] > 85 ? 'danger' : ($day['bor'] > 60 ? 'warning' : 'success') }}">
                                                {{ $day['bor'] }}%
                                            </span>
                                        </td>
                                        <td>{{ $day['occupied'] }}</td>
                                        <td>{{ $day['available'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No trend data</td>
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
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>BOR Benchmark:</strong> Ideal BOR is 60-85%. Above 85% indicates overcapacity, below 60% indicates
                underutilization.
            </div>
        </div>
    </div>
@endsection
