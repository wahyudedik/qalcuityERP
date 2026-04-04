@extends('layouts.app')

@section('title', 'Rate Calendar')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Rate Calendar</h1>
                <p class="text-gray-600">View and manage dynamic rates across all room types</p>
            </div>
            <div class="flex space-x-2">
                <a href="?start_date={{ $startDate->copy()->subMonth()->format('Y-m-d') }}&end_date={{ $startDate->copy()->subMonth()->addDays(30)->format('Y-m-d') }}"
                    class="px-4 py-2 border rounded hover:bg-gray-50">← Previous</a>
                <a href="?start_date={{ now()->format('Y-m-d') }}&end_date={{ now()->addDays(30)->format('Y-m-d') }}"
                    class="px-4 py-2 border rounded hover:bg-gray-50">Today</a>
                <a href="?start_date={{ $endDate->copy()->addDay()->format('Y-m-d') }}&end_date={{ $endDate->copy()->addMonth()->format('Y-m-d') }}"
                    class="px-4 py-2 border rounded hover:bg-gray-50">Next →</a>
            </div>
        </div>

        <!-- Date Range Display -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <span class="text-gray-600">Showing rates from</span>
                    <span class="font-medium">{{ $startDate->format('M d, Y') }}</span>
                    <span class="text-gray-600">to</span>
                    <span class="font-medium">{{ $endDate->format('M d, Y') }}</span>
                </div>
                <div class="flex items-center space-x-4 text-sm">
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-green-100 border border-green-300 rounded mr-1"></span>
                        <span>Low Demand</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-blue-100 border border-blue-300 rounded mr-1"></span>
                        <span>Normal</span>
                    </div>
                    <div class="flex items-center">
                        <span class="w-3 h-3 bg-red-100 border border-red-300 rounded mr-1"></span>
                        <span>High Demand</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rate Calendar -->
        @if (isset($calendar) && !empty($calendar))
            <div class="space-y-6">
                @foreach ($calendar as $roomTypeId => $data)
                    <div class="bg-white rounded-lg shadow">
                        <div class="px-4 py-3 border-b flex justify-between items-center">
                            <h3 class="font-semibold text-gray-800">{{ $data['room_type'] }}</h3>
                            <div class="text-sm text-gray-600">
                                Base Rate: <span class="font-medium">${{ number_format($data['base_rate'], 2) }}</span>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <div class="flex min-w-max">
                                @foreach ($data['rates'] as $rate)
                                    @php
                                        $occupancyRate = $rate['factors']['forecasted_occupancy'] ?? 50;
                                        $bgClass =
                                            $occupancyRate >= 80
                                                ? 'bg-red-50 border-red-200'
                                                : ($occupancyRate >= 60
                                                    ? 'bg-blue-50 border-blue-200'
                                                    : 'bg-green-50 border-green-200');
                                        $rateDate = \Carbon\Carbon::parse($rate['date']);
                                        $isWeekend = in_array($rateDate->dayOfWeek, [5, 6, 0]);
                                    @endphp
                                    <div
                                        class="w-24 flex-shrink-0 p-2 border-r {{ $bgClass }} {{ $isWeekend ? 'bg-opacity-75' : '' }}">
                                        <div class="text-center">
                                            <div class="text-xs text-gray-500">{{ $rateDate->format('D') }}</div>
                                            <div class="font-medium">{{ $rateDate->format('d') }}</div>
                                            <div class="text-xs text-gray-500 mb-2">{{ $rateDate->format('M') }}</div>

                                            <div
                                                class="text-lg font-bold {{ $rate['final_rate'] > $data['base_rate'] ? 'text-red-600' : ($rate['final_rate'] < $data['base_rate'] ? 'text-green-600' : 'text-gray-800') }}">
                                                ${{ number_format($rate['final_rate'], 0) }}
                                            </div>

                                            @if ($rate['total_adjustment'] != 0)
                                                <div
                                                    class="text-xs {{ $rate['total_adjustment'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                                                    {{ $rate['total_adjustment'] > 0 ? '+' : '' }}${{ number_format($rate['total_adjustment'], 0) }}
                                                </div>
                                            @endif

                                            @if (isset($rate['factors']['forecasted_occupancy']))
                                                <div class="mt-1 text-xs text-gray-500">
                                                    {{ number_format($rate['factors']['forecasted_occupancy'], 0) }}% occ
                                                </div>
                                            @endif

                                            @if (!empty($rate['factors']['active_events']))
                                                <div class="mt-1">
                                                    <span class="text-xs px-1 bg-purple-100 text-purple-700 rounded"
                                                        title="{{ implode(', ', $rate['factors']['active_events']) }}">
                                                        Event
                                                    </span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-lg shadow p-8 text-center">
                <p class="text-gray-500">No rate data available. Please ensure room types are configured.</p>
            </div>
        @endif
    </div>
@endsection
