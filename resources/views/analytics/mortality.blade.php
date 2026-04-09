@extends('layouts.app')

@section('title', 'Mortality Rate')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-primary"></i> Mortality Rate Analysis
            </h1>
            <p class="text-muted mb-0">Patient mortality metrics and trends</p>
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
                            <h2 class="text-danger">{{ $stats['mortality_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Overall Mortality Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-primary">{{ $stats['total_deaths'] ?? 0 }}</h2>
                            <small class="text-muted">Total Deaths</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['total_discharges'] ?? 0 }}</h2>
                            <small class="text-muted">Total Discharges</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['benchmark_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">National Benchmark</small>
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
                    <h5 class="mb-0">Mortality by Department</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Deaths</th>
                                    <th>Discharges</th>
                                    <th>Rate</th>
                                    <th>vs Benchmark</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deptMortality ?? [] as $dept)
                                    <tr>
                                        <td><strong>{{ $dept['name'] }}</strong></td>
                                        <td>{{ $dept['deaths'] }}</td>
                                        <td>{{ $dept['discharges'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $dept['rate'] > ($stats['benchmark_rate'] ?? 3) ? 'danger' : 'success' }}">
                                                {{ $dept['rate'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $diff = $dept['rate'] - ($stats['benchmark_rate'] ?? 3);
                                            @endphp
                                            @if ($diff > 0)
                                                <span class="text-danger">+{{ number_format($diff, 2) }}%</span>
                                            @else
                                                <span class="text-success">{{ number_format($diff, 2) }}%</span>
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
                    <h5 class="mb-0">Monthly Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Deaths</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($monthlyTrend ?? [] as $month)
                                    <tr>
                                        <td>{{ $month['month'] }}</td>
                                        <td>{{ $month['deaths'] }}</td>
                                        <td><strong>{{ $month['rate'] }}%</strong></td>
                                        <td>
                                            @if ($month['trend'] > 0)
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i></span>
                                            @elseif($month['trend'] < 0)
                                                <span class="text-success"><i class="fas fa-arrow-down"></i></span>
                                            @else
                                                <span class="text-muted"><i class="fas fa-minus"></i></span>
                                            @endif
                                        </td>
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
@endsection
