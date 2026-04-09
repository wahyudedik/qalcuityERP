@extends('layouts.app')

@section('title', 'Average Length of Stay (ALOS)')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clock text-primary"></i> Average Length of Stay (ALOS)
            </h1>
            <p class="text-muted mb-0">Patient hospitalization duration metrics</p>
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
                            <h2 class="text-primary">{{ $stats['alos'] ?? 0 }} days</h2>
                            <small class="text-muted">Overall ALOS</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['total_discharges'] ?? 0 }}</h2>
                            <small class="text-muted">Total Discharges</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['total_days'] ?? 0 }}</h2>
                            <small class="text-muted">Total Patient Days</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning">{{ $stats['target_alos'] ?? 0 }} days</h2>
                            <small class="text-muted">Target ALOS</small>
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
                    <h5 class="mb-0">ALOS by Department</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>ALOS</th>
                                    <th>Discharges</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptALOS ?? [] as $dept)
                                    <tr>
                                        <td><strong>{{ $dept['name'] }}</strong></td>
                                        <td>{{ $dept['alos'] }} days</td>
                                        <td>{{ $dept['discharges'] }}</td>
                                        <td>
                                            @if ($dept['alos'] <= ($stats['target_alos'] ?? 5))
                                                <span class="badge bg-success">On Target</span>
                                            @else
                                                <span class="badge bg-warning">Above Target</span>
                                            @endif
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
                    <h5 class="mb-0">ALOS Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>ALOS</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($alosTrend ?? [] as $period)
                                    <tr>
                                        <td>{{ $period['period'] }}</td>
                                        <td><strong>{{ $period['alos'] }} days</strong></td>
                                        <td>
                                            @if ($period['trend'] > 0)
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i>
                                                    +{{ $period['trend'] }}%</span>
                                            @elseif($period['trend'] < 0)
                                                <span class="text-success"><i class="fas fa-arrow-down"></i>
                                                    {{ $period['trend'] }}%</span>
                                            @else
                                                <span class="text-muted"><i class="fas fa-minus"></i> 0%</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No trend data</td>
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
