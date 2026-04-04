@extends('layouts.app')

@section('title', 'Yield Optimization')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Yield Optimization</h1>
                <p class="text-gray-600">Maximize revenue through strategic yield management</p>
            </div>
        </div>

        <!-- Overbooking Recommendation -->
        @if (isset($overbooking))
            <div class="bg-white rounded-lg shadow p-4 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-gray-800">Overbooking Recommendation</h3>
                        <p class="text-sm text-gray-600 mt-1">{{ $overbooking['recommendation'] }}</p>
                    </div>
                    <div class="text-right">
                        <div
                            class="text-2xl font-bold 
                    {{ $overbooking['risk_level'] === 'high' ? 'text-red-600' : ($overbooking['risk_level'] === 'medium' ? 'text-yellow-600' : 'text-green-600') }}">
                            {{ $overbooking['recommended_overbooking_percentage'] }}%
                        </div>
                        <div class="text-sm text-gray-500">Cancellation rate:
                            {{ $overbooking['average_cancellation_rate'] }}%</div>
                    </div>
                </div>
                <div class="mt-3 p-3 bg-yellow-50 rounded text-sm text-yellow-800">
                    <strong>Caution:</strong> {{ $overbooking['caution'] }}
                </div>
            </div>
        @endif

        <!-- LOS Restrictions -->
        @if (isset($losRestrictions) && !empty($losRestrictions))
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Length of Stay Restrictions</h3>
                </div>
                <div class="p-4">
                    <div class="space-y-3">
                        @foreach ($losRestrictions as $restriction)
                            <div class="flex items-center justify-between p-3 bg-gray-50 rounded">
                                <div>
                                    <span
                                        class="font-medium">{{ ucfirst(str_replace('_', ' ', $restriction['type'])) }}</span>
                                    <span class="text-gray-600">: {{ $restriction['value'] }}
                                        {{ $restriction['type'] === 'minimum_stay' ? 'nights' : '%' }}</span>
                                </div>
                                <div class="text-sm text-gray-500">{{ $restriction['reason'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Channel Mix Optimization -->
        @if (isset($channelMix) && isset($channelMix['channel_performance']))
            <div class="bg-white rounded-lg shadow mb-6">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Channel Mix Optimization</h3>
                </div>
                <div class="p-4">
                    <div class="overflow-x-auto">
                        <table class="w-full mb-4">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-2 text-left">Channel</th>
                                    <th class="px-4 py-2 text-center">Bookings</th>
                                    <th class="px-4 py-2 text-center">Revenue</th>
                                    <th class="px-4 py-2 text-center">Commission</th>
                                    <th class="px-4 py-2 text-center">Net Revenue</th>
                                    <th class="px-4 py-2 text-center">Avg Booking</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($channelMix['channel_performance'] as $channel => $data)
                                    <tr class="border-b">
                                        <td class="px-4 py-2 font-medium">{{ ucfirst($channel) }}</td>
                                        <td class="px-4 py-2 text-center">{{ $data['bookings'] }}</td>
                                        <td class="px-4 py-2 text-center">${{ number_format($data['revenue'], 2) }}</td>
                                        <td class="px-4 py-2 text-center">${{ number_format($data['commission_cost'], 2) }}
                                            ({{ number_format($data['commission_percentage'], 1) }}%)</td>
                                        <td class="px-4 py-2 text-center font-medium">
                                            ${{ number_format($data['net_revenue'], 2) }}</td>
                                        <td class="px-4 py-2 text-center">
                                            ${{ number_format($data['avg_booking_value'], 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (!empty($channelMix['recommendations']))
                        <div class="mt-4">
                            <h4 class="font-medium mb-2">Recommendations</h4>
                            @foreach ($channelMix['recommendations'] as $rec)
                                <div class="p-3 bg-blue-50 rounded mb-2">
                                    <div class="flex items-center mb-1">
                                        <span
                                            class="px-2 py-1 text-xs rounded {{ $rec['priority'] === 'high' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700' }}">
                                            {{ ucfirst($rec['priority']) }}
                                        </span>
                                        <span class="ml-2 font-medium">{{ $rec['message'] }}</span>
                                    </div>
                                    <ul class="text-sm text-gray-600 ml-4">
                                        @foreach ($rec['suggested_actions'] as $action)
                                            <li>• {{ $action }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        <!-- Yield Optimization by Room Type -->
        @if (isset($optimization))
            <div class="bg-white rounded-lg shadow">
                <div class="px-4 py-3 border-b">
                    <h3 class="font-semibold text-gray-800">Yield Optimization Analysis</h3>
                </div>
                <div class="p-4">
                    @foreach ($optimization as $roomTypeId => $data)
                        <div class="mb-6 p-4 border rounded">
                            <div class="flex justify-between items-center mb-4">
                                <h4 class="font-semibold text-lg">{{ $data['room_type'] }}</h4>
                                <div class="text-right">
                                    <div class="text-sm text-gray-600">Projected Revenue Lift</div>
                                    <div
                                        class="text-2xl font-bold {{ $data['revenue_lift_percentage'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $data['revenue_lift_percentage'] > 0 ? '+' : '' }}{{ number_format($data['revenue_lift_percentage'], 1) }}%
                                    </div>
                                    <div class="text-sm">${{ number_format($data['revenue_lift_amount'], 2) }}</div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div class="p-3 bg-gray-50 rounded">
                                    <div class="text-sm text-gray-600">Current Projected Revenue</div>
                                    <div class="text-xl font-medium">
                                        ${{ number_format($data['current_projected_revenue'], 2) }}</div>
                                </div>
                                <div class="p-3 bg-green-50 rounded">
                                    <div class="text-sm text-gray-600">Optimized Projected Revenue</div>
                                    <div class="text-xl font-medium text-green-700">
                                        ${{ number_format($data['optimized_projected_revenue'], 2) }}</div>
                                </div>
                            </div>

                            @if (!empty($data['recommendations']))
                                <div>
                                    <h5 class="font-medium mb-2">Daily Recommendations</h5>
                                    <div class="overflow-x-auto">
                                        <table class="w-full text-sm">
                                            <thead class="bg-gray-50">
                                                <tr>
                                                    <th class="px-3 py-2 text-left">Date</th>
                                                    <th class="px-3 py-2 text-center">Current Rate</th>
                                                    <th class="px-3 py-2 text-center">Recommended</th>
                                                    <th class="px-3 py-2 text-center">Current Occ.</th>
                                                    <th class="px-3 py-2 text-center">Adj. Occ.</th>
                                                    <th class="px-3 py-2 text-center">Revenue Impact</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach (array_slice($data['recommendations'], 0, 7) as $rec)
                                                    <tr class="border-b">
                                                        <td class="px-3 py-2">{{ $rec['date'] }}</td>
                                                        <td class="px-3 py-2 text-center">
                                                            ${{ number_format($rec['current_rate'], 2) }}</td>
                                                        <td class="px-3 py-2 text-center font-medium">
                                                            ${{ number_format($rec['recommended_rate'], 2) }}</td>
                                                        <td class="px-3 py-2 text-center">
                                                            {{ number_format($rec['current_occupancy'], 1) }}%</td>
                                                        <td class="px-3 py-2 text-center">
                                                            {{ number_format($rec['adjusted_occupancy'], 1) }}%</td>
                                                        <td
                                                            class="px-3 py-2 text-center {{ $rec['revenue_impact'] > 0 ? 'text-green-600' : 'text-red-600' }}">
                                                            {{ $rec['revenue_impact'] > 0 ? '+' : '' }}${{ number_format($rec['revenue_impact'], 2) }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
@endsection
