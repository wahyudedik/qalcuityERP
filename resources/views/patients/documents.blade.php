@extends('layouts.app')

@section('title', 'Documents - ' . $patient->name)

@section('header')
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="fas fa-folder-open text-primary"></i> Patient Documents
            </h1>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload Document
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
                            <div class="border-end">
                                <h3 class="text-primary">{{ count($documents) }}</h3>
                                <small class="text-muted">Total Documents</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-success">{{ collect($documents)->where('category', 'lab_result')->count() }}
                                </h3>
                                <small class="text-muted">Lab Results</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="border-end">
                                <h3 class="text-info">{{ collect($documents)->where('category', 'radiology')->count() }}
                                </h3>
                                <small class="text-muted">Radiology</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning">{{ collect($documents)->where('category', 'consent_form')->count() }}
                            </h3>
                            <small class="text-muted">Consent Forms</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @forelse($documents as $doc)
            <div class="col-md-4 mb-4">
                <div class="card h-100">
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="badge bg-primary">{{ ucwords(str_replace('_', ' ', $doc->category)) }}</span>
                            <small class="text-muted">{{ $doc->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    <div class="card-body text-center">
                        <i class="fas fa-{{ $doc->icon ?? 'file' }} fa-3x text-muted mb-3"></i>
                        <h6 class="card-title">{{ $doc->title ?? 'Untitled Document' }}</h6>
                        <p class="card-text small text-muted">{{ $doc->description ?? 'No description' }}</p>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="btn-group w-100">
                            <a href="{{ $doc->file_url ?? '#' }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ $doc->file_url ?? '#' }}" class="btn btn-sm btn-outline-success" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="btn btn-sm btn-outline-danger">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No documents uploaded yet</p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Upload Modal -->
    <div class="modal fade" id="uploadModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.patients.documents.store', $patient) }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Upload Document</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Document Title</label>
                            <input type="text" name="title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select" required>
                                <option value="">Select category</option>
                                <option value="lab_result">Lab Result</option>
                                <option value="radiology">Radiology</option>
                                <option value="consent_form">Consent Form</option>
                                <option value="referral">Referral Letter</option>
                                <option value="medical_certificate">Medical Certificate</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="2"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">File</label>
                            <input type="file" name="document" class="form-control" required
                                accept=".pdf,.jpg,.jpeg,.png">
                            <small class="text-muted">Accepted: PDF, JPG, PNG (Max 10MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
