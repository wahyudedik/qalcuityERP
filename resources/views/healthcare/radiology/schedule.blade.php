<x-app-layout>
    <x-slot name="header">{{ __('Radiology Schedule') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="mb-4">
                <form method="GET" class="flex items-center gap-2">
                    <input type="date" name="date"
                        value="{{ $date instanceof \Carbon\Carbon ? $date->format('Y-m-d') : $date }}"
                        class="border-gray-300 rounded-md shadow-sm">
                    <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Filter</button>
                </form>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Exam</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Priority
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($exams as $exam)
                                <tr>
                                    <td class="px-6 py-4 text-sm">{{ $exam->scheduled_date?->format('H:i') ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm">{{ $exam->patient?->name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ $exam->exam?->exam_name ?? '-' }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst($exam->priority) }}</td>
                                    <td class="px-6 py-4 text-sm">{{ ucfirst(str_replace('_', ' ', $exam->status)) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500">No exams scheduled
                                        for this date.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
