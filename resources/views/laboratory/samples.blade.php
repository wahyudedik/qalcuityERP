<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-vials text-blue-600"></i> Sample Management
            </h1>
            <p class="text-gray-500">Track and manage laboratory samples</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addSampleModal">
                <i class="fas fa-plus"></i> Register Sample
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $samples->where('status', 'collected')->count() }}</h3>
                    <small class="text-gray-500">Collected</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $samples->where('status', 'processing')->count() }}</h3>
                    <small class="text-gray-500">Processing</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $samples->where('status', 'completed')->count() }}</h3>
                    <small class="text-gray-500">Completed</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $samples->where('status', 'rejected')->count() }}</h3>
                    <small class="text-gray-500">Rejected</small>
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
                                    <th>Sample ID</th>
                                    <th>Patient</th>
                                    <th>Sample Type</th>
                                    <th>Collected At</th>
                                    <th>Tests</th>
                                    <th>Status</th>
                                    <th>Technician</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($samples as $sample)
                                    <tr>
                                        <td><code>{{ $sample->sample_id }}</code></td>
                                        <td>{{ $sample->patient?->name ?? '-' }}</td>
                                        <td>
                                            @php
                                                $icons = [
                                                    'blood' => 'fa-tint',
                                                    'urine' => 'fa-flask',
                                                    'tissue' => 'fa-dna',
                                                    'swab' => 'fa-stroopwafel',
                                                ];
                                            @endphp
                                            <i class="fas {{ $icons[$sample->sample_type] ?? 'fa-vial' }} mr-1"></i>
                                            {{ ucfirst($sample->sample_type ?? '-') }}
                                        </td>
                                        <td>{{ $sample->collected_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td>{{ $sample->tests_count ?? 0 }}</td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'collected' => 'info',
                                                    'processing' => 'warning',
                                                    'completed' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$sample->status] ?? 'secondary'  }}">
                                                {{ ucfirst($sample->status) }}
                                            </span>
                                        </td>
                                        <td>{{ $sample->technician?->name ?? '-' }}</td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($sample->status == 'collected')
                                                    <button class="px-3 py-1.5 border border-amber-500 text-amber-600 hover:bg-amber-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No samples found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $samples->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add Sample Modal -->
    <div class="modal fade" id="addSampleModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.laboratory.samples.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Register Sample</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">{{ $patient->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sample Type</label>
                            <select name="sample_type" class="form-select" required>
                                <option value="">Select type</option>
                                <option value="blood">Blood</option>
                                <option value="urine">Urine</option>
                                <option value="tissue">Tissue</option>
                                <option value="swab">Swab</option>
                                <option value="stool">Stool</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Collection Date/Time</label>
                            <input type="datetime-local" name="collected_at" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Register Sample</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
