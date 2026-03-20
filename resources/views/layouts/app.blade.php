<!DOCTYPE html>
<html lang="id" class="h-full dark" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'Qalcuity ERP') }}</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.png">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
    <script>
        // Apply theme before render to avoid flash
        if (localStorage.getItem('theme') === 'light') {
            document.getElementById('html-root')?.classList.remove('dark');
        }
    </script>
</head>
<body class="h-full font-[Inter,sans-serif] antialiased transition-colors duration-200
             bg-[#f8f8f8] dark:bg-[#0f172a] text-gray-900 dark:text-gray-100">
<div class="flex h-full">

    {{-- ── Sidebar ──────────────────────────────────────────── --}}
    <aside id="sidebar"
        class="fixed inset-y-0 left-0 z-40 w-60 flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full
               bg-[#f0f0f0] dark:bg-[#0f172a] border-r border-gray-200 dark:border-white/10">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-200 dark:border-white/10 shrink-0 bg-[#f0f0f0] dark:bg-[#0f172a]">
            <img src="/logo.png" alt="Qalcuity" class="h-8 w-auto object-contain brightness-0 dark:brightness-100">
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto scrollbar-dark px-3 py-4 space-y-0.5">

            @php
            $navLinkClass = fn(bool $active) => 'flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all '
                . ($active
                    ? 'bg-blue-100 text-blue-700 dark:bg-white/10 dark:text-white'
                    : 'text-gray-600 hover:text-gray-900 hover:bg-[#e4e4e4] dark:text-slate-400 dark:hover:text-white dark:hover:bg-white/5');
            $sectionLabel = 'text-xs font-semibold uppercase tracking-wider text-gray-400 dark:text-slate-500';
            @endphp

            @if(auth()->user()?->isSuperAdmin())
            {{-- ── SUPER ADMIN NAV ── --}}
            <a href="{{ route('dashboard') }}" class="{{ $navLinkClass(request()->routeIs('dashboard')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Manajemen</p></div>
            <a href="{{ route('super-admin.tenants.index') }}" class="{{ $navLinkClass(request()->routeIs('super-admin.tenants*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Semua Tenant
            </a>
            <a href="{{ route('super-admin.plans.index') }}" class="{{ $navLinkClass(request()->routeIs('super-admin.plans*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Kelola Paket
            </a>

            @else
            {{-- ── TENANT USER NAV ── --}}
            @php
                $nav = [
                    ['route' => 'dashboard',          'label' => 'Dashboard',  'icon' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                    ['route' => 'chat.index',          'label' => 'AI Chat',   'icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                    ['route' => 'reports.index',       'label' => 'Laporan',   'icon' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                    ['route' => 'notifications.index', 'label' => 'Notifikasi','icon' => 'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                ];
            @endphp
            @foreach($nav as $item)
            <a href="{{ route($item['route']) }}" class="{{ $navLinkClass(request()->routeIs(explode('.', $item['route'])[0] . '*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/></svg>
                {{ $item['label'] }}
            </a>
            @endforeach

            @if(auth()->user()?->isAdmin())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Pengaturan</p></div>
            <a href="{{ route('tenant.users.index') }}" class="{{ $navLinkClass(request()->routeIs('tenant.users*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Kelola Pengguna
            </a>
            @endif

            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Operasional</p></div>
            <a href="{{ route('pos.index') }}" class="{{ $navLinkClass(request()->routeIs('pos*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Kasir (POS)
            </a>
            <a href="{{ route('approvals.index') }}" class="{{ $navLinkClass(request()->routeIs('approvals*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Persetujuan
                @php $pendingCount = auth()->user()?->tenant_id ? \App\Models\ApprovalRequest::where('tenant_id', auth()->user()->tenant_id)->where('status','pending')->count() : 0; @endphp
                @if($pendingCount > 0)<span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">{{ $pendingCount }}</span>@endif
            </a>
            <a href="{{ route('shipping.index') }}" class="{{ $navLinkClass(request()->routeIs('shipping*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Pengiriman
            </a>
            <a href="{{ route('ecommerce.index') }}" class="{{ $navLinkClass(request()->routeIs('ecommerce*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                E-Commerce
            </a>

            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Keuangan</p></div>
            <a href="{{ route('bank.reconciliation') }}" class="{{ $navLinkClass(request()->routeIs('bank*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Rekonsiliasi Bank
            </a>

            @if(auth()->user()?->isAdmin())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Sistem</p></div>
            <a href="{{ route('audit.index') }}" class="{{ $navLinkClass(request()->routeIs('audit*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Audit Trail
            </a>
            <a href="{{ route('bot.settings') }}" class="{{ $navLinkClass(request()->routeIs('bot*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Bot WA/Telegram
            </a>
            @endif

            @if(auth()->user()?->tenant_id)
            <a href="{{ route('subscription.index') }}" class="{{ $navLinkClass(request()->routeIs('subscription.index')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Langganan
                @php $tenant = auth()->user()->tenant; @endphp
                @if($tenant && $tenant->plan === 'trial')<span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">Trial</span>
                @elseif($tenant && $tenant->isPlanExpired())<span class="ml-auto text-xs bg-red-500/20 text-red-300 px-1.5 py-0.5 rounded-md font-medium">Expired</span>@endif
            </a>
            @endif
            @endif {{-- end isSuperAdmin else --}}
        </nav>

        {{-- User Footer --}}
        <div class="px-3 py-3 border-t border-gray-200 dark:border-white/10 shrink-0 bg-[#f0f0f0] dark:bg-[#0f172a]">
            <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-[#e4e4e4] dark:hover:bg-white/5 transition group">
                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                    {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400 capitalize">{{ auth()->user()?->role }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-500 hover:text-red-400 transition opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- Sidebar overlay (mobile) --}}
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

    {{-- ── Main ─────────────────────────────────────────────── --}}
    <div class="flex-1 flex flex-col min-w-0 lg:pl-60">

        {{-- Topbar --}}
        <header class="sticky top-0 z-20 h-16 backdrop-blur border-b flex items-center px-4 sm:px-6 gap-4
                       bg-[#f0f0f0]/95 dark:bg-[#0f172a]/95 border-gray-200 dark:border-white/10">
            <button onclick="toggleSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            <div class="flex-1">
                @isset($header)
                    <h1 class="text-base font-semibold text-gray-900 dark:text-white">{{ $header }}</h1>
                @endisset
            </div>

            <div class="flex items-center gap-2">
                @isset($topbarActions){{ $topbarActions }}@endisset

                {{-- Dark/Light Toggle --}}
                <button id="theme-toggle" title="Ganti tema"
                    class="p-2 rounded-xl transition hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-slate-400">
                    {{-- Sun icon (shown in dark mode) --}}
                    <svg id="icon-sun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    {{-- Moon icon (shown in light mode) --}}
                    <svg id="icon-moon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                {{-- Notification bell --}}
                @php
                    $notifTenantId = auth()->user()?->tenant_id;
                    $unreadCount = $notifTenantId ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)->whereNull('read_at')->count() : 0;
                @endphp
                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                    <button @click="open = !open" class="relative p-2 rounded-xl hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        @if($unreadCount > 0)<span class="absolute top-1.5 right-1.5 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>@endif
                    </button>
                    <div x-show="open" x-transition
                        class="absolute right-0 mt-2 w-80 rounded-2xl shadow-xl border overflow-hidden z-50
                               bg-white dark:bg-[#1e293b] border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-white/10">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white">Notifikasi</span>
                            <a href="{{ route('notifications.index') }}" class="text-xs text-blue-500 dark:text-blue-400 hover:underline">Lihat semua</a>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                            @forelse(\App\Models\ErpNotification::when(auth()->user()?->tenant_id, fn($q, $tid) => $q->where('tenant_id', $tid))->latest()->take(5)->get() as $notif)
                            <div class="px-4 py-3 hover:bg-[#f0f0f0] dark:hover:bg-white/5 {{ $notif->isRead() ? 'opacity-50' : '' }}">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $notif->title }}</p>
                                <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($notif->body, 80) }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                            </div>
                            @empty
                            <div class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi</div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </header>

        {{-- Page Content --}}
        <main class="flex-1 p-4 sm:p-6 bg-[#f8f8f8] dark:bg-[#0f172a]">
            @if(session('success'))
            <div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
            <div class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                {{ session('error') }}
            </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</div>

<script>
function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebar-overlay');
    s.classList.toggle('-translate-x-full');
    o.classList.toggle('hidden');
}

// Theme toggle
document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const html = document.getElementById('html-root');
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});

// Register Service Worker (PWA)
if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js').catch(() => {});
    });
}
</script>
@stack('scripts')
</body>
</html>
