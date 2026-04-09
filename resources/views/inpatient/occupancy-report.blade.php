@extends('layouts.app')

@section('title', 'Bed Occupancy Report')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bed text-primary"></i> Bed Occupancy Report
            </h1>
            <p class="text-muted mb-0">Hospital bed utilization and occupancy statistics</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
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
                            <h2 class="text-primary">{{ $stats['total_beds'] ?? 0 }}</h2>
                            <small class="text-muted">Total Beds</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['occupied_beds'] ?? 0 }}</h2>
                            <small class="text-muted">Occupied</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['available_beds'] ?? 0 }}</h2>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-{{ ($stats['occupancy_rate'] ?? 0) > 85 ? 'danger' : 'warning' }}">
                                {{ $stats['occupancy_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Occupancy Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Occupancy by Ward
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Total</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wardStats ?? [] as $ward)
                                    <tr>
                                        <td>{{ $ward['name'] }}</td>
                                        <td>{{ $ward['total'] }}</td>
                                        <td>{{ $ward['occupied'] }}</td>
                                        <td>{{ $ward['available'] }}</td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-{{ $ward['rate'] > 85 ? 'danger' : ($ward['rate'] > 60 ? 'warning' : 'success') }}"
                                                    style="width: {{ $ward['rate'] }}%">
                                                    {{ $ward['rate'] }}%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No ward data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Average Length of Stay
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Avg LOS (days)</th>
                                    <th>Admissions</th>
                                    <th>Discharges</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departmentStats ?? [] as $dept)
                                    <tr>
                                        <td>{{ $dept['name'] }}</td>
                                        <td><strong>{{ $dept['avg_los'] ?? 0 }}</strong></td>
                                        <td>{{ $dept['admissions'] ?? 0 }}</td>
                                        <td>{{ $dept['discharges'] ?? 0 }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No department data</td>
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
                        <i class="fas fa-bed"></i> Current Bed Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bed Number</th>
                                    <th>Ward</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                    <th>Admission Date</th>
                                    <th>Length of Stay</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($beds ?? [] as $bed)
                                    <tr>
                                        <td><strong>{{ $bed->bed_number }}</strong></td>
                                        <td>{{ $bed->ward?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'occupied' => 'success',
                                                    'available' => 'info',
                                                    'maintenance' => 'warning',
                                                    'reserved' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$bed->status] ?? 'secondary' }}">
                                                {{ ucfirst($bed->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $bed->currentPatient?->name ?? '-' }}</td>
                                        <td>{{ $bed->currentPatient?->admission?->admission_date?->format('d/m/Y') ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($bed->currentPatient?->admission)
                                                {{ $bed->currentPatient->admission->admission_date->diffInDays(now()) }}
                                                days
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No bed data available</td>
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
