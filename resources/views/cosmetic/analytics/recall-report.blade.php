@extends('layouts.app')

@section('title', 'Recall Report')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Product Recall Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Track recall effectiveness and resolution</p>
                </div>
                <a href="{{ route('cosmetic.analytics.dashboard') }}" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <input type="date" name="date_from" value="{{ $dateFrom }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <input type="date" name="date_to" value="{{ $dateTo }}"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Filter</button>
            </form>
        </div>

        <!-- Recall Stats -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total Recalls</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $recallStats['total_recalls'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Active Recalls</div>
                <div class="mt-2 text-3xl font-bold text-red-600">{{ $recallStats['active_recalls'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Completed</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $recallStats['completed_recalls'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Avg Completion Time</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">
                    {{ number_format($recallStats['avg_completion_days'], 1) }} days</div>
            </div>
        </div>

        <!-- Recall Details -->
        <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Recall Effectiveness</h3>
            </div>
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recall Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Distributed</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recovered</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Recovery Rate</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Open</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach ($recalls as $item)
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                {{ $item['recall']->batch?->formula?->formula_name ?? 'Unknown' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $item['recall']->recall_date->format('d M Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item['total_distributed'], 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ number_format($item['recovered'], 0) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="text-sm font-bold {{ $item['recovery_rate'] >= 90 ? 'text-green-600' : ($item['recovery_rate'] >= 70 ? 'text-yellow-600' : 'text-red-600') }}">
                                    {{ number_format($item['recovery_rate'], 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $item['days_open'] }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full
                                    {{ $item['recall']->status === 'completed'
                                        ? 'bg-green-100 text-green-800'
                                        : ($item['recall']->status === 'in_progress'
                                            ? 'bg-yellow-100 text-yellow-800'
                                            : 'bg-red-100 text-red-800') }}">
                                    {{ ucfirst(str_replace('_', ' ', $item['recall']->status)) }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
