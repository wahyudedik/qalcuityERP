<x-app-layout>
    <x-slot name="title">Notifikasi — Qalcuity ERP</x-slot>
    <x-slot name="header">Notifikasi</x-slot>
    <x-slot name="pageHeader">
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button type="submit"
                class="text-sm text-blue-400 hover:text-blue-300 font-medium px-3 py-2 rounded-xl hover:bg-gray-50 transition">
                Tandai semua dibaca
            </button>
        </form>
    </x-slot>

    @php
        $tabs = [
            'all' => ['label' => 'Semua', 'icon' => '🔔'],
            'inventory' => ['label' => 'Inventori', 'icon' => '📦'],
            'finance' => ['label' => 'Keuangan', 'icon' => '💰'],
            'hrm' => ['label' => 'HRM', 'icon' => '👥'],
            'sales' => ['label' => 'Penjualan', 'icon' => '🛒'],
            'ai' => ['label' => 'AI', 'icon' => '🤖'],
            'system' => ['label' => 'Sistem', 'icon' => '⚙️'],
        ];

        $moduleColors = [
            'inventory' => 'bg-orange-500/20 text-orange-400 border-orange-500/30',
            'finance' => 'bg-green-500/20 text-green-400 border-green-500/30',
            'hrm' => 'bg-purple-500/20 text-purple-400 border-purple-500/30',
            'sales' => 'bg-cyan-500/20 text-cyan-400 border-cyan-500/30',
            'ai' => 'bg-pink-500/20 text-pink-400 border-pink-500/30',
            'system' => 'bg-slate-500/20 text-slate-400 border-slate-500/30',
        ];

        $iconColors = [
            'inventory' => ['bg' => 'bg-orange-500/20', 'text' => 'text-orange-400'],
            'finance' => ['bg' => 'bg-green-500/20', 'text' => 'text-green-400'],
            'hrm' => ['bg' => 'bg-purple-500/20', 'text' => 'text-purple-400'],
            'sales' => ['bg' => 'bg-cyan-500/20', 'text' => 'text-cyan-400'],
            'ai' => ['bg' => 'bg-pink-500/20', 'text' => 'text-pink-400'],
            'system' => ['bg' => 'bg-slate-500/20', 'text' => 'text-slate-400'],
        ];

        $totalUnread = $moduleCounts->sum();
    @endphp

    <div class="max-w-2xl space-y-4">

        {{-- Module Tab Bar --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-1.5">
            <div class="flex flex-wrap gap-1">
                @foreach ($tabs as $key => $tab)
                    @php
                        $count = $key === 'all' ? $totalUnread : $moduleCounts[$key] ?? 0;
                        $isActive = $activeModule === $key;
                    @endphp
                    <a href="{{ route('notifications.index', $key !== 'all' ? ['module' => $key] : []) }}"
                        class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium transition-all
                               {{ $isActive
                                   ? 'bg-blue-600 text-white shadow-sm shadow-blue-500/30'
                                   : 'text-gray-500 hover:bg-gray-100 hover:text-gray-900' }}">
                        <span>{{ $tab['icon'] }}</span>
                        <span>{{ $tab['label'] }}</span>
                        @if ($count > 0)
                            <span
                                class="inline-flex items-center justify-center min-w-[18px] h-[18px] px-1 rounded-full text-xs font-bold
                                         {{ $isActive ? 'bg-white/20 text-white' : 'bg-red-500 text-white' }}">
                                {{ $count > 99 ? '99+' : $count }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Notifications List --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            @forelse($notifications as $notif)
                @php
                    $mod = $notif->module ?? 'system';
                    $iconBg = $iconColors[$mod]['bg'] ?? 'bg-blue-500/20';
                    $iconTxt = $iconColors[$mod]['text'] ?? 'text-blue-400';
                    $badgeCls = $moduleColors[$mod] ?? 'bg-slate-500/20 text-slate-400 border-slate-500/30';
                    $badgeLabel = $tabs[$mod]['label'] ?? ucfirst($mod);
                    $badgeIcon = $tabs[$mod]['icon'] ?? '🔔';
                @endphp
                <div x-data="{ expanded: false }"
                    class="border-b border-gray-100 last:border-0 hover:bg-gray-50 transition {{ $notif->isRead() ? 'opacity-50' : '' }}">
                    <div @click="expanded = !expanded" class="flex items-start gap-4 px-6 py-4 cursor-pointer">
                        <div class="w-9 h-9 rounded-xl shrink-0 flex items-center justify-center {{ $iconBg }}">
                            <svg class="w-4 h-4 {{ $iconTxt }}" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between gap-2 flex-wrap">
                                <div class="flex items-center gap-2 flex-wrap min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $notif->title }}</p>
                                    @if ($activeModule === 'all')
                                        <span
                                            class="inline-flex items-center gap-1 px-1.5 py-0.5 rounded-md text-xs font-medium border {{ $badgeCls }} shrink-0">
                                            {{ $badgeIcon }} {{ $badgeLabel }}
                                        </span>
                                    @endif
                                </div>
                                @if (!$notif->isRead())
                                    <form method="POST" action="{{ route('notifications.read', $notif) }}"
                                        class="shrink-0" @click.stop>
                                        @csrf
                                        <button type="submit"
                                            class="text-xs text-blue-400 hover:underline whitespace-nowrap">Tandai
                                            dibaca</button>
                                    </form>
                                @endif
                            </div>
                            <p class="text-sm text-gray-500 mt-0.5 leading-relaxed">{{ $notif->body }}
                            </p>
                            <p class="text-xs text-gray-400 mt-1.5">
                                {{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                        <div class="shrink-0">
                            <svg class="w-4 h-4 text-gray-400 transition-transform"
                                :class="expanded ? 'rotate-180' : ''" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    {{-- Detail notifikasi yang bisa di-expand --}}
                    <div x-show="expanded" x-collapse class="px-6 pb-4 pl-[4.5rem]">
                        <div class="bg-gray-50 rounded-lg p-4 space-y-3">
                            @if ($notif->data && is_array($notif->data))
                                @foreach ($notif->data as $key => $value)
                                    @if (!in_array($key, ['url', 'action']))
                                        <div class="flex gap-2">
                                            <span
                                                class="text-xs font-medium text-gray-600 min-w-[100px]">{{ ucfirst(str_replace('_', ' ', $key)) }}:</span>
                                            <span
                                                class="text-xs text-gray-900">{{ is_array($value) ? json_encode($value) : $value }}</span>
                                        </div>
                                    @endif
                                @endforeach
                            @endif

                            @if ($notif->data && isset($notif->data['url']))
                                <div class="pt-2 border-t border-gray-200">
                                    <a href="{{ $notif->data['url'] }}"
                                        class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-700 font-medium">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                        </svg>
                                        Lihat Detail
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="flex flex-col items-center py-16 text-gray-400">
                    <svg class="w-12 h-12 mb-3 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                    </svg>
                    <p class="text-sm">Tidak ada notifikasi</p>
                    @if ($activeModule !== 'all')
                        <a href="{{ route('notifications.index') }}"
                            class="mt-2 text-xs text-blue-400 hover:underline">Lihat semua notifikasi</a>
                    @endif
                </div>
            @endforelse
        </div>

        @if (method_exists($notifications, 'hasPages') && $notifications->hasPages())
            <div class="mt-4">{{ $notifications->links() }}</div>
        @endif
    </div>
</x-app-layout>
