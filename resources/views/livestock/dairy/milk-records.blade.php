@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Milk Production Records</h1>
            <p class="mt-2 text-gray-600">Track daily milk production and quality metrics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Records</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">{{ $stats['total_records'] }}</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Today's Production</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">{{ number_format($stats['today_production'], 1) }} L</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Avg Daily (7d)</div>
                <div class="mt-2 text-3xl font-bold text-green-600">{{ number_format($stats['avg_daily_production'], 1) }} L</div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">High Quality %</div>
                <div class="mt-2 text-3xl font-bold text-purple-600">{{ $stats['high_quality_percentage'] }}%</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Milk Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Herd</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Session</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Volume (L)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quality</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($records as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ $record->record_date->format('d M Y') }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $record->herd?->name ?? 'N/A' }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    {{ ucfirst($record->milking_session) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ number_format($record->milk_volume_liters, 2) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    @if ($record->quality_grade === 'A') bg-green-100 text-green-800
                                    @elseif($record->quality_grade === 'B') bg-yellow-100 text-yellow-800
                                    @else bg-red-100 text-red-800 @endif">
                                        {{ $record->quality_grade ?? 'N/A' }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No milk records found</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                {{ $records->links() }}
            </div>
        </div>
    </div>
@endsection
