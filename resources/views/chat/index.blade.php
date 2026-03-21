<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chat — Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .nav-tooltip { pointer-events:none; white-space:nowrap; }
        .nav-icon-link:hover .nav-tooltip { opacity:1; transform:translateX(0); }
        .nav-tooltip { opacity:0; transform:translateX(-4px); transition:all .15s ease; }
        /* Pastikan tooltip tidak trigger horizontal scroll */
        #sidebar { overflow-x: hidden !important; }
        #sidebar nav { overflow-x: hidden !important; }
    </style>
</head>
<body class="h-full bg-[#f5f6fa] font-[Inter,sans-serif] antialiased overflow-hidden">


{{-- ═══════════════════════════════════════════════════════════
     ICON-ONLY APP SIDEBAR (w-16 = 64px, dengan tooltip hover)
═══════════════════════════════════════════════════════════ --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-16 bg-[#0f172a] flex flex-col overflow-hidden
           transition-transform duration-300 lg:translate-x-0 -translate-x-full">

    {{-- Logo --}}
    <div class="flex items-center justify-center h-16 border-b border-white/10 shrink-0">
        <a href="{{ route('dashboard') }}">
            <img src="/logo.png" alt="Q" class="h-7 w-auto object-contain brightness-0 invert">
        </a>
    </div>

    {{-- Nav icons --}}
    <nav class="flex-1 overflow-y-auto overflow-x-hidden py-3 flex flex-col items-center gap-0.5 scrollbar-dark">
        @php
        $ic = fn(bool $active) => 'nav-icon-link relative flex items-center justify-center w-10 h-10 rounded-xl transition-all '
            . ($active ? 'bg-white/15 text-white' : 'text-slate-400 hover:text-white hover:bg-white/10');

        $navItems = [];
        if(auth()->user()?->isSuperAdmin()) {
            $navItems = [
                ['route'=>'dashboard',              'label'=>'Dashboard',       'active'=>request()->routeIs('dashboard'),              'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route'=>'super-admin.tenants.index','label'=>'Semua Tenant',  'active'=>request()->routeIs('super-admin.tenants*'),   'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route'=>'super-admin.plans.index', 'label'=>'Kelola Paket',   'active'=>request()->routeIs('super-admin.plans*'),     'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
            ];
        } else {
            $navItems = [
                ['route'=>'dashboard',          'label'=>'Dashboard',       'active'=>request()->routeIs('dashboard'),       'icon'=>'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6'],
                ['route'=>'chat.index',         'label'=>'AI Chat',         'active'=>request()->routeIs('chat*'),           'icon'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z'],
                ['route'=>'notifications.index','label'=>'Notifikasi',      'active'=>request()->routeIs('notifications*'),  'icon'=>'M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9'],
                ['route'=>'inventory.index',    'label'=>'Inventori',       'active'=>request()->routeIs('inventory*'),      'icon'=>'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
                ['route'=>'pos.index',          'label'=>'Kasir (POS)',     'active'=>request()->routeIs('pos*'),            'icon'=>'M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z'],
                ['route'=>'hrm.index',          'label'=>'SDM & Karyawan',  'active'=>request()->routeIs('hrm*'),            'icon'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z'],
                ['route'=>'payroll.index',      'label'=>'Penggajian',      'active'=>request()->routeIs('payroll*'),        'icon'=>'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                ['route'=>'assets.index',       'label'=>'Aset',            'active'=>request()->routeIs('assets*'),         'icon'=>'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                ['route'=>'crm.index',          'label'=>'CRM & Pipeline',  'active'=>request()->routeIs('crm*'),            'icon'=>'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
                ['route'=>'purchasing.suppliers','label'=>'Pembelian',       'active'=>request()->routeIs('purchasing*'),     'icon'=>'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10'],
                ['route'=>'invoices.index',     'label'=>'Invoice',         'active'=>request()->routeIs('invoices*'),       'icon'=>'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route'=>'approvals.index',    'label'=>'Persetujuan',     'active'=>request()->routeIs('approvals*'),      'icon'=>'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['route'=>'shipping.index',     'label'=>'Pengiriman',      'active'=>request()->routeIs('shipping*'),       'icon'=>'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4'],
                ['route'=>'ecommerce.index',    'label'=>'E-Commerce',      'active'=>request()->routeIs('ecommerce*'),      'icon'=>'M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z'],
                ['route'=>'bank.reconciliation','label'=>'Rekonsiliasi Bank','active'=>request()->routeIs('bank*'),          'icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                ['route'=>'reports.index',      'label'=>'Laporan',         'active'=>request()->routeIs('reports*'),        'icon'=>'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
                ['route'=>'audit.index',        'label'=>'Audit Trail',     'active'=>request()->routeIs('audit*'),          'icon'=>'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01'],
                ['route'=>'subscription.index', 'label'=>'Langganan',       'active'=>request()->routeIs('subscription.index'),'icon'=>'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ];
        }
        @endphp

        @foreach($navItems as $item)
        <a href="{{ route($item['route']) }}" class="{{ $ic($item['active']) }}" title="{{ $item['label'] }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $item['icon'] }}"/>
            </svg>
            {{-- Tooltip --}}
            <span class="nav-tooltip absolute left-full ml-3 px-2.5 py-1.5 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                {{ $item['label'] }}
            </span>
        </a>
        @endforeach
    </nav>

    {{-- User avatar --}}
    <div class="py-3 border-t border-white/10 shrink-0 flex justify-center">
        <a href="{{ route('profile.edit') }}" title="{{ auth()->user()->name }}" class="nav-icon-link relative">
            <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}"
                class="w-9 h-9 rounded-full object-cover ring-2 ring-white/20 hover:ring-blue-400 transition">
            <span class="nav-tooltip absolute left-full ml-3 px-2.5 py-1.5 bg-gray-900 text-white text-xs rounded-lg shadow-lg z-50">
                {{ auth()->user()->name }}
            </span>
        </a>
    </div>
</aside>

{{-- App sidebar overlay (mobile) --}}
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ═══════════════════════════════════════════════════════════
     MAIN LAYOUT — offset 64px (icon sidebar)
═══════════════════════════════════════════════════════════ --}}
<div class="flex h-full lg:pl-16 overflow-hidden">

    {{-- Chat sidebar overlay mobile --}}
    <div id="chat-sidebar-overlay" class="fixed inset-0 z-[45] bg-black/40 hidden md:hidden" onclick="toggleChatSidebar()"></div>

    {{-- ── CHAT SIDEBAR (daftar percakapan) ───────────────── --}}
    <aside id="chat-sidebar"
        class="fixed top-0 right-0 bottom-0 z-[46] w-72
               md:static md:w-56 md:z-auto
               bg-white border-l md:border-l-0 md:border-r border-gray-100
               flex flex-col shrink-0
               translate-x-full md:translate-x-0
               transition-transform duration-300 ease-in-out
               shadow-2xl md:shadow-none">

        <div class="px-4 pt-4 pb-3 border-b border-gray-100 shrink-0 space-y-2.5">
            <div class="flex items-center justify-between">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Percakapan</p>
                <button onclick="toggleChatSidebar()" class="md:hidden w-7 h-7 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <button id="btn-new-chat" class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-gray-200 text-sm text-gray-500 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Percakapan Baru
            </button>
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-gray-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/></svg>
                <input id="session-search" type="text" placeholder="Cari percakapan..." class="w-full pl-8 pr-3 py-1.5 text-xs bg-gray-50 border border-gray-200 rounded-lg focus:outline-none focus:border-blue-400 focus:ring-1 focus:ring-blue-100 transition placeholder-gray-400">
            </div>
        </div>

        <div class="flex-1 overflow-y-auto px-2 py-2 space-y-0.5" id="session-list">
            @forelse($sessions as $s)
            <div class="session-item group flex items-center rounded-xl hover:bg-gray-50 transition cursor-pointer"
                 data-session="{{ $s->id }}" data-title="{{ $s->title ?? 'Percakapan baru' }}">
                <button class="flex-1 text-left px-3 py-2.5 text-sm text-gray-600 truncate session-btn leading-snug">{{ $s->title ?? 'Percakapan baru' }}</button>
                <div class="hidden group-hover:flex items-center gap-0.5 pr-1.5 shrink-0">
                    <button class="session-rename w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-blue-500 hover:bg-blue-50 transition" data-session="{{ $s->id }}" title="Ganti nama">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                    </button>
                    <button class="session-delete w-6 h-6 flex items-center justify-center rounded text-gray-300 hover:text-red-500 hover:bg-red-50 transition" data-session="{{ $s->id }}" title="Hapus">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            @empty
            <p class="text-xs text-gray-400 px-3 py-4 text-center" id="empty-sessions-msg">Belum ada percakapan</p>
            @endforelse
        </div>

        <div class="px-3 py-3 border-t border-gray-100 shrink-0">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 px-2 py-2 rounded-xl hover:bg-gray-50 transition group">
                <img src="{{ auth()->user()->avatarUrl() }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-gray-100">
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold text-gray-700 truncate group-hover:text-blue-600 transition">{{ auth()->user()->name }}</p>
                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                </div>
                <svg class="w-3.5 h-3.5 text-gray-300 group-hover:text-blue-400 transition shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </aside>

    {{-- ── MAIN CHAT AREA ───────────────────────────────────── --}}
    <main class="flex-1 flex flex-col min-w-0 overflow-hidden bg-[#f5f6fa]">

        {{-- Top bar --}}
        <div class="h-14 bg-white border-b border-gray-100 px-4 flex items-center justify-between shrink-0 gap-3">
            <div class="flex items-center gap-3 min-w-0">
                <button onclick="toggleSidebar()" class="lg:hidden shrink-0 w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-gray-800 truncate leading-tight" id="chat-title">Percakapan Baru</p>
                    <p class="text-xs text-gray-400 leading-tight" id="model-label">Qalcuity AI · Siap membantu</p>
                </div>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                @php
                    $tenant = auth()->user()->tenant;
                    $maxAi  = $tenant?->maxAiMessages() ?? 20;
                    $usedAi = $tenant ? \App\Models\AiUsageLog::tenantMonthlyCount($tenant->id) : 0;
                    $quotaPercent = $maxAi > 0 ? min(100, round($usedAi / $maxAi * 100)) : 0;
                @endphp
                @if($tenant && $maxAi !== -1)
                <div class="hidden sm:flex items-center gap-2">
                    <span class="text-xs text-gray-400 tabular-nums whitespace-nowrap">{{ $usedAi }}/{{ $maxAi }} pesan</span>
                    <div class="w-14 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $quotaPercent >= 90 ? 'bg-red-400' : ($quotaPercent >= 70 ? 'bg-amber-400' : 'bg-blue-400') }}" style="width:{{ $quotaPercent }}%"></div>
                    </div>
                </div>
                @endif
                <div id="typing-indicator" class="hidden items-center gap-1.5 text-xs text-blue-500 font-medium">
                    <span class="flex gap-0.5">
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.15s"></span>
                        <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.3s"></span>
                    </span>
                    <span class="hidden sm:inline">Memproses...</span>
                </div>
                {{-- Mobile: buka chat sidebar --}}
                <button onclick="toggleChatSidebar()" class="md:hidden w-8 h-8 flex items-center justify-center rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition" title="Daftar percakapan">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </button>
            </div>
        </div>

        {{-- Messages --}}
        <div id="chat-messages" class="flex-1 overflow-y-auto scrollbar-accent px-4 py-6">
            <div id="empty-state" class="flex flex-col items-center justify-center h-full text-center py-12">
                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mb-4 shadow-lg shadow-blue-200/60">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                </div>
                <h3 class="text-base font-semibold text-gray-700 mb-1">Halo, {{ explode(' ', auth()->user()->name)[0] }}!</h3>
                <p class="text-sm text-gray-400 max-w-xs leading-relaxed mb-5">Tanyakan apa saja tentang bisnis Anda — stok, penjualan, keuangan, atau SDM.</p>
                <div class="grid grid-cols-2 gap-2 w-full max-w-sm">
                    @foreach([
                        ['icon'=>'📊','text'=>'Grafik omzet 7 hari'],
                        ['icon'=>'💰','text'=>'KPI bisnis hari ini'],
                        ['icon'=>'📋','text'=>'Daftar semua produk'],
                        ['icon'=>'📈','text'=>'Laba rugi bulan ini'],
                        ['icon'=>'🏭','text'=>'Stok semua gudang'],
                        ['icon'=>'👥','text'=>'Absensi karyawan hari ini'],
                    ] as $hint)
                    <button class="hint-btn flex items-center gap-2 text-left text-xs bg-white border border-gray-200 rounded-xl px-3 py-2.5 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm text-gray-600 font-medium">
                        <span class="text-base shrink-0">{{ $hint['icon'] }}</span>
                        <span>{{ $hint['text'] }}</span>
                    </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Input --}}
        <div class="bg-gradient-to-t from-white via-white to-transparent px-4 pt-2 pb-4 shrink-0">
            <div class="max-w-3xl mx-auto">

                {{-- File preview strip --}}
                <div id="file-preview-strip" class="hidden flex gap-2 mb-2 flex-wrap px-1"></div>

                {{-- Input card --}}
                <div class="relative bg-white border border-gray-200 rounded-2xl shadow-lg shadow-gray-100/80 focus-within:border-blue-400 focus-within:shadow-blue-100/60 focus-within:shadow-xl transition-all duration-200">

                    {{-- Textarea --}}
                    <textarea id="chat-input" rows="1"
                        placeholder="Tanya apa saja..."
                        class="w-full resize-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none leading-relaxed px-4 pt-3.5 pb-12 max-h-40 overflow-y-auto"></textarea>

                    {{-- Bottom toolbar --}}
                    <div class="absolute bottom-0 left-0 right-0 flex items-center justify-between px-3 pb-2.5">

                        {{-- Left: attach + hint --}}
                        <div class="flex items-center gap-1.5">
                            <label for="file-input" title="Lampirkan file (Gambar, PDF, CSV, TXT)"
                                class="flex items-center gap-1.5 text-xs text-gray-400 hover:text-blue-500 cursor-pointer transition px-2 py-1.5 rounded-lg hover:bg-blue-50 group">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                                </svg>
                                <span class="hidden sm:inline group-hover:text-blue-500">Lampirkan</span>
                            </label>
                            <input type="file" id="file-input" class="hidden" multiple accept="image/*,.pdf,.txt,.csv,.xlsx,.docx">
                            <span class="hidden sm:inline text-xs text-gray-300">·</span>
                            <span class="hidden sm:inline text-xs text-gray-300">Shift+Enter baris baru</span>
                        </div>

                        {{-- Right: send button --}}
                        <button type="button" id="btn-send"
                            class="flex items-center gap-1.5 bg-blue-600 hover:bg-blue-700 active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed text-white text-xs font-medium px-3.5 py-2 rounded-xl transition-all duration-150 shadow-sm shadow-blue-200">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                            </svg>
                            <span>Kirim</span>
                        </button>
                    </div>
                </div>

                {{-- Disclaimer --}}
                <p class="text-[11px] text-gray-300 mt-2 text-center tracking-wide">
                    Qalcuity AI dapat membuat kesalahan. Verifikasi informasi penting.
                </p>
            </div>
        </div>

    </main>
</div>

<script>
function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebar-overlay');
    s.classList.toggle('-translate-x-full');
    o.classList.toggle('hidden');
}
function toggleChatSidebar() {
    const cs = document.getElementById('chat-sidebar');
    const co = document.getElementById('chat-sidebar-overlay');
    cs.classList.toggle('translate-x-full');
    co.classList.toggle('hidden');
}
</script>
@vite(['resources/js/chat.js'])
</body>
</html>
