<!DOCTYPE html>
<html lang="id" class="h-full dark" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? (View::hasSection('title') ? View::yieldContent('title') : config('app.name', 'Qalcuity ERP')) }}</title>
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
        class="fixed inset-y-0 left-0 z-40 w-60 flex flex-col overflow-hidden transition-transform duration-300 lg:translate-x-0 -translate-x-full
               bg-[#f0f0f0] dark:bg-[#0f172a] border-r border-gray-200 dark:border-white/10">

        {{-- Logo --}}
        <div class="flex items-center gap-3 px-5 h-16 border-b border-gray-200 dark:border-white/10 shrink-0 bg-[#f0f0f0] dark:bg-[#0f172a]">
            <img src="/logo.png" alt="Qalcuity" class="h-8 w-auto object-contain brightness-0 dark:brightness-100">
        </div>

        {{-- Nav --}}
        <nav class="flex-1 overflow-y-auto overflow-x-hidden scrollbar-dark px-3 py-4 space-y-0.5">

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
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Monitoring</p></div>
            <a href="{{ route('super-admin.monitoring.index') }}" class="{{ $navLinkClass(request()->routeIs('super-admin.monitoring*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Monitoring
                @php $openErrors = \App\Models\ErrorLog::where('is_resolved', false)->count(); @endphp
                @if($openErrors > 0)
                <span class="ml-auto text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded-md font-medium">{{ $openErrors }}</span>
                @endif
            </a>
            @else
            {{-- ── TENANT USER NAV ── --}}
            @php $navTenant = auth()->user()?->tenant; @endphp

            {{-- 1. Dashboard --}}
            <a href="{{ route('dashboard') }}" class="{{ $navLinkClass(request()->routeIs('dashboard')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                Dashboard
            </a>

            {{-- 2. AI Chat --}}
            <a href="{{ route('chat.index') }}" class="{{ $navLinkClass(request()->routeIs('chat*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                AI Chat
            </a>

            {{-- ── KASIR: hanya POS ── --}}
            @if(auth()->user()?->isKasir())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Kasir</p></div>
            <a href="{{ route('pos.index') }}" class="{{ $navLinkClass(request()->routeIs('pos*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Kasir (POS)
            </a>
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Saya</p></div>
            <a href="{{ route('self-service.attendance.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.attendance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Absensi Saya
            </a>
            <a href="{{ route('self-service.leave.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.leave*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cuti Saya
            </a>
            <a href="{{ route('payroll.slip.index') }}" class="{{ $navLinkClass(request()->routeIs('payroll.slip*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Slip Gaji
            </a>
            @endif

            {{-- ── GUDANG: hanya inventory ── --}}
            @if(auth()->user()?->isGudang())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Gudang</p></div>
            <a href="{{ route('inventory.index') }}" class="{{ $navLinkClass(request()->routeIs('inventory*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Inventori
            </a>
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Saya</p></div>
            <a href="{{ route('self-service.attendance.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.attendance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Absensi Saya
            </a>
            <a href="{{ route('self-service.leave.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.leave*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cuti Saya
            </a>
            <a href="{{ route('payroll.slip.index') }}" class="{{ $navLinkClass(request()->routeIs('payroll.slip*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Slip Gaji
            </a>
            @endif

            {{-- ── ADMIN / MANAGER / STAFF: menu lengkap ── --}}
            @if(!auth()->user()?->isKasir() && !auth()->user()?->isGudang())

            {{-- 3. Penjualan (admin/manager) --}}
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            @if($navTenant?->isModuleEnabled('invoicing') ?? true)
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Penjualan</p></div>
            <a href="{{ route('quotations.index') }}" class="{{ $navLinkClass(request()->routeIs('quotations*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Penawaran (Quotation)
            </a>
            <a href="{{ route('invoices.index') }}" class="{{ $navLinkClass(request()->routeIs('invoices*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Invoice
            </a>
            <a href="{{ route('delivery-orders.index') }}" class="{{ $navLinkClass(request()->routeIs('delivery-orders*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                Surat Jalan
            </a>
            <a href="{{ route('down-payments.index') }}" class="{{ $navLinkClass(request()->routeIs('down-payments*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Uang Muka (DP)
            </a>
            <a href="{{ route('sales-returns.index') }}" class="{{ $navLinkClass(request()->routeIs('sales-returns*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/></svg>
                Retur Penjualan
            </a>
            @endif {{-- invoicing --}}
            @if($navTenant?->isModuleEnabled('crm') ?? true)
            <a href="{{ route('crm.index') }}" class="{{ $navLinkClass(request()->routeIs('crm*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                CRM & Pipeline
            </a>
            @endif
            @if($navTenant?->isModuleEnabled('loyalty') ?? true)
            <a href="{{ route('loyalty.index') }}" class="{{ $navLinkClass(request()->routeIs('loyalty*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                Program Loyalitas
            </a>
            @endif
            @endif {{-- admin/manager --}}

            {{-- 4. Inventori & Pembelian --}}
            @if($navTenant?->isModuleEnabled('inventory') ?? true)
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Inventori & Pembelian</p></div>
            <a href="{{ route('inventory.index') }}" class="{{ $navLinkClass(request()->routeIs('inventory*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                Inventori
            </a>
            @endif
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            @if($navTenant?->isModuleEnabled('purchasing') ?? true)
            <a href="{{ route('purchasing.suppliers') }}" class="{{ $navLinkClass(request()->routeIs('purchasing.suppliers*') || request()->routeIs('purchasing.orders*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                Pembelian
            </a>
            <a href="{{ route('purchasing.requisitions') }}" class="{{ $navLinkClass(request()->routeIs('purchasing.requisitions*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                Purchase Requisition
            </a>
            <a href="{{ route('purchasing.rfq') }}" class="{{ $navLinkClass(request()->routeIs('purchasing.rfq*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                RFQ
            </a>
            <a href="{{ route('purchasing.goods-receipts') }}" class="{{ $navLinkClass(request()->routeIs('purchasing.goods-receipts*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Goods Receipt
            </a>
            <a href="{{ route('purchasing.matching') }}" class="{{ $navLinkClass(request()->routeIs('purchasing.matching*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                3-Way Matching
            </a>
            <a href="{{ route('purchase-returns.index') }}" class="{{ $navLinkClass(request()->routeIs('purchase-returns*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M21 10H11a8 8 0 00-8 8v2m18-10l-6-6m6 6l-6 6"/></svg>
                Retur Pembelian
            </a>
            @endif {{-- purchasing --}}
            @endif {{-- admin/manager --}}

            {{-- 5. Operasional --}}
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Operasional</p></div>
            @if($navTenant?->isModuleEnabled('pos') ?? true)
            <a href="{{ route('pos.index') }}" class="{{ $navLinkClass(request()->routeIs('pos*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                Kasir (POS)
            </a>
            @endif
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            @if($navTenant?->isModuleEnabled('production') ?? true)
            <a href="{{ route('production.index') }}" class="{{ $navLinkClass(request()->routeIs('production*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Produksi / WO
            </a>
            @endif
            <a href="{{ route('shipping.index') }}" class="{{ $navLinkClass(request()->routeIs('shipping*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                Pengiriman
            </a>
            <a href="{{ route('approvals.index') }}" class="{{ $navLinkClass(request()->routeIs('approvals*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Persetujuan
                @php $pendingCount = auth()->user()?->tenant_id ? \App\Models\ApprovalRequest::where('tenant_id', auth()->user()->tenant_id)->where('status','pending')->count() : 0; @endphp
                @if($pendingCount > 0)<span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">{{ $pendingCount }}</span>@endif
            </a>
            @if($navTenant?->isModuleEnabled('ecommerce') ?? true)
            <a href="{{ route('ecommerce.index') }}" class="{{ $navLinkClass(request()->routeIs('ecommerce*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                E-Commerce
            </a>
            @endif
            <a href="{{ route('documents.index') }}" class="{{ $navLinkClass(request()->routeIs('documents*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z"/></svg>
                Dokumen
            </a>
            @endif {{-- admin/manager --}}

            {{-- 6. SDM --}}
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            @if($navTenant?->isModuleEnabled('hrm') ?? true)
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">SDM</p></div>
            <a href="{{ route('hrm.recruitment.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.recruitment*') || request()->routeIs('hrm.onboarding*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                Rekrutmen
            </a>
            <a href="{{ route('hrm.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.index') || request()->routeIs('hrm.store') || request()->routeIs('hrm.update') || request()->routeIs('hrm.destroy') || request()->routeIs('hrm.attendance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                SDM & Karyawan
            </a>
            <a href="{{ route('hrm.leave') }}" class="{{ $navLinkClass(request()->routeIs('hrm.leave*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Manajemen Cuti
            </a>
            <a href="{{ route('hrm.performance') }}" class="{{ $navLinkClass(request()->routeIs('hrm.performance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                Penilaian Kinerja
            </a>
            <a href="{{ route('hrm.orgchart') }}" class="{{ $navLinkClass(request()->routeIs('hrm.orgchart')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                Struktur Organisasi
            </a>
            <a href="{{ route('hrm.shifts.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.shifts*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Jadwal Shift
            </a>
            <a href="{{ route('hrm.overtime.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.overtime*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Lembur
                @php $pendingOt = \App\Models\OvertimeRequest::where('tenant_id', auth()->user()->tenant_id ?? 0)->where('status','pending')->count(); @endphp
                @if($pendingOt > 0)
                <span class="ml-auto text-xs bg-amber-500/20 text-amber-300 px-1.5 py-0.5 rounded-md font-medium">{{ $pendingOt }}</span>
                @endif
            </a>
            <a href="{{ route('hrm.training.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.training*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l9-5-9-5-9 5 9 5z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"/></svg>
                Pelatihan & Sertifikasi
                @php $expCerts = \App\Models\EmployeeCertification::where('tenant_id', auth()->user()->tenant_id ?? 0)->where('status','active')->whereNotNull('expiry_date')->where('expiry_date','<=',now()->addDays(90))->count(); @endphp
                @if($expCerts > 0)
                <span class="ml-auto text-xs bg-red-500/20 text-red-400 px-1.5 py-0.5 rounded-md font-medium">{{ $expCerts }}</span>
                @endif
            </a>
            <a href="{{ route('hrm.disciplinary.index') }}" class="{{ $navLinkClass(request()->routeIs('hrm.disciplinary*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Surat Peringatan
                @php $activeSp = \App\Models\DisciplinaryLetter::where('tenant_id', auth()->user()->tenant_id ?? 0)->whereIn('status',['issued','acknowledged'])->count(); @endphp
                @if($activeSp > 0)
                <span class="ml-auto text-xs bg-orange-500/20 text-orange-400 px-1.5 py-0.5 rounded-md font-medium">{{ $activeSp }}</span>
                @endif
            </a>
            @endif {{-- hrm --}}
            @if($navTenant?->isModuleEnabled('payroll') ?? true)
            <a href="{{ route('payroll.index') }}" class="{{ $navLinkClass(request()->routeIs('payroll.index') || request()->routeIs('payroll.store') || request()->routeIs('payroll.show') || request()->routeIs('payroll.process') || request()->routeIs('payroll.run*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                Penggajian
            </a>
            @endif
            {{-- ESS Portal — semua karyawan --}}
            <a href="{{ route('self-service.dashboard') }}" class="{{ $navLinkClass(request()->routeIs('self-service.dashboard') || request()->routeIs('self-service.profile*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                Portal Karyawan
            </a>
            <a href="{{ route('payroll.slip.index') }}" class="{{ $navLinkClass(request()->routeIs('payroll.slip*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Slip Gaji
            </a>
            <a href="{{ route('self-service.attendance.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.attendance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Absensi Saya
            </a>
            <a href="{{ route('self-service.leave.index') }}" class="{{ $navLinkClass(request()->routeIs('self-service.leave*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Cuti Saya
            </a>
            @if($navTenant?->isModuleEnabled('projects') ?? true)
            <a href="{{ route('projects.index') }}" class="{{ $navLinkClass(request()->routeIs('projects*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Manajemen Proyek
            </a>
            <a href="{{ route('timesheets.index') }}" class="{{ $navLinkClass(request()->routeIs('timesheets*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Timesheet
            </a>
            @endif
            @endif {{-- admin/manager --}}

            {{-- 7. Keuangan & Akuntansi --}}
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Keuangan & Akuntansi</p></div>
            @if($navTenant?->isModuleEnabled('budget') ?? true)
            <a href="{{ route('budget.index') }}" class="{{ $navLinkClass(request()->routeIs('budget*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                Anggaran
            </a>
            @endif
            @if($navTenant?->isModuleEnabled('assets') ?? true)
            <a href="{{ route('assets.index') }}" class="{{ $navLinkClass(request()->routeIs('assets*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Aset
            </a>
            @endif
            @if($navTenant?->isModuleEnabled('invoicing') ?? true)
            <a href="{{ route('receivables.index') }}" class="{{ $navLinkClass(request()->routeIs('receivables*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                Piutang & Hutang
            </a>
            <a href="{{ route('bulk-payments.index') }}" class="{{ $navLinkClass(request()->routeIs('bulk-payments*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Bulk Payment
            </a>
            @endif
            @if($navTenant?->isModuleEnabled('bank_reconciliation') ?? true)
            <a href="{{ route('bank.reconciliation') }}" class="{{ $navLinkClass(request()->routeIs('bank*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                Rekonsiliasi Bank
            </a>
            @endif
            @if($navTenant?->isModuleEnabled('accounting') ?? true)
            <a href="{{ route('accounting.coa') }}" class="{{ $navLinkClass(request()->routeIs('accounting.coa*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 10h16M4 14h16M4 18h16"/></svg>
                Bagan Akun (COA)
            </a>
            <a href="{{ route('journals.index') }}" class="{{ $navLinkClass(request()->routeIs('journals*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Jurnal Umum
            </a>
            <a href="{{ route('accounting.periods') }}" class="{{ $navLinkClass(request()->routeIs('accounting.periods*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                Periode Akuntansi
            </a>
            @if(auth()->user()?->isAdmin())
            <a href="{{ route('accounting.period-lock.index') }}" class="{{ $navLinkClass(request()->routeIs('accounting.period-lock*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Kunci Periode & Backup
            </a>
            @endif
            <a href="{{ route('accounting.trial-balance') }}" class="{{ $navLinkClass(request()->routeIs('accounting.trial-balance*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"/></svg>
                Neraca Saldo
            </a>
            <a href="{{ route('accounting.balance-sheet') }}" class="{{ $navLinkClass(request()->routeIs('accounting.balance-sheet*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/></svg>
                Neraca (Balance Sheet)
            </a>
            <a href="{{ route('accounting.income-statement') }}" class="{{ $navLinkClass(request()->routeIs('accounting.income-statement*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                Laba Rugi (P&L)
            </a>
            <a href="{{ route('accounting.cash-flow') }}" class="{{ $navLinkClass(request()->routeIs('accounting.cash-flow*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                Arus Kas (Cash Flow)
            </a>
            @endif {{-- accounting --}}
            @endif {{-- admin/manager --}}

            {{-- 8. Analitik (admin/manager) --}}
            @if(auth()->user()?->isAdmin() || auth()->user()?->isManager())
            @if($navTenant?->isModuleEnabled('reports') ?? true)
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Analitik</p></div>
            <a href="{{ route('reports.index') }}" class="{{ $navLinkClass(request()->routeIs('reports.index')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan
            </a>
            <a href="{{ route('kpi.index') }}" class="{{ $navLinkClass(request()->routeIs('kpi*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                KPI Dashboard
            </a>
            <a href="{{ route('reports.cash-flow-projection') }}" class="{{ $navLinkClass(request()->routeIs('reports.cash-flow-projection*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                Proyeksi Arus Kas
            </a>
            @endif {{-- reports --}}
            @endif {{-- admin/manager --}}

            {{-- 9. Pengaturan (admin only, always at bottom) --}}
            @if(auth()->user()?->isAdmin())
            <div class="pt-4 pb-1 px-3"><p class="{{ $sectionLabel }}">Pengaturan</p></div>
            <a href="{{ route('company-profile.index') }}" class="{{ $navLinkClass(request()->routeIs('company-profile*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                Profil Perusahaan
            </a>
            <a href="{{ route('settings.modules.index') }}" class="{{ $navLinkClass(request()->routeIs('settings.modules*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 6h16M4 12h16M4 18h7"/></svg>
                Pengaturan Modul
            </a>
            <a href="{{ route('tenant.users.index') }}" class="{{ $navLinkClass(request()->routeIs('tenant.users*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                Kelola Pengguna
            </a>
            <a href="{{ route('reminders.index') }}" class="{{ $navLinkClass(request()->routeIs('reminders*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Pengingat
            </a>
            <a href="{{ route('import.index') }}" class="{{ $navLinkClass(request()->routeIs('import*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                Import CSV
            </a>
            <a href="{{ route('audit.index') }}" class="{{ $navLinkClass(request()->routeIs('audit*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                Audit Trail
            </a>
            <a href="{{ route('notifications.index') }}" class="{{ $navLinkClass(request()->routeIs('notifications*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                Notifikasi
            </a>
            <a href="{{ route('bot.settings') }}" class="{{ $navLinkClass(request()->routeIs('bot*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/></svg>
                Bot WA/Telegram
            </a>
            <a href="{{ route('api-settings.index') }}" class="{{ $navLinkClass(request()->routeIs('api-settings*')) }}">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/></svg>
                API & Webhook
            </a>
            @endif {{-- admin --}}

            @endif {{-- end !isKasir && !isGudang --}}

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
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-3 px-2 py-2 rounded-xl hover:bg-[#e4e4e4] dark:hover:bg-white/5 transition group">
                <img src="{{ auth()->user()?->avatarUrl() }}" alt="{{ auth()->user()?->name }}"
                    class="w-8 h-8 rounded-full object-cover shrink-0 ring-2 ring-white dark:ring-white/10">
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900 dark:text-white truncate">{{ auth()->user()?->name }}</p>
                    <p class="text-xs text-gray-500 dark:text-slate-400">{{ auth()->user()?->roleLabel() }}</p>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="text-slate-500 hover:text-red-400 transition opacity-0 group-hover:opacity-100">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    </button>
                </form>
            </a>
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
                @if(isset($header))
                    <h1 class="text-base font-semibold text-gray-900 dark:text-white">{{ $header }}</h1>
                @elseif(View::hasSection('header'))
                    <h1 class="text-base font-semibold text-gray-900 dark:text-white">@yield('header')</h1>
                @endif
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
                    $authUser      = auth()->user();
                    $notifTenantId = $authUser?->tenant_id;
                    $unreadCount   = $notifTenantId
                        ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)->whereNull('read_at')->count()
                        : ($authUser?->isSuperAdmin() ? \App\Models\ErpNotification::where('user_id', $authUser->id)->whereNull('read_at')->count() : 0);
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
                            @php
                                $topbarNotifs = $notifTenantId
                                    ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)->latest()->take(5)->get()
                                    : ($authUser?->isSuperAdmin() ? \App\Models\ErpNotification::where('user_id', $authUser->id)->latest()->take(5)->get() : collect());
                            @endphp
                            @forelse($topbarNotifs as $notif)
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
            {{ $slot ?? '' }}
            @hasSection('content')
                @yield('content')
            @endif
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
