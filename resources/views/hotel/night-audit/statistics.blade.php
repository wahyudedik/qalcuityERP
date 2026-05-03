<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800">
            {{ __('Occupancy & ADR Statistics') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
            {{-- Date Range Filter --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
                <form method="GET" action="{{ route('hotel.night-audit.statistics') }}" class="flex gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                        <input type="date" name="date_from" value="{{ $dateFrom }}"
                            class="rounded-md border-gray-300">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                        <input type="date" name="date_to" value="{{ $dateTo }}"
                            class="rounded-md border-gray-300">
                    </div>

                    <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Apply
                    </button>
                </form>
            </div>

            {{-- Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average Occupancy</h3>
                    <p class="text-3xl font-bold text-blue-600">
                        {{ number_format($avgOccupancy, 1) }}%</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average ADR</h3>
                    <p class="text-3xl font-bold text-green-600">Rp
                        {{ number_format($avgADR, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Average RevPAR</h3>
                    <p class="text-3xl font-bold text-purple-600">Rp
                        {{ number_format($avgRevPAR, 0, ',', '.') }}</p>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-2">Total Revenue</h3>
                    <p class="text-3xl font-bold text-orange-600">Rp
                        {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Occupancy Statistics Table --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden mb-6">
                <div class="p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Daily Occupancy Statistics</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Total Rooms</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Occupied</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Available</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Occupancy %</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Check-ins</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Check-outs</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($occupancyStats as $stat)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $stat->stat_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->total_rooms }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->occupied_rooms }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->available_rooms }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $stat->occupancy_percentage >= 80
                                            ? 'bg-green-100 text-green-800'
                                            : ($stat->occupancy_percentage >= 50
                                                ? 'bg-yellow-100 text-yellow-800'
                                                : 'bg-red-100 text-red-800') }}">
                                            {{ number_format($stat->occupancy_percentage, 1) }}%
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->check_ins }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->check_outs }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                        No occupancy data for selected period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- ADR & RevPAR Table --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="p-6 border-b border-gray-200 flex justify-between items-center">
                    <h3 class="text-lg font-semibold text-gray-900">Daily Rate Statistics (ADR & RevPAR)
                    </h3>
                    <form action="{{ route('hotel.night-audit.recalculate-rates') }}" method="POST"
                        class="flex gap-2">
                        @csrf
                        <input type="date" name="stat_date" required
                            class="rounded-md border-gray-300 text-sm">
                        <button type="submit"
                            class="px-3 py-1 bg-blue-600 text-white rounded-md hover:bg-blue-700 text-sm">
                            Recalculate
                        </button>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Date</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Rooms Sold</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    Room Revenue</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    ADR</th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                    RevPAR</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @forelse($rateStats as $stat)
                                <tr>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                        {{ $stat->stat_date->format('d/m/Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $stat->rooms_sold }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        Rp {{ number_format($stat->total_room_revenue, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-green-600">
                                        Rp {{ number_format($stat->adr, 0, ',', '.') }}
                                    </td>
                                    <td
                                        class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-purple-600">
                                        Rp {{ number_format($stat->revpar, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                        No rate data for selected period
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
