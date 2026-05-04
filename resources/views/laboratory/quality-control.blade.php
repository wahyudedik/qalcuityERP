<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-check-double text-blue-600"></i> Quality Control
            </h1>
            <p class="text-gray-500">Laboratory quality control and calibration</p>
        </div>
        <div>
            <button class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition" data-bs-toggle="modal" data-bs-target="#addQCModal">
                <i class="fas fa-plus"></i> Add QC Record
            </button>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Quality Control:</strong> Regular QC checks ensure accurate and reliable laboratory test results.
                All equipment must be calibrated and validated according to schedule.
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
                                    <th>Equipment/Test</th>
                                    <th>QC Type</th>
                                    <th>Result</th>
                                    <th>Acceptable Range</th>
                                    <th>Status</th>
                                    <th>Performed By</th>
                                    <th>Next Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($qcRecords as $qc)
                                    <tr>
                                        <td>{{ $qc->performed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                                        <td><strong>{{ $qc->equipment_name ?? ($qc->test_name ?? '-') }}</strong></td>
                                        <td>{{ ucfirst($qc->qc_type ?? 'Routine') }}</td>
                                        <td><code>{{ $qc->result_value ?? '-' }}</code></td>
                                        <td>{{ $qc->acceptable_range ?? '-' }}</td>
                                        <td>
                                            @if ($qc->status == 'pass')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Pass</span>
                                            @elseif($qc->status == 'fail')
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-700">Fail</span>
                                            @else
                                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700">Warning</span>
                                            @endif
                                        </td>
                                        <td>{{ $qc->performed_by?->name ?? '-' }}</td>
                                        <td>
                                            @if ($qc->next_due_date)
                                                @if ($qc->next_due_date->isPast())
                                                    <span
                                                        class="text-red-600 font-bold">{{ $qc->next_due_date->format('d/m/Y') }}</span>
                                                @else
                                                    {{ $qc->next_due_date->format('d/m/Y') }}
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-6 text-gray-400">No QC records found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $qcRecords->links() }}
                </div>
            </div>
        </div>
    </div>

    <!-- Add QC Record Modal -->
    <div class="modal fade" id="addQCModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('healthcare.laboratory.quality-control.store') }}" method="POST">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title">Add Quality Control Record</h5>
                        <button type="button" class="text-gray-400 hover:text-gray-600" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Equipment/Test Name</label>
                            <input type="text" name="equipment_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">QC Type</label>
                            <select name="qc_type" class="form-select" required>
                                <option value="routine">Routine QC</option>
                                <option value="calibration">Calibration</option>
                                <option value="validation">Validation</option>
                                <option value="maintenance">Maintenance Check</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Result Value</label>
                                <input type="text" name="result_value" class="form-control" required>
                            </div>
                            <div class="w-full md:w-1/2">
                                <label class="form-label">Acceptable Range</label>
                                <input type="text" name="acceptable_range" class="form-control"
                                    placeholder="e.g., 95-105">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pass">Pass</option>
                                <option value="warning">Warning</option>
                                <option value="fail">Fail</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="px-4 py-2 bg-gray-500 hover:bg-gray-600 text-white rounded-xl text-sm font-medium transition" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Save QC Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
