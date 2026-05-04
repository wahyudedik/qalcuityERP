<x-app-layout>
    <x-slot name="title">Achievement & Gamification — Qalcuity ERP</x-slot>
    <x-slot name="header">Achievement & Gamification</x-slot>

    @php
        $categoryLabels = [
            'sales' => 'Penjualan',
            'finance' => 'Keuangan',
            'inventory' => 'Inventori',
            'hrm' => 'SDM',
            'production' => 'Produksi',
            'general' => 'Umum',
        ];
        $categoryColors = [
            'sales' => [
                'bg' => 'bg-blue-500/20',
                'text' => 'text-blue-400',
                'border' => 'border-blue-500/30',
                'badge' => 'bg-blue-600',
            ],
            'finance' => [
                'bg' => 'bg-emerald-500/20',
                'text' => 'text-emerald-400',
                'border' => 'border-emerald-500/30',
                'badge' => 'bg-emerald-600',
            ],
            'inventory' => [
                'bg' => 'bg-amber-500/20',
                'text' => 'text-amber-400',
                'border' => 'border-amber-500/30',
                'badge' => 'bg-amber-600',
            ],
            'hrm' => [
                'bg' => 'bg-pink-500/20',
                'text' => 'text-pink-400',
                'border' => 'border-pink-500/30',
                'badge' => 'bg-pink-600',
            ],
            'production' => [
                'bg' => 'bg-cyan-500/20',
                'text' => 'text-cyan-400',
                'border' => 'border-cyan-500/30',
                'badge' => 'bg-cyan-600',
            ],
            'general' => [
                'bg' => 'bg-purple-500/20',
                'text' => 'text-purple-400',
                'border' => 'border-purple-500/30',
                'badge' => 'bg-purple-600',
            ],
        ];
        $activeTab = request()->routeIs('gamification.achievements')
            ? 'achievements'
            : (request()->routeIs('gamification.leaderboard')
                ? 'leaderboard'
                : (request()->routeIs('gamification.points')
                    ? 'points'
                    : 'all'));
    @endphp

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Total Poin --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500">Total Poin</p>
                <div class="w-9 h-9 rounded-xl bg-yellow-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ number_format($stats['total_points']) }}</p>
            <p class="text-xs text-gray-400 mt-1">poin terkumpul</p>
        </div>

        {{-- Level --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500">Level</p>
                <div class="w-9 h-9 rounded-xl bg-indigo-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['level'] }}</p>
            <div class="mt-2">
                <div class="flex items-center justify-between mb-1">
                    <span
                        class="text-xs text-gray-400">{{ $stats['progress_to_next_level'] }}/{{ $stats['points_needed_for_next'] }}
                        poin</span>
                    <span class="text-xs font-medium text-indigo-400">{{ $stats['progress_percent'] }}%</span>
                </div>
                <div class="w-full bg-gray-200 rounded-full h-1.5">
                    <div class="bg-indigo-500 h-1.5 rounded-full transition-all duration-500"
                        style="width: {{ $stats['progress_percent'] }}%"></div>
                </div>
            </div>
        </div>

        {{-- Rank --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500">Peringkat</p>
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">#{{ $stats['rank'] }}</p>
            <p class="text-xs text-gray-400 mt-1">dari {{ $stats['total_users'] }} pengguna</p>
        </div>

        {{-- Achievement --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <div class="flex items-center justify-between mb-3">
                <p class="text-xs font-medium text-gray-500">Achievement</p>
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 flex items-center justify-center shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900">{{ $stats['earned_achievements'] }}<span
                    class="text-base font-normal text-gray-400">/{{ $stats['total_achievements'] }}</span>
            </p>
            <p class="text-xs text-gray-400 mt-1">achievement terbuka</p>
        </div>

    </div>

    {{-- Tab Navigation --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-1.5 mb-6">
        <div class="flex gap-1">
            <a href="{{ route('gamification.index') }}"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $activeTab === 'all' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                🏆 Semua
            </a>
            <a href="{{ route('gamification.achievements') }}"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $activeTab === 'achievements' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                🎖️ Achievement
            </a>
            <a href="{{ route('gamification.leaderboard') }}"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $activeTab === 'leaderboard' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                📊 Leaderboard
            </a>
            <a href="{{ route('gamification.points') }}"
                class="flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-medium transition-all
                      {{ $activeTab === 'points' ? 'bg-indigo-600 text-white shadow-sm shadow-indigo-500/30' : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                💰 Riwayat Poin
            </a>
        </div>
    </div>

    {{-- Recent Achievements --}}
    @if (!($showLeaderboardOnly ?? false) && $stats['recent_achievements']->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Achievement Terbaru</h3>
            <div class="flex gap-3 overflow-x-auto pb-1 scrollbar-hide">
                @foreach ($stats['recent_achievements'] as $ua)
                    <div
                        class="flex-shrink-0 flex flex-col items-center gap-2 p-3 bg-gray-50 rounded-xl border border-gray-200 w-28 text-center">
                        <div
                            class="w-12 h-12 rounded-full flex items-center justify-center text-2xl
                    @php
$cat = $ua->achievement?->category ?? 'general';
                        echo $categoryColors[$cat]['bg'] ?? 'bg-purple-500/20'; @endphp">
                            {{ $ua->achievement?->icon ?? '🏆' }}
                        </div>
                        <p class="text-xs font-medium text-gray-900 leading-tight line-clamp-2">
                            {{ $ua->achievement?->name }}</p>
                        <p class="text-[10px] text-gray-400">
                            {{ \Carbon\Carbon::parse($ua->earned_at)->format('d M Y') }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Leaderboard --}}
    @if (!($showAchievementsOnly ?? false))
        <div class="bg-white rounded-2xl border border-gray-200 p-5 mb-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-gray-900">Leaderboard</h3>
                @if ($activeTab !== 'leaderboard')
                    <a href="{{ route('gamification.leaderboard') }}"
                        class="text-xs text-indigo-400 hover:text-indigo-300 transition">Lihat semua →</a>
                @endif
            </div>

            @if ($leaderboard->isEmpty())
                <div class="text-center py-8">
                    <p class="text-sm text-gray-400">Belum ada data leaderboard</p>
                </div>
            @else
                <div class="space-y-2">
                    @foreach ($leaderboard as $i => $member)
                        @php
                            $rank = $i + 1;
                            $isCurrentUser = $member->id === $user->id;
                            $medalEmoji = match ($rank) {
                                1 => '🥇',
                                2 => '🥈',
                                3 => '🥉',
                                default => null,
                            };
                            $rowClass = $isCurrentUser
                                ? 'bg-indigo-50 border border-indigo-200 rounded-xl'
                                : 'hover:bg-gray-50 rounded-xl transition';
                        @endphp
                        <div class="flex items-center gap-3 px-3 py-2.5 {{ $rowClass }}">
                            {{-- Rank --}}
                            <div class="w-8 text-center shrink-0">
                                @if ($medalEmoji)
                                    <span class="text-lg">{{ $medalEmoji }}</span>
                                @else
                                    <span
                                        class="text-sm font-semibold text-gray-500">#{{ $rank }}</span>
                                @endif
                            </div>

                            {{-- Avatar + Name --}}
                            <div class="flex items-center gap-2.5 flex-1 min-w-0">
                                @if ($member->avatar)
                                    <img src="{{ $member->avatar }}" alt="{{ $member->name }}"
                                        class="w-8 h-8 rounded-full object-cover shrink-0">
                                @else
                                    <div
                                        class="w-8 h-8 rounded-full bg-indigo-500/20 flex items-center justify-center shrink-0">
                                        <span
                                            class="text-xs font-bold text-indigo-400">{{ strtoupper(substr($member->name, 0, 1)) }}</span>
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p
                                        class="text-sm font-medium text-gray-900 truncate {{ $isCurrentUser ? 'text-indigo-600' : '' }}">
                                        {{ $member->name }}
                                        @if ($isCurrentUser)
                                            <span class="text-xs text-indigo-400">(Kamu)</span>
                                        @endif
                                    </p>
                                    <p class="text-xs text-gray-400 capitalize">
                                        {{ $member->role }}
                                    </p>
                                </div>
                            </div>

                            {{-- Level --}}
                            <div class="shrink-0 text-center hidden sm:block">
                                <span
                                    class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-indigo-500/20 text-indigo-400">
                                    Lv. {{ $member->gamification_level ?? 1 }}
                                </span>
                            </div>

                            {{-- Points --}}
                            <div class="shrink-0 text-right">
                                <p class="text-sm font-bold text-gray-900">
                                    {{ number_format($member->gamification_points) }}</p>
                                <p class="text-xs text-gray-400">poin</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @endif

    {{-- All Achievements by Category --}}
    @if (!($showLeaderboardOnly ?? false))
        <div class="space-y-8">
            @foreach ($grouped as $category => $achievements)
                @php
                    $label = $categoryLabels[$category] ?? ucfirst($category);
                    $colors = $categoryColors[$category] ?? $categoryColors['general'];
                @endphp
                <div>
                    {{-- Category Header --}}
                    <div class="flex items-center gap-2 mb-4">
                        <span
                            class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
                            {{ $label }}
                        </span>
                        <div class="flex-1 h-px bg-gray-200"></div>
                        <span class="text-xs text-gray-400">
                            {{ $achievements->filter(fn($a) => in_array($a->id, $earnedIds ?? []))->count() }}/{{ $achievements->count() }}
                            tercapai
                        </span>
                    </div>

                    {{-- Achievement Grid --}}
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                        @foreach ($achievements as $achievement)
                            @php
                                $earned = in_array($achievement->id, $earnedIds ?? []);
                                $progress = $userAchievements[$achievement->id] ?? 0;
                                $target = $achievement->requirement_value ?? 1;
                                $pct = $target > 0 ? min(100, (int) round(($progress / $target) * 100)) : 0;
                            @endphp

                            <div
                                class="relative rounded-2xl border overflow-hidden transition-all duration-200
                    {{ $earned
                        ? $colors['bg'] . ' ' . $colors['border'] . ' shadow-sm'
                        : 'bg-white border-gray-200 opacity-70 hover:opacity-90' }}">

                                {{-- Lock overlay for unearned --}}
                                @if (!$earned)
                                    <div class="absolute top-2 right-2 z-10">
                                        <div
                                            class="w-6 h-6 rounded-lg bg-gray-500/20 flex items-center justify-center">
                                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none"
                                                stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                            </svg>
                                        </div>
                                    </div>
                                @endif

                                <div class="p-4">
                                    {{-- Icon --}}
                                    <div class="text-3xl mb-3 leading-none">{{ $achievement->icon ?? '🏆' }}</div>

                                    {{-- Name & Desc --}}
                                    <h4 class="text-sm font-semibold text-gray-900 mb-1">
                                        {{ $achievement->name }}</h4>
                                    <p class="text-xs text-gray-500 leading-relaxed mb-3">
                                        {{ $achievement->description }}</p>

                                    {{-- Points Badge --}}
                                    <div class="flex items-center justify-between">
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-semibold {{ $colors['bg'] }} {{ $colors['text'] }}">
                                            ⭐ {{ $achievement->points }} poin
                                        </span>

                                        @if ($earned)
                                            <span class="text-xs font-medium text-emerald-400 flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2.5" d="M5 13l4 4L19 7" />
                                                </svg>
                                                Tercapai!
                                            </span>
                                        @else
                                            {{-- Progress --}}
                                            <span
                                                class="text-xs text-gray-400">{{ $progress }}/{{ $target }}</span>
                                        @endif
                                    </div>

                                    {{-- Progress Bar (only for unearned) --}}
                                    @if (!$earned)
                                        <div class="mt-3">
                                            <div class="w-full bg-gray-200 rounded-full h-1.5">
                                                <div class="h-1.5 rounded-full transition-all duration-500 {{ $colors['badge'] }}"
                                                    style="width: {{ $pct }}%"></div>
                                            </div>
                                            <p class="text-[10px] text-gray-400 mt-1 text-right">
                                                {{ $pct }}%</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            @if ($grouped->isEmpty())
                <div
                    class="bg-white rounded-2xl border border-gray-200 p-12 text-center">
                    <div class="text-5xl mb-4">🎖️</div>
                    <p class="text-sm font-medium text-gray-900 mb-1">Belum ada achievement</p>
                    <p class="text-xs text-gray-400">Achievement akan muncul setelah dikonfigurasi
                        oleh admin.</p>
                </div>
            @endif
        </div>
    @endif

    {{-- Points History --}}
    @if (!empty($showPointsHistory))
        <div class="bg-white rounded-2xl border border-gray-200 p-5">
            <h3 class="text-sm font-semibold text-gray-900 mb-4">Riwayat Poin</h3>
            @if (isset($points) && $points->isNotEmpty())
                <div class="space-y-2">
                    @foreach ($points as $log)
                        <div
                            class="flex items-center justify-between px-3 py-2.5 rounded-xl hover:bg-gray-50 transition">
                            <div class="flex items-center gap-3">
                                <div
                                    class="w-8 h-8 rounded-full {{ $log->points > 0 ? 'bg-emerald-500/20' : 'bg-red-500/20' }} flex items-center justify-center shrink-0">
                                    <span class="text-sm">{{ $log->points > 0 ? '⬆' : '⬇' }}</span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-900">{{ $log->reason }}</p>
                                    <p class="text-xs text-gray-400">
                                        {{ $log->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                            <span
                                class="text-sm font-bold {{ $log->points > 0 ? 'text-emerald-400' : 'text-red-400' }} shrink-0">
                                {{ $log->points > 0 ? '+' : '' }}{{ number_format($log->points) }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10">
                    <div class="text-4xl mb-3">💰</div>
                    <p class="text-sm text-gray-400">Belum ada riwayat poin.</p>
                </div>
            @endif
        </div>
    @endif

</x-app-layout>
