<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('healthcare.patients.index') }}">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="{{ route('healthcare.patients.show', $patient) }}">{{ $patient->name }}</a></li>
                    <li class="breadcrumb-item active">Documents</li>
                </ol>
            </nav>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-folder-open text-blue-600"></i> Patient Documents
            </h1>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#uploadModal">
                <i class="fas fa-upload"></i> Upload Document
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                        <div class="w-full md:w-1/4">
                            <div class="border-end">
                                <h3 class="text-blue-600">{{ count($documents) }}</h3>
                                <small class="text-gray-500">Total Documents</small>
                            </div>
                        </div>
                        <div class="w-full md:w-1/4">
                            <div class="border-end">
                                <h3 class="text-emerald-600">{{ collect($documents)->where('category', 'lab_result')->count() }}
                                </h3>
                                <small class="text-gray-500">Lab Results</small>
                            </div>
                        </div>
                        <div class="w-full md:w-1/4">
                            <div class="border-end">
                                <h3 class="text-sky-600">{{ collect($documents)->where('category', 'radiology')->count() }}
                                </h3>
                                <small class="text-gray-500">Radiology</small>
                            </div>
                        </div>
                        <div class="w-full md:w-1/4">
                            <h3 class="text-amber-600">{{ collect($documents)->where('category', 'consent_form')->count() }}
                            </h3>
                            <small class="text-gray-500">Consent Forms</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @forelse($documents as $doc)
            <div class="w-full md:w-1/3">
                <div class="bg-white rounded-2xl border border-gray-200 h-full">
                    <div class="px-5 py-4 border-b border-gray-200">
                        <div class="flex items-center justify-between">
                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">{{ ucwords(str_replace('_', ' ', $doc->category)) }}</span>
                            <small class="text-gray-500">{{ $doc->created_at->format('d/m/Y') }}</small>
                        </div>
                    </div>
                    <div class="p-5 text-center">
                        <i class="fas fa-{{ $doc->icon ?? 'file' }} fa-3x text-gray-500 mb-3"></i>
                        <h6 class="font-semibold text-gray-900">{{ $doc->title ?? 'Untitled Document' }}</h6>
                        <p class="text-sm text-gray-500">{{ $doc->description ?? 'No description' }}</p>
                    </div>
                    <div class="px-5 py-4 border-t border-gray-200 bg-gray-50">
                        <div class="flex w-full gap-1">
                            <a href="{{ $doc->file_url ?? '#' }}" class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition" target="_blank">
                                <i class="fas fa-eye"></i> View
                            </a>
                            <a href="{{ $doc->file_url ?? '#' }}" class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition" download>
                                <i class="fas fa-download"></i> Download
                            </a>
                            <button class="px-3 py-1.5 border border-red-500 text-red-600 hover:bg-red-50 rounded-lg text-xs transition">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="w-full">
                <div class="bg-white rounded-2xl border border-gray-200">
                    <div class="p-5 text-center py-10">
                        <i class="fas fa-folder-open fa-3x text-gray-500 mb-3"></i>
                        <p class="text-gray-500">No documents uploaded yet</p>
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
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
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
                            <small class="text-gray-500">Accepted: PDF, JPG, PNG (Max 10MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Upload</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
