<x-app-layout>
    <x-slot name="header">{{ __('Ministry Report #' . $report->id) }}</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('healthcare.ministry-reports.index') }}" class="text-blue-600 hover:text-blue-900"><i
                    class="fas fa-arrow-left mr-2"></i>Back to List</a>
    </div>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Information</h3>
                <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Report ID</dt>
                        <dd class="mt-1 text-lg font-bold text-gray-900">#{{ $report->id }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Report Type</dt>
                        <dd class="mt-1">
                            <span
                                class="px-3 py-1 text-sm font-semibold rounded-full {{ $report->report_type === 'monthly' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'quarterly' ? 'bg-green-100 text-green-800' : ($report->report_type === 'annual' ? 'bg-purple-100 text-purple-800' : 'bg-orange-100 text-orange-800')) }}">{{ ucfirst($report->report_type) }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Reporting Period</dt>
                        <dd class="mt-1 text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($report->reporting_period)->format('F Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Status</dt>
                        <dd class="mt-1">
                            @if ($report->status === 'draft')
                                <span
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                            @else
                                <span
                                    class="px-3 py-1 text-sm font-semibold rounded-full bg-green-100 text-green-800">Submitted</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm font-medium text-gray-500">Created At</dt>
                        <dd class="mt-1 text-sm text-gray-900">{{ $report->created_at->format('d/m/Y H:i') }}</dd>
                    </div>
                    @if ($report->submitted_at)
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->submitted_at->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                </dl>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Report Data</h3>
                <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto">{{ json_encode($report->report_data, JSON_PRETTY_PRINT) }}</pre>
            </div>

            @if ($report->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Notes</h3>
                    <p class="text-sm text-gray-700">{{ $report->notes }}</p>
                </div>
            @endif

            @if ($report->status === 'draft')
                <div class="flex justify-end space-x-3">
                    <button onclick="submitReport({{ $report->id }})"
                        class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700"><i
                            class="fas fa-paper-plane mr-2"></i>Submit to Ministry</button>
                </div>
            @endif
        </div>
    </div>

    <script>
        function submitReport(id) {
            if (confirm('Submit this report to Ministry of Health?')) {
                fetch(`{{ route('healthcare.ministry-reports.submit', '') }}/${id}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        Toast.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    })
                    .catch(error => Toast.error('Submit failed'));
            }
        }
    </script>
</x-app-layout>
