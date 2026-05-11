@extends('layouts.app')
@section('title', 'KPI Dashboard')

@push('head')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
<div class="p-6 space-y-6">

    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">KPI Dashboard</h1>
            <p class="text-sm text-gray-500 mt-1">Target vs Aktual per periode</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="{{ route('kpi.index') }}">
                <input type="month" name="period" value="{{ $period }}"
                       onchange="this.form.submit()"
                       class="px-3 py-2 rounded-lg text-sm bg-white border border-gray-200 text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </form>
            <button onclick="document.getElementById('addKpiModal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Target
            </button>
        </div>
    </div>

    @if(session('success'))
    <div class="p-3 rounded-lg bg-emerald-50 border border-emerald-200 text-sm text-emerald-700">
        {{ session('success') }}
    </div>
    @endif

    {{-- KPI Target Cards --}}
    @if($targets->isNotEmpty())
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach($targets ?? [] as $kpi)
        @php
            $pct    = $kpi->achievementPercent();
            $color  = $kpi->statusColor();
            $colors = [
                'emerald' => ['ring' => 'ring-emerald-500', 'bar' => 'bg-emerald-500', 'text' => 'text-emerald-600', 'badge' => 'bg-emerald-100 text-emerald-700'],
                'blue'    => ['ring' => 'ring-blue-500',    'bar' => 'bg-blue-500',    'text' => 'text-blue-600',       'badge' => 'bg-blue-100 text-blue-700'],
                'amber'   => ['ring' => 'ring-amber-500',   'bar' => 'bg-amber-500',   'text' => 'text-amber-600',     'badge' => 'bg-amber-100 text-amber-700'],
                'red'     => ['ring' => 'ring-red-500',     'bar' => 'bg-red-500',     'text' => 'text-red-600',         'badge' => 'bg-red-100 text-red-700'],
            ][$color];
        @endphp
        <div class="bg-white rounded-xl border border-gray-200 p-5 cursor-pointer hover:shadow-md transition-shadow"
             onclick="loadDrilldown('{{ $kpi->metric }}', '{{ $kpi->label }}')">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wide">{{ $kpi->label }}</p>
                <div class="flex items-center gap-1">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $colors['badge'] }}">{{ $pct }}%</span>
                    <form method="POST" action="{{ route('kpi.destroy', $kpi) }}" data-confirm="Hapus target ini?" data-confirm-type="danger">
                        @csrf @method('DELETE')
                        <button type="submit" class="text-gray-300 hover:text-red-400 transition ml-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">
                @if($kpi->unit === 'currency') Rp {{ number_format($kpi->actual, 0, ',', '.') }}
                @elseif($kpi->unit === 'percent') {{ $kpi->actual }}%
                @else {{ number_format($kpi->actual, 0, ',', '.') }}
                @endif
            </p>
            <p class="text-xs text-gray-400 mt-1">
                Target:
                @if($kpi->unit === 'currency') Rp {{ number_format($kpi->target, 0, ',', '.') }}
                @elseif($kpi->unit === 'percent') {{ $kpi->target }}%
                @else {{ number_format($kpi->target, 0, ',', '.') }}
                @endif
            </p>
            <div class="mt-3 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                <div class="{{ $colors['bar'] }} h-full rounded-full transition-all duration-500"
                     style="width: {{ min($pct, 100) }}%"></div>
            </div>
            <p class="text-xs text-gray-400 mt-1.5">Klik untuk drill-down ?</p>
        </div>
        @endforeach
    </div>
    @else
    <div class="bg-white rounded-xl border border-dashed border-gray-300 p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm text-gray-500">Belum ada target KPI untuk periode ini.</p>
        <button onclick="document.getElementById('addKpiModal').classList.remove('hidden')"
                class="mt-3 text-sm text-blue-500 hover:underline">Tambah target pertama ?</button>
    </div>
    @endif

    {{-- All KPI Actuals (read-only overview) --}}
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700">Semua Metrik — {{ \Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y') }}</h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y divide-gray-100">
            @foreach($kpis ?? [] as $key => $kpi)
            <div class="p-4 cursor-pointer hover:bg-gray-50 transition-colors"
                 onclick="loadDrilldown('{{ $key }}', '{{ $kpi['label'] }}')">
                <p class="text-xs text-gray-500">{{ $kpi['label'] }}</p>
                <p class="text-lg font-bold text-gray-900 mt-1">
                    @if($kpi['unit'] === 'currency') Rp {{ number_format($kpi['actual'], 0, ',', '.') }}
                    @elseif($kpi['unit'] === 'percent') {{ $kpi['actual'] }}%
                    @else {{ number_format($kpi['actual'], 0, ',', '.') }}
                    @endif
                </p>
                <p class="text-xs text-blue-400 mt-0.5">drill-down ?</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Trend Chart --}}
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Tren 6 Bulan Terakhir</h2>
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Drill-down Panel --}}
    <div id="drilldownPanel" class="hidden bg-white rounded-xl border border-gray-200 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 id="drilldownTitle" class="text-sm font-semibold text-gray-700">Detail</h2>
            <button onclick="document.getElementById('drilldownPanel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="relative h-64">
            <canvas id="drilldownChart"></canvas>
        </div>
    </div>

</div>

{{-- Add KPI Modal --}}
<div id="addKpiModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl border border-gray-200 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900">Tambah Target KPI</h3>
            <button onclick="document.getElementById('addKpiModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('kpi.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Metrik</label>
                <select name="metric" required
                        class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($metrics ?? [] as $key => $meta)
                    <option value="{{ $key }}">{{ $meta['label'] }} ({{ $meta['unit'] }})</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                <input type="month" name="period" value="{{ $period }}" required
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Target</label>
                <input type="number" name="target" min="0" step="any" required placeholder="0"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 border border-gray-200 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Warna</label>
                <input type="color" name="color" value="#3b82f6"
                       class="h-9 w-full rounded-lg border border-gray-200 cursor-pointer">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addKpiModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                    Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
(function () {
    const isDark    = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)';
    const textColor = isDark ? '#94a3b8' : '#6b7280';
    const period    = '{{ $period }}';

    // -- Trend chart ----------------------------------------------
    const trend = @json($trend);
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.month),
            datasets: [
                { label: 'Pendapatan', data: trend.map(t => t.revenue), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Laba',       data: trend.map(t => t.profit),  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Pengeluaran',data: trend.map(t => t.expense), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.08)',  fill: true, tension: 0.4, pointRadius: 3 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { color: textColor, font: { size: 11 }, boxWidth: 10, usePointStyle: true } } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, font: { size: 10 }, callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt' }, grid: { color: gridColor } },
            },
        },
    });

    // -- Drill-down -----------------------------------------------
    let drillChart = null;

    window.loadDrilldown = function (metric, label) {
        const panel = document.getElementById('drilldownPanel');
        document.getElementById('drilldownTitle').textContent = 'Detail: ' + label;
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        fetch('{{ url("kpi/drilldown") }}/' + metric + `?period=${period}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(d => {
            if (drillChart) drillChart.destroy();
            const ctx = document.getElementById('drilldownChart').getContext('2d');

            const colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16'];

            drillChart = new Chart(ctx, {
                type: d.type,
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: d.label,
                        data: d.data,
                        backgroundColor: d.type === 'doughnut'
                            ? colors.slice(0, d.data.length)
                            : 'rgba(59,130,246,0.2)',
                        borderColor: d.type === 'doughnut' ? colors.slice(0, d.data.length) : '#3b82f6',
                        borderWidth: 2,
                        borderRadius: d.type === 'bar' ? 6 : 0,
                        tension: 0.4,
                        fill: d.type === 'line',
                        pointRadius: d.type === 'line' ? 3 : 0,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: d.type === 'doughnut', labels: { color: textColor, font: { size: 11 }, boxWidth: 10 } },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + Number(ctx.parsed.y ?? ctx.parsed).toLocaleString('id-ID') } },
                    },
                    scales: d.type !== 'doughnut' ? {
                        x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, font: { size: 10 }, callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt' }, grid: { color: gridColor } },
                    } : {},
                },
            });
        });
    };
})();
</script>
@endpush
@endsection
