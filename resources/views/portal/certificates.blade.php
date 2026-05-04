<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-certificate text-blue-600"></i> My Medical Certificates
            </h1>
            <p class="text-gray-500">Download your medical certificates and documents</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#requestCertificateModal">
                <i class="fas fa-plus"></i> Request Certificate
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-blue-400">
                <div class="p-5 text-center">
                    <h3 class="text-blue-600">{{ $stats['total_certificates'] ?? 0 }}</h3>
                    <small class="text-gray-500">Total Certificates</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['approved'] ?? 0 }}</h3>
                    <small class="text-gray-500">Approved</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Certificate List</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Type</th>
                                    <th>Issue Date</th>
                                    <th>Valid Until</th>
                                    <th>Issued By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($certificates as $cert)
                                    <tr>
                                        <td>
                                            <strong>{{ $cert->certificate_number ?? '-' }}</strong>
                                        </td>
                                        <td>
                                            @if ($cert->type == 'sick_leave')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Sick Leave</span>
                                            @elseif($cert->type == 'fitness')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Fitness Certificate</span>
                                            @elseif($cert->type == 'medical_report')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Medical Report</span>
                                            @elseif($cert->type == 'vaccination')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">Vaccination Certificate</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($cert->type ?? '-') }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <small>{{ $cert->issue_date->format('d/m/Y') ?? '-' }}</small>
                                        </td>
                                        <td>
                                            @if ($cert->valid_until)
                                                <small>{{ $cert->valid_until->format('d/m/Y') }}</small>
                                                @if ($cert->valid_until->isPast())
                                                    <br><span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Expired</span>
                                                @endif
                                            @else
                                                <small class="text-gray-500">No Expiry</small>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="flex items-center">
                                                <div class="rounded-full bg-primary text-white flex items-center justify-center mr-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user-md fa-xs"></i>
                                                </div>
                                                <strong>{{ $cert->doctor_name ?? '-' }}</strong>
                                            </div>
                                        </td>
                                        <td>
                                            @if ($cert->status == 'approved')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Approved</span>
                                            @elseif($cert->status == 'pending')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Pending</span>
                                            @elseif($cert->status == 'rejected')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejected</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($cert->status) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($cert->status == 'approved')
                                                <div class="flex gap-1">
                                                    <button class="px-3 py-1.5 bg-sky-500 hover:bg-sky-600 text-white rounded-lg text-xs transition" data-bs-toggle="modal"
                                                        data-bs-target="#viewCertModal{{ $cert->id }}">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="px-3 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs transition" title="Download"
                                                        onclick="window.print()">
                                                        <i class="fas fa-download"></i> Download
                                                    </button>
                                                </div>
                                            @else
                                                <span class="text-gray-500">-</span>
                                            @endif
                                        </td>
                                    </tr>

                                    <!-- View Certificate Modal -->
                                    <div class="modal fade" id="viewCertModal{{ $cert->id }}" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Medical Certificate</h5>
                                                    <button type="button" class="text-gray-400 hover:text-gray-600"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-center mb-4">
                                                        <h4>
                                                            <i class="fas fa-hospital text-blue-600"></i>
                                                            Qalcuity Medical Center
                                                        </h4>
                                                        <p class="text-gray-500">Medical Certificate</p>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Certificate Number:</strong>
                                                            <p>{{ $cert->certificate_number ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Type:</strong>
                                                            <p>{{ ucfirst(str_replace('_', ' ', $cert->type ?? '-')) }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Patient Name:</strong>
                                                            <p>{{ $cert->patient_name ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Issue Date:</strong>
                                                            <p>{{ $cert->issue_date->format('d/m/Y') ?? '-' }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Diagnosis/Condition:</strong>
                                                        <p>{{ $cert->diagnosis ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Medical Notes:</strong>
                                                        <p class="bg-gray-50 p-3 rounded" style="white-space: pre-wrap;">
                                                            {{ $cert->notes ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Issued By:</strong>
                                                            <p>{{ $cert->doctor_name ?? '-' }}</p>
                                                        </div>
                                                        <div class="w-full md:w-1/2">
                                                            <strong>Doctor's Signature:</strong>
                                                            <p class="text-gray-500">[Digital Signature]</p>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        This certificate is digitally signed and verified.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-medium transition" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print Certificate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-10">
                                            <i class="fas fa-certificate fa-3x text-gray-500 mb-3"></i>
                                            <p class="text-gray-500">No medical certificates found</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Request Certificate Modal -->
    <div class="modal fade" id="requestCertificateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Medical Certificate</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('portal.certificates.request') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Certificate Type <span class="text-red-600">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="sick_leave">Sick Leave Certificate</option>
                                <option value="fitness">Fitness Certificate</option>
                                <option value="medical_report">Medical Report</option>
                                <option value="vaccination">Vaccination Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose <span class="text-red-600">*</span></label>
                            <input type="text" name="purpose" class="form-control" required
                                placeholder="e.g., Work requirement, Insurance claim">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any specific requirements..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
