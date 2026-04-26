@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Breeding Records</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Track breeding programs and genetics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Records</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_records'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Pregnant</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ $stats['pregnant_count'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Upcoming Births</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600 dark:text-yellow-400">{{ $stats['upcoming_births'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Success Rate</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['success_rate'] }}%</div>
            </div>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Breeding Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Mating Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Dam ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Sire ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->mating_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $record->dam_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $record->sire_id }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ ucfirst(str_replace('_', ' ', $record->mating_type)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($record->status === 'pregnant') bg-blue-100 dark:bg-blue-900 text-blue-800 dark:text-blue-200
                                    @elseif($record->status === 'born') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                    @elseif($record->status === 'failed') bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200
                                    @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                        {{ ucfirst($record->status) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No records found</td>
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
