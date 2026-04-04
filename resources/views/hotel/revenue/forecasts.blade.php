@extends('layouts.app')

@section('title', 'Occupancy Forecasts')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Occupancy Forecasts</h1>
                <p class="text-gray-600">AI-powered demand predictions and occupancy forecasting</p>
            </div>
            <form action="{{ route('revenue.forecasts.generate') }}" method="POST" class="inline">
                @csrf
                <input type="hidden" name="days" value="{{ $days }}">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    Refresh Forecasts
                </button>
            </form>
        </div>

        <!-- Demand Indicators -->
        @if (isset($demandIndicators))
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">High Demand Days</div>
                    <div class="text-2xl font-bold text-red-600">{{ $demandIndicators['high_demand_days'] }}</div>
                    <div class="text-xs text-gray-500">Next {{ $days }} days</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Low Demand Days</div>
                    <div class="text-2xl font-bold text-yellow-600">{{ $demandIndicators['low_demand_days'] }}</div>
                    <div class="text-xs text-gray-500">Next {{ $days }} days</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Average Occupancy</div>
                    <div class="text-2xl font-bold text-blue-600">
                        {{ number_format($demandIndicators['average_occupancy'], 1) }}%</div>
                    <div class="text-xs text-gray-500">Forecasted average</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Peak Date</div>
                    <div class="text-2xl font-bold text-green-600">
                        {{ $demandIndicators['peak_date'] ? \Carbon\Carbon::parse($demandIndicators['peak_date'])->format('M d') : 'N/A' }}
                    </div>
                    <div class="text-xs text-gray-500">Highest occupancy</div>
                </div>
            </div>
        @endif

        <!-- Forecasts Table -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Detailed Forecasts</h3>
                <div class="flex space-x-2">
                    <a href="?days=30"
                        class="px-3 py-1 text-sm rounded {{ $days == 30 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">30
                        Days</a>
                    <a href="?days=60"
                        class="px-3 py-1 text-sm rounded {{ $days == 60 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">60
                        Days</a>
                    <a href="?days=90"
                        class="px-3 py-1 text-sm rounded {{ $days == 90 ? 'bg-blue-600 text-white' : 'bg-gray-200 text-gray-700' }}">90
                        Days</a>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Day</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Total Rooms</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Projected Booked</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Occupancy Rate</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Projected ADR</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">RevPAR</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Confidence</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($forecasts as $forecast)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $forecast->forecast_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3">{{ $forecast->forecast_date->format('l') }}</td>
                                <td class="px-4 py-3 text-center">{{ $forecast->total_rooms }}</td>
                                <td class="px-4 py-3 text-center">{{ $forecast->projected_booked }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 rounded text-xs font-medium
                                {{ $forecast->projected_occupancy_rate >= 80
                                    ? 'bg-red-100 text-red-700'
                                    : ($forecast->projected_occupancy_rate >= 60
                                        ? 'bg-green-100 text-green-700'
                                        : 'bg-yellow-100 text-yellow-700') }}">
                                        {{ number_format($forecast->projected_occupancy_rate, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">${{ number_format($forecast->projected_adr, 2) }}</td>
                                <td class="px-4 py-3 text-center">${{ number_format($forecast->projected_revpar, 2) }}</td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center">
                                        <div class="w-16 bg-gray-200 rounded-full h-2 mr-2">
                                            <div class="bg-blue-600 h-2 rounded-full"
                                                style="width: {{ $forecast->confidence_level }}%"></div>
                                        </div>
                                        <span class="text-sm">{{ number_format($forecast->confidence_level, 0) }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                    No forecasts available. Click "Refresh Forecasts" to generate predictions.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
