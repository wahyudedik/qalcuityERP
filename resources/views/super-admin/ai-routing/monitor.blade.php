<x-app-layout>
    <x-slot name="title">AI Routing Monitor — Qalcuity ERP</x-slot>
    <x-slot name="header">AI Routing Monitor — SuperAdmin</x-slot>

    <div class="mb-4">
        <a href="{{ route('super-admin.ai.routing.index') }}"
            class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali ke Routing Rules
        </a>
    </div>

    {{-- Stats Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @php
            $totalRequests = collect($useCaseDistribution)->sum('count');
            $totalFallbacks = collect($fallbackCountByUseCase)->sum('count');
            $avgResponseTime = collect($responseTimeByUseCase)->avg('avg_response_time');
            $activeProviders = count($providerDistribution);
        @endphp
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-blue-400">{{ number_format($totalRequests) }}</p>
            <p class="text-xs text-gray-400 mt-1">Total Request (24 Jam)</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-purple-400">{{ $activeProviders }}</p>
            <p class="text-xs text-gray-400 mt-1">Provider Aktif</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-amber-400">{{ number_format($totalFallbacks) }}</p>
            <p class="text-xs text-gray-400 mt-1">Fallback Event (24 Jam)</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-200 p-5 text-center">
            <p class="text-2xl font-bold text-green-400">{{ number_format($avgResponseTime ?? 0, 0) }} ms</p>
            <p class="text-xs text-gray-400 mt-1">Avg Response Time</p>
        </div>
    </div>

    {{-- Charts Row 1: Use Case & Provider Distribution --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        {{-- Use Case Distribution Pie Chart --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                Distribusi Request per Use Case (24 Jam)
            </h3>
            <div class="relative" style="height: 300px;">
                <canvas id="useCaseChart"></canvas>
            </div>
            @if (empty($useCaseDistribution))
                <p class="text-center text-sm text-gray-400 mt-4">Belum ada data request dalam 24 jam terakhir</p>
            @endif
        </div>

        {{-- Provider Distribution Bar Chart --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                Distribusi Request per Provider (24 Jam)
            </h3>
            <div class="relative" style="height: 300px;">
                <canvas id="providerChart"></canvas>
            </div>
            @if (empty($providerDistribution))
                <p class="text-center text-sm text-gray-400 mt-4">Belum ada data request dalam 24 jam terakhir</p>
            @endif
        </div>
    </div>

    {{-- Fallback Trend Line Chart --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <h3 class="text-base font-bold text-gray-900 mb-4 flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
            Tren Fallback Event (24 Jam Terakhir)
        </h3>
        <div class="relative" style="height: 250px;">
            <canvas id="fallbackTrendChart"></canvas>
        </div>
        @if (empty($fallbackTrend))
            <p class="text-center text-sm text-gray-400 mt-4">Belum ada fallback event dalam 24 jam terakhir</p>
        @endif
    </div>

    {{-- Tables Row: Response Time & Fallback Count --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Response Time by Use Case Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-green-500"></span>
                    Rata-rata Response Time per Use Case
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Use Case</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Avg Time (ms)</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($responseTimeByUseCase as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item['use_case'] }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span
                                        class="font-semibold {{ $item['avg_response_time'] > 5000 ? 'text-red-500' : ($item['avg_response_time'] > 2000 ? 'text-amber-500' : 'text-green-500') }}">
                                        {{ number_format($item['avg_response_time'], 0) }} ms
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-400">
                                    Belum ada data response time
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Fallback Count by Use Case Table --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                    <span class="w-2 h-2 rounded-full bg-red-500"></span>
                    Fallback Event per Use Case (24 Jam)
                </h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b border-gray-200 bg-gray-50">
                            <th
                                class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Use Case</th>
                            <th
                                class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                                Fallback Count</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($fallbackCountByUseCase as $item)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-4 py-3 text-sm text-gray-900">{{ $item['use_case'] }}</td>
                                <td class="px-4 py-3 text-sm text-right">
                                    <span
                                        class="font-semibold {{ $item['count'] > 10 ? 'text-red-500' : ($item['count'] > 5 ? 'text-amber-500' : 'text-gray-700') }}">
                                        {{ number_format($item['count']) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-400">
                                    Belum ada fallback event
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Response Time by Provider Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden mt-6">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-base font-bold text-gray-900 flex items-center gap-2">
                <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                Rata-rata Response Time per Provider
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-gray-50">
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Provider</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">
                            Avg Time (ms)</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($responseTimeByProvider as $item)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-4 py-3 text-sm font-medium text-gray-900">{{ ucfirst($item['provider']) }}
                            </td>
                            <td class="px-4 py-3 text-sm text-right">
                                <span
                                    class="font-semibold {{ $item['avg_response_time'] > 5000 ? 'text-red-500' : ($item['avg_response_time'] > 2000 ? 'text-amber-500' : 'text-green-500') }}">
                                    {{ number_format($item['avg_response_time'], 0) }} ms
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="px-4 py-8 text-center text-sm text-gray-400">
                                Belum ada data response time
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Info Box --}}
    <div class="mt-6 bg-blue-50 rounded-2xl border border-blue-200 p-5">
        <div class="flex items-start gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <div class="text-sm text-blue-700">
                <p class="font-semibold mb-1">Catatan Monitoring:</p>
                <ul class="list-disc list-inside space-y-1 text-xs">
                    <li>Data statistik di-cache selama 5 menit untuk performa optimal</li>
                    <li>Semua data menampilkan aktivitas 24 jam terakhir</li>
                    <li>Response time di atas 5 detik ditandai merah (perlu investigasi)</li>
                    <li>Fallback event tinggi (>10 dalam 24 jam) mengindikasikan masalah provider</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Chart.js Scripts --}}
    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Use Case Distribution Pie Chart
                @if (!empty($useCaseDistribution))
                    const useCaseData = @json($useCaseDistribution);
                    const useCaseCtx = document.getElementById('useCaseChart');
                    if (useCaseCtx) {
                        new Chart(useCaseCtx, {
                            type: 'pie',
                            data: {
                                labels: useCaseData.map(item => item.use_case),
                                datasets: [{
                                    data: useCaseData.map(item => item.count),
                                    backgroundColor: [
                                        'rgba(59, 130, 246, 0.8)', // blue
                                        'rgba(168, 85, 247, 0.8)', // purple
                                        'rgba(34, 197, 94, 0.8)', // green
                                        'rgba(251, 146, 60, 0.8)', // orange
                                        'rgba(236, 72, 153, 0.8)', // pink
                                        'rgba(14, 165, 233, 0.8)', // sky
                                        'rgba(132, 204, 22, 0.8)', // lime
                                        'rgba(249, 115, 22, 0.8)', // orange-600
                                        'rgba(139, 92, 246, 0.8)', // violet
                                        'rgba(6, 182, 212, 0.8)', // cyan
                                        'rgba(245, 158, 11, 0.8)', // amber
                                        'rgba(239, 68, 68, 0.8)', // red
                                        'rgba(99, 102, 241, 0.8)', // indigo
                                        'rgba(20, 184, 166, 0.8)', // teal
                                        'rgba(217, 70, 239, 0.8)', // fuchsia
                                        'rgba(244, 63, 94, 0.8)', // rose
                                    ],
                                    borderWidth: 2,
                                    borderColor: '#fff'
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                plugins: {
                                    legend: {
                                        position: 'right',
                                        labels: {
                                            boxWidth: 12,
                                            padding: 10,
                                            font: {
                                                size: 11
                                            }
                                        }
                                    },
                                    tooltip: {
                                        callbacks: {
                                            label: function(context) {
                                                const label = context.label || '';
                                                const value = context.parsed || 0;
                                                const total = context.dataset.data.reduce((a, b) => a + b,
                                                    0);
                                                const percentage = ((value / total) * 100).toFixed(1);
                                                return `${label}: ${value} (${percentage}%)`;
                                            }
                                        }
                                    }
                                }
                            }
                        });
                    }
                @endif

                // Provider Distribution Bar Chart
                @if (!empty($providerDistribution))
                    const providerData = @json($providerDistribution);
                    const providerCtx = document.getElementById('providerChart');
                    if (providerCtx) {
                        new Chart(providerCtx, {
                            type: 'bar',
                            data: {
                                labels: providerData.map(item => item.provider.charAt(0).toUpperCase() + item
                                    .provider.slice(1)),
                                datasets: [{
                                    label: 'Request Count',
                                    data: providerData.map(item => item.count),
                                    backgroundColor: 'rgba(168, 85, 247, 0.8)',
                                    borderColor: 'rgba(168, 85, 247, 1)',
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    }
                @endif

                // Fallback Trend Line Chart
                @if (!empty($fallbackTrend))
                    const fallbackData = @json($fallbackTrend);
                    const fallbackCtx = document.getElementById('fallbackTrendChart');
                    if (fallbackCtx) {
                        new Chart(fallbackCtx, {
                            type: 'line',
                            data: {
                                labels: fallbackData.map(item => {
                                    const date = new Date(item.hour);
                                    return date.toLocaleString('id-ID', {
                                        month: 'short',
                                        day: 'numeric',
                                        hour: '2-digit',
                                        hour12: false
                                    });
                                }),
                                datasets: [{
                                    label: 'Fallback Events',
                                    data: fallbackData.map(item => item.count),
                                    borderColor: 'rgba(251, 146, 60, 1)',
                                    backgroundColor: 'rgba(251, 146, 60, 0.1)',
                                    borderWidth: 2,
                                    fill: true,
                                    tension: 0.4
                                }]
                            },
                            options: {
                                responsive: true,
                                maintainAspectRatio: false,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            precision: 0
                                        }
                                    },
                                    x: {
                                        ticks: {
                                            maxRotation: 45,
                                            minRotation: 45,
                                            font: {
                                                size: 10
                                            }
                                        }
                                    }
                                },
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                }
                            }
                        });
                    }
                @endif
            });
        </script>
    @endpush
</x-app-layout>
