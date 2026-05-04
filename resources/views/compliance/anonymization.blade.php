<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-user-secret text-blue-600"></i> Data Anonymization
            </h1>
            <p class="text-gray-500">Patient data privacy and anonymization tools</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#anonymizeModal">
                <i class="fas fa-user-secret"></i> Anonymize Data
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $stats['anonymized_records'] ?? 0 }}</h3>
                    <small class="text-gray-500">Anonymized Records</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $stats['pending_anonymization'] ?? 0 }}</h3>
                    <small class="text-gray-500">Pending Anonymization</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/3">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $stats['retention_days'] ?? 0 }}</h3>
                    <small class="text-gray-500">Days Until Auto-Anonymize</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Anonymization Rules</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Data Type</th>
                                    <th>Trigger</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Last Run</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rules ?? [] as $rule)
                                    <tr>
                                        <td><strong>{{ $rule['name'] ?? '-' }}</strong></td>
                                        <td>
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-sky-100 text-sky-700">{{ $rule['data_type'] ?? '-' }}</span>
                                        </td>
                                        <td>{{ $rule['trigger'] ?? '-' }}</td>
                                        <td>
                                            @if ($rule['method'] == 'pseudonymization')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Pseudonymization</span>
                                            @elseif($rule['method'] == 'generalization')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Generalization</span>
                                            @elseif($rule['method'] == 'suppression')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Suppression</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">{{ ucfirst($rule['method']) }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule['is_active'])
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Active</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-700">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($rule['last_run'])
                                                <small>{{ $rule['last_run'] }}</small>
                                                <br><small class="text-gray-500">{{ $rule['last_run_diff'] ?? '-' }}</small>
                                            @else
                                                <span class="text-gray-500">Never</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-gray-400">No anonymization rules configured
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

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="px-5 py-4 border-b border-gray-200">
                    <h5 class="mb-0">Recent Anonymization Activity</h5>
                </div>
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Record Type</th>
                                    <th>Records Processed</th>
                                    <th>Method</th>
                                    <th>Triggered By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($activities ?? [] as $activity)
                                    <tr>
                                        <td>
                                            <small>{{ $activity['date'] ?? '-' }}</small>
                                        </td>
                                        <td>{{ $activity['record_type'] ?? '-' }}</td>
                                        <td>
                                            <strong>{{ $activity['records_processed'] ?? 0 }}</strong> records
                                        </td>
                                        <td>{{ $activity['method'] ?? '-' }}</td>
                                        <td>{{ $activity['triggered_by'] ?? 'System' }}</td>
                                        <td>
                                            @if ($activity['status'] == 'completed')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Completed</span>
                                            @elseif($activity['status'] == 'in_progress')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">In Progress</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Failed</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-user-secret fa-2x text-gray-500 mb-2"></i>
                                            <p class="text-gray-500">No anonymization activity</p>
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

    <!-- Anonymize Data Modal -->
    <div class="modal fade" id="anonymizeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Anonymize Patient Data</h5>
                    <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('compliance.anonymization.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action will permanently anonymize patient data.
                            Original data cannot be recovered after anonymization.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Scope <span class="text-red-600">*</span></label>
                            <select name="scope" class="form-select" required>
                                <option value="">Select Scope</option>
                                <option value="discharged">Discharged Patients (> 90 days)</option>
                                <option value="deceased">Deceased Patients</option>
                                <option value="consent_withdrawn">Patients Who Withdrew Consent</option>
                                <option value="custom">Custom Date Range</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Anonymization Method <span class="text-red-600">*</span></label>
                            <select name="method" class="form-select" required>
                                <option value="">Select Method</option>
                                <option value="pseudonymization">Pseudonymization (Replace identifiers)</option>
                                <option value="generalization">Generalization (Broaden values)</option>
                                <option value="suppression">Suppression (Remove data)</option>
                                <option value="aggregation">Aggregation (Combine data)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Fields to Anonymize</label>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div class="w-full md:w-1/3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="name"
                                            id="field_name" checked>
                                        <label class="form-check-label" for="field_name">Patient Name</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="phone"
                                            id="field_phone" checked>
                                        <label class="form-check-label" for="field_phone">Phone Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="email"
                                            id="field_email" checked>
                                        <label class="form-check-label" for="field_email">Email</label>
                                    </div>
                                </div>
                                <div class="w-full md:w-1/3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="address"
                                            id="field_address" checked>
                                        <label class="form-check-label" for="field_address">Address</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]"
                                            value="id_number" id="field_id_number" checked>
                                        <label class="form-check-label" for="field_id_number">ID Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]"
                                            value="insurance" id="field_insurance">
                                        <label class="form-check-label" for="field_insurance">Insurance Details</label>
                                    </div>
                                </div>
                                <div class="w-full md:w-1/3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="notes"
                                            id="field_notes">
                                        <label class="form-check-label" for="field_notes">Clinical Notes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="images"
                                            id="field_images">
                                        <label class="form-check-label" for="field_images">Medical Images</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason for Anonymization <span class="text-red-600">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required
                                placeholder="Explain why this data needs to be anonymized..."></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_anonymize" required>
                            <label class="form-check-label" for="confirm_anonymize">
                                I confirm that I have authorization to anonymize this data
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">
                            <i class="fas fa-user-secret"></i> Anonymize Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-3">
        <div class="w-full">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Privacy Compliance:</strong> Patient data anonymization is performed in accordance with HIPAA and
                data protection regulations.
                Anonymized data cannot be used to identify individual patients and is retained for research and statistical
                purposes.
            </div>
        </div>
    </div>
</x-app-layout>
