<x-app-layout>
    <x-slot name="header">Quality Control Dashboard</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('manufacturing.quality.checks.create') }}"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    + New Quality Check
                </a>
        <a href="{{ route('manufacturing.quality.defects') }}"
                    class="px-4 py-2 text-sm bg-orange-600 text-white rounded-xl hover:bg-orange-700">
                    View Defects
                </a>
    </div>

    <div class="max-w-7xl mx-auto">
        {{-- Statistics Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">
                            {{ $statistics['quality_checks']['total'] }}</p>
                        <p class="text-xs text-gray-500">Total QC Checks</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-green-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-green-600">
                            {{ number_format($statistics['quality_checks']['pass_rate'], 1) }}%</p>
                        <p class="text-xs text-gray-500">Pass Rate</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-red-600">
                            {{ $statistics['defects']['open'] }}</p>
                        <p class="text-xs text-gray-500">Open Defects</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-gray-900">Rp
                            {{ number_format($statistics['defects']['total_cost_impact'], 0, ',', '.') }}</p>
                        <p class="text-xs text-gray-500">Cost Impact</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            {{-- QC Trend Chart --}}
            <div
                class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">QC Trend (Last 7 Days)</h3>
                <canvas id="qcTrendChart" style="max-height: 300px;"></canvas>
            </div>

            {{-- QC by Stage --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">QC by Stage</h3>
                <div class="space-y-3">
                    @php
                        $stages = [
                            'pre_production' => 'Pre-Production',
                            'in_process' => 'In-Process',
                            'post_production' => 'Post-Production',
                            'final' => 'Final',
                        ];
                    @endphp
                    @foreach ($stages as $key => $label)
                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                            <span class="text-sm text-gray-700">{{ $label }}</span>
                            <span class="text-sm font-semibold text-gray-900">
                                {{ $qc_by_stage[$key]->count ?? 0 }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Recent Quality Checks --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Recent Quality Checks</h3>
                <div class="space-y-3">
                    @forelse($recent_checks as $check)
                        <div class="p-4 bg-gray-50 rounded-lg">
                            <div class="flex items-start justify-between mb-2">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        {{ $check->check_number }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $check->product?->name ?? 'N/A' }}</p>
                                </div>
                                @php
                                    $statusColor = match ($check->status) {
                                        'passed'
                                            => 'bg-green-100 text-green-700',
                                        'failed' => 'bg-red-100 text-red-700',
                                        'conditional_pass'
                                            => 'bg-yellow-100 text-yellow-700',
                                        default => 'bg-gray-100 text-gray-700',
                                    };
                                @endphp
                                <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $statusColor }}">
                                    {{ ucfirst(str_replace('_', ' ', $check->status)) }}
                                </span>
                            </div>
                            <div class="flex items-center gap-4 text-xs text-gray-500">
                                <span>Pass Rate: {{ number_format($check->pass_rate, 1) }}%</span>
                                <span>•</span>
                                <span>{{ $check->inspected_at?->diffForHumans() ?? 'Pending' }}</span>
                            </div>
                            @if (in_array($check->status, ['passed', 'conditional_pass']))
                                <a href="{{ route('manufacturing.quality.coa', $check) }}"
                                    class="mt-2 inline-block text-xs text-blue-600 hover:underline">
                                    View COA →
                                </a>
                            @endif
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">No quality checks yet</p>
                    @endforelse
                </div>
            </div>

            {{-- Open CAPAs --}}
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Open CAPAs</h3>
                <div class="space-y-3">
                    @forelse($open_capas as $capa)
                        <div
                            class="p-4 bg-gradient-to-r from-orange-50 to-red-50 rounded-lg border border-orange-200">
                            <p class="text-sm font-semibold text-gray-900">{{ $capa->defect_code }}</p>
                            <p class="text-xs text-gray-600 mt-1">{{ $capa->product?->name }}</p>
                            <div class="flex items-center gap-2 mt-2">
                                @php
                                    $severityColor = match ($capa->severity) {
                                        'critical' => 'bg-red-600',
                                        'major' => 'bg-orange-600',
                                        default => 'bg-yellow-600',
                                    };
                                @endphp
                                <span class="px-2 py-0.5 text-xs text-white rounded {{ $severityColor }}">
                                    {{ ucfirst($capa->severity) }}
                                </span>
                                <span class="text-xs text-gray-500">
                                    {{ $capa->quantity_defected }} units
                                </span>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500 text-center py-8">No open CAPAs</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            // QC Trend Chart
            const trendData = @json($trend_data);

            if (trendData.length > 0) {
                const ctx = document.getElementById('qcTrendChart').getContext('2d');
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: trendData.map(d => new Date(d.date).toLocaleDateString()),
                        datasets: [{
                                label: 'Passed',
                                data: trendData.map(d => parseInt(d.passed)),
                                borderColor: 'rgb(34, 197, 94)',
                                backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                tension: 0.4,
                                fill: true,
                            },
                            {
                                label: 'Failed',
                                data: trendData.map(d => parseInt(d.failed)),
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                fill: true,
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    stepSize: 1
                                }
                            }
                        }
                    }
                });
            }
        </script>
    @endpush
</x-app-layout>
