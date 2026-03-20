<x-app-layout>
    <x-slot name="title">Langganan — Qalcuity ERP</x-slot>
    <x-slot name="header">Langganan</x-slot>

    @php
        $status     = $tenant?->subscriptionStatus() ?? 'trial';
        $planColors = [
            'trial'      => ['bg' => 'bg-amber-50',  'text' => 'text-amber-700',  'border' => 'border-amber-200',  'dot' => 'bg-amber-500'],
            'basic'      => ['bg' => 'bg-blue-50',   'text' => 'text-blue-700',   'border' => 'border-blue-200',   'dot' => 'bg-blue-500'],
            'pro'        => ['bg' => 'bg-purple-50', 'text' => 'text-purple-700', 'border' => 'border-purple-200', 'dot' => 'bg-purple-500'],
            'enterprise' => ['bg' => 'bg-indigo-50', 'text' => 'text-indigo-700', 'border' => 'border-indigo-200', 'dot' => 'bg-indigo-500'],
        ];
        $color = $planColors[$tenant?->plan ?? 'trial'];

        $statusLabels = [
            'trial'         => ['label' => 'Trial Aktif',         'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'active'        => ['label' => 'Aktif',               'color' => 'bg-green-50 text-green-700 border-green-200'],
            'trial_expired' => ['label' => 'Trial Berakhir',      'color' => 'bg-red-50 text-red-700 border-red-200'],
            'expired'       => ['label' => 'Langganan Berakhir',  'color' => 'bg-red-50 text-red-700 border-red-200'],
            'nonaktif'      => ['label' => 'Akun Nonaktif',       'color' => 'bg-gray-100 text-gray-600 border-gray-200'],
        ];
        $statusInfo = $statusLabels[$status] ?? $statusLabels['trial'];
    @endphp

    <div class="max-w-4xl space-y-5">

        {{-- Status Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between flex-wrap gap-4">
                <div>
                    <p class="text-xs font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-1">Paket Saat Ini</p>
                    <div class="flex items-center gap-3">
                        <h2 class="text-2xl font-bold text-gray-900 dark:text-white">{{ ucfirst($tenant?->plan ?? 'Trial') }}</h2>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium border {{ $statusInfo['color'] }}">
                            {{ $statusInfo['label'] }}
                        </span>
                    </div>
                    @if($tenant?->subscriptionPlan)
                        <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">{{ $tenant->subscriptionPlan->name }} —
                            Rp {{ number_format($tenant->subscriptionPlan->price_monthly, 0, ',', '.') }}/bulan
                        </p>
                    @endif
                </div>

                <div class="text-right">
                    @if($tenant?->plan === 'trial' && $tenant?->trial_ends_at)
                        <p class="text-xs text-gray-400 mb-0.5">Trial berakhir</p>
                        <p class="text-lg font-bold {{ $tenant->isTrialExpired() ? 'text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $tenant->trial_ends_at->format('d M Y') }}
                        </p>
                        @if(!$tenant->isTrialExpired())
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $tenant->trial_ends_at->diffForHumans() }}</p>
                        @endif
                    @elseif($tenant?->plan_expires_at)
                        <p class="text-xs text-gray-400 mb-0.5">Langganan berakhir</p>
                        <p class="text-lg font-bold {{ $tenant->isPlanExpired() ? 'text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $tenant->plan_expires_at->format('d M Y') }}
                        </p>
                        @if(!$tenant->isPlanExpired())
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">{{ $tenant->plan_expires_at->diffForHumans() }}</p>
                        @endif
                    @else
                        <p class="text-xs text-gray-500 dark:text-slate-400">Tidak ada tanggal kedaluwarsa</p>
                    @endif
                </div>
            </div>

            {{-- Limits --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 mt-6 pt-5 border-t border-gray-200 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Pengguna</p>
                    @php $maxUsers = $tenant?->maxUsers() ?? 3; @endphp
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $tenant?->users()->count() ?? 0 }}
                        <span class="font-normal text-gray-500 dark:text-slate-400">/ {{ $maxUsers === -1 ? '∞' : $maxUsers }}</span>
                    </p>
                    @if($maxUsers !== -1)
                    <div class="mt-1.5 h-1.5 bg-[#f8f8f8] dark:bg-white/10 rounded-full overflow-hidden">
                        @php $pct = $maxUsers > 0 ? min(100, round(($tenant?->users()->count() ?? 0) / $maxUsers * 100)) : 0; @endphp
                        <div class="h-full rounded-full {{ $pct >= 90 ? 'bg-red-400' : 'bg-blue-400' }}" style="width: {{ $pct }}%"></div>
                    </div>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Pesan AI / Bulan</p>
                    @php
                        $maxAi   = $tenant?->maxAiMessages() ?? 20;
                        $usedAi  = $tenant ? \App\Models\AiUsageLog::tenantMonthlyCount($tenant->id) : 0;
                    @endphp
                    <p class="text-sm font-semibold text-gray-900 dark:text-white">
                        {{ $usedAi }}
                        <span class="font-normal text-gray-500 dark:text-slate-400">/ {{ $maxAi === -1 ? '∞' : $maxAi }}</span>
                    </p>
                    @if($maxAi !== -1)
                    <div class="mt-1.5 h-1.5 bg-[#f8f8f8] dark:bg-white/10 rounded-full overflow-hidden">
                        @php $aiPct = $maxAi > 0 ? min(100, round($usedAi / $maxAi * 100)) : 0; @endphp
                        <div class="h-full rounded-full {{ $aiPct >= 90 ? 'bg-red-400' : ($aiPct >= 70 ? 'bg-amber-400' : 'bg-purple-400') }}" style="width: {{ $aiPct }}%"></div>
                    </div>
                    @endif
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Status Akun</p>
                    <p class="text-sm font-semibold {{ $tenant?->is_active ? 'text-green-400' : 'text-red-400' }}">
                        {{ $tenant?->is_active ? 'Aktif' : 'Nonaktif' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Upgrade CTA (tampil jika trial atau expired) --}}
        @if(in_array($status, ['trial', 'trial_expired', 'expired']))
        <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-6 text-gray-900 dark:text-white">
            <div class="flex items-center justify-between flex-wrap gap-4">
                <div>
                    <h3 class="font-bold text-lg">
                        {{ $status === 'trial' ? 'Upgrade sebelum trial berakhir' : 'Perpanjang langganan Anda' }}
                    </h3>
                    <p class="text-blue-100 text-sm mt-1">
                        Hubungi tim kami untuk upgrade ke paket berbayar dan nikmati semua fitur tanpa batas.
                    </p>
                </div>
                   <a href="https://wa.me/6281654932383?text=Halo,%20saya%20ingin%20upgrade%20paket%20untuk%20{{ urlencode($tenant?->name ?? 'tenant saya') }}"
                   target="_blank"
                   class="shrink-0 bg-white text-blue-700 font-semibold text-sm px-5 py-2.5 rounded-xl hover:bg-blue-50 transition">
                    Hubungi Kami
                </a>
            </div>
        </div>
        @endif

        {{-- Daftar Paket --}}
        @if($plans->isNotEmpty())
        <div>
            <h3 class="text-sm font-semibold text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-3">Pilihan Paket</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @foreach($plans as $plan)
                @php $isCurrent = $tenant?->subscription_plan_id == $plan->id; @endphp
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border shadow-sm p-5 flex flex-col relative
                    {{ $isCurrent ? 'border-blue-500 ring-2 ring-blue-500/20' : 'border-gray-200 dark:border-white/10' }}">
                    @if($isCurrent)
                        <span class="absolute -top-2.5 left-4 bg-blue-600 text-gray-900 dark:text-white text-xs font-semibold px-2.5 py-0.5 rounded-full">Paket Anda</span>
                    @endif
                    <h4 class="font-bold text-gray-900 dark:text-white text-base">{{ $plan->name }}</h4>
                    <p class="text-2xl font-bold text-gray-900 dark:text-white mt-2">
                        Rp {{ number_format($plan->price_monthly, 0, ',', '.') }}
                        <span class="text-sm font-normal text-gray-500 dark:text-slate-400">/bln</span>
                    </p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mb-4">Rp {{ number_format($plan->price_yearly, 0, ',', '.') }}/tahun</p>

                    <ul class="space-y-1.5 text-sm text-gray-600 dark:text-slate-300 flex-1 mb-5">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $plan->max_users === -1 ? 'User tak terbatas' : 'Maks. ' . $plan->max_users . ' user' }}
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $plan->max_ai_messages === -1 ? 'AI tak terbatas' : $plan->max_ai_messages . ' pesan AI/bln' }}
                        </li>
                        @foreach($plan->features ?? [] as $feature)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-green-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            {{ $feature }}
                        </li>
                        @endforeach
                    </ul>

                    <a href="mailto:support@qalcuity.com?subject=Upgrade ke {{ $plan->name }} - {{ $tenant?->name }}"
                       class="block text-center text-sm font-semibold py-2.5 rounded-xl transition
                           {{ $isCurrent
                               ? 'bg-blue-500/20 text-blue-400 cursor-default'
                               : 'bg-blue-600 text-gray-900 dark:text-white hover:bg-blue-500' }}">
                        {{ $isCurrent ? 'Paket Aktif' : 'Pilih Paket Ini' }}
                    </a>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Info kontak --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 flex items-center gap-4">
            <div class="w-10 h-10 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <div>
                <p class="text-sm font-semibold text-gray-900 dark:text-white">Butuh bantuan atau ingin upgrade?</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Hubungi kami di <a href="mailto:info@qalcuity.com" class="text-blue-400 hover:underline">info@qalcuity.com</a> — tim kami siap membantu.</p>
            </div>
        </div>

    </div>
</x-app-layout>
