@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Composting Batches</h1>
            <p class="mt-2 text-gray-600">Manage composting operations and quality</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Active Batches</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ $stats['active_batches'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Compost</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($stats['total_compost_kg']) }} kg</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Avg Quality Score</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ number_format($stats['avg_quality_score'], 1) }}/10</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Composting Batches</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Code</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Start Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Initial Weight</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quality</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($batches as $batch)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $batch->batch_code }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $batch->start_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ number_format($batch->initial_weight_kg) }} kg</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($batch->status === 'active') bg-blue-100 text-blue-800
                                    @elseif($batch->status === 'curing') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800 @endif">
                                        {{ ucfirst($batch->status) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $batch->quality_score ? number_format($batch->quality_score, 1) . '/10' : 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No batches found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $batches->links() }}
            </div>
        </div>
    </div>
@endsection
