<x-app-layout>
    <x-slot name="header">{{ __('Compliance Report Details') }} -
        {{ $report->report_number }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.compliance-reports.index') }}"
            class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                class="fas fa-arrow-left mr-2"></i>Back</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-file-alt mr-2 text-blue-600"></i>Report Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $report->report_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full {{ $report->report_type === 'hipaa' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'jci' ? 'bg-green-100 text-green-800' : ($report->report_type === 'iso' ? 'bg-purple-100 text-purple-800' : ($report->report_type === 'regulatory' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'))) }}">{{ strtoupper($report->report_type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Report Date</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->report_date->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reporting Period</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $report->reporting_period_start->format('d/m/Y') }} -
                                {{ $report->reporting_period_end->format('d/m/Y') }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                @if ($report->status === 'draft')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                @elseif($report->status === 'pending_review')
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">Pending
                                        Review</span>
                                @else
                                    <span
                                        class="px-2 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                @endif
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-user mr-2 text-purple-600"></i>Workflow Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created By</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->createdBy?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                        </div>
                        @if ($report->submitted_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $report->submitted_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif
                        @if ($report->approved_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved By</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $report->reviewer?->name ?? 'N/A' }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved At</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $report->approved_at->format('d/m/Y H:i') }}
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if ($report->findings)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-search mr-2 text-orange-600"></i>Findings</h3>
                    <div class="space-y-2">
                        @if (is_array($report->findings))
                            @foreach ($report->findings as $index => $finding)
                                <div class="flex items-start">
                                    <span
                                        class="flex-shrink-0 w-6 h-6 bg-orange-100 text-orange-600 rounded-full flex items-center justify-center text-xs font-semibold mr-3">{{ $index + 1 }}</span>
                                    <p class="text-sm text-gray-700">{{ $finding }}</p>
                                </div>
                            @endforeach
                        @else
                            <p class="text-sm text-gray-700">{{ $report->findings }}</p>
                        @endif
                    </div>
                </div>
            @endif

            @if ($report->recommendations)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-lightbulb mr-2 text-yellow-600"></i>Recommendations</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->recommendations }}</p>
                </div>
            @endif

            @if ($report->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-green-600"></i>Additional Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->notes }}</p>
                </div>
            @endif

            @if ($report->review_notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-clipboard-check mr-2 text-blue-600"></i>Review Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $report->review_notes }}</p>
                </div>
            @endif

            @if ($report->status === 'draft')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Actions</h3>
                    <button onclick="submitForReview()"
                        class="px-6 py-3 bg-purple-600 text-white rounded-md hover:bg-purple-700"><i
                            class="fas fa-paper-plane mr-2"></i>Submit for Review</button>
                </div>
            @elseif($report->status === 'pending_review')
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-tasks mr-2 text-indigo-600"></i>Review Actions</h3>
                    <div class="space-y-4">
                        <div>
                            <label for="review_notes" class="block text-sm font-medium text-gray-700 mb-2">Review
                                Notes</label>
                            <textarea id="review_notes" rows="3"
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Enter review notes..."></textarea>
                        </div>
                        <button onclick="approveReport()"
                            class="px-6 py-3 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                                class="fas fa-check mr-2"></i>Approve Report</button>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        async function submitForReview() {
            const confirmed = await Dialog.confirm('Submit this report for review?');
            if (!confirmed) return;
            fetch('{{ route('healthcare.compliance-reports.submit-for-review', $report) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    Dialog.alert(data.message);
                    location.reload();
                })
                .catch(error => Dialog.warning('Submit failed'));
        }

        async function approveReport() {
            const reviewNotes = document.getElementById('review_notes').value;

            const confirmed = await Dialog.confirm('Approve this report?');
            if (!confirmed) return;
            fetch('{{ route('healthcare.compliance-reports.approve', $report) }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        review_notes: reviewNotes
                    })
                })
                .then(response => response.json())
                .then(data => {
                    Dialog.alert(data.message);
                    location.reload();
                })
                .catch(error => Dialog.warning('Approval failed'));
        }
    </script>
</x-app-layout>
