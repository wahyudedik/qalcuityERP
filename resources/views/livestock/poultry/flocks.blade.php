@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Poultry Flocks Management</h1>
            <p class="mt-2 text-gray-600 dark:text-gray-400">Manage and monitor your poultry flocks</p>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Flocks</div>
                <div class="mt-2 text-3xl font-bold text-gray-900 dark:text-gray-100">{{ $stats['total_flocks'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Active Flocks</div>
                <div class="mt-2 text-3xl font-bold text-green-600 dark:text-green-400">{{ $stats['active_flocks'] }}</div>
            </div>
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Birds</div>
                <div class="mt-2 text-3xl font-bold text-blue-600 dark:text-blue-400">{{ number_format($stats['total_birds']) }}</div>
            </div>
        </div>

        <!-- Flocks Table -->
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-700">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">Flock List</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Name</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Entry Date</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white dark:bg-gray-800 divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse($flocks as $flock)
                            <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 dark:text-gray-100">
                                    {{ $flock->code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $flock->name }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">{{ $flock->animalLabel() }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">{{ $flock->current_count }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                @if ($flock->status === 'active') bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200
                                @else bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200 @endif">
                                        {{ ucfirst($flock->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 dark:text-gray-400">
                                    {{ $flock->entry_date->format('d M Y') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No flocks found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                {{ $flocks->links() }}
            </div>
        </div>
    </div>
@endsection
