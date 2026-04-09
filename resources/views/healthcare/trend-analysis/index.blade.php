<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Trend Analysis') }}</h2>
            <form method="GET" class="flex items-center space-x-2">
                <label class="text-sm font-medium text-gray-700">Period:</label>
                <select name="period" onchange="this.form.submit()" class="border-gray-300 rounded-md shadow-sm">
                    <option value="6" {{ $period == '6' ? 'selected' : '' }}>Last 6 Months</option>
                    <option value="12" {{ $period == '12' ? 'selected' : '' }}>Last 12 Months</option>
                    <option value="24" {{ $period == '24' ? 'selected' : '' }}>Last 24 Months</option>
                </select>
            </form>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-chart-line mr-2 text-blue-600"></i>Visit Trends (Last {{ $period }} Months)
                </h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Visits
                                </th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($visitTrends as $index => $trend)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($trend['period'])->format('F Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">
                                        {{ number_format($trend['count']) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($index > 0)
                                            @php
                                                $change =
                                                    $visitTrends[$index - 1]['count'] > 0
                                                        ? (($trend['count'] - $visitTrends[$index - 1]['count']) /
                                                                $visitTrends[$index - 1]['count']) *
                                                            100
                                                        : 0;
                                            @endphp
                                            <span
                                                class="text-sm font-semibold {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                <i class="fas {{ $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                {{ number_format(abs($change), 1) }}%
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-money-bill-wave mr-2 text-green-600"></i>Revenue Trends (Last {{ $period }}
                    Months)</h3>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Period</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total
                                    Revenue</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach ($revenueTrends as $index => $trend)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ \Carbon\Carbon::parse($trend['period'])->format('F Y') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-semibold text-gray-900">Rp
                                        {{ number_format($trend['revenue'], 0, ',', '.') }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if ($index > 0)
                                            @php
                                                $change =
                                                    $revenueTrends[$index - 1]['revenue'] > 0
                                                        ? (($trend['revenue'] - $revenueTrends[$index - 1]['revenue']) /
                                                                $revenueTrends[$index - 1]['revenue']) *
                                                            100
                                                        : 0;
                                            @endphp
                                            <span
                                                class="text-sm font-semibold {{ $change >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                                <i
                                                    class="fas {{ $change >= 0 ? 'fa-arrow-up' : 'fa-arrow-down' }}"></i>
                                                {{ number_format(abs($change), 1) }}%
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-stethoscope mr-2 text-purple-600"></i>Top 10 Diagnoses</h3>
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">#</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Diagnosis</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Count</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Percentage</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @php $totalDiagnoses = $diagnosisTrends->sum('count'); @endphp
                        @forelse($diagnosisTrends as $index => $diagnosis)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $diagnosis->diagnosis }}</td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-700">
                                    {{ number_format($diagnosis->count) }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div class="w-32 bg-gray-200 rounded-full h-2.5 mr-3">
                                            <div class="bg-purple-600 h-2.5 rounded-full"
                                                style="width: {{ $totalDiagnoses > 0 ? ($diagnosis->count / $totalDiagnoses) * 100 : 0 }}%">
                                            </div>
                                        </div>
                                        <span
                                            class="text-sm text-gray-700">{{ $totalDiagnoses > 0 ? number_format(($diagnosis->count / $totalDiagnoses) * 100, 1) : 0 }}%</span>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No diagnosis data
                                    available.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
