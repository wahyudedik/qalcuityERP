@extends('layouts.app')

@section('title', 'Patient Satisfaction')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-smile text-primary"></i> Patient Satisfaction Score
            </h1>
            <p class="text-muted mb-0">Patient experience and satisfaction metrics</p>
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
                            <h2 class="text-warning">
                                @for ($i = 1; $i <= 5; $i++)
                                    <i
                                        class="fas fa-star {{ $i <= ($stats['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                @endfor
                            </h2>
                            <small class="text-muted">Average Rating</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['satisfaction_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Satisfaction Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-primary">{{ $stats['total_surveys'] ?? 0 }}</h2>
                            <small class="text-muted">Total Surveys</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['response_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Response Rate</small>
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
                    <h5 class="mb-0">Satisfaction by Category</h5>
                </div>
                <div class="card-body">
                    @forelse($categoryScores ?? [] as $category)
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong>{{ $category['name'] }}</strong>
                                <span>{{ $category['score'] }}/5.0</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-{{ $category['score'] >= 4 ? 'success' : ($category['score'] >= 3 ? 'warning' : 'danger') }}"
                                    style="width: {{ ($category['score'] / 5) * 100 }}%">
                                    {{ $category['score'] }}/5.0
                                </div>
                            </div>
                        </div>
                    @empty
                        <p class="text-muted text-center">No category data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Satisfaction Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Rating</th>
                                    <th>Surveys</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($satisfactionTrend ?? [] as $period)
                                    <tr>
                                        <td>{{ $period['period'] }}</td>
                                        <td>
                                            <strong>{{ $period['rating'] }}/5.0</strong>
                                        </td>
                                        <td>{{ $period['surveys'] }}</td>
                                        <td>
                                            @if ($period['trend'] > 0)
                                                <span class="text-success"><i class="fas fa-arrow-up"></i>
                                                    +{{ $period['trend'] }}%</span>
                                            @elseif($period['trend'] < 0)
                                                <span class="text-danger"><i class="fas fa-arrow-down"></i>
                                                    {{ $period['trend'] }}%</span>
                                            @else
                                                <span class="text-muted"><i class="fas fa-minus"></i> 0%</span>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Feedback</h5>
                </div>
                <div class="card-body">
                    @forelse($recentFeedback ?? [] as $feedback)
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong>{{ $feedback['patient_name'] ?? 'Anonymous' }}</strong>
                                    <br><small class="text-muted">{{ $feedback['created_at'] ?? '-' }}</small>
                                </div>
                                <div>
                                    @for ($i = 1; $i <= 5; $i++)
                                        <i
                                            class="fas fa-star {{ $i <= ($feedback['rating'] ?? 0) ? 'text-warning' : 'text-muted' }}"></i>
                                    @endfor
                                </div>
                            </div>
                            <p class="mb-0">{{ $feedback['comment'] ?? 'No comment' }}</p>
                        </div>
                    @empty
                        <p class="text-muted text-center">No recent feedback</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
