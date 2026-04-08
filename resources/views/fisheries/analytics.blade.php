<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Fisheries Analytics & Reports</h1>
        </div>
    </x-slot>

    {{-- Period Selector --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-6">
        <form class="flex items-center justify-between">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white">Performance Overview</h2>
            <select name="period" onchange="this.form.submit()"
                class="px-4 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white">
                <option value="7d" @selected($analytics['period'] === '7d')>7 Hari Terakhir</option>
                <option value="30d" @selected($analytics['period'] === '30d')>30 Hari Terakhir</option>
                <option value="90d" @selected($analytics['period'] === '90d')>3 Bulan Terakhir</option>
                <option value="1y" @selected($analytics['period'] === '1y')>1 Tahun Terakhir</option>
            </select>
        </form>
    </div>

    {{-- Key Metrics Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div
            class="bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-xl border border-emerald-200 dark:border-emerald-500/30 p-5">
            <p class="text-xs text-emerald-600 dark:text-emerald-400 font-medium">Total Tangkapan</p>
            <p class="text-3xl font-bold text-emerald-700 dark:text-emerald-300 mt-2">
                {{ number_format($analytics['production']['total_weight'], 1) }} kg</p>
            <p class="text-xs text-emerald-600 dark:text-emerald-400 mt-1">
                {{ $analytics['production']['total_catches'] }} entry</p>
        </div>

        <div
            class="bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-xl border border-orange-200 dark:border-orange-500/30 p-5">
            <p class="text-xs text-orange-600 dark:text-orange-400 font-medium">Total Revenue</p>
            <p class="text-3xl font-bold text-orange-700 dark:text-orange-300 mt-2">Rp
                {{ number_format($analytics['production']['total_revenue'], 0, ',', '.') }}</p>
            <p class="text-xs text-orange-600 dark:text-orange-400 mt-1">
                {{ $analytics['production']['completed_trips'] }} trip selesai</p>
        </div>

        <div
            class="bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-xl border border-blue-200 dark:border-blue-500/30 p-5">
            <p class="text-xs text-blue-600 dark:text-blue-400 font-medium">Rata-rata/Trip</p>
            <p class="text-3xl font-bold text-blue-700 dark:text-blue-300 mt-2">
                {{ number_format($analytics['production']['avg_catch_per_trip'], 1) }} kg</p>
            <p class="text-xs text-blue-600 dark:text-blue-400 mt-1">Rp
                {{ number_format($analytics['production']['avg_revenue_per_trip'], 0, ',', '.') }}/trip</p>
        </div>

        <div
            class="bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-xl border border-purple-200 dark:border-purple-500/30 p-5">
            <p class="text-xs text-purple-600 dark:text-purple-400 font-medium">Harga per Kg</p>
            <p class="text-3xl font-bold text-purple-700 dark:text-purple-300 mt-2">Rp
                {{ number_format($analytics['production']['revenue_per_kg'], 0, ',', '.') }}</p>
            <p class="text-xs text-purple-600 dark:text-purple-400 mt-1">Average selling price</p>
        </div>
    </div>

    {{-- Top Species Chart --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">🐟 Top 5 Spesies by Weight</h3>

        @if (count($analytics['top_species']) > 0)
            <div class="space-y-3">
                @foreach ($analytics['top_species'] as $index => $species)
                    @php
                        $percentage =
                            $analytics['production']['total_weight'] > 0
                                ? ($species->total_weight / $analytics['production']['total_weight']) * 100
                                : 0;
                        $colors = ['emerald', 'blue', 'cyan', 'purple', 'orange'];
                        $color = $colors[$index] ?? 'gray';
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-1">
                            <div class="flex items-center gap-2">
                                <span
                                    class="w-6 h-6 rounded-full bg-{{ $color }}-100 dark:bg-{{ $color }}-500/20 flex items-center justify-center text-xs font-bold text-{{ $color }}-600 dark:text-{{ $color }}-400">
                                    {{ $index + 1 }}
                                </span>
                                <span class="text-sm font-medium text-gray-900 dark:text-white">
                                    {{ $species->species->common_name ?? 'Unknown' }}
                                </span>
                            </div>
                            <div class="text-right">
                                <span
                                    class="text-sm font-bold text-gray-900 dark:text-white">{{ number_format($species->total_weight, 1) }}
                                    kg</span>
                                <span
                                    class="text-xs text-gray-500 dark:text-slate-400 ml-2">({{ number_format($percentage, 1) }}%)</span>
                            </div>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                            <div class="bg-{{ $color }}-600 h-2 rounded-full transition-all"
                                style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <p class="text-4xl mb-2">📊</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada data tangkapan untuk periode ini.</p>
            </div>
        @endif
    </div>

    {{-- Aquaculture Performance --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Kolam Aktif</h4>
                <span class="text-2xl">🐠</span>
            </div>
            <p class="text-3xl font-bold text-cyan-600">{{ $analytics['aquaculture']['active_ponds'] }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Sedang beroperasi</p>
        </div>

        <div class="bg-white dark:bg-[1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Utilisasi Rata-rata</h4>
                <span class="text-2xl">📈</span>
            </div>
            <p class="text-3xl font-bold text-blue-600">
                {{ number_format($analytics['aquaculture']['avg_utilization'], 1) }}%</p>
            <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2 mt-2">
                <div class="bg-blue-600 h-2 rounded-full"
                    style="width: {{ min($analytics['aquaculture']['avg_utilization'], 100) }}%"></div>
            </div>
        </div>

        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Biaya Pakan</h4>
                <span class="text-2xl">🍽️</span>
            </div>
            <p class="text-3xl font-bold text-orange-600">Rp
                {{ number_format($analytics['aquaculture']['total_feeding_cost'], 0, ',', '.') }}</p>
            <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">Periode ini</p>
        </div>
    </div>

    {{-- Cold Chain Performance --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Cold Chain Performance</h2>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600 dark:text-slate-400">Temperature Breaches</span>
                    <span
                        class="text-2xl font-bold {{ $analytics['cold_chain']['temp_breaches'] > 0 ? 'text-red-600' : 'text-green-600' }}">
                        {{ $analytics['cold_chain']['temp_breaches'] }}
                    </span>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Pelanggaran suhu dalam periode ini</p>
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600 dark:text-slate-400">Storage Utilization</span>
                    <span class="text-2xl font-bold text-blue-600">
                        {{ number_format($analytics['cold_chain']['avg_storage_utilization'], 1) }}%
                    </span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                    <div class="bg-blue-600 h-2 rounded-full"
                        style="width: {{ min($analytics['cold_chain']['avg_storage_utilization'], 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Daily Catch Trend (Simple Bar Chart Visualization) --}}
    @if (count($analytics['daily_catch_trend']) > 0)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Daily Catch Trend (30 Days)</h2>

            <div class="h-64 flex items-end gap-1 overflow-x-auto">
                @php
                    $maxWeight = $analytics['daily_catch_trend']->max('total_weight') ?: 1;
                @endphp
                @foreach ($analytics['daily_catch_trend'] as $trend)
                    @php
                        $height = ($trend->total_weight / $maxWeight) * 100;
                    @endphp
                    <div class="flex-1 min-w-[30px] flex flex-col items-center group">
                        <div class="w-full bg-emerald-500 hover:bg-emerald-600 rounded-t transition-all relative"
                            style="height: {{ max($height, 5) }}px">
                            <div
                                class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                                {{ number_format($trend->total_weight, 1) }} kg
                            </div>
                        </div>
                        <span class="text-[10px] text-gray-500 dark:text-slate-400 mt-1 rotate-45 origin-left">
                            {{ \Carbon\Carbon::parse($trend->date)->format('d/m') }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Weekly Revenue Trend --}}
    @if (count($analytics['weekly_revenue']) > 0)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Weekly Revenue Trend (12 Weeks)
            </h2>

            <div class="h-64 flex items-end gap-2 overflow-x-auto">
                @php
                    $maxRevenue = $analytics['weekly_revenue']->max('revenue') ?: 1;
                @endphp
                @foreach ($analytics['weekly_revenue'] as $week)
                    @php
                        $height = ($week->revenue / $maxRevenue) * 100;
                    @endphp
                    <div class="flex-1 min-w-[40px] flex flex-col items-center group">
                        <div class="w-full bg-orange-500 hover:bg-orange-600 rounded-t transition-all relative"
                            style="height: {{ max($height, 5) }}px">
                            <div
                                class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-gray-900 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition whitespace-nowrap z-10">
                                Rp {{ number_format($week->revenue, 0, ',', '.') }}
                            </div>
                        </div>
                        <span
                            class="text-[10px] text-gray-500 dark:text-slate-400 mt-1">W{{ substr($week->week, -2) }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Efficiency Metrics Summary --}}
    <div
        class="bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-2xl border border-indigo-200 dark:border-indigo-500/30 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Efficiency Metrics</h2>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="text-center">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Catch Rate</p>
                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                    {{ number_format($analytics['production']['avg_catch_per_trip'], 1) }} kg/trip
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Revenue Efficiency</p>
                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                    Rp {{ number_format($analytics['production']['revenue_per_kg'], 0, ',', '.') }}/kg
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Trip Success Rate</p>
                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                    {{ $analytics['production']['completed_trips'] }} completed
                </p>
            </div>
            <div class="text-center">
                <p class="text-xs text-indigo-600 dark:text-indigo-400 mb-1">Quality Score</p>
                <p class="text-2xl font-bold text-indigo-700 dark:text-indigo-300">
                    {{ $analytics['cold_chain']['temp_breaches'] == 0 ? 'Excellent' : 'Good' }}
                </p>
            </div>
        </div>
    </div>

    {{-- Export Report Button --}}
    <div class="mt-6 flex justify-end">
        <button onclick="window.print()"
            class="px-6 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition flex items-center gap-2">
            <span>🖨️</span> Print Report
        </button>
    </div>
</x-app-layout>
