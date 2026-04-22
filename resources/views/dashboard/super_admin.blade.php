<x-app-layout>
    <x-slot name="title">Dashboard Super Admin — Qalcuity ERP</x-slot>
    <x-slot name="header">Dashboard Platform</x-slot>

    @push('head')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
    @endpush

    {{-- BUG-DASH-002 FIX: Error feedback alert --}}
    @if (isset($dashboard_error) && $dashboard_error)
        <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl p-4 animate-pulse">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-400">⚠️ Dashboard Error</p>
                    <p class="text-xs text-red-300/80 mt-0.5">{{ $error_message }}</p>

                    @if (isset($error_details) && $error_details)
                        <div class="mt-2 p-2 bg-black/20 rounded-lg">
                            <p class="text-[10px] text-red-200 font-mono">
                                <strong>Error:</strong> {{ $error_details['message'] }}<br>
                                <strong>File:</strong> {{ $error_details['file'] }}:{{ $error_details['line'] }}
                            </p>
                        </div>
                    @endif

                    <div class="mt-2 flex gap-2">
                        <button onclick="location.reload()"
                            class="text-xs bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1 rounded-lg transition">
                            🔄 Refresh Dashboard
                        </button>
                        <a href="{{ route('super-admin.monitoring.index') }}"
                            class="text-xs bg-red-500/20 hover:bg-red-500/30 text-red-300 px-3 py-1 rounded-lg transition">
                            📊 View Error Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6">
        <h2 class="text-xl font-semibold text-gray-900 dark:text-white">Selamat datang, {{ auth()->user()->name }} 👋
        </h2>
        <p class="text-sm text-gray-500 dark:text-slate-400 mt-0.5">{{ now()->translatedFormat('l, d F Y') }} · Platform
            Overview</p>
    </div>

    {{-- Alert: Expiring in 7 days --}}
    @if (isset($expiringIn7) && $expiringIn7->isNotEmpty())
        <div class="mb-6 bg-red-500/10 border border-red-500/30 rounded-2xl p-4">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-red-400 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-semibold text-red-400">{{ $expiringIn7->count() }} tenant akan expired dalam
                        7 hari</p>
                    <p class="text-xs text-red-300/80 mt-0.5">
                        {{ $expiringIn7->pluck('name')->join(', ') }}
                    </p>
                </div>
                <a href="{{ route('super-admin.tenants.index') }}?status=expired"
                    class="text-xs text-red-400 hover:underline shrink-0">Lihat →</a>
            </div>
        </div>
    @endif

    @php
        // BUG-DASH-002 FIX: Handle error state with fallback values
        $activeCount = $activeTenants->count() ?? 0;
        $cards = [
            [
                'label' => 'Total Tenant',
                'value' => $totalTenants ?? 0,
                'sub' => '+' . ($newThisMonth ?? 0) . ' bulan ini · +' . ($newThisWeek ?? 0) . ' minggu ini',
                'icon' =>
                    'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
                'bg' => 'bg-blue-500/20',
                'ic' => 'text-blue-400',
            ],
            [
                'label' => 'Tenant Aktif',
                'value' => $activeCount,
                'sub' => ($trialTenants ?? 0) . ' dalam masa trial',
                'icon' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                'bg' => 'bg-green-500/20',
                'ic' => 'text-green-400',
            ],
            [
                'label' => 'Tenant Expired',
                'value' => $expiredTenants ?? 0,
                'sub' => 'Perlu tindak lanjut',
                'icon' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'bg' => ($expiredTenants ?? 0) > 0 ? 'bg-red-500/20' : 'bg-gray-50 dark:bg-white/5',
                'ic' => ($expiredTenants ?? 0) > 0 ? 'text-red-400' : 'text-gray-500 dark:text-slate-400',
            ],
            [
                'label' => 'Total User',
                'value' => $totalUsers ?? 0,
                'sub' => 'AI: ' . number_format($aiThisMonth ?? 0) . ' pesan bulan ini',
                'icon' =>
                    'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                'bg' => 'bg-purple-500/20',
                'ic' => 'text-purple-400',
            ],
            [
                'label' => 'Est. MRR',
                'value' => 'Rp ' . number_format($mrrEstimate ?? 0, 0, ',', '.'),
                'sub' => 'Monthly Recurring Revenue',
                'icon' =>
                    'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                'bg' => 'bg-emerald-500/20',
                'ic' => 'text-emerald-400',
            ],
            [
                'label' => 'Expiring 30 Hari',
                'value' => $expiringIn30->count() ?? 0,
                'sub' => ($expiringIn7->count() ?? 0) . ' dalam 7 hari ke depan',
                'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
                'bg' => ($expiringIn30->count() ?? 0) > 0 ? 'bg-amber-500/20' : 'bg-gray-50 dark:bg-white/5',
                'ic' => ($expiringIn30->count() ?? 0) > 0 ? 'text-amber-400' : 'text-gray-500 dark:text-slate-400',
            ],
        ];
    @endphp

    <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-6 gap-3 sm:gap-4 mb-6">
        @foreach ($cards as $card)
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-3 sm:p-5 overflow-hidden">
                <div class="flex items-start justify-between mb-2 sm:mb-4">
                    <p class="text-[10px] sm:text-xs font-medium text-gray-500 dark:text-slate-400 leading-tight">
                        {{ $card['label'] }}</p>
                    <div
                        class="w-7 h-7 sm:w-9 sm:h-9 rounded-xl {{ $card['bg'] }} flex items-center justify-center shrink-0 ml-1">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 {{ $card['ic'] }}" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                d="{{ $card['icon'] }}" />
                        </svg>
                    </div>
                </div>
                <p class="text-base sm:text-xl font-bold text-gray-900 dark:text-white truncate">{{ $card['value'] }}
                </p>
                <p class="text-[10px] sm:text-xs text-gray-400 dark:text-slate-500 mt-1 truncate">{{ $card['sub'] }}
                </p>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 sm:p-5 min-w-0">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">Pertumbuhan Tenant (6 Bulan)</p>
                <a href="{{ route('super-admin.tenants.index') }}"
                    class="text-xs text-blue-400 hover:underline shrink-0 ml-2">Lihat semua →</a>
            </div>
            <div class="relative" style="height:200px"><canvas id="growthChart"></canvas></div>
        </div>

        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 sm:p-5 min-w-0">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Distribusi Paket</p>
                <a href="{{ route('super-admin.plans.index') }}"
                    class="text-xs text-blue-400 hover:underline shrink-0 ml-2">Kelola paket →</a>
            </div>
            @php
                $planColors = [
                    'trial' => '#f59e0b',
                    'starter' => '#6366f1',
                    'basic' => '#3b82f6',
                    'business' => '#3b82f6',
                    'pro' => '#8b5cf6',
                    'professional' => '#8b5cf6',
                    'enterprise' => '#10b981',
                ];
                $planLabels = [
                    'trial' => 'Trial',
                    'starter' => 'Starter',
                    'basic' => 'Basic',
                    'business' => 'Business',
                    'pro' => 'Pro',
                    'professional' => 'Professional',
                    'enterprise' => 'Enterprise',
                ];
            @endphp
            <div class="flex flex-col sm:flex-row items-center gap-4 sm:gap-6">
                <div class="relative shrink-0 mx-auto sm:mx-0" style="height:120px;width:120px"><canvas
                        id="planChart"></canvas></div>
                <div class="space-y-2 flex-1 w-full">
                    @foreach ($planDist as $plan => $count)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full shrink-0"
                                    style="background:{{ $planColors[$plan] ?? '#94a3b8' }}"></span>
                                <span
                                    class="text-sm text-gray-700 dark:text-slate-300">{{ $planLabels[$plan] ?? ucfirst($plan) }}</span>
                            </div>
                            <span
                                class="text-sm font-semibold text-gray-900 dark:text-white">{{ $count }}</span>
                        </div>
                    @endforeach
                    @if (empty($planDist))
                        <p class="text-sm text-gray-400 dark:text-slate-500">Belum ada data</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Expiring Soon List --}}
    @if ($expiringIn30->isNotEmpty())
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Akan Expired dalam 30 Hari</p>
                <span
                    class="text-xs bg-amber-500/20 text-amber-400 font-medium px-2 py-0.5 rounded-full">{{ $expiringIn30->count() }}
                    tenant</span>
            </div>
            <div class="divide-y divide-gray-100 dark:divide-white/5">
                @foreach ($expiringIn30 as $tenant)
                    @php
                        $expiryDate = $tenant->plan === 'trial' ? $tenant->trial_ends_at : $tenant->plan_expires_at;
                        $daysLeft = $expiryDate ? now()->diffInDays($expiryDate, false) : null;
                        $urgency = $daysLeft !== null && $daysLeft <= 7 ? 'text-red-400' : 'text-amber-400';
                    @endphp
                    <div class="flex items-center justify-between py-2.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div
                                class="w-8 h-8 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                {{ strtoupper(substr($tenant->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                    {{ $tenant->name }}</p>
                                <p class="text-xs text-gray-400 dark:text-slate-500">
                                    {{ $tenant->admins->first()?->email ?? $tenant->slug }}</p>
                            </div>
                        </div>
                        <div class="text-right shrink-0 ml-3">
                            <p class="text-xs font-semibold {{ $urgency }}">{{ $expiryDate?->format('d M Y') }}
                            </p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">
                                {{ $daysLeft !== null ? $daysLeft . ' hari lagi' : '—' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Tenant Terbaru</p>
                <a href="{{ route('super-admin.tenants.index') }}" class="text-xs text-blue-400 hover:underline">Lihat
                    semua →</a>
            </div>
            @if ($recentTenants->isEmpty())
                <p class="text-sm text-gray-400 dark:text-slate-500 py-4 text-center">Belum ada tenant terdaftar.</p>
            @else
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($recentTenants as $tenant)
                        @php
                            $subscriptionStatus = get_tenant_subscription_status($tenant);
                            $statusColor = match ($subscriptionStatus) {
                                'active' => 'bg-green-500/20 text-green-400',
                                'trial' => 'bg-amber-500/20 text-amber-400',
                                'trial_expired', 'expired' => 'bg-red-500/20 text-red-400',
                                default => 'bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400',
                            };
                            $statusLabel = match ($subscriptionStatus) {
                                'active' => 'Aktif',
                                'trial' => 'Trial',
                                'trial_expired' => 'Trial Expired',
                                'expired' => 'Expired',
                                default => 'Nonaktif',
                            };
                        @endphp
                        <div class="flex items-center justify-between py-2.5">
                            <div class="flex items-center gap-3 min-w-0">
                                <div
                                    class="w-8 h-8 rounded-xl bg-gradient-to-br from-slate-600 to-slate-800 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    {{ strtoupper(substr($tenant->name, 0, 1)) }}
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                        {{ $tenant->name }}</p>
                                    <p class="text-xs text-gray-400 dark:text-slate-500">
                                        {{ $tenant->created_at->format('d M Y') }}</p>
                                </div>
                            </div>
                            <span
                                class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 {{ $statusColor }}">{{ $statusLabel }}</span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="space-y-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Top AI Usage Bulan Ini</p>
                @if ($topAiTenants->isEmpty())
                    <p class="text-sm text-gray-400 dark:text-slate-500 py-2 text-center">Belum ada penggunaan AI bulan
                        ini.</p>
                @else
                    <div class="space-y-2">
                        @foreach ($topAiTenants as $log)
                            <div class="flex items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center justify-between mb-1">
                                        <span
                                            class="text-xs font-medium text-gray-700 dark:text-slate-300 truncate">{{ $log->tenant?->name ?? 'Tenant #' . $log->tenant_id }}</span>
                                        <span
                                            class="text-xs text-gray-400 dark:text-slate-500 shrink-0 ml-2">{{ number_format($log->total) }}
                                            pesan</span>
                                    </div>
                                    @php
                                        $maxAi = $log->tenant?->maxAiMessages() ?? 100;
                                        $pct = $maxAi > 0 ? min(100, round(($log->total / $maxAi) * 100)) : 0;
                                    @endphp
                                    <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-1.5">
                                        <div class="h-1.5 rounded-full {{ $pct >= 90 ? 'bg-red-500' : ($pct >= 70 ? 'bg-amber-500' : 'bg-blue-500') }}"
                                            style="width:{{ $pct }}%"></div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-sm font-semibold text-gray-900 dark:text-white mb-3">Aksi Cepat</p>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                    <a href="{{ route('super-admin.tenants.index') }}"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-medium text-gray-700 dark:text-slate-300">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        Kelola Tenant
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-gray-50 dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 transition text-sm font-medium text-gray-700 dark:text-slate-300">
                        <svg class="w-4 h-4 text-gray-500 dark:text-slate-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        Kelola Paket
                    </a>
                    <a href="{{ route('super-admin.tenants.index') }}?status=expired"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 transition text-sm font-medium text-red-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tenant Expired
                        @if ($expiredTenants > 0)
                            <span
                                class="ml-auto text-xs bg-red-500/30 text-red-300 px-1.5 py-0.5 rounded-full font-semibold">{{ $expiredTenants }}</span>
                        @endif
                    </a>
                    <a href="{{ route('super-admin.tenants.index') }}?plan=trial"
                        class="flex items-center gap-2 px-3 py-2.5 rounded-xl bg-amber-500/10 hover:bg-amber-500/20 transition text-sm font-medium text-amber-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        Tenant Trial
                        <span
                            class="ml-auto text-xs bg-amber-500/30 text-amber-300 px-1.5 py-0.5 rounded-full font-semibold">{{ $trialTenants }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            // Adaptive chart colors based on current theme
            const isDark = document.getElementById('html-root')?.classList.contains('dark');
            const tickFont = {
                size: 10,
                family: 'Inter'
            };
            const tickColor = isDark ? '#94a3b8' : '#6b7280';
            const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';

            @php
                $planChartData = array_values($planDist);
                $planChartLabels = array_map(fn($k) => $planLabels[$k] ?? ucfirst($k), array_keys($planDist));
                $planChartColors = array_map(fn($k) => $planColors[$k] ?? '#94a3b8', array_keys($planDist));
            @endphp

            requestAnimationFrame(() => setTimeout(() => {

                new Chart(document.getElementById('growthChart'), {
                    type: 'bar',
                    data: {
                        labels: {!! json_encode(array_column($growthChart, 'month')) !!},
                        datasets: [{
                            label: 'Tenant Baru',
                            data: {!! json_encode(array_column($growthChart, 'count')) !!},
                            backgroundColor: 'rgba(99,102,241,0.2)',
                            borderColor: '#6366f1',
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            }
                        },
                        scales: {
                            y: {
                                ticks: {
                                    stepSize: 1,
                                    font: tickFont,
                                    color: tickColor
                                },
                                grid: {
                                    color: gridColor
                                }
                            },
                            x: {
                                ticks: {
                                    font: tickFont,
                                    color: tickColor
                                },
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });

                @if (!empty($planDist))
                    new Chart(document.getElementById('planChart'), {
                        type: 'doughnut',
                        data: {
                            labels: {!! json_encode($planChartLabels) !!},
                            datasets: [{
                                data: {!! json_encode($planChartData) !!},
                                backgroundColor: {!! json_encode($planChartColors) !!},
                                borderWidth: 0,
                                hoverOffset: 4
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    display: false
                                },
                                tooltip: {
                                    callbacks: {
                                        label: ctx => ' ' + ctx.label + ': ' + ctx.raw + ' tenant'
                                    }
                                }
                            },
                            cutout: '70%'
                        }
                    });
                @endif

            }, 50));
        </script>
    @endpush
</x-app-layout>
