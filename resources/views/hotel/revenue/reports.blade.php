@extends('layouts.app')

@section('title', 'Revenue Reports')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-800">Revenue Reports</h1>
                <p class="text-gray-600">Detailed analytics and performance metrics</p>
            </div>
            <form action="{{ route('revenue.reports') }}" method="GET" class="flex space-x-2">
                <input type="date" name="start_date" value="{{ $startDate->format('Y-m-d') }}"
                    class="border rounded px-3 py-2">
                <input type="date" name="end_date" value="{{ $endDate->format('Y-m-d') }}"
                    class="border rounded px-3 py-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Update</button>
            </form>
        </div>

        <!-- KPIs -->
        @if (isset($kpis) && !isset($kpis['message']))
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg Occupancy</div>
                    <div class="text-2xl font-bold text-blue-600">{{ number_format($kpis['occupancy']['average'], 1) }}%
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg ADR</div>
                    <div class="text-2xl font-bold text-green-600">${{ number_format($kpis['adr']['average'], 2) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Avg RevPAR</div>
                    <div class="text-2xl font-bold text-purple-600">${{ number_format($kpis['revpar']['average'], 2) }}
                    </div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Total Revenue</div>
                    <div class="text-2xl font-bold text-orange-600">${{ number_format($kpis['revenue']['total'], 0) }}</div>
                </div>
                <div class="bg-white rounded-lg shadow p-4">
                    <div class="text-sm text-gray-600">Net Pickup</div>
                    <div
                        class="text-2xl font-bold {{ $kpis['pickup']['net_pickup'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $kpis['pickup']['net_pickup'] > 0 ? '+' : '' }}{{ $kpis['pickup']['net_pickup'] }}
                    </div>
                </div>
            </div>
        @endif

        <!-- Daily Breakdown -->
        <div class="bg-white rounded-lg shadow">
            <div class="px-4 py-3 border-b">
                <h3 class="font-semibold text-gray-800">Daily Performance</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-sm font-semibold text-gray-700">Date</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Occupancy</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">ADR</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">RevPAR</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Revenue</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">New Bookings</th>
                            <th class="px-4 py-3 text-center text-sm font-semibold text-gray-700">Cancellations</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y">
                        @forelse($snapshots as $snapshot)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">{{ $snapshot->snapshot_date->format('M d, Y') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span
                                        class="px-2 py-1 text-xs rounded
                                {{ $snapshot->occupancy_rate >= 80 ? 'bg-red-100 text-red-700' : '' }}
                                {{ $snapshot->occupancy_rate >= 60 && $snapshot->occupancy_rate < 80 ? 'bg-green-100 text-green-700' : '' }}
                                {{ $snapshot->occupancy_rate < 60 ? 'bg-yellow-100 text-yellow-700' : '' }}">
                                        {{ number_format($snapshot->occupancy_rate, 1) }}%
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-center">${{ number_format($snapshot->adr, 2) }}</td>
                                <td class="px-4 py-3 text-center">${{ number_format($snapshot->revpar, 2) }}</td>
                                <td class="px-4 py-3 text-center font-medium">
                                    ${{ number_format($snapshot->total_revenue, 0) }}</td>
                                <td class="px-4 py-3 text-center text-green-600">+{{ $snapshot->new_bookings_today }}</td>
                                <td class="px-4 py-3 text-center text-red-600">-{{ $snapshot->cancellations_today }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                    No data available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
