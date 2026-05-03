<x-app-layout>
    <x-slot name="header">/</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('supplier-performance.dashboard') }}" class="text-gray-400 hover:text-blue-500 transition-colors">
                Supplier Performance
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            {{-- Page Title Bar --}}
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="min-w-0">
                    <h1 class="text-xl font-bold text-gray-800 truncate">
                        {{ $supplier->name }}
                    </h1>
                    <div class="flex items-center gap-2 mt-0.5 flex-wrap">
                        @if($supplier->company)
                            <span class="text-sm text-gray-500">{{ $supplier->company }}</span>
                        @endif
                        @if($supplier->email)
                            <span class="text-gray-300">·</span>
                            <span class="text-sm text-gray-400">{{ $supplier->email }}</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <form method="GET" action="{{ request()->url() }}">
                        <select name="period" onchange="this.form.submit()"
                            class="text-sm border border-gray-200 bg-white text-gray-700 rounded-lg px-3 py-1.5 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 Hari</option>
                            <option value="90" {{ $period == 90 ? 'selected' : '' }}>90 Hari</option>
                            <option value="180" {{ $period == 180 ? 'selected' : '' }}>6 Bulan</option>
                        </select>
                    </form>
                    <a href="{{ route('supplier-performance.dashboard') }}"
                        class="inline-flex items-center gap-1.5 text-sm bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                        </svg>
                        Kembali
                    </a>
                </div>
            </div>

            {{-- Performance Summary Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Current Grade</div>
                    <div
                        class="text-4xl font-bold {{ str_starts_with($performance['current_grade'], 'A') ? 'text-green-600' : 'text-blue-600' }}">
                        {{ $performance['current_grade'] }}
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Overall Score</div>
                    <div class="text-3xl font-bold text-purple-600">
                        {{ number_format($performance['avg_overall_score'], 1) }}</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">On-Time Rate</div>
                    <div
                        class="text-3xl font-bold {{ $performance['on_time_delivery_rate'] >= 90 ? 'text-green-600' : 'text-red-600' }}">
                        {{ $performance['on_time_delivery_rate'] }}%
                    </div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Quality Rate</div>
                    <div class="text-3xl font-bold text-blue-600">
                        {{ number_format($performance['avg_quality_rate'], 1) }}%</div>
                </div>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <div class="text-sm text-gray-500 mb-1">Total Evaluations</div>
                    <div class="text-3xl font-bold text-orange-600">{{ $performance['total_evaluations'] }}</div>
                </div>
            </div>

            {{-- Performance Trend Chart --}}
            @if ($performance['chart_data']['labels'])
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Performance Trends</h3>
                    <canvas id="performanceChart" height="100"></canvas>
                </div>
            @endif

            {{-- Score Breakdown --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Score Breakdown</h3>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Delivery (30%)</span>
                                <span
                                    class="text-sm font-bold">{{ number_format($performance['avg_delivery_score'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-blue-600 h-3 rounded-full"
                                    style="width: {{ $performance['avg_delivery_score'] }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Quality (35%)</span>
                                <span
                                    class="text-sm font-bold">{{ number_format($performance['avg_quality_score'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-green-600 h-3 rounded-full"
                                    style="width: {{ $performance['avg_quality_score'] }}%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between mb-1">
                                <span class="text-sm font-medium">Cost (20%)</span>
                                <span
                                    class="text-sm font-bold">{{ number_format($performance['avg_cost_score'], 1) }}</span>
                            </div>
                            <div class="w-full bg-gray-200 rounded-full h-3">
                                <div class="bg-purple-600 h-3 rounded-full"
                                    style="width: {{ $performance['avg_cost_score'] }}%"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="font-semibold text-gray-900 mb-4">Purchase Summary</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total POs Evaluated:</span>
                            <span class="font-bold">{{ $performance['total_pos'] }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total PO Value:</span>
                            <span class="font-bold">Rp
                                {{ number_format($performance['total_po_value'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Trend:</span>
                            @if ($performance['trend'] === 'improving')
                                <span class="text-green-600 font-bold">Improving</span>
                            @elseif($performance['trend'] === 'declining')
                                <span class="text-red-600 font-bold">Declining</span>
                            @else
                                <span class="text-gray-600">Stable</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Evaluation History --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="font-semibold text-gray-900 mb-4">Evaluation History</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Date</th>
                                <th class="px-4 py-3 text-left">PO Number</th>
                                <th class="px-4 py-3 text-center">Grade</th>
                                <th class="px-4 py-3 text-right">Score</th>
                                <th class="px-4 py-3 text-right">Delivery</th>
                                <th class="px-4 py-3 text-right">Quality</th>
                                <th class="px-4 py-3 text-right">Cost</th>
                                <th class="px-4 py-3 text-center">On-Time</th>
                                <th class="px-4 py-3 text-left">Evaluated By</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @forelse($evaluations as $eval)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">{{ $eval->evaluation_date->format('d M Y') }}</td>
                                    <td class="px-4 py-3">
                                        @if ($eval->purchaseOrder)
                                            <a href="#"
                                                class="text-blue-600 hover:underline">{{ $eval->purchaseOrder->number }}</a>
                                        @else
                                            <span class="text-gray-400">Manual</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        @php
                                            $gradeColor = match (str_split($eval->rating_grade)[0]) {
                                                'A' => 'bg-green-100 text-green-700',
                                                'B' => 'bg-blue-100 text-blue-700',
                                                'C' => 'bg-yellow-100 text-yellow-700',
                                                'D' => 'bg-orange-100 text-orange-700',
                                                default => 'bg-red-100 text-red-700',
                                            };
                                        @endphp
                                        <span class="px-2 py-1 rounded-full text-xs font-bold {{ $gradeColor }}">
                                            {{ $eval->rating_grade }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-right font-bold">
                                        {{ number_format($eval->overall_score, 1) }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($eval->delivery_score, 1) }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($eval->quality_score, 1) }}</td>
                                    <td class="px-4 py-3 text-right">{{ number_format($eval->cost_score, 1) }}</td>
                                    <td class="px-4 py-3 text-center">
                                        @if ($eval->on_time_delivery)
                                            <span class="text-green-600">✓ Yes</span>
                                        @else
                                            <span class="text-red-600">✗ No</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">{{ $eval->evaluatedBy?->name ?? '-' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                                        No evaluations recorded yet
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="mt-4">
                    {{ $evaluations->links() }}
                </div>
            </div>

        </div>{{-- end page content --}}
    </div>

    @if ($performance['chart_data']['labels'])
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            const chartData = @json($performance['chart_data']);

            new Chart(document.getElementById('performanceChart'), {
                type: 'line',
                data: {
                    labels: chartData.labels.map(d => new Date(d).toLocaleDateString('id-ID', {
                        day: '2-digit',
                        month: 'short'
                    })),
                    datasets: [{
                            label: 'Overall Score',
                            data: chartData.overall,
                            borderColor: 'rgb(147, 51, 234)',
                            backgroundColor: 'rgba(147, 51, 234, 0.1)',
                            tension: 0.4,
                            fill: true
                        },
                        {
                            label: 'Delivery',
                            data: chartData.delivery,
                            borderColor: 'rgb(59, 130, 246)',
                            tension: 0.4
                        },
                        {
                            label: 'Quality',
                            data: chartData.quality,
                            borderColor: 'rgb(16, 185, 129)',
                            tension: 0.4
                        },
                        {
                            label: 'Cost',
                            data: chartData.cost,
                            borderColor: 'rgb(249, 115, 22)',
                            tension: 0.4
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: 'top'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            max: 100,
                            title: {
                                display: true,
                                text: 'Score (0-100)'
                            }
                        }
                    }
                }
            });
        </script>
    @endif
</x-app-layout>
