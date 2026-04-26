@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Vaccination Records</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage livestock vaccination schedules and history</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Vaccinations</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_vaccinations'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['upcoming_vaccinations'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Completed</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['completed_vaccinations'] }}</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Vaccination Schedule</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Herd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Vaccine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Batch No.</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($vaccinations as $vaccination)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $vaccination->vaccination_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $vaccination->herd?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $vaccination->vaccine_name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $vaccination->batch_number ?? '-' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if ($vaccination->status === 'scheduled') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                @elseif($vaccination->status === 'completed') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @endif">
                                        {{ ucfirst($vaccination->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No vaccination records found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $vaccinations->links() }}
            </div>
        </div>
    </div>
@endsection
