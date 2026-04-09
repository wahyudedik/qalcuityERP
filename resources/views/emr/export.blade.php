@extends('layouts.app')

@section('title', 'Export Medical Records')

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-export text-primary"></i> Export Medical Records
            </h1>
            <p class="text-muted mb-0">Export patient records in various formats</p>
        </div>
    </div>
@endsection

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-cog"></i> Export Configuration
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('healthcare.emr.export.process') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-bold">Export Type</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="export_type" id="fullRecord"
                                            value="full" checked>
                                        <label class="form-check-label" for="fullRecord">
                                            <strong>Full Medical Record</strong>
                                            <br><small class="text-muted">Complete patient history, diagnoses, treatments,
                                                lab results</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="export_type" id="summaryRecord"
                                            value="summary">
                                        <label class="form-check-label" for="summaryRecord">
                                            <strong>Summary Report</strong>
                                            <br><small class="text-muted">Key information only - diagnoses, medications,
                                                allergies</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="export_type" id="labResults"
                                            value="lab">
                                        <label class="form-check-label" for="labResults">
                                            <strong>Lab Results Only</strong>
                                            <br><small class="text-muted">All laboratory test results and reports</small>
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="export_type"
                                            id="billingRecords" value="billing">
                                        <label class="form-check-label" for="billingRecords">
                                            <strong>Billing Records</strong>
                                            <br><small class="text-muted">Invoices, payments, insurance claims</small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Date Range</label>
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small">From</label>
                                    <input type="date" name="date_from" class="form-control">
                                </div>
                                <div class="col-md-6 mb-2">
                                    <label class="form-label small">To</label>
                                    <input type="date" name="date_to" class="form-control">
                                </div>
                            </div>
                            <small class="text-muted">Leave empty to include all records</small>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Patients</label>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="patient_scope" id="allPatients"
                                    value="all" checked>
                                <label class="form-check-label" for="allPatients">All Patients</label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="patient_scope" id="specificPatient"
                                    value="specific">
                                <label class="form-check-label" for="specificPatient">Specific Patient</label>
                            </div>
                            <select name="patient_id" class="form-select mt-2" disabled>
                                <option value="">Select patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}
                                        ({{ $patient->medical_record_number }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Export Format</label>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="pdfFormat"
                                            value="pdf" checked>
                                        <label class="form-check-label" for="pdfFormat">
                                            <i class="fas fa-file-pdf text-danger"></i> PDF
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="excelFormat"
                                            value="excel">
                                        <label class="form-check-label" for="excelFormat">
                                            <i class="fas fa-file-excel text-success"></i> Excel
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="format" id="jsonFormat"
                                            value="json">
                                        <label class="form-check-label" for="jsonFormat">
                                            <i class="fas fa-file-code text-primary"></i> JSON
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Privacy Notice:</strong> Exported data contains sensitive medical information.
                            Ensure compliance with HIPAA and data protection regulations.
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-download"></i> Generate Export
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-history"></i> Recent Exports
                    </h5>
                </div>
                <div class="card-body">
                    @forelse($recentExports ?? [] as $export)
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                            <div>
                                <strong>{{ $export->type }}</strong>
                                <br><small class="text-muted">{{ $export->created_at->diffForHumans() }}</small>
                            </div>
                            <a href="{{ $export->file_url }}" class="btn btn-sm btn-outline-primary" download>
                                <i class="fas fa-download"></i>
                            </a>
                        </div>
                    @empty
                        <p class="text-muted text-center">No recent exports</p>
                    @endforelse
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-info-circle"></i> Export Guidelines
                    </h5>
                </div>
                <div class="card-body">
                    <ul class="mb-0 small">
                        <li class="mb-2">Maximum export size: 1000 patients per request</li>
                        <li class="mb-2">Large exports will be processed in background</li>
                        <li class="mb-2">Export files are retained for 30 days</li>
                        <li class="mb-2">All exports are logged for audit purposes</li>
                        <li>Encrypted storage for sensitive medical data</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            document.querySelectorAll('input[name="patient_scope"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    document.querySelector('select[name="patient_id"]').disabled = this.value !== 'specific';
                });
            });
        </script>
    @endpush
@endsection
