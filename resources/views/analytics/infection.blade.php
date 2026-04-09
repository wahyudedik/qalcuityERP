@extends('layouts.app')

@section('title', 'Infection Rate')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-virus text-primary"></i> Healthcare-Associated Infection Rate
            </h1>
            <p class="text-muted mb-0">Hospital-acquired infection tracking</p>
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
                            <h2 class="text-danger">{{ $stats['infection_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Overall HAI Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning">{{ $stats['total_infections'] ?? 0 }}</h2>
                            <small class="text-muted">Total Infections</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success">{{ $stats['total_admissions'] ?? 0 }}</h2>
                            <small class="text-muted">Total Admissions</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info">{{ $stats['target_rate'] ?? 0 }}%</h2>
                            <small class="text-muted">Target Rate</small>
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
                    <h5 class="mb-0">Infections by Type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Infection Type</th>
                                    <th>Cases</th>
                                    <th>Rate</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($infectionTypes ?? [] as $type)
                                    <tr>
                                        <td><strong>{{ $type['name'] }}</strong></td>
                                        <td>{{ $type['cases'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $type['rate'] > ($stats['target_rate'] ?? 2) ? 'danger' : 'success' }}">
                                                {{ $type['rate'] }}%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger"
                                                    style="width: {{ $type['percentage'] }}%">
                                                    {{ $type['percentage'] }}%
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
                    <h5 class="mb-0">Infections by Ward</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Infections</th>
                                    <th>Admissions</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($wardInfections ?? [] as $ward)
                                    <tr>
                                        <td>{{ $ward['name'] }}</td>
                                        <td>{{ $ward['infections'] }}</td>
                                        <td>{{ $ward['admissions'] }}</td>
                                        <td>
                                            <span
                                                class="badge bg-{{ $ward['rate'] > ($stats['target_rate'] ?? 2) ? 'danger' : 'success' }}">
                                                {{ $ward['rate'] }}%
                                            </span>
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
@endsection
