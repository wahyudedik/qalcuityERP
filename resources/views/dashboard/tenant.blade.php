<x-app-layout>
    <x-slot name="title">Dashboard — Qalcuity ERP</x-slot>
    <x-slot name="header">Dashboard</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush

    {{-- Greeting --}}
    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Selamat datang, {{ auth()->user()->name }} 👋</h2>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }}</p>
    </div>

    {{-- AI Insights Widget --}}
    @if(!empty($insights))
    <div class="mb-6" id="ai-insights-section">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-indigo-500/20 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Insight AI</p>
                <span class="text-xs text-gray-400 dark:text-slate-500" id="insights-updated-at">— diperbarui otomatis setiap jam</span>
            </div>
            <button id="btn-refresh-insights"
                onclick="refreshDashboardInsights()"
                class="flex items-center gap-1.5 text-xs text-indigo-400 hover:text-indigo-300 transition font-medium">
                <svg id="refresh-icon" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-3" id="insights-grid">
            @foreach(array_slice($insights, 0, 6) as $insight)
            @php
                $borderColor = match($insight['severity']) {
                    'critical' => 'border-red-500/40 bg-red-500/5',
                    'warning'  => 'border-yellow-500/40 bg-yellow-500/5',
                    default    => 'border-blue-500/20 bg-blue-500/5',
                };
                $badgeColor = match($insight['severity']) {
                    'critical' => 'bg-red-500/20 text-red-400',
                    'warning'  => 'bg-yellow-500/20 text-yellow-400',
                    default    => 'bg-blue-500/20 text-blue-400',
                };
                $badgeLabel = match($insight['severity']) {
                    'critical' => 'Kritis',
                    'warning'  => 'Perhatian',
                    default    => 'Info',
                };
            @endphp
            <div class="rounded-xl border {{ $borderColor }} p-4 flex flex-col gap-2">
                <div class="flex items-start justify-between gap-2">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">{{ $insight['title'] }}</p>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 {{ $badgeColor }}">{{ $badgeLabel }}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">{{ $insight['body'] }}</p>
                @if(!empty($insight['action']))
                <a href="{{ route('chat.index') }}?q={{ urlencode($insight['action']) }}"
                   class="text-xs text-indigo-400 hover:text-indigo-300 font-medium mt-auto">
                    Tanya AI → {{ $insight['action'] }}
                </a>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Anomaly Highlight --}}
    @if(!empty($openAnomalies) && $openAnomalies->isNotEmpty())
    <div class="mb-6" id="anomaly-section">
        <div class="flex items-center justify-between gap-2 mb-3">
            <div class="flex items-center gap-2">
                <div class="w-6 h-6 rounded-lg bg-red-500/20 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Anomali Terdeteksi</p>
                <span class="text-xs bg-red-500/20 text-red-400 font-medium px-2 py-0.5 rounded-full">{{ $openAnomalies->count() }} open</span>
            </div>
            <a href="{{ route('anomalies.index') }}" class="text-xs text-red-400 hover:text-red-300 font-medium">Lihat semua →</a>
        </div>
        <div class="space-y-2" id="anomaly-list">
            @foreach($openAnomalies as $anomaly)
            @php
                $aBorder = match($anomaly->severity) {
                    'critical' => 'border-red-500/40 bg-red-500/5',
                    'warning'  => 'border-yellow-500/40 bg-yellow-500/5',
                    default    => 'border-orange-500/20 bg-orange-500/5',
                };
                $aBadge = match($anomaly->severity) {
                    'critical' => 'bg-red-500/20 text-red-400',
                    'warning'  => 'bg-yellow-500/20 text-yellow-400',
                    default    => 'bg-orange-500/20 text-orange-400',
                };
                $aIcon = match($anomaly->severity) {
                    'critical' => 'text-red-400',
                    'warning'  => 'text-yellow-400',
                    default    => 'text-orange-400',
                };
            @endphp
            <div class="rounded-xl border {{ $aBorder }} p-3.5 flex items-start gap-3" id="anomaly-{{ $anomaly->id }}">
                <svg class="w-4 h-4 {{ $aIcon }} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-0.5">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $anomaly->title }}</p>
                        <span class="text-xs font-medium px-1.5 py-0.5 rounded-full shrink-0 {{ $aBadge }}">
                            {{ match($anomaly->severity) { 'critical' => 'Kritis', 'warning' => 'Perhatian', default => 'Info' } }}
                        </span>
                    </div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">{{ $anomaly->description }}</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">{{ $anomaly->created_at->diffForHumans() }}</p>
                </div>
                <button
                    onclick="acknowledgeAnomaly({{ $anomaly->id }}, this)"
                    class="text-xs text-gray-400 hover:text-green-400 transition shrink-0 font-medium"
                    title="Tandai sudah ditinjau">
                    ✓ Tinjau
                </button>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- KPI Cards --}}
    @php
    $cards = [
        ['label' => 'Pendapatan Bulan Ini', 'value' => 'Rp ' . number_format($finance['income'], 0, ',', '.'), 'sub' => 'Profit: Rp ' . number_format($finance['profit'], 0, ',', '.'), 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z', 'bg' => 'bg-blue-500/20', 'ic' => 'text-blue-400'],
        ['label' => 'Order Bulan Ini', 'value' => number_format($sales['this_month_orders']), 'sub' => ($sales['growth_percent'] >= 0 ? '▲ ' : '▼ ') . abs($sales['growth_percent']) . '% vs bulan lalu', 'icon' => 'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z', 'bg' => 'bg-green-500/20', 'ic' => 'text-green-400'],
        ['label' => 'Stok Menipis', 'value' => $inventory['low_stock_count'], 'sub' => $inventory['total_products'] . ' total produk', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4', 'bg' => $inventory['low_stock_count'] > 0 ? 'bg-red-500/20' : 'bg-green-500/20', 'ic' => $inventory['low_stock_count'] > 0 ? 'text-red-400' : 'text-green-400'],
        ['label' => 'Karyawan Hadir', 'value' => $hrm['present_today'], 'sub' => 'Dari ' . $hrm['total_employees'] . ' karyawan aktif', 'icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'bg' => 'bg-purple-500/20', 'ic' => 'text-purple-400'],
    ];
    @endphp

    <div class="grid grid-cols-2 xl:grid-cols-4 gap-4 mb-6">
        @foreach($cards as $card)
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-start justify-between mb-4">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400 leading-tight">{{ $card['label'] }}</p>
                <div class="w-9 h-9 rounded-xl {{ $card['bg'] }} flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 {{ $card['ic'] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $card['icon'] }}"/>
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $card['value'] }}</p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>

    {{-- Charts --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Penjualan 7 Hari Terakhir</p>
                <a href="{{ route('reports.index') }}" class="text-xs text-blue-400 hover:underline">Lihat laporan →</a>
            </div>
            <div style="height:200px;position:relative">
                <canvas id="salesChart"></canvas>
            </div>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Keuangan 6 Bulan Terakhir</p>
            <div style="height:200px;position:relative">
                <canvas id="financeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Bottom --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        {{-- Low Stock --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Stok Menipis</p>
                <span class="text-xs bg-red-500/20 text-red-400 font-medium px-2 py-0.5 rounded-full">{{ $inventory['low_stock_count'] }} item</span>
            </div>
            @if($inventory['low_stock_items']->isEmpty())
            <div class="flex flex-col items-center py-6 text-gray-400 dark:text-slate-500">
                <svg class="w-10 h-10 mb-2 text-green-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <p class="text-sm">Semua stok aman</p>
            </div>
            @else
            <div class="space-y-0">
                @foreach($inventory['low_stock_items'] as $item)
                <div class="flex items-center justify-between py-2.5 border-b border-white/5 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $item->product->name }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">{{ $item->warehouse->name }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-red-400">{{ $item->quantity }} {{ $item->product->unit }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">min: {{ $item->product->stock_min }}</p>
                    </div>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        {{-- Quick Stats + CTA --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Cepat</p>
            <div class="space-y-3">
                @php
                $stats = [
                    ['label' => 'Order Pending',        'value' => $sales['pending_orders'],                                    'color' => 'text-yellow-400'],
                    ['label' => 'PO Belum Diterima',    'value' => $finance['pending_po'],                                      'color' => 'text-orange-400'],
                    ['label' => 'Total Pelanggan',      'value' => $hrm['total_customers'],                                     'color' => 'text-blue-400'],
                    ['label' => 'Total Gudang',         'value' => $inventory['total_warehouses'],                              'color' => 'text-slate-300'],
                    ['label' => 'Pengeluaran Bulan Ini','value' => 'Rp ' . number_format($finance['expense'], 0, ',', '.'),     'color' => 'text-red-400'],
                ];
                @endphp
                @foreach($stats as $stat)
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500 dark:text-slate-400">{{ $stat['label'] }}</span>
                    <span class="text-sm font-semibold {{ $stat['color'] }}">{{ $stat['value'] }}</span>
                </div>
                @endforeach
            </div>
            <div class="mt-5 pt-4 border-t border-gray-200 dark:border-white/10">
                <a href="{{ route('chat.index') }}"
                   class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-gray-900 dark:text-white text-sm font-semibold py-2.5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                    Tanya Qalcuity AI
                </a>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    // ── Dashboard AI Refresh ──────────────────────────────────────
    const REFRESH_URL     = '{{ route('dashboard.refresh-insights') }}';
    const ACK_URL_BASE    = '{{ url('/dashboard/anomalies') }}';
    const CSRF            = '{{ csrf_token() }}';

    async function refreshDashboardInsights() {
        const btn  = document.getElementById('btn-refresh-insights');
        const icon = document.getElementById('refresh-icon');
        if (!btn) return;

        btn.disabled = true;
        icon.classList.add('animate-spin');

        try {
            const res  = await fetch(REFRESH_URL, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const data = await res.json();

            // Update insights grid
            const grid = document.getElementById('insights-grid');
            if (grid && data.insights?.length) {
                grid.innerHTML = data.insights.map(insight => insightCard(insight)).join('');
            }

            // Update anomaly list
            const list = document.getElementById('anomaly-list');
            if (list && data.anomalies?.length) {
                list.innerHTML = data.anomalies.map(a => anomalyRow(a)).join('');
                document.getElementById('anomaly-section')?.classList.remove('hidden');
            } else if (list && !data.anomalies?.length) {
                document.getElementById('anomaly-section')?.classList.add('hidden');
            }

            // Update timestamp
            const ts = document.getElementById('insights-updated-at');
            if (ts) ts.textContent = `— diperbarui pukul ${data.updated_at}`;

        } catch (e) {
            console.error('Refresh insights failed', e);
        } finally {
            btn.disabled = false;
            icon.classList.remove('animate-spin');
        }
    }

    async function acknowledgeAnomaly(id, btn) {
        btn.disabled = true;
        btn.textContent = '...';
        try {
            await fetch(`${ACK_URL_BASE}/${id}/acknowledge`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            });
            const el = document.getElementById(`anomaly-${id}`);
            if (el) {
                el.style.opacity = '0';
                el.style.transition = 'opacity 0.3s';
                setTimeout(() => el.remove(), 300);
            }
        } catch (e) {
            btn.disabled = false;
            btn.textContent = '✓ Tinjau';
        }
    }

    function insightCard(insight) {
        const borders = { critical: 'border-red-500/40 bg-red-500/5', warning: 'border-yellow-500/40 bg-yellow-500/5', info: 'border-blue-500/20 bg-blue-500/5' };
        const badges  = { critical: 'bg-red-500/20 text-red-400', warning: 'bg-yellow-500/20 text-yellow-400', info: 'bg-blue-500/20 text-blue-400' };
        const labels  = { critical: 'Kritis', warning: 'Perhatian', info: 'Info' };
        const sev     = insight.severity || 'info';
        const action  = insight.action ? `<a href="/chat?q=${encodeURIComponent(insight.action)}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium mt-auto">Tanya AI → ${insight.action}</a>` : '';
        return `<div class="rounded-xl border ${borders[sev] || borders.info} p-4 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-gray-900 dark:text-white leading-snug">${insight.title}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
            </div>
            <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">${insight.body}</p>
            ${action}
        </div>`;
    }

    function anomalyRow(a) {
        const borders = { critical: 'border-red-500/40 bg-red-500/5', warning: 'border-yellow-500/40 bg-yellow-500/5', info: 'border-orange-500/20 bg-orange-500/5' };
        const badges  = { critical: 'bg-red-500/20 text-red-400', warning: 'bg-yellow-500/20 text-yellow-400', info: 'bg-orange-500/20 text-orange-400' };
        const icons   = { critical: 'text-red-400', warning: 'text-yellow-400', info: 'text-orange-400' };
        const labels  = { critical: 'Kritis', warning: 'Perhatian', info: 'Info' };
        const sev     = a.severity || 'info';
        return `<div class="rounded-xl border ${borders[sev] || borders.info} p-3.5 flex items-start gap-3" id="anomaly-${a.id}">
            <svg class="w-4 h-4 ${icons[sev]} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">${a.title}</p>
                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400 leading-relaxed">${a.description}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">${a.age}</p>
            </div>
            <button onclick="acknowledgeAnomaly(${a.id}, this)" class="text-xs text-gray-400 hover:text-green-400 transition shrink-0 font-medium" title="Tandai sudah ditinjau">✓ Tinjau</button>
        </div>`;
    }

    // ── Charts ────────────────────────────────────────────────────
    const chartDefaults = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false } },
    };
    const isDark    = document.getElementById('html-root')?.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const tickColor = isDark ? '#94a3b8' : '#6b7280';
    const tickFont  = { size: 10, family: 'Inter' };

    // Delay chart init until layout is fully painted — fixes mobile glitch
    requestAnimationFrame(() => setTimeout(() => {

        new Chart(document.getElementById('salesChart'), {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($sales['chart'], 'date')) !!},
                datasets: [{ label: 'Penjualan', data: {!! json_encode(array_column($sales['chart'], 'total')) !!},
                    backgroundColor: 'rgba(59,130,246,0.2)', borderColor: '#3b82f6',
                    borderWidth: 2, borderRadius: 8, borderSkipped: false }]
            },
            options: { ...chartDefaults, scales: {
                y: { ticks: { callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt', font: tickFont, color: tickColor }, grid: { color: gridColor } },
                x: { ticks: { font: tickFont, color: tickColor }, grid: { display: false } }
            }}
        });

        new Chart(document.getElementById('financeChart'), {
            type: 'line',
            data: {
                labels: {!! json_encode(array_column($finance['chart'], 'month')) !!},
                datasets: [
                    { label: 'Pemasukan',   data: {!! json_encode(array_column($finance['chart'], 'income')) !!},  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#10b981' },
                    { label: 'Pengeluaran', data: {!! json_encode(array_column($finance['chart'], 'expense')) !!}, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)',   tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#ef4444' },
                ]
            },
            options: { ...chartDefaults,
                plugins: { legend: { display: true, labels: { font: { size: 11, family: 'Inter' }, color: tickColor, boxWidth: 10, usePointStyle: true } } },
                scales: {
                    y: { ticks: { callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt', font: tickFont, color: tickColor }, grid: { color: gridColor } },
                    x: { ticks: { font: tickFont, color: tickColor }, grid: { display: false } }
                }
            }
        });

    }, 50));
    </script>
    @endpush
</x-app-layout>
