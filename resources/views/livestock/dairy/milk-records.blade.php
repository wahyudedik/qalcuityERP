@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Milk Production Records</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Track daily milk production and quality metrics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Records</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_records'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Today's Production</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['today_production'], 1) }} L</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Avg Daily (7d)</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ number_format($stats['avg_daily_production'], 1) }} L</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">High Quality %</div>
                <div class="mt-2 text-3xl font-bold text-purple-600 dark:text-purple-400">{{ $stats['high_quality_percentage'] }}%</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Milk Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Herd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Volume (L)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Quality</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->record_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                    {{ $record->herd?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ ucfirst($record->milking_session) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ number_format($record->milk_volume_liters, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($record->quality_grade === 'A') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @elseif($record->quality_grade === 'B') bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200
                                    @else bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200 @endif">
                                        {{ $record->quality_grade ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No milk records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection
