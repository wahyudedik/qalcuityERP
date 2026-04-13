<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
                📊 Supplier Performance Dashboard
            </h2>
            <div class="flex gap-2">
                <select id="periodSelector" onchange="changePeriod(this.value)" class="border rounded-lg px-3 py-2">
                    <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 Days</option>
                    <option value="90" {{ $period == 90 ? 'selected' : '' }}>90 Days</option>
                    <option value="180" {{ $period == 180 ? 'selected' : '' }}>6 Months</option>
                    <option value="365" {{ $period == 365 ? 'selected' : '' }}>1 Year</option>
                </select>
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Summary KPI Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Total Suppliers</div>
                    <div class="text-3xl font-bold text-blue-600">{{ $totalSuppliers }}</div>
                    <div class="text-xs text-gray-400 mt-2">Active suppliers</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Average Score</div>
                    <div class="text-3xl font-bold text-purple-600">{{ number_format($avgScore ?? 0, 1) }}</div>
                    <div class="text-xs text-gray-400 mt-2">Overall performance</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Top Performer</div>
                    <div class="text-xl font-bold text-green-600">{{ $topGrade?->name ?? 'N/A' }}</div>
                    <div class="text-xs text-gray-400 mt-2">Grade:
                        {{ $topGrade?->performance['current_grade'] ?? 'N/A' }}</div>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                    <div class="text-sm text-gray-500 dark:text-slate-400 mb-1">Evaluation Period</div>
                    <div class="text-2xl font-bold text-orange-600">{{ $period }} Days</div>
                    <div class="text-xs text-gray-400 mt-2">Last {{ $period }} days</div>
                </div>
            </div>

            {{-- Top 10 Rankings --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🏆 Top 10 Supplier Rankings</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Rank</th>
                                <th class="px-4 py-3 text-left">Supplier</th>
                                <th class="px-4 py-3 text-center">Grade</th>
                                <th class="px-4 py-3 text-right">Overall Score</th>
                                <th class="px-4 py-3 text-right">Delivery</th>
                                <th class="px-4 py-3 text-right">Quality</th>
                                <th class="px-4 py-3 text-right">Cost</th>
                                <th class="px-4 py-3 text-right">On-Time %</th>
                                <th class="px-4 py-3 text-right">Evaluations</th>
                                <th class="px-4 py-3 text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @forelse($rankings as $index => $ranking)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        @if ($index === 0)
                                            <span class="text-2xl">🥇</span>
                                        @elseif($index === 1)
                                            <span class="text-2xl">🥈</span>
                                        @elseif($index === 2)
                                            <span class="text-2xl">🥉</span>
                                        @else
                                            <span class="font-bold text-gray-600">#{{ $index + 1 }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('supplier-performance.detail', $ranking['supplier_id']) }}"
                                            class="text-blue-600 hover:underline font-medium">
                                            {{ $ranking['supplier_name'] }}
                                        </a>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $gradeColor = match (str_split($ranking['grade'])[0]) {
                                                'A'
                                                    => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                                'B'
                                                    => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                                'C'
                                                    => 'bg-yellow-100 text-yellow-700 dark:bg-yellow-500/20 dark:text-yellow-400',
                                                'D'
                                                    => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                                default
                                                    => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $gradeColor }}">
                                            {{ $ranking['grade'] }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold text-gray-900 dark:text-white">
                                        {{ number_format($ranking['avg_score'], 1) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($ranking['avg_delivery'], 1) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($ranking['avg_quality'], 1) }}
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ number_format($ranking['avg_cost'], 1) }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <span
                                            class="{{ $ranking['on_time_rate'] >= 90 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $ranking['on_time_rate'] }}%
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right">{{ $ranking['evaluation_count'] }}</td>
                                    <td class="px-4 py-3 text-center">
                                        <a href="{{ route('supplier-performance.detail', $ranking['supplier_id']) }}"
                                            class="text-blue-600 hover:underline text-xs">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="px-4 py-8 text-center text-gray-500">
                                        No supplier evaluations yet. Start evaluating suppliers after receiving POs.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- All Suppliers Performance Overview --}}
            <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">📋 All Suppliers Performance</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach ($suppliers as $supplier)
                        <div
                            class="border border-gray-200 dark:border-white/10 rounded-lg p-4 hover:shadow-lg transition-shadow">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <h4 class="font-bold text-gray-900 dark:text-white">{{ $supplier->name }}</h4>
                                    <p class="text-xs text-gray-500">{{ $supplier->company ?? '-' }}</p>
                                </div>
                                @if ($supplier->performance['current_grade'] !== 'N/A')
                                    @php
                                        $gradeColor = match (str_split($supplier->performance['current_grade'])[0]) {
                                            'A' => 'bg-green-100 text-green-700',
                                            'B' => 'bg-blue-100 text-blue-700',
                                            'C' => 'bg-yellow-100 text-yellow-700',
                                            'D' => 'bg-orange-100 text-orange-700',
                                            default => 'bg-red-100 text-red-700',
                                        };
                                    @endphp
                                    <span class="px-2 py-1 rounded-full text-xs font-bold {{ $gradeColor }}">
                                        {{ $supplier->performance['current_grade'] }}
                                    </span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs bg-gray-100 text-gray-600">No
                                        Data</span>
                                @endif
                            </div>

                            @if ($supplier->performance['total_evaluations'] > 0)
                                <div class="space-y-2 text-sm">
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Overall Score:</span>
                                        <span
                                            class="font-bold">{{ number_format($supplier->performance['avg_overall_score'], 1) }}</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">On-Time Delivery:</span>
                                        <span
                                            class="{{ $supplier->performance['on_time_delivery_rate'] >= 90 ? 'text-green-600' : 'text-red-600' }}">
                                            {{ $supplier->performance['on_time_delivery_rate'] }}%
                                        </span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Quality Rate:</span>
                                        <span>{{ number_format($supplier->performance['avg_quality_rate'], 1) }}%</span>
                                    </div>
                                    <div class="flex justify-between">
                                        <span class="text-gray-500">Total POs:</span>
                                        <span>{{ $supplier->performance['total_pos'] }}</span>
                                    </div>

                                    {{-- Trend Indicator --}}
                                    <div class="pt-2 border-t border-gray-200 dark:border-white/10">
                                        <div class="flex justify-between items-center">
                                            <span class="text-xs text-gray-500">Trend:</span>
                                            @if ($supplier->performance['trend'] === 'improving')
                                                <span class="text-xs text-green-600 font-semibold">📈 Improving</span>
                                            @elseif($supplier->performance['trend'] === 'declining')
                                                <span class="text-xs text-red-600 font-semibold">📉 Declining</span>
                                            @else
                                                <span class="text-xs text-gray-600">➡️ Stable</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @else
                                <div class="text-center py-4 text-gray-400 text-sm">
                                    No evaluations yet
                                </div>
                            @endif

                            <div class="mt-3 pt-3 border-t border-gray-200 dark:border-white/10">
                                <a href="{{ route('supplier-performance.detail', $supplier->id) }}"
                                    class="text-blue-600 hover:underline text-sm font-medium">
                                    View Full Details →
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        function changePeriod(period) {
            const url = new URL(window.location.href);
            url.searchParams.set('period', period);
            window.location.href = url.toString();
        }
    </script>
</x-app-layout>
