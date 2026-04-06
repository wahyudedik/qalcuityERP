@extends('layouts.app')
@section('title', 'Seasonal Trend Analysis')
@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <a href="{{ route('analytics.dashboard') }}" class="text-blue-600 hover:text-blue-900 text-sm">← Back</a>
            <h1 class="text-3xl font-bold text-gray-900 mt-2">Seasonal Trend Analysis</h1>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Monthly Trends</h3>
                <div class="space-y-2 max-h-96 overflow-y-auto">
                    @foreach ($seasonalData['monthly_trends'] as $trend)
                        <div class="flex justify-between items-center p-2 bg-gray-50 rounded">
                            <div class="text-sm">{{ $trend->month_name }} {{ $trend->year }}</div>
                            <div class="text-sm font-semibold">Rp {{ number_format($trend->total_revenue, 0, ',', '.') }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Seasonal Index</h3>
                <div class="space-y-2">
                    @foreach ($seasonalData['seasonal_index'] as $index)
                        <div class="flex justify-between items-center">
                            <div class="text-sm">{{ $index['month_name'] }}</div>
                            <div class="flex items-center space-x-2">
                                <div class="w-32 bg-gray-200 rounded-full h-2">
                                    <div class="bg-blue-600 h-2 rounded-full"
                                        style="width: {{ min(100, $index['seasonal_index'] * 50) }}%"></div>
                                </div>
                                <span
                                    class="text-xs font-medium {{ $index['seasonal_index'] > 1 ? 'text-green-600' : 'text-red-600' }}">{{ $index['seasonal_index'] }}x</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @if (!empty($seasonalData['peak_seasons']))
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-semibold mb-4">Peak Seasons Identified</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach ($seasonalData['peak_seasons'] as $peak)
                        <div class="bg-green-50 border border-green-200 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-800">{{ $peak['month_name'] }} {{ $peak['year'] }}
                            </div>
                            <div class="text-xs text-green-600 mt-1">Revenue: Rp
                                {{ number_format($peak['revenue'], 0, ',', '.') }}</div>
                            <div class="text-xs text-green-600">Orders: {{ number_format($peak['orders']) }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if (!empty($seasonalData['insights']))
            <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg mt-6">
                <h4 class="text-sm font-semibold text-blue-800 mb-2">Key Insights:</h4>
                <ul class="space-y-1">
                    @foreach ($seasonalData['insights'] as $insight)
                        <li class="text-sm text-blue-700 flex items-start">
                            <svg class="w-4 h-4 mr-2 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{ $insight }}
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endsection
