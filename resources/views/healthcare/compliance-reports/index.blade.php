<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Compliance Reports') }}</h2>
            <a href="{{ route('healthcare.compliance-reports.create') }}"
                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                    class="fas fa-plus mr-2"></i>New Report</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                    {{ session('success') }}</div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-blue-100 rounded-md p-3"><i
                                class="fas fa-file-alt text-blue-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Total Reports</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['total'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3"><i
                                class="fas fa-pencil-alt text-yellow-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Draft</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['draft'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-purple-100 rounded-md p-3"><i
                                class="fas fa-clock text-purple-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Pending Review</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['pending_review'] }}</p>
                        </div>
                    </div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 rounded-md p-3"><i
                                class="fas fa-check-circle text-green-600 text-xl"></i></div>
                        <div class="ml-4">
                            <p class="text-sm font-medium text-gray-500">Completed</p>
                            <p class="text-2xl font-bold text-gray-900">{{ $statistics['completed'] }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Reports</h3>
                </div>
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Report Number</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Type</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Period</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse ($reports as $report)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $report->report_number }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 py-1 text-xs font-semibold rounded-full {{ $report->report_type === 'hipaa' ? 'bg-blue-100 text-blue-800' : ($report->report_type === 'jci' ? 'bg-green-100 text-green-800' : ($report->report_type === 'iso' ? 'bg-purple-100 text-purple-800' : ($report->report_type === 'regulatory' ? 'bg-orange-100 text-orange-800' : 'bg-gray-100 text-gray-800'))) }}">{{ strtoupper($report->report_type) }}</span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $report->report_date->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                        {{ $report->reporting_period_start->format('d/m/Y') }} -
                                        {{ $report->reporting_period_end->format('d/m/Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($report->status === 'draft')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800">Draft</span>
                                        @elseif($report->status === 'pending_review')
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-purple-100 text-purple-800">Pending
                                                Review</span>
                                        @else
                                            <span
                                                class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Completed</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="{{ route('healthcare.compliance-reports.show', $report) }}"
                                            class="text-blue-600 hover:text-blue-900 mr-3"><i
                                                class="fas fa-eye"></i></a>
                                        <a href="{{ route('healthcare.compliance-reports.print', $report) }}"
                                            class="text-green-600 hover:text-green-900 mr-3"><i
                                                class="fas fa-print"></i></a>
                                        <button onclick="deleteReport({{ $report->id }})"
                                            class="text-red-600 hover:text-red-900"><i
                                                class="fas fa-trash"></i></button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No compliance reports
                                        found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="mt-4">
                {{ $reports->links() }}
            </div>
        </div>
    </div>

    <script>
        function deleteReport(id) {
            if (confirm('Are you sure you want to delete this report?')) {
                fetch(`{{ route('healthcare.compliance-reports.destroy', '') }}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        alert(data.message);
                        location.reload();
                    })
                    .catch(error => alert('Delete failed'));
            }
        }
    </script>
</x-app-layout>
