<x-app-layout>
    <x-slot name="header">{{ __('Radiology Exams') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total</p>
                    <p class="text-2xl font-semibold">{{ $statistics['total_exams'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Scheduled</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $statistics['scheduled'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">In Progress</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $statistics['in_progress'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Completed</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $statistics['completed'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending Reports</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $statistics['reports_pending'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Exams Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Order #</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($exams as $exam)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $exam->order_number }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $exam->patient?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $exam->exam?->exam_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $exam->scheduled_date?->format('d M Y') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <span
                                            class="px-2 py-1 text-xs rounded-full
                                            @if ($exam->status === 'completed') bg-green-100 text-green-800
                                            @elseif($exam->status === 'in_progress') bg-yellow-100 text-yellow-800
                                            @elseif($exam->status === 'scheduled') bg-blue-100 text-blue-800
                                            @else bg-gray-100 text-gray-800 @endif">
                                            {{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <a href="{{ route('healthcare.radiology-exams.show', $exam) }}"
                                            class="text-blue-600 hover:underline">View</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">No radiology exams
                                        found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                    <div class="mt-4">
                        {{ $exams->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
