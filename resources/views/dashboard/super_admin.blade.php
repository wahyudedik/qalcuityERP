<x-app-layout>
    <x-slot name="title">Dashboard Super Admin — Qalcuity ERP</x-slot>
    <x-slot name="header">Dashboard Platform</x-slot>

    @push('head')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Selamat datang, {{ auth()->user()->name }} 👋</h2>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }} · Platform Overview</p>
    </div>

    @php
    $activeCount = $activeTenants->count();
    $cards = [
        ['label'=>'Total Tenant',   'value'=>$totalTenants,  'sub'=>'+' . $newThisMonth . ' bulan ini',                    'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4', 'bg'=>'bg-blue-500/20',   'ic'=>'text-blue-400'],
        ['label'=>'Tenant Aktif',   'value'=>$activeCount,   'sub'=>$trialTenants . ' dalam masa trial',                   'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                    'bg'=>'bg-green-500/20',  'ic'=>'text-green-400'],
        ['label'=>'Tenant Expired', 'value'=>$expiredTenants,'sub'=>'Perlu tindak lanjut',                                 'icon'=>'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',                                                                                                                                                                                'bg'=>$expiredTenants>0?'bg-red-500/20':'bg-gray-50 dark:bg-white/5', 'ic'=>$expiredTenants>0?'text-red-400':'text-gray-500 dark:text-slate-400'],
        ['label'=>'Total User',     'value'=>$totalUsers,    'sub'=>'AI: ' . number_format($aiThisMonth) . ' pesan bulan ini', 'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'bg'=>'bg-purple-500/20', 'ic'=>'text-purple-400'],
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

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Pertumbuhan Tenant (6 Bulan)</p>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-blue-400 hover:underline">Lihat semua →</a>
            </div>
            <div style="height:200px;position:relative"><canvas id="growthChart"></canvas></div>
        </div>

        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Distribusi Paket</p>
                <a href="{{ route('super-admin.plans.index') }}" class="text-xs text-blue-400 hover:underline">Kelola paket →</a>
            </div>
            @php
            $planColors = ['trial'=>'#f59e0b','basic'=>'#3b82f6','pro'=>'#8b5cf6','enterprise'=>'#10b981'];
            $planLabels = ['trial'=>'Trial','basic'=>'Basic','pro'=>'Pro','enterprise'=>'Enterprise'];
            @endphp
            <div class="flex flex-col sm:flex-row items-center gap-6">
                <div style="height:160px;width:160px;position:relative;flex-shrink:0" class="mx-auto sm:mx-0"><canvas id="planChart"></canvas></div>
                <div class="space-y-2 flex-1">
                    @foreach($planDist as $plan => $count)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2.5 h-2.5 rounded-full shrink-0" style="background:{{ $planColors[$plan] ?? '#94a3b8' }}"></span>
                            <span class="text-sm text-gray-700 dark:text-slate-300">{{ $planLabels[$plan] ?? ucfirst($plan) }}</span>
                        </div>
                        <span class="text-sm font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                    </div>
                    @endforeach
                    @if(empty($planDist))<p class="text-sm text-gray-400 dark:text-slate-500">Belum ada data</p>@endif
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Tenant Terbaru</p>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-blue-400 hover:underline">Lihat semua →</a>
            </div>
            @if($recentTenants->isEmpty())
            <p class="text-sm text-gray-400 dark:text-slate-500 py-4 text-center">Belum ada tenant terdaftar.</p>
            @else
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach($recentTenants as $tenant)
                @php
                $statusColor = match($tenant->subscriptionStatus()) {
                    'active'        => 'bg-green-500/20 text-green-400',
                    'trial'         => 'bg-amber-500/20 text-amber-400',
                    'trial_expired','expired' => 'bg-red-500/20 text-red-400',
                    default         => 'bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400',
                };
                $statusLabel = match($tenant->subscriptionStatus()) {
                    'active'=>'Aktif','trial'=>'Trial','trial_expired'=>'Trial Expired','expired'=>'Expired',default=>'Nonaktif',
                };
                @endphp
                <div class="flex items-center justify-between py-2.5">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-gray-900 dark:text-white text-xs font-bold shrink-0">
                            {{ strtoupper(substr($tenant->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ $tenant->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ $tenant->created_at->format('d M Y') }}</p>
                        </div>
                    </div>
                    <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 {{ $statusColor }}">{{ $statusLabel }}</span>
                </div>
                @endforeach
            </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Top AI Usage Bulan Ini</p>
                @if($topAiTenants->isEmpty())
                <p class="text-sm text-gray-400 dark:text-slate-500 py-2 text-center">Belum ada penggunaan AI bulan ini.</p>
                @else
                <div class="space-y-2">
                    @foreach($topAiTenants as $log)
                    <div class="flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-xs font-medium text-gray-700 dark:text-slate-300 truncate">{{ $log->tenant?->name ?? 'Tenant #'.$log->tenant_id }}</span>
                                <span class="text-xs text-gray-400 dark:text-slate-500 shrink-0 ml-2">{{ number_format($log->total) }} pesan</span>
                            </div>
                            @php $maxAi=$log->tenant?->maxAiMessages()??100; $pct=$maxAi>0?min(100,round(($log->total/$maxAi)*100)):0; @endphp
                            <div class="w-full bg-[#f8f8f8] dark:bg-white/10 rounded-full h-1.5">
                                <div class="h-1.5 rounded-full {{ $pct>=90?'bg-red-500':($pct>=70?'bg-amber-500':'bg-blue-500') }}" style="width:{{ $pct }}%"></div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
                @endif
            </div>

            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Aksi Cepat</p>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('super-admin.tenants.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-medium text-gray-700 dark:text-slate-300">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        Kelola Tenant
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}" class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-medium text-gray-700 dark:text-slate-300">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Kelola Paket
                    </a>
                    <a href="{{ route('super-admin.tenants.index') }}?status=expired" class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 transition text-sm font-medium text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tenant Expired
                        @if($expiredTenants>0)<span class="ml-auto text-xs bg-red-500/30 text-red-300 px-1.5 py-0.5 rounded-full font-semibold">{{ $expiredTenants }}</span>@endif
                    </a>
                    <a href="{{ route('super-admin.tenants.index') }}?plan=trial" class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 transition text-sm font-medium text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Tenant Trial
                        <span class="ml-auto text-xs bg-amber-500/30 text-amber-300 px-1.5 py-0.5 rounded-full font-semibold">{{ $trialTenants }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
    const tickFont  = { size: 10, family: 'Inter' };
    const tickColor = '#94a3b8';
    const gridColor = 'rgba(255,255,255,0.06)';

    new Chart(document.getElementById('growthChart'), {
        type: 'bar',
        data: {
            labels: {!! json_encode(array_column($growthChart, 'month')) !!},
            datasets: [{ label: 'Tenant Baru', data: {!! json_encode(array_column($growthChart, 'count')) !!},
                backgroundColor: 'rgba(99,102,241,0.2)', borderColor: '#6366f1',
                borderWidth: 2, borderRadius: 8, borderSkipped: false }]
        },
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { ticks: { stepSize: 1, font: tickFont, color: tickColor }, grid: { color: gridColor } },
                x: { ticks: { font: tickFont, color: tickColor }, grid: { display: false } }
            }
        }
    });

    @php
    $planChartData   = array_values($planDist);
    $planChartLabels = array_map(fn($k) => $planLabels[$k] ?? ucfirst($k), array_keys($planDist));
    $planChartColors = array_map(fn($k) => $planColors[$k] ?? '#94a3b8', array_keys($planDist));
    @endphp

    @if(!empty($planDist))
    new Chart(document.getElementById('planChart'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($planChartLabels) !!},
            datasets: [{ data: {!! json_encode($planChartData) !!}, backgroundColor: {!! json_encode($planChartColors) !!}, borderWidth: 0, hoverOffset: 4 }]
        },
        options: { responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { callbacks: { label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' tenant' } } },
            cutout: '70%'
        }
    });
    @endif
    </script>
    @endpush
</x-app-layout>
