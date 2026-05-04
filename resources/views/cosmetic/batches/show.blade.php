@extends('layouts.app')

@section('title', 'Batch ' . $batch->batch_number)

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="{{ route('cosmetic.batches.index') }}" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Batches
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900">{{ $batch->batch_number }}</h1>
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full
                        @if ($batch->status == 'draft') bg-gray-100 text-gray-800
                        @elseif($batch->status == 'in_progress') bg-blue-100 text-blue-800
                        @elseif($batch->status == 'qc_pending') bg-yellow-100 text-yellow-800
                        @elseif($batch->status == 'released') bg-green-100 text-green-800
                        @elseif($batch->status == 'rejected') bg-red-100 text-red-800
                        @else bg-orange-100 text-orange-800 @endif">
                            {{ $batch->status_label }}
                        </span>
                    </div>
                    <p class="mt-1 text-lg text-gray-600">
                        {{ $batch->formula?->formula_name }}
                        <span class="text-sm text-gray-500">({{ $batch->formula?->formula_code }})</span>
                    </p>
                </div>
                <div class="flex gap-2">
                    @if (!$batch->isReleased() && !$batch->isRejected())
                        <form method="POST" action="{{ route('cosmetic.batches.update-status', $batch) }}" class="inline">
                            @csrf
                            <select name="status" onchange="this.form.submit()"
                                class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                                <option value="draft" {{ $batch->status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="in_progress" {{ $batch->status == 'in_progress' ? 'selected' : '' }}>In
                                    Progress</option>
                                <option value="qc_pending" {{ $batch->status == 'qc_pending' ? 'selected' : '' }}>QC Pending
                                </option>
                                @if ($batch->canBeReleased())
                                    <option value="released">Release Batch</option>
                                @endif
                                <option value="rejected">Reject</option>
                                <option value="on_hold">On Hold</option>
                            </select>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Planned Quantity</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">{{ number_format($batch->planned_quantity, 2) }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Actual Quantity</div>
                <div class="mt-2 text-2xl font-bold text-blue-600">
                    {{ $batch->actual_quantity ? number_format($batch->actual_quantity, 2) : '-' }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Yield</div>
                <div
                    class="mt-2 text-2xl font-bold 
                @if ($batch->yield_percentage >= 95) text-green-600
                @elseif($batch->yield_percentage >= 90) text-yellow-600
                @elseif($batch->yield_percentage) text-red-600
                @else text-gray-400 @endif">
                    {{ $batch->yield_percentage ? number_format($batch->yield_percentage, 1) . '%' : '-' }}
                </div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">QC Checks</div>
                <div class="mt-2 text-2xl font-bold text-gray-900">
                    {{ $qualityChecks->where('result', 'pass')->count() }}/{{ $qualityChecks->count() }}
                </div>
                <div class="text-xs text-gray-500">Passed</div>
            </div>
        </div>

        <!-- Tabs -->
        <div x-data="{ activeTab: 'production' }" class="space-y-6">
            <!-- Tab Navigation -->
            <div class="bg-white rounded-lg shadow">
                <div class="border-b border-gray-200">
                    <nav class="flex -mb-px">
                        <button @click="activeTab = 'production'"
                            :class="activeTab === 'production' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            Production Info
                        </button>
                        <button @click="activeTab = 'qc'"
                            :class="activeTab === 'qc' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            QC Checks ({{ $qualityChecks->count() }})
                        </button>
                        <button @click="activeTab = 'rework'"
                            :class="activeTab === 'rework' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            Rework Logs ({{ $reworkLogs->count() }})
                        </button>
                        <button @click="activeTab = 'actions'"
                            :class="activeTab === 'actions' ? 'border-blue-500 text-blue-600' :
                                'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                            class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                            Actions
                        </button>
                    </nav>
                </div>

                <!-- Production Info Tab -->
                <div x-show="activeTab === 'production'" class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Production Details</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <strong class="text-sm text-gray-700">Batch Number:</strong>
                            <p class="text-gray-900">{{ $batch->batch_number }}</p>
                        </div>
                        <div>
                            <strong class="text-sm text-gray-700">Formula:</strong>
                            <p class="text-gray-900">{{ $batch->formula?->formula_name }}</p>
                        </div>
                        <div>
                            <strong class="text-sm text-gray-700">Production Date:</strong>
                            <p class="text-gray-900">{{ $batch->production_date->format('d M Y') }}</p>
                        </div>
                        @if ($batch->expiry_date)
                            <div>
                                <strong class="text-sm text-gray-700">Expiry Date:</strong>
                                <p class="{{ $batch->isExpired() ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                    {{ $batch->expiry_date->format('d M Y') }}
                                    @if ($batch->isExpired())
                                        ⚠️ Expired
                                    @elseif($batch->days_until_expiry <= 30)
                                        ({{ $batch->days_until_expiry }} days left)
                                    @endif
                                </p>
                            </div>
                        @endif
                        <div>
                            <strong class="text-sm text-gray-700">Created By:</strong>
                            <p class="text-gray-900">{{ $batch->creator?->name ?? 'Unknown' }}</p>
                        </div>
                        @if ($batch->producer)
                            <div>
                                <strong class="text-sm text-gray-700">Produced By:</strong>
                                <p class="text-gray-900">{{ $batch->producer?->name }}</p>
                            </div>
                        @endif
                    </div>

                    @if ($batch->production_notes)
                        <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                            <strong class="text-sm text-gray-700">Production Notes:</strong>
                            <p class="text-gray-900 mt-1">{{ $batch->production_notes }}</p>
                        </div>
                    @endif
                </div>

                <!-- QC Checks Tab -->
                <div x-show="activeTab === 'qc'" class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Quality Control Checks</h3>
                        @if (!$batch->isReleased() && !$batch->isRejected())
                            <button onclick="document.getElementById('add-qc-modal').classList.remove('hidden')"
                                class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                + Add QC Check
                            </button>
                        @endif
                    </div>

                    @if ($qualityChecks->count() > 0)
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Checkpoint</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Parameter</th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Target
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Actual
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">Result
                                        </th>
                                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Inspector</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach ($qualityChecks as $check)
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $check->check_point_label }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $check->parameter }}</td>
                                            <td class="px-4 py-3 text-sm text-gray-600">{{ $check->target_value ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">{{ $check->actual_value ?? '-' }}
                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full
                                            @if ($check->result == 'pass') bg-green-100 text-green-800
                                            @elseif($check->result == 'fail') bg-red-100 text-red-800
                                            @else bg-yellow-100 text-yellow-800 @endif">
                                                    {{ $check->result_label }}
                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                {{ $check->inspector?->name ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <p>No quality checks yet. Add QC checks during production.</p>
                        </div>
                    @endif
                </div>

                <!-- Rework Logs Tab -->
                <div x-show="activeTab === 'rework'" class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Rework Logs</h3>
                        @if (!$batch->isReleased() && !$batch->isRejected())
                            <button onclick="document.getElementById('add-rework-modal').classList.remove('hidden')"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium rounded-lg transition">
                                + Add Rework
                            </button>
                        @endif
                    </div>

                    @if ($reworkLogs->count() > 0)
                        <div class="space-y-4">
                            @foreach ($reworkLogs as $rework)
                                <div class="border border-gray-200 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <span
                                                class="text-sm font-medium text-gray-900">{{ $rework->rework_code }}</span>
                                            <span
                                                class="ml-2 px-2 py-1 text-xs font-medium rounded-full
                                        @if ($rework->status == 'in_progress') bg-yellow-100 text-yellow-800
                                        @elseif($rework->status == 'completed') bg-green-100 text-green-800
                                        @else bg-red-100 text-red-800 @endif">
                                                {{ $rework->status_label }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="text-sm text-gray-700 space-y-1">
                                        <div><strong>Reason:</strong> {{ $rework->reason }}</div>
                                        <div><strong>Action:</strong> {{ $rework->rework_action }}</div>
                                        <div class="grid grid-cols-3 gap-2 mt-2">
                                            <div>
                                                <span class="text-gray-500">Before:</span>
                                                <span
                                                    class="text-gray-900">{{ number_format($rework->quantity_before, 2) }}</span>
                                            </div>
                                            @if ($rework->quantity_after)
                                                <div>
                                                    <span class="text-gray-500">After:</span>
                                                    <span
                                                        class="text-gray-900">{{ number_format($rework->quantity_after, 2) }}</span>
                                                </div>
                                                <div>
                                                    <span class="text-gray-500">Loss:</span>
                                                    <span
                                                        class="text-red-600 font-medium">{{ number_format($rework->loss_quantity, 2) }}
                                                        ({{ $rework->loss_percentage }}%)</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-400">
                            <p>No rework logs. Good job! No rework needed for this batch.</p>
                        </div>
                    @endif
                </div>

                <!-- Actions Tab -->
                <div x-show="activeTab === 'actions'" class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Batch Actions</h3>

                    <div class="space-y-4">
                        @if ($batch->isDraft())
                            <div class="p-4 bg-blue-50 rounded-lg">
                                <h4 class="font-medium text-blue-900 mb-2">Start Production</h4>
                                <form method="POST" action="{{ route('cosmetic.batches.update-status', $batch) }}"
                                    class="flex gap-2">
                                    @csrf
                                    <input type="hidden" name="status" value="in_progress">
                                    <input type="number" name="actual_quantity" placeholder="Actual quantity"
                                        step="0.01" class="flex-1 px-3 py-2 border border-gray-300 rounded-lg">
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">
                                        Start Production
                                    </button>
                                </form>
                            </div>
                        @endif

                        @if ($batch->canBeReleased())
                            <div class="p-4 bg-green-50 rounded-lg">
                                <h4 class="font-medium text-green-900 mb-2">Release Batch</h4>
                                <p class="text-sm text-green-700 mb-3">All QC checks passed and no open rework. Ready to
                                    release?</p>
                                <form method="POST" action="{{ route('cosmetic.batches.release', $batch) }}">
                                    @csrf
                                    <button type="submit"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg">
                                        Release Batch ✓
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Add QC Check Modal -->
        <div id="add-qc-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Quality Check</h3>
                <form method="POST" action="{{ route('cosmetic.batches.quality-check.add', $batch) }}">
                    @csrf
                    <div class="space-y-3">
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Checkpoint *</label>
                                <select name="check_point" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="mixing">Mixing</option>
                                    <option value="filling">Filling</option>
                                    <option value="packaging">Packaging</option>
                                    <option value="final">Final QC</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Parameter *</label>
                                <input type="text" name="parameter" placeholder="e.g., pH, Viscosity" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div class="grid grid-cols-3 gap-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Target Value</label>
                                <input type="number" name="target_value" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Lower Limit</label>
                                <input type="number" name="lower_limit" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Upper Limit</label>
                                <input type="number" name="upper_limit" step="0.01"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Actual Value</label>
                            <input type="number" name="actual_value" step="0.01"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Observations</label>
                            <textarea name="observations" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                            Add Check
                        </button>
                        <button type="button" onclick="document.getElementById('add-qc-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Add Rework Modal -->
        <div id="add-rework-modal"
            class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Add Rework Log</h3>
                <form method="POST" action="{{ route('cosmetic.batches.rework.add', $batch) }}">
                    @csrf
                    <div class="space-y-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Reason *</label>
                            <textarea name="reason" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Rework Action *</label>
                            <textarea name="rework_action" rows="2" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Quantity Before *</label>
                            <input type="number" name="quantity_before" step="0.01" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <div class="mt-4 flex gap-2">
                        <button type="submit"
                            class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                            Create Rework Log
                        </button>
                        <button type="button"
                            onclick="document.getElementById('add-rework-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
