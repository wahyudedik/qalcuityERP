<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-file-invoice-dollar text-blue-600"></i> BPJS Claims
            </h1>
            <p class="text-gray-500">Indonesian national insurance claim submission and tracking</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#submitClaimModal">
                <i class="fas fa-plus"></i> Submit Claim
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['approved'] ?? 0 }}</h3>
                    <small class="text-gray-500">Approved</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $stats['rejected'] ?? 0 }}</h3>
                    <small class="text-gray-500">Rejected</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">
                        Rp {{ number_format(($stats['total_amount'] ?? 0) / 1000000, 1) }}M
                    </h3>
                    <small class="text-gray-500">Total Claims</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Claims History</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Patient</th>
                                    <th>Submission Date</th>
                                    <th>Diagnosis</th>
                                    <th>Claim Amount</th>
                                    <th>Status</th>
                                    <th>BPJS Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr>
                                        <td>
                                            <strong>{{ $claim->claim_id ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $claim->patient?->name ?? '-' }}</strong>
                                                <br><small
                                                    class="text-gray-500">{{ $claim->patient?->bpjs_number ?? '-' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <small>{{ $claim->submission_date->format('d/m/Y') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            <small>{{ $claim->diagnosis_code ?? '-' }}</small>
                                            <br><small
                                                class="text-gray-500">{{ Str::limit($claim->diagnosis_name ?? '-', 30) }}</small>
                                        </td>
                                        <td>
                                            <strong>Rp {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if ($claim->status == 'approved')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                                    <i class="fas fa-check-circle"></i> Approved
                                                </span>
                                            @elseif($claim->status == 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            @elseif($claim->status == 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                </span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($claim->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($claim->approved_amount)
                                                <small class="text-emerald-600">
                                                    Approved: Rp {{ number_format($claim->approved_amount, 0, ',', '.') }}
                                                </small>
                                            @elseif($claim->rejection_reason)
                                                <small class="text-red-600" title="{{ $claim->rejection_reason }}">
                                                    <i class="fas fa-exclamation-triangle"></i> Rejected
                                                </small>
                                            @else
                                                <small class="text-gray-500">-</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                    data-bs-target="#viewClaimModal{{ $claim->id }}">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($claim->status == 'rejected')
                                                    <button class="px-3 py-1.5 bg-amber-500 hover:bg-amber-600 text-white rounded-lg text-xs transition" title="Resubmit">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Claim Modal -->
                                    <div class="modal fade" id="viewClaimModal{{ $claim->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">BPJS Claim Details</h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Claim ID:</strong>
                                                            <p>{{ $claim->claim_id ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                @if ($claim->status == 'approved')
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Approved</span>
                                                                @elseif($claim->status == 'pending')
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                                                @else
                                                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Patient Name:</strong>
                                                            <p>{{ $claim->patient?->name ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>BPJS Number:</strong>
                                                            <p>{{ $claim->patient?->bpjs_number ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Diagnosis:</strong>
                                                            <p>{{ $claim->diagnosis_code }} -
                                                                {{ $claim->diagnosis_name ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Procedure:</strong>
                                                            <p>{{ $claim->procedure_code ?? '-' }} -
                                                                {{ $claim->procedure_name ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Claim Amount:</strong>
                                                            <p class="text-blue-600">
                                                                <strong>Rp
                                                                    {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Approved Amount:</strong>
                                                            <p class="text-emerald-600">
                                                                <strong>Rp
                                                                    {{ number_format($claim->approved_amount ?? 0, 0, ',', '.') }}</strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    @if ($claim->rejection_reason)
                                                        <div class="alert alert-danger">
                                                            <strong>Rejection Reason:</strong>
                                                            <p class="mb-0">{{ $claim->rejection_reason }}</p>
                                                        </div>
                                                    @endif
                                                    <div class="mb-3">
                                                        <strong>Submission Details:</strong>
                                                        <pre class="bg-gray-50 p-2 rounded"><code>{{ json_encode($claim->submission_data ?? [], JSON_PRETTY_PRINT) }}</code></pre>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-10">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No BPJS claims found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if (isset($claims) && $claims->hasPages())
                        <div class="mt-3">
                            {{ $claims->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Claim Modal -->
    <div class="modal fade" id="submitClaimModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit BPJS Claim</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('integration.bpjs-claims.submit') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-red-600">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                @foreach ($patients ?? [] as $patient)
                                    <option value="{{ $patient->id }}">
                                        {{ $patient->name }} - {{ $patient->bpjs_number ?? 'No BPJS Number' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis Code (ICD-10) <span class="text-red-600">*</span></label>
                            <input type="text" name="diagnosis_code" class="form-control" required
                                placeholder="e.g., J06.9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Procedure Code (ICD-9-CM)</label>
                            <input type="text" name="procedure_code" class="form-control" placeholder="e.g., 47.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Amount <span class="text-red-600">*</span></label>
                            <input type="number" name="claim_amount" class="form-control" required placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supporting Documents</label>
                            <textarea name="documents" class="form-control" rows="3" placeholder="List supporting documents..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-paper-plane"></i> Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
