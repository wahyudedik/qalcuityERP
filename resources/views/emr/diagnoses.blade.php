<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-stethoscope text-blue-600"></i> Diagnoses
            </h1>
            <p class="text-gray-500">ICD-10 diagnosis codes and records</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addDiagnosisModal">
                <i class="fas fa-plus"></i> Add Diagnosis
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <form method="GET" class="grid grid-cols-1 md:grid-cols-2 gap-6 g-2">
                        <div class="w-full md:w-1/3">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search ICD-10 code or description..." value="{{ request('search') }}">
                        </div>
                        <div class="w-full md:w-1/6">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="primary" {{ request('type') == 'primary' ? 'selected' : '' }}>Primary
                                </option>
                                <option value="secondary" {{ request('type') == 'secondary' ? 'selected' : '' }}>Secondary
                                </option>
                            </select>
                        </div>
                        <div class="w-full md:w-1/6">
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>ICD-10 Code</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($diagnoses as $diag)
                                    <tr>
                                        <td>{{ $diag->diagnosis_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $diag->patient) }}">
                                                {{ $diag->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td><code>{{ $diag->icd10_code }}</code></td>
                                        <td>{{ Str::limit($diag->description, 50) }}</td>
                                        <td>
                                            @if ($diag->type == 'primary')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Primary</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Secondary</span>
                                            @endif
                                        </td>
                                        <td>{{ $diag->doctor?->name ?? '-' }}</td>
                                        <td>
                                            @if ($diag->status == 'active')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                                            @elseif($diag->status == 'resolved')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Resolved</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Chronic</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="px-3 py-1.5 border border-amber-500 text-amber-600 hover:bg-amber-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">
                                            No diagnoses found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $diagnoses->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Diagnosis Modal -->
    <div class="modal fade" id="addDiagnosisModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="{{ route('healthcare.emr.diagnoses.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Diagnosis</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    @foreach ($patients ?? [] as $patient)
                                        <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Diagnosis Date</label>
                                <input type="date" name="diagnosis_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/3">
                                <label class="form-label">ICD-10 Code</label>
                                <input type="text" name="icd10_code" class="form-control" placeholder="e.g., J06.9"
                                    required>
                            </div>
                            <div class="w-full md:w-1/3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="primary">Primary</option>
                                    <option value="secondary">Secondary</option>
                                </select>
                            </div>
                            <div class="w-full md:w-1/3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="chronic">Chronic</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Add Diagnosis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
