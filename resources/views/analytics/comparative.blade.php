<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Comparative Analysis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Comparison Type Selector -->
            <div class="mb-6 flex space-x-2">
                @foreach ([['yoy', 'Year over Year'], ['mom', 'Month over Month'], ['qoq', 'Quarter over Quarter']] as [$key, $label])
                    <a href="{{ route('analytics.comparative', ['comparison' => $key]) }}"
                        class="px-6 py-3 rounded-lg text-sm font-medium transition {{ $comparison === $key ? 'bg-indigo-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-100' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>

            <!-- Growth Summary Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                @foreach ($analysis['growth'] as $metric => $data)
                    <div class="bg-white rounded-xl p-6 shadow">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-sm font-medium text-gray-500">
                                {{ ucwords(str_replace('_', ' ', $metric)) }}
                            </h3>
                            <span class="text-2xl">
                                @if ($metric === 'revenue')
                                    💰
                                @elseif($metric === 'orders')
                                    📦
                                @elseif($metric === 'customers')
                                    👥
                                @else
                                    📊
                                @endif
                            </span>
                        </div>
                        <p class="text-3xl font-bold text-gray-900 mb-2">
                            @if ($metric === 'revenue')
                                Rp {{ number_format($data['current'], 0, ',', '.') }}
                            @else
                                {{ number_format($data['current'], 0, ',', '.') }}
                            @endif
                        </p>
                        <div
                            class="flex items-center text-sm {{ $data['trend'] === 'up' ? 'text-green-600' : 'text-red-600' }}">
                            <span class="text-xl">{{ $data['trend'] === 'up' ? '↑' : '↓' }}</span>
                            <span class="ml-1 font-semibold">{{ abs($data['percentage']) }}%</span>
                            <span class="ml-1 text-gray-500">vs previous</span>
                        </div>
                        <p class="mt-2 text-xs text-gray-500">
                            Absolute change:
                            @if ($metric === 'revenue')
                                Rp {{ number_format($data['absolute'], 0, ',', '.') }}
                            @else
                                {{ number_format($data['absolute'], 0, ',', '.') }}
                            @endif
                        </p>
                    </div>
                @endforeach
            </div>

            <!-- Detailed Comparison Table -->
            <div class="bg-white rounded-xl p-6 shadow mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">📊 Detailed Comparison</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Metric</th>
                                <th class="px-4 py-3 text-right">Current Period</th>
                                <th class="px-4 py-3 text-right">Previous Period</th>
                                <th class="px-4 py-3 text-right">Absolute Change</th>
                                <th class="px-4 py-3 text-right">Growth %</th>
                                <th class="px-4 py-3 text-center">Trend</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($analysis['growth'] as $metric => $data)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 font-medium text-gray-900">
                                        {{ ucwords(str_replace('_', ' ', $metric)) }}
                                    </td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                        @if ($metric === 'revenue')
                                            Rp {{ number_format($data['current'], 0, ',', '.') }}
                                        @else
                                            {{ number_format($data['current'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-right text-gray-600">
                                        @if ($metric === 'revenue')
                                            Rp {{ number_format($data['previous'], 0, ',', '.') }}
                                        @else
                                            {{ number_format($data['previous'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right {{ $data['absolute'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        @if ($metric === 'revenue')
                                            Rp {{ number_format($data['absolute'], 0, ',', '.') }}
                                        @else
                                            {{ $data['absolute'] > 0 ? '+' : '' }}{{ number_format($data['absolute'], 0, ',', '.') }}
                                        @endif
                                    </td>
                                    <td
                                        class="px-4 py-3 text-right font-semibold {{ $data['percentage'] >= 0 ? 'text-green-600' : 'text-red-600' }}">
                                        {{ $data['percentage'] > 0 ? '+' : '' }}{{ $data['percentage'] }}%
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-3 py-1 text-xs rounded-full {{ $data['trend'] === 'up' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                            {{ $data['trend'] === 'up' ? '↑ Up' : '↓ Down' }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Period Information -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div
                    class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">📅 Current Period</h4>
                    <p class="text-sm text-gray-600">
                        {{ $analysis['current_period']['start']->format('d M Y') }} -
                        {{ $analysis['current_period']['end']->format('d M Y') }}
                    </p>
                </div>
                <div
                    class="bg-gradient-to-br from-gray-50 to-slate-50 rounded-xl p-6">
                    <h4 class="font-semibold text-gray-900 mb-2">📅 Previous Period</h4>
                    <p class="text-sm text-gray-600">
                        {{ $analysis['previous_period']['start']->format('d M Y') }} -
                        {{ $analysis['previous_period']['end']->format('d M Y') }}
                    </p>
                </div>
            </div>

            <!-- Insights & Recommendations -->
            <div class="mt-6 bg-white rounded-xl p-6 shadow">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">💡 Key Insights</h3>
                <div class="space-y-3">
                    @foreach ($analysis['growth'] as $metric => $data)
                        @if ($data['percentage'] > 10)
                            <div class="flex items-start p-3 bg-green-50 rounded-lg">
                                <span class="text-green-600 mr-2">✅</span>
                                <p class="text-sm text-gray-700">
                                    <strong>{{ ucwords(str_replace('_', ' ', $metric)) }}</strong> shows strong growth
                                    of {{ $data['percentage'] }}% compared to previous period.
                                </p>
                            </div>
                        @elseif($data['percentage'] < -5)
                            <div class="flex items-start p-3 bg-red-50 rounded-lg">
                                <span class="text-red-600 mr-2">⚠️</span>
                                <p class="text-sm text-gray-700">
                                    <strong>{{ ucwords(str_replace('_', ' ', $metric)) }}</strong> declined by
                                    {{ abs($data['percentage']) }}%. Consider investigating the root cause.
                                </p>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
