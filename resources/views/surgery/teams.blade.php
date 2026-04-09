@extends('layouts.app')

@section('title', 'Surgery Teams')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-users text-primary"></i> Surgery Teams
            </h1>
            <p class="text-muted mb-0">Manage surgical team assignments</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                <i class="fas fa-plus"></i> Create Team
            </button>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        @forelse($teams as $team)
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong>{{ $team->team_name ?? 'Surgery Team' }}</strong>
                        <span class="badge bg-{{ $team->is_active ? 'success' : 'secondary' }}">
                            {{ $team->is_active ? 'Active' : 'Inactive' }}
                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Team Members</h6>
                        <div class="mb-3">
                            @forelse($team->members ?? [] as $member)
                                <div class="d-flex justify-content-between mb-2 p-2 bg-light rounded">
                                    <div>
                                        <strong>{{ $member['role'] ?? '-' }}</strong>
                                        <br><small>{{ $member['name'] ?? '-' }}</small>
                                    </div>
                                    <span class="badge bg-{{ $member['available'] ? 'success' : 'danger' }}">
                                        {{ $member['available'] ? 'Available' : 'Busy' }}
                                    </span>
                                </div>
                            @empty
                                <p class="text-muted text-center">No members assigned</p>
                            @endforelse
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <h5 class="text-success">{{ $team->surgeries_completed ?? 0 }}</h5>
                                <small class="text-muted">Completed</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-warning">{{ $team->surgeries_scheduled ?? 0 }}</h5>
                                <small class="text-muted">Scheduled</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-info">{{ $team->success_rate ?? 0 }}%</h5>
                                <small class="text-muted">Success Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No surgery teams created yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>
@endsection
