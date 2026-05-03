@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Waste Management Logs</h1>
            <p class="mt-2 text-gray-600">Track livestock waste collection and disposal</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Logs</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_logs'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Waste</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($stats['total_waste_kg']) }} kg</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Eco-Friendly %</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ $stats['eco_friendly_percentage'] }}%</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Revenue</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">Rp {{ number_format($stats['total_revenue']) }}</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Waste Logs</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Herd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Method</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Revenue</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($logs as $log)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $log->collection_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $log->herd?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ ucfirst(str_replace('_', ' ', $log->waste_type)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($log->quantity_kg) }} kg</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ ucfirst(str_replace('_', ' ', $log->disposal_method)) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-green-600">
                                    {{ $log->revenue_amount ? 'Rp ' . number_format($log->revenue_amount) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">No logs found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
@endsection
