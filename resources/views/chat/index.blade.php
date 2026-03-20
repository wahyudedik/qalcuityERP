<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Chat — Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet"/>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#f5f6fa] font-[Inter,sans-serif] antialiased overflow-hidden">

{{-- ── App Sidebar (sama dengan app.blade.php) ──────────────── --}}
<aside id="sidebar"
    class="fixed inset-y-0 left-0 z-40 w-60 bg-[#0f172a] flex flex-col transition-transform duration-300 lg:translate-x-0 -translate-x-full shrink-0">

    {{-- Logo --}}
    <div class="flex items-center gap-3 px-5 h-16 border-b border-white/10 shrink-0">
        <img src="/logo.png" alt="Qalcuity" class="h-8 w-auto object-contain">
    </div>

    {{-- Nav --}}
    <nav class="flex-1 overflow-y-auto scrollbar-dark px-3 py-4 space-y-0.5">

        @if(auth()->user()?->isSuperAdmin())
        <a href="{{ route('dashboard') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('dashboard') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            Dashboard
        </a>
        <div class="pt-4 pb-1 px-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Manajemen</p>
        </div>
        <a href="{{ route('super-admin.tenants.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('super-admin.tenants*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
            </svg>
            Semua Tenant
        </a>
        <a href="{{ route('super-admin.plans.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('super-admin.plans*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Kelola Paket
        </a>

        @else
        {{-- Main nav --}}
        @php
            $mainNav = [
                ['route' => 'dashboard',          'label' => 'Dashboard',  'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>'],
                ['route' => 'chat.index',          'label' => 'AI Chat',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>'],
                ['route' => 'reports.index',       'label' => 'Laporan',   'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>'],
                ['route' => 'notifications.index', 'label' => 'Notifikasi','icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>'],
            ];
        @endphp
        @foreach($mainNav as $item)
        <a href="{{ route($item['route']) }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs(explode('.', $item['route'])[0] . '*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">{!! $item['icon'] !!}</svg>
            {{ $item['label'] }}
        </a>
        @endforeach

        {{-- Admin: Kelola User --}}
        @if(auth()->user()?->isAdmin())
        <div class="pt-4 pb-1 px-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pengaturan</p>
        </div>
        <a href="{{ route('tenant.users.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('tenant.users*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Kelola Pengguna
        </a>
        @endif

        {{-- Operasional --}}
        <div class="pt-4 pb-1 px-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Operasional</p>
        </div>
        <a href="{{ route('pos.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('pos*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            Kasir (POS)
        </a>
        <a href="{{ route('approvals.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('approvals*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Persetujuan
            @php $pendingCount = auth()->user()?->tenant_id ? \App\Models\ApprovalRequest::where('tenant_id', auth()->user()->tenant_id)->where('status','pending')->count() : 0; @endphp
            @if($pendingCount > 0)
                <span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">{{ $pendingCount }}</span>
            @endif
        </a>
        <a href="{{ route('shipping.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('shipping*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
            </svg>
            Pengiriman
        </a>
        <a href="{{ route('ecommerce.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('ecommerce*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            E-Commerce
        </a>

        {{-- Keuangan --}}
        <div class="pt-4 pb-1 px-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Keuangan</p>
        </div>
        <a href="{{ route('bank.reconciliation') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('bank*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Rekonsiliasi Bank
        </a>

        {{-- Admin: Sistem --}}
        @if(auth()->user()?->isAdmin())
        <div class="pt-4 pb-1 px-3">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Sistem</p>
        </div>
        <a href="{{ route('audit.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('audit*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
            </svg>
            Audit Trail
        </a>
        <a href="{{ route('bot.settings') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('bot*') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Bot WA/Telegram
        </a>
        @endif

        {{-- Langganan --}}
        @if(auth()->user()?->tenant_id)
        <a href="{{ route('subscription.index') }}"
           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all
                  {{ request()->routeIs('subscription.index') ? 'bg-white/10 text-white' : 'text-slate-400 hover:text-white hover:bg-white/5' }}">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
            </svg>
            Langganan
            @php $tenant = auth()->user()->tenant; @endphp
            @if($tenant && $tenant->plan === 'trial')
                <span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">Trial</span>
            @elseif($tenant && $tenant->isPlanExpired())
                <span class="ml-auto text-xs bg-red-500/20 text-red-300 px-1.5 py-0.5 rounded-md font-medium">Expired</span>
            @endif
        </a>
        @endif

        @endif {{-- end isSuperAdmin else --}}
    </nav>

    {{-- User Footer --}}
    <div class="px-3 py-3 border-t border-white/10 shrink-0">
        <div class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-white/5 transition group">
            <div class="w-8 h-8 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()?->name ?? 'U', 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-white truncate">{{ auth()->user()?->name }}</p>
                <p class="text-xs text-slate-400 capitalize">{{ auth()->user()?->role }}</p>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" title="Keluar"
                    class="text-slate-500 hover:text-red-400 transition opacity-0 group-hover:opacity-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                    </svg>
                </button>
            </form>
        </div>
    </div>
</aside>

{{-- Sidebar overlay (mobile) --}}
<div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="toggleSidebar()"></div>

{{-- ── Wrapper (offset sidebar) ─────────────────────────────── --}}
<div class="flex h-full lg:pl-60 overflow-hidden">

{{-- ── Chat Sidebar ─────────────────────────────────────────── --}}
<aside class="w-56 bg-white border-r border-gray-100 flex flex-col shrink-0 hidden md:flex">
    <div class="px-4 py-4 border-b border-gray-100">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider mb-3">Percakapan</p>
        <button id="btn-new-chat"
            class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-dashed border-gray-200 text-sm text-gray-400 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Baru
        </button>
    </div>

    <div class="flex-1 overflow-y-auto scrollbar-dark px-2 py-2 space-y-0.5" id="session-list">
        @forelse($sessions as $s)
        <div class="session-item group flex items-center rounded-xl hover:bg-gray-50 transition cursor-pointer"
             data-session="{{ $s->id }}" data-title="{{ $s->title ?? 'Percakapan baru' }}">
            <button class="flex-1 text-left px-3 py-2.5 text-sm text-gray-600 truncate session-btn">
                {{ $s->title ?? 'Percakapan baru' }}
            </button>
            <button class="session-delete hidden group-hover:flex items-center px-2 py-2 text-gray-300 hover:text-red-500 transition"
                    data-session="{{ $s->id }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        @empty
        <p class="text-xs text-gray-400 px-3 py-3">Belum ada percakapan</p>
        @endforelse
    </div>

    <div class="px-4 py-3 border-t border-gray-100">
        <div class="flex items-center gap-2">
            <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div class="min-w-0">
                <p class="text-xs font-medium text-gray-700 truncate">{{ auth()->user()->name }}</p>
                <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
            </div>
        </div>
    </div>
</aside>

{{-- ── Main Chat ────────────────────────────────────────────── --}}
<main class="flex-1 flex flex-col min-w-0 bg-[#f5f6fa] overflow-hidden">

    {{-- Header --}}
    <div class="h-14 bg-white border-b border-gray-100 px-5 flex items-center justify-between shrink-0">
        <div>
            <p class="text-sm font-semibold text-gray-800" id="chat-title">Percakapan Baru</p>
            <p class="text-xs text-gray-400" id="model-label">Siap membantu</p>
        </div>
        <div class="flex items-center gap-3">
            @php
                $tenant = auth()->user()->tenant;
                $maxAi = $tenant?->maxAiMessages() ?? 20;
                $usedAi = $tenant ? \App\Models\AiUsageLog::tenantMonthlyCount($tenant->id) : 0;
                $quotaPercent = $maxAi > 0 ? min(100, round($usedAi / $maxAi * 100)) : 0;
            @endphp
            @if($tenant && $maxAi !== -1)
            <div class="hidden sm:flex items-center gap-2 text-xs text-gray-400">
                <span>{{ $usedAi }}/{{ $maxAi }} pesan</span>
                <div class="w-16 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all {{ $quotaPercent >= 90 ? 'bg-red-400' : ($quotaPercent >= 70 ? 'bg-amber-400' : 'bg-blue-400') }}"
                         style="width: {{ $quotaPercent }}%"></div>
                </div>
            </div>
            @endif
            <div id="typing-indicator" class="hidden items-center gap-1.5 text-xs text-blue-500">
                <span class="flex gap-0.5">
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:0s"></span>
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.15s"></span>
                    <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce" style="animation-delay:.3s"></span>
                </span>
                Memproses...
            </div>
        </div>
    </div>

    {{-- Messages --}}
    <div id="chat-messages" class="flex-1 overflow-y-auto scrollbar-accent px-4 py-6">
        <div id="empty-state" class="flex flex-col items-center justify-center h-full text-center py-16">
            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center mb-4 shadow-lg shadow-blue-200">
                <svg class="w-8 h-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-gray-700 mb-1">Halo! Saya Qalcuity AI</h3>
            <p class="text-sm text-gray-400 max-w-xs leading-relaxed">Asisten ERP Anda. Tanyakan apa saja tentang stok, penjualan, keuangan, atau SDM.</p>
            <div class="mt-5 flex flex-wrap gap-2 justify-center max-w-lg">
                @foreach([
                    "📊 Grafik omzet 7 hari terakhir",
                    "📋 Tampilkan semua produk dalam tabel",
                    "💰 KPI ringkasan bisnis hari ini",
                    "📄 Buat surat penawaran ke pelanggan",
                    "🧾 Buat invoice untuk pesanan terakhir",
                    "📈 Laporan laba rugi bulan ini",
                    "🏭 Stok semua gudang",
                    "👥 Rekap absensi karyawan hari ini",
                ] as $hint)
                <button class="hint-btn text-xs bg-white border border-gray-200 rounded-full px-3 py-1.5 hover:border-blue-400 hover:text-blue-600 hover:bg-blue-50 transition shadow-sm">
                    {{ $hint }}
                </button>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Input --}}
    <div class="bg-white border-t border-gray-100 px-4 py-3 shrink-0">
        <div class="max-w-3xl mx-auto">
            {{-- File preview strip --}}
            <div id="file-preview-strip" class="hidden flex gap-2 mb-2 flex-wrap"></div>

            <div class="flex gap-3 items-end bg-gray-50 border border-gray-200 rounded-2xl px-4 py-3 focus-within:border-blue-400 focus-within:ring-2 focus-within:ring-blue-100 transition">
                {{-- Attach button --}}
                <label for="file-input" title="Lampirkan gambar/PDF/dokumen"
                    class="shrink-0 w-8 h-8 flex items-center justify-center text-gray-400 hover:text-blue-500 cursor-pointer transition rounded-lg hover:bg-blue-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"/>
                    </svg>
                </label>
                <input type="file" id="file-input" class="hidden" multiple
                    accept="image/*,.pdf,.txt,.csv,.xlsx,.docx">

                <textarea id="chat-input" rows="1"
                    placeholder="Ketik pesan atau lampirkan file... (Enter kirim, Shift+Enter baris baru)"
                    class="flex-1 resize-none bg-transparent text-sm text-gray-800 placeholder-gray-400 focus:outline-none max-h-32 overflow-y-auto leading-relaxed"
                ></textarea>
                <button type="button" id="btn-send"
                    class="shrink-0 w-9 h-9 bg-gradient-to-br from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 disabled:from-gray-200 disabled:to-gray-200 text-white rounded-xl flex items-center justify-center transition shadow-sm shadow-blue-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                    </svg>
                </button>
            </div>
            <p class="text-xs text-gray-400 mt-2 text-center">
                Mendukung gambar (JPG/PNG), PDF, TXT, CSV &bull; Maks 20MB per file &bull; Qalcuity AI dapat membuat kesalahan
            </p>
        </div>
    </div>
</main>

</div>{{-- end lg:pl-60 wrapper --}}

<script>
function toggleSidebar() {
    const s = document.getElementById('sidebar');
    const o = document.getElementById('sidebar-overlay');
    s.classList.toggle('-translate-x-full');
    o.classList.toggle('hidden');
}
</script>
@vite(['resources/js/chat.js'])
</body>
</html>
