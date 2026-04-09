@extends('layouts.app')

@section('title', 'Operating Room Utilization')

@section('header')
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-chart-pie text-primary"></i> OR Utilization Report
        </h1>
        <p class="text-muted mb-0">Operating room efficiency and usage analytics</p>
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
                        <h3 class="text-primary">{{ $stats['total_ors'] ?? 0 }}</h3>
                        <small class="text-muted">Total ORs</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success">{{ $stats['utilization_rate'] ?? 0 }}%</h3>
                        <small class="text-muted">Utilization Rate</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info">{{ $stats['avg_turnaround'] ?? 0 }} min</h3>
                        <small class="text-muted">Avg Turnaround</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning">{{ $stats['cancellation_rate'] ?? 0 }}%</h3>
                        <small class="text-muted">Cancellation Rate</small>
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
                    <i class="fas fa-door-open"></i> OR Utilization by Room
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Available hrs</th>
                                <th>Used hrs</th>
                                <th>Utilization</th>
                                <th>Surgeries</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orUtilization ?? [] as $or)
                            <tr>
                                <td><strong>{{ $or['name'] }}</strong></td>
                                <td>{{ $or['available_hours'] }}</td>
                                <td>{{ $or['used_hours'] }}</td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-{{ $or['utilization'] > 80 ? 'success' : ($or['utilization'] > 50 ? 'warning' : 'danger') }}" 
                                             style="width: {{ $or['utilization'] }}%">
                                            {{ $or['utilization'] }}%
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $or['surgery_count'] }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data available</td>
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
                    <i class="fas fa-procedures"></i> Surgeries by Type
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Surgery Type</th>
                                <th>Count</th>
                                <th>Avg Duration</th>
                                <th>Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($surgeryTypes ?? [] as $type)
                            <tr>
                                <td>{{ $type['name'] }}</td>
                                <td>{{ $type['count'] }}</td>
                                <td>{{ $type['avg_duration'] }} min</td>
                                <td>
                                    <span class="badge bg-success">{{ $type['success_rate'] }}%</span>
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
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week"></i> Weekly Schedule
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($weeklySchedule ?? [] as $schedule)
                            <tr>
                                <td><strong>{{ $schedule['or_name'] }}</strong></td>
                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                <td class="text-center">
                                    @if(isset($schedule[$day]) && $schedule[$day] > 0)
                                        <span class="badge bg-primary">{{ $schedule[$day }} surgeries</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                @endforeach
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted">No schedule data available</td>
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
