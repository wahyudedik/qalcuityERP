@extends('layouts.app')

@section('title', 'Medical History - ' . $patient->name)

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Medical History</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="fas fa-history text-primary"></i> Medical History
            </h1>
            <p class="text-muted mb-0">Complete medical history for {{ $patient->name }}</p>
        </div>
        <div>
            <a href="{{ route('healthcare.patients.show', $patient) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Patient
            </a>
        </div>
    </div>
@endsection

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filter Timeline
                    </h5>
                    <form method="GET" class="d-flex gap-2">
                        <select name="type" class="form-select form-select-sm" style="width: 180px;">
                            <option value="">All Types</option>
                            <option value="visit" {{ request('type') == 'visit' ? 'selected' : '' }}>Visits</option>
                            <option value="diagnosis" {{ request('type') == 'diagnosis' ? 'selected' : '' }}>Diagnoses
                            </option>
                            <option value="procedure" {{ request('type') == 'procedure' ? 'selected' : '' }}>Procedures
                            </option>
                            <option value="lab" {{ request('type') == 'lab' ? 'selected' : '' }}>Lab Results</option>
                            <option value="prescription" {{ request('type') == 'prescription' ? 'selected' : '' }}>
                                Prescriptions</option>
                        </select>
                        <input type="date" name="date_from" class="form-control form-control-sm" style="width: 150px;"
                            value="{{ request('date_from') }}">
                        <input type="date" name="date_to" class="form-control form-control-sm" style="width: 150px;"
                            value="{{ request('date_to') }}">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-timeline"></i> Medical Timeline
                    </h5>
                </div>
                <div class="card-body">
                    @if ($timeline->count() > 0)
                        <div class="timeline">
                            @foreach ($timeline as $item)
                                <div class="timeline-item mb-4">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <div class="timeline-marker bg-{{ $item['type_color'] }}">
                                                <i class="fas {{ $item['type_icon'] }} text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="card border-{{ $item['type_color'] }}">
                                                <div class="card-header bg-light py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span
                                                                class="badge bg-{{ $item['type_color'] }} me-2">{{ ucfirst($item['type']) }}</span>
                                                            <strong>{{ $item['title'] }}</strong>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> {{ $item['date'] }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2">
                                                    <p class="mb-1 small">{{ $item['description'] }}</p>
                                                    @if ($item['provider'])
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-md"></i> {{ $item['provider'] }}
                                                        </small>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        @if ($timeline->hasPages())
                            <div class="d-flex justify-content-center mt-4">
                                {{ $timeline->links() }}
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medical history records found</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: -16px;
            width: 2px;
            background: #dee2e6;
        }
    </style>
@endsection
