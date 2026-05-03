<!DOCTYPE html>
<html lang="id" class="h-full" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>
        {{ $title ?? (View::hasSection('title') ? View::yieldContent('title') : config('app.name', 'Qalcuity ERP')) }}
    </title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#ffffff">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <link rel="apple-touch-icon" href="/favicon.png">
    @if (config('services.vapid.public_key'))
        <meta name="vapid-public-key" content="{{ config('services.vapid.public_key') }}">
    @endif
    <script>
        (function() {
            try {
                if (!localStorage.getItem('_theme_cleaned')) {
                    localStorage.removeItem('theme');
                    localStorage.setItem('_theme_cleaned', '1');
                }
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/offline-manager.js', 'resources/js/conflict-resolution.js', 'resources/js/topbar-offline-indicator.js'])
    @stack('head')
    <style>
        /* ═══════════════════════════════════════════════
           QALCUITY SIDEBAR — Orbital Design System
           Rail: 56px | Panel: 240px | Frosted glass
        ═══════════════════════════════════════════════ */

        /* Rail */
        #sidebar-rail {
            width: 56px;
            background: linear-gradient(180deg, #080f1e 0%, #0a1628 60%, #080f1e 100%);
            border-right: 1px solid rgba(255, 255, 255, 0.04);
        }

        /* Panel */
        #sidebar-panel {
            width: 240px;
            transform: translateX(-244px);
            transition: transform 0.26s cubic-bezier(.16, 1, .3, 1), opacity 0.2s;
            opacity: 0;
            pointer-events: none;
            background: #0a1226;
            border-right: 1px solid rgba(255, 255, 255, 0.06);
            box-shadow: 4px 0 32px rgba(0, 0, 0, 0.5);
        }

        #sidebar-panel.panel-open {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* Rail button */
        .rail-btn {
            position: relative;
            width: 40px;
            height: 40px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: none;
            background: transparent;
            transition: all 0.18s cubic-bezier(.4, 0, .2, 1);
            color: #475569;
        }

        .rail-btn:hover {
            color: #e2e8f0;
            transform: scale(1.08);
        }

        .rail-btn.rail-active {
            color: var(--group-color, #60a5fa);
            background: rgba(var(--group-rgb, 96, 165, 250), 0.12);
        }

        /* Glow dot indicator */
        .rail-btn::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 50%;
            transform: translateY(-50%);
            width: 3px;
            height: 0;
            border-radius: 0 3px 3px 0;
            background: var(--group-color, #60a5fa);
            transition: height 0.2s cubic-bezier(.4, 0, .2, 1);
            box-shadow: 0 0 8px var(--group-color, #60a5fa);
        }

        .rail-btn.rail-active::before {
            height: 20px;
        }

        /* Tooltip */
        .rail-btn .rail-tip {
            position: absolute;
            left: 52px;
            top: 50%;
            transform: translateY(-50%);
            background: #1e293b;
            color: #f1f5f9;
            font-size: 11px;
            font-weight: 600;
            padding: 5px 10px;
            border-radius: 8px;
            white-space: nowrap;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.15s, left 0.15s;
            border: 1px solid rgba(255, 255, 255, 0.08);
            z-index: 200;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.4);
        }

        .rail-btn:hover .rail-tip {
            opacity: 1;
            left: 56px;
        }

        /* Badge on rail icon */
        .rail-badge {
            position: absolute;
            top: 4px;
            right: 4px;
            min-width: 14px;
            height: 14px;
            border-radius: 7px;
            background: #ef4444;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 3px;
            border: 1.5px solid #080f1e;
            animation: pulse-badge 2s infinite;
        }

        @keyframes pulse-badge {

            0%,
            100% {
                box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4);
            }

            50% {
                box-shadow: 0 0 0 4px rgba(239, 68, 68, 0);
            }
        }

        /* Panel header accent line */
        #panel-accent {
            height: 2px;
            background: var(--group-color, #60a5fa);
            box-shadow: 0 0 12px var(--group-color, #60a5fa);
            transition: background 0.3s, box-shadow 0.3s;
        }

        /* Panel search */
        #panel-search {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 10px;
            color: #e2e8f0;
            font-size: 12px;
            padding: 7px 12px 7px 32px;
            width: 100%;
            outline: none;
            transition: border-color 0.15s, background 0.15s;
        }

        #panel-search:focus {
            border-color: rgba(var(--group-rgb, 96, 165, 250), 0.4);
            background: rgba(255, 255, 255, 0.07);
        }

        #panel-search::placeholder {
            color: #475569;
        }

        /* Panel nav link */
        .panel-link {
            display: flex;
            align-items: center;
            gap: 9px;
            padding: 8px 12px;
            border-radius: 9px;
            font-size: 13px;
            font-weight: 500;
            color: #64748b;
            transition: all 0.15s;
            cursor: pointer;
            text-decoration: none;
            position: relative;
            margin: 2px 0;
            line-height: 1.4;
        }

        .panel-icon {
            font-size: 15px;
            width: 22px;
            text-align: center;
            flex-shrink: 0;
            line-height: 1;
        }

        .panel-link:hover {
            background: rgba(255, 255, 255, 0.06);
            color: #cbd5e1;
            padding-left: 16px;
        }

        .panel-link.active {
            background: rgba(var(--group-rgb, 96, 165, 250), 0.12);
            color: var(--group-color, #60a5fa);
            font-weight: 600;
        }

        .panel-link.active::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 2px;
            height: 16px;
            border-radius: 0 2px 2px 0;
            background: var(--group-color, #60a5fa);
        }

        /* Panel section label */
        .panel-section {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #475569;
            padding: 14px 12px 6px;
            margin-top: 6px;
            border-top: 1px solid rgba(255, 255, 255, 0.04);
        }

        .panel-section:first-child {
            border-top: none;
            padding-top: 4px;
            margin-top: 0;
        }

        /* Badge */
        .panel-link .badge {
            margin-left: auto;
            font-size: 10px;
            font-weight: 700;
            padding: 1px 6px;
            border-radius: 20px;
            background: rgba(245, 158, 11, 0.15);
            color: #fbbf24;
            border: 1px solid rgba(245, 158, 11, 0.2);
        }

        .panel-link .badge.badge-red {
            background: rgba(239, 68, 68, 0.15);
            color: #f87171;
            border-color: rgba(239, 68, 68, 0.2);
        }

        /* Scrollbar */
        .scrollbar-thin::-webkit-scrollbar {
            width: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.08);
            border-radius: 4px;
        }

        /* Logo glow */
        #rail-logo:hover {
            filter: drop-shadow(0 0 8px rgba(96, 165, 250, 0.6));
        }

        /* Mobile responsiveness */
        @media (max-width: 1023px) {
            #sidebar-rail {
                width: 100vw !important;
                flex-direction: row !important;
                height: auto !important;
                bottom: 0 !important;
                top: auto !important;
                padding: 8px 12px !important;
                gap: 0 !important;
                justify-content: space-around !important;
                border-right: none !important;
                border-top: 1px solid rgba(255, 255, 255, 0.06) !important;
                transform: translateY(100%) !important;
                transition: transform 0.3s cubic-bezier(.16, 1, .3, 1) !important;
            }

            #sidebar-rail.mobile-open {
                transform: translateY(0) !important;
            }

            #sidebar-panel {
                left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                top: 0 !important;
                bottom: 0 !important;
                transform: translateX(-100%) !important;
                transition: transform 0.26s cubic-bezier(.16, 1, .3, 1) !important;
            }

            #sidebar-panel.panel-open {
                transform: translateX(0) !important;
            }

            #main-wrap {
                padding-left: 0 !important;
                padding-bottom: 64px !important;
                transition: none !important;
            }

            #panel-backdrop {
                display: none !important;
            }

            .rail-btn .rail-tip {
                display: none !important;
            }

            .rail-btn::before {
                display: none !important;
            }

            /* Rail buttons mobile */
            .rail-btn {
                width: 44px !important;
                height: 44px !important;
                min-width: 44px !important;
            }

            #rail-logo {
                display: none !important;
            }
        }

        /* Sidebar rail visual override (dark rail is by design, not dark mode) */
        #sidebar-rail {
            background: linear-gradient(180deg, #1e293b 0%, #1a2744 100%);
        }

        #sidebar-panel {
            background: #f8fafc;
            border-color: #e2e8f0;
            box-shadow: 4px 0 24px rgba(0, 0, 0, 0.08);
        }

        .panel-link {
            color: #64748b;
        }

        .panel-link:hover {
            background: #f1f5f9;
            color: #1e293b;
        }

        .panel-link.active {
            background: rgba(var(--group-rgb, 59, 130, 246), 0.08);
            color: var(--group-color, #2563eb);
        }

        .panel-section {
            color: #64748b;
            border-top-color: #e2e8f0;
        }

        #panel-search {
            background: #f1f5f9;
            border-color: #e2e8f0;
            color: #1e293b;
        }

        #panel-search::placeholder {
            color: #94a3b8;
        }

        /* Hide scrollbar for mobile breadcrumb */
        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body class="h-full font-[Inter,sans-serif] antialiased bg-[#f8f8f8] text-gray-900">
    <div class="flex h-full">

        {{-- ── ICON RAIL (always visible on desktop, slide-in on mobile) ── --}}
        <aside id="sidebar-rail"
            class="fixed inset-y-0 left-0 z-60 flex flex-col items-center py-3 gap-0.5 shrink-0
               -translate-x-full lg:translate-x-0 transition-transform duration-300">

            {{-- Logo: always white since rail bg is always dark --}}
            <a href="{{ route('dashboard') }}" id="rail-logo"
                class="flex items-center justify-center w-9 h-9 mb-3 rounded-xl transition-all duration-200">
                <img src="/logo.png" alt="Q" class="h-6 w-auto object-contain" loading="lazy"
                    style="filter: brightness(0) invert(1);">
            </a>

            @php
                $user = auth()->user();
                $navTenant = $user?->tenant;

                // BUG-1.1 & BUG-1.4 FIX: resolveActiveGroup() — array-priority approach.
                // Each route pattern belongs to exactly ONE group. The first matching group wins.
                // This prevents double-active when a route could match multiple patterns.
                function resolveActiveGroup(): string
                {
                    $groupMap = [
                        // 1. Super Admin (checked first — most specific)
                        'superadmin' => ['super-admin*'],
                        // 2. Dashboard & Analytics
                        'home' => [
                            'dashboard',
                            'reports*',
                            'kpi*',
                            'forecast*',
                            'anomalies*',
                            'zero-input*',
                            'simulations*',
                        ],
                        // 3. AI Assistant
                        'ai' => ['chat*'],
                        // 4. Transaksi & Master Data
                        'transactions' => [
                            'quotations*',
                            'invoices*',
                            'delivery-orders*',
                            'down-payments*',
                            'sales-returns*',
                            'sales.*',
                            'sales.index',
                            'price-lists*',
                            'purchase-returns*',
                            'customers*',
                            'suppliers*',
                            'supplier-performance*',
                            'products*',
                            'warehouses*',
                            'categories*',
                            'crm*',
                            'commission*',
                            'helpdesk*',
                            'subscription-billing*',
                            'loyalty*',
                        ],
                        // 5. Inventori
                        'inventory' => ['inventory*', 'wms*', 'purchasing*', 'landed-cost*', 'consignment*', 'iot*'],
                        // 6. SDM & Operasional
                        'operations' => [
                            'hrm*',
                            'payroll*',
                            'self-service*',
                            'reimbursement*',
                            'production*',
                            'manufacturing*',
                            'qc*',
                            'printing*',
                            'cosmetic*',
                            'tour-travel*',
                            'livestock-enhancement*',
                            'fisheries*',
                            'fleet*',
                            'contracts*',
                            'shipping*',
                            'approvals*',
                            'ecommerce*',
                            'documents*',
                            'projects*',
                            'timesheets*',
                            'project-billing*',
                            'farm*',
                            'pos*',
                            // BUG-1.4: telecom routes were missing — added here
                            'telecom*',
                        ],
                        // 7. Keuangan
                        'finance' => [
                            'accounting*',
                            'expenses*',
                            'bank.*',
                            'bank-accounts*',
                            'receivables*',
                            'payables*',
                            'bulk-payments*',
                            'assets*',
                            'budget*',
                            'journals*',
                            'deferred*',
                            'writeoffs*',
                        ],
                        // 8. Pengaturan (hotel routes kept here per existing design)
                        'settings' => [
                            'company-profile*',
                            'settings*',
                            'tenant.users*',
                            'reminders*',
                            'import*',
                            'audit*',
                            'notifications*',
                            'bot*',
                            'api-settings*',
                            'subscription.index',
                            'cost-centers*',
                            'ai-memory*',
                            'taxes*',
                            'custom-fields*',
                            'constraints*',
                            'company-groups*',
                            'hotel*',
                        ],
                    ];

                    foreach ($groupMap as $group => $patterns) {
                        if (request()->routeIs(...$patterns)) {
                            return $group; // First match wins — no double-active possible
                        }
                    }

                    return '';
                }

                $activeGroup = resolveActiveGroup();
            @endphp

            @if ($user?->isSuperAdmin())
                @include('layouts._rail_btn', [
                    'group' => 'home',
                    'icon' => 'home',
                    'label' => 'Dashboard',
                ])
                @include('layouts._rail_btn', [
                    'group' => 'superadmin',
                    'icon' => 'building',
                    'label' => 'Admin',
                ])
            @elseif($user?->isAffiliate())
                @include('layouts._rail_btn', [
                    'group' => 'home',
                    'icon' => 'home',
                    'label' => 'Dashboard',
                ])
            @else
                {{-- TASK-016: Simplified to 7 top-level groups --}}
                @include('layouts._rail_btn', [
                    'group' => 'home',
                    'icon' => 'home',
                    'label' => 'Dashboard',
                ])
                @include('layouts._rail_btn', ['group' => 'ai', 'icon' => 'sparkle', 'label' => 'AI Chat'])
                @include('layouts._rail_btn', [
                    'group' => 'transactions',
                    'icon' => 'tag',
                    'label' => 'Transaksi',
                ])
                @include('layouts._rail_btn', [
                    'group' => 'inventory',
                    'icon' => 'cube',
                    'label' => 'Inventori',
                ])
                @if (!$user?->isKasir() && !$user?->isGudang())
                    @include('layouts._rail_btn', [
                        'group' => 'operations',
                        'icon' => 'cog',
                        'label' => 'Operasional',
                    ])
                    @include('layouts._rail_btn', [
                        'group' => 'finance',
                        'icon' => 'currency',
                        'label' => 'Keuangan',
                    ])
                    @include('layouts._rail_btn', [
                        'group' => 'settings',
                        'icon' => 'gear',
                        'label' => 'Pengaturan',
                    ])
                @endif
            @endif

            {{-- Mode Lapangan — visible for all regular users --}}
            @if (!$user?->isSuperAdmin() && !$user?->isAffiliate())
                <a href="{{ route('mobile.hub') }}" title="Mode Lapangan" class="rail-btn mt-1"
                    style="--group-color:#34d399;--group-rgb:52,211,153;background:rgba(52,211,153,0.12);color:#34d399;"
                    @if (request()->routeIs('mobile*')) aria-current="page" @endif>
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                            d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z" />
                    </svg>
                    <span class="rail-tip">Mode Lapangan</span>
                </a>
            @endif

            {{-- Spacer --}}
            <div class="flex-1"></div>

            {{-- User Avatar --}}
            <button onclick="toggleGroup('profile')"
                class="rail-btn w-9 h-9 rounded-full overflow-hidden ring-2 ring-white/10 hover:ring-blue-500/50 transition mb-1 relative"
                data-group="profile" data-color="#60a5fa" data-rgb="96,165,250"
                style="--group-color:#60a5fa;--group-rgb:96,165,250">
                <img src="{{ $user?->avatarUrl() }}" alt="{{ $user?->name }}" class="w-full h-full object-cover"
                    loading="lazy">
                <span class="rail-tip">{{ $user?->name }}</span>
            </button>
        </aside>

        {{-- ── SLIDE PANEL (240px, appears on group click) ── --}}
        <div id="sidebar-panel" class="fixed inset-y-0 left-0 lg:left-14 z-50 flex flex-col overflow-hidden">

            {{-- Accent line --}}
            <div id="panel-accent"></div>

            {{-- Panel Header --}}
            <div class="flex items-center justify-between px-4 h-14 border-b border-white/10 shrink-0">
                <span id="panel-title" class="text-xs font-bold uppercase tracking-widest text-slate-400"></span>
                <button onclick="closePanel()"
                    class="text-slate-600 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            {{-- Search --}}
            <div class="px-3 py-2.5 border-b border-white/5 shrink-0">
                <div class="relative">
                    <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500 pointer-events-none"
                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <input id="panel-search" type="text" placeholder="Cari menu..."
                        oninput="filterPanel(this.value)">
                </div>
            </div>

            {{-- Panel Content --}}
            <nav class="flex-1 overflow-y-auto scrollbar-thin py-2 px-2" id="panel-nav">
                {{-- Filled by JS --}}
            </nav>
        </div>

        {{-- Panel backdrop (click outside to close) --}}
        <div id="panel-backdrop" class="fixed inset-0 z-40 hidden" onclick="closePanel()"></div>

        {{-- Mobile sidebar overlay --}}
        <div id="sidebar-overlay" class="fixed inset-0 z-40 bg-black/50 hidden lg:hidden"
            onclick="closeMobileSidebar()" style="pointer-events:auto"></div>


        {{-- ── MAIN CONTENT ── --}}
        <div class="flex-1 flex flex-col min-w-0 pl-0 lg:pl-14" id="main-wrap">

            {{-- Topbar --}}
            <header
                class="sticky top-0 z-20 h-14 border-b flex items-center px-4 sm:px-6 gap-4
                       bg-[#f0f0f0] border-gray-200">

                {{-- Mobile menu --}}
                <button onclick="toggleMobileSidebar()"
                    class="lg:hidden p-2 rounded-lg hover:bg-[#e4e4e4] text-gray-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                </button>

                {{-- Breadcrumb / Page title --}}
                <div class="flex-1 flex items-center gap-2 min-w-0 overflow-x-auto scrollbar-hide">
                    <span
                        class="text-xs text-slate-400 hidden sm:block whitespace-nowrap">{{ config('app.name') }}</span>
                    <span class="text-xs text-slate-600 hidden sm:block whitespace-nowrap">/</span>
                    @if (isset($header))
                        @if (is_string($header) && !str_contains($header, '<'))
                            <h1 class="text-base font-semibold text-gray-900 truncate whitespace-nowrap">
                                {{ $header }}</h1>
                        @else
                            <div class="flex items-center gap-2 whitespace-nowrap">{!! $header !!}</div>
                        @endif
                    @elseif(View::hasSection('header'))
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            <h1 class="text-base font-semibold text-gray-900 truncate">
                                @yield('header')
                            </h1>
                        </div>
                    @endif
                </div>

                <div class="flex items-center gap-1.5 shrink-0">
                    {{-- TASK 1.6: Enhanced Offline Indicator di Topbar --}}
                    <div id="topbar-offline-indicator" class="flex items-center gap-2"></div>

                    {{-- Legacy offline indicator (hidden, kept for backward compat) --}}
                    <div id="offline-indicator"
                        class="hidden items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 text-xs font-medium"
                        data-pending="0">
                        <span class="relative flex h-2 w-2">
                            <span
                                class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                        </span>
                        <span>Offline</span>
                        <span id="offline-sync-badge"
                            class="hidden ml-1 px-1.5 py-0.5 rounded-full bg-amber-500/20 text-[10px] font-bold">0</span>
                    </div>

                    {{-- Notification bell --}}
                    @php
                        // N+1 FIX: Use cached sidebarBadges from View Composer instead of direct DB query
                        $unreadCount = $sidebarBadges['notifications'] ?? 0;
                        $notifTenantId = $user->tenant_id ?? null;
                        $authUser = $user;
                    @endphp
                    <div class="relative" id="notif-wrapper">
                        <button onclick="toggleNotif()"
                            class="relative p-2 rounded-xl hover:bg-[#e4e4e4] text-gray-500 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                            @if ($unreadCount > 0)
                                <span
                                    class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                            @endif
                        </button>
                        <div id="notif-dropdown"
                            class="hidden absolute right-0 mt-2 w-80 rounded-2xl shadow-xl border overflow-hidden z-50
                               bg-white border-gray-200">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                <span class="font-semibold text-sm text-gray-900">Notifikasi</span>
                                <a href="{{ route('notifications.index') }}"
                                    class="text-xs text-blue-500 hover:underline">Lihat semua</a>
                            </div>
                            <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                                @php
                                    $topbarNotifs = $notifTenantId
                                        ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)
                                            ->latest()
                                            ->take(5)
                                            ->get()
                                        : ($authUser?->isSuperAdmin()
                                            ? \App\Models\ErpNotification::where('user_id', $authUser->id)
                                                ->latest()
                                                ->take(5)
                                                ->get()
                                            : collect());
                                @endphp
                                @forelse($topbarNotifs as $notif)
                                    <div
                                        class="px-4 py-3 hover:bg-[#f0f0f0] {{ $notif->isRead() ? 'opacity-60' : '' }}">
                                        <p class="text-sm font-medium text-gray-900">
                                            {{ $notif->title }}</p>
                                        <p class="text-xs text-slate-400 mt-0.5">{{ Str::limit($notif->body, 80) }}
                                        </p>
                                        <p class="text-xs text-slate-500 mt-1">
                                            {{ $notif->created_at->diffForHumans() }}</p>
                                    </div>
                                @empty
                                    <div class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi
                                    </div>
                                @endforelse
                            </div>
                            {{-- Push notification opt-in --}}
                            <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50">
                                <button id="btn-enable-push" onclick="enablePushNotifications()"
                                    class="w-full text-xs text-center py-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition">
                                    🔔 Aktifkan Notifikasi Push
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            {{-- Page Content --}}
            <main class="flex-1 p-4 sm:p-6 bg-[#f8f8f8]">
                {{-- BUG-1.11 FIX: Page header section — action buttons live here, not in topbar --}}
                @isset($pageHeader)
                    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                        <div class="min-w-0">
                            @isset($pageTitle)
                                <h1 class="text-xl font-semibold text-gray-900 truncate">{{ $pageTitle }}
                                </h1>
                            @endisset
                        </div>
                        <div class="flex flex-wrap items-center gap-2 shrink-0">
                            {{ $pageHeader }}
                        </div>
                    </div>
                @endisset
                @if (session('success'))
                    <div
                        class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-600 text-sm px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 13l4 4L19 7" />
                        </svg>
                        {{ session('success') }}
                    </div>
                @endif
                @if (session('warning'))
                    <div
                        class="mb-4 flex items-start gap-3 bg-amber-500/10 border border-amber-500/20 text-amber-700 text-sm px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>{{ session('warning') }}</span>
                    </div>
                @endif
                @if (session('error'))
                    <div
                        class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-600 text-sm px-4 py-3 rounded-xl">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
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


    {{-- ── NAV DATA (PHP → JS) ── --}}
    @php
        // Permission helper for sidebar — only for non-admin/non-superadmin
        $ps = app(\App\Services\PermissionService::class);
        $canView = function (string $module) use ($user, $ps): bool {
            if (!$user || $user->isAdmin() || $user->isSuperAdmin()) {
                return true;
            }
            return $ps->check($user, $module, 'view');
        };
    @endphp
    <script>
        const NAV_GROUPS = {
            @if ($user?->isSuperAdmin())
                home: {
                    title: 'Dashboard',
                    items: [{
                        label: 'Dashboard',
                        href: '{{ route('dashboard') }}',
                        active: {{ request()->routeIs('dashboard') ? 'true' : 'false' }}
                    }, ]
                },
                superadmin: {
                    title: 'Super Admin',
                    items: [{
                            label: 'Semua Tenant',
                            href: '{{ route('super-admin.tenants.index') }}',
                            active: {{ request()->routeIs('super-admin.tenants*') ? 'true' : 'false' }}
                        },
                        {
                            label: 'Kelola Paket',
                            href: '{{ route('super-admin.plans.index') }}',
                            active: {{ request()->routeIs('super-admin.plans*') ? 'true' : 'false' }}
                        },
                        {
                            section: 'Monitoring'
                        },
                        {
                            label: 'Monitoring',
                            href: '{{ route('super-admin.monitoring.index') }}',
                            active: {{ request()->routeIs('super-admin.monitoring*') ? 'true' : 'false' }},
                            badge: {{ $sidebarBadges['error_logs'] ?? 0 ?: 'null' }},
                            badgeClass: 'badge-red'
                        },
                        {
                            section: 'Konten'
                        },
                        {
                            label: 'Popup Iklan',
                            href: '{{ route('super-admin.popup-ads.index') }}',
                            active: {{ request()->routeIs('super-admin.popup-ads*') ? 'true' : 'false' }},
                        },
                        {
                            section: 'Afiliasi'
                        },
                        {
                            label: 'Kelola Affiliate',
                            href: '{{ route('super-admin.affiliates.index') }}',
                            active: {{ request()->routeIs('super-admin.affiliates.index') ? 'true' : 'false' }}
                        },
                        {
                            label: 'Komisi',
                            href: '{{ route('super-admin.affiliates.commissions') }}',
                            active: {{ request()->routeIs('super-admin.affiliates.commissions*') ? 'true' : 'false' }},
                            badge: {{ $sidebarBadges['affiliate_commissions'] ?? 0 ?: 'null' }},
                            badgeClass: 'badge-amber'
                        },
                        {
                            label: 'Payout',
                            href: '{{ route('super-admin.affiliates.payouts') }}',
                            active: {{ request()->routeIs('super-admin.affiliates.payouts*') ? 'true' : 'false' }}
                        },
                        {
                            label: 'Fraud Monitor',
                            href: '{{ route('super-admin.affiliates.audit-logs') }}',
                            active: {{ request()->routeIs('super-admin.affiliates.audit-logs*') ? 'true' : 'false' }},
                            badge: {{ $sidebarBadges['affiliate_fraud'] ?? 0 ?: 'null' }},
                            badgeClass: 'badge-red'
                        },
                        {
                            section: 'Platform'
                        },
                        {
                            label: 'Pengaturan Platform',
                            href: '{{ route('super-admin.settings.index') }}',
                            active: {{ request()->routeIs('super-admin.settings*') ? 'true' : 'false' }}
                        },
                    ]
                },
            @elseif ($user?->isAffiliate())
                home: {
                        title: 'Affiliate',
                        items: [{
                            label: 'Dashboard',
                            href: '{{ route('affiliate.dashboard') }}',
                            active: {{ request()->routeIs('affiliate.dashboard') ? 'true' : 'false' }}
                        }, ]
                    },
            @else
                home: {
                    title: 'Dashboard',
                    items: [
                        @if ($canView('dashboard'))
                            {
                                section: 'Overview'
                            }, {
                                label: 'Dashboard',
                                href: '{{ route('dashboard') }}',
                                active: {{ request()->routeIs('dashboard') ? 'true' : 'false' }}
                            },
                        @endif
                        @if (($navTenant?->isModuleEnabled('reports') ?? true) && $canView('reports'))
                            {
                                section: 'Reports & Analytics'
                            }, {
                                label: 'Laporan',
                                href: '{{ route('reports.index') }}',
                                active: {{ request()->routeIs('reports.index', 'reports.sales*', 'reports.finance*', 'reports.inventory*', 'reports.hrm*', 'reports.receivables*', 'reports.profit-loss*', 'reports.income-statement*', 'reports.payroll*', 'reports.aging*', 'reports.balance-sheet*', 'reports.cash-flow*', 'reports.budget*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('kpi'))
                            {
                                label: 'KPI Dashboard',
                                href: '{{ route('kpi.index') }}',
                                active: {{ request()->routeIs('kpi*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('reports'))
                            {
                                label: 'AI Forecasting',
                                href: '{{ route('forecast.index') }}',
                                active: {{ request()->routeIs('forecast*') ? 'true' : 'false' }}
                            }, {
                                label: 'Proyeksi Arus Kas',
                                href: '{{ route('reports.cash-flow-projection') }}',
                                active: {{ request()->routeIs('reports.cash-flow-projection*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('anomalies'))
                            {
                                section: 'AI & Intelligence'
                            }, {
                                label: 'Deteksi Anomali',
                                href: '{{ route('anomalies.index') }}',
                                active: {{ request()->routeIs('anomalies*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('zero_input'))
                            {
                                label: 'Input Cerdas (AI)',
                                href: '{{ route('zero-input.index') }}',
                                active: {{ request()->routeIs('zero-input*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('simulations'))
                            {
                                label: 'Simulasi Keuangan',
                                href: '{{ route('simulations.index') }}',
                                active: {{ request()->routeIs('simulations*') ? 'true' : 'false' }}
                            },
                        @endif
                    ]
                },
                ai: {
                    title: 'AI Chat',
                    items: [{
                        label: 'AI Chat',
                        href: '{{ route('chat.index') }}',
                        active: {{ request()->routeIs('chat*') ? 'true' : 'false' }}
                    }, ]
                },
                transactions: {
                    title: 'Transaksi & Master Data',
                    items: [{
                            section: 'Kontak'
                        },
                        @if ($canView('customers'))
                            {
                                label: 'Data Customer',
                                href: '{{ route('customers.index') }}',
                                active: {{ request()->routeIs('customers*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('suppliers'))
                            {
                                label: 'Data Supplier',
                                href: '{{ route('suppliers.index') }}',
                                active: {{ request()->routeIs('suppliers*') && !request()->routeIs('suppliers.scorecards*') && !request()->routeIs('suppliers.scorecard*') && !request()->routeIs('suppliers.strategic-sourcing*') && !request()->routeIs('suppliers.sourcing*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('suppliers'))
                            {
                                label: 'Supplier Scorecard',
                                href: '{{ route('suppliers.scorecards.index') }}',
                                active: {{ request()->routeIs('suppliers.scorecards*') ? 'true' : 'false' }}
                            }, {
                                label: 'Supplier Performance',
                                href: '{{ route('supplier-performance.dashboard') }}',
                                active: {{ request()->routeIs('supplier-performance*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('suppliers'))
                            {
                                label: 'Strategic Sourcing',
                                href: '{{ route('suppliers.strategic-sourcing') }}',
                                active: {{ request()->routeIs('suppliers.strategic-sourcing*') ? 'true' : 'false' }}
                            },
                        @endif {
                            section: 'Produk & Gudang'
                        },
                        @if ($canView('products'))
                            {
                                label: 'Data Produk',
                                href: '{{ route('products.index') }}',
                                active: {{ request()->routeIs('products*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('warehouses'))
                            {
                                label: 'Data Gudang',
                                href: '{{ route('warehouses.index') }}',
                                active: {{ request()->routeIs('warehouses*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('price_lists'))
                            {
                                label: 'Daftar Harga',
                                href: '{{ route('price-lists.index') }}',
                                active: {{ request()->routeIs('price-lists*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if ($canView('categories'))
                            {
                                label: 'Kategori Produk',
                                href: '{{ route('categories.index') }}',
                                active: {{ request()->routeIs('categories*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if (!$user?->isGudang())
                            {
                                section: 'Penjualan'
                            },
                            @if ($navTenant?->isModuleEnabled('invoicing') ?? true)
                                @if ($canView('sales'))
                                    {
                                        label: 'Sales Order',
                                        href: '{{ route('sales.index') }}',
                                        active: {{ request()->routeIs('sales.index', 'sales.create', 'sales.show', 'sales.store') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('invoices'))
                                    {
                                        label: 'Penawaran (Quotation)',
                                        href: '{{ route('quotations.index') }}',
                                        active: {{ request()->routeIs('quotations*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('invoices'))
                                    {
                                        label: 'Invoice',
                                        href: '{{ route('invoices.index') }}',
                                        active: {{ request()->routeIs('invoices*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('delivery'))
                                    {
                                        label: 'Surat Jalan',
                                        href: '{{ route('delivery-orders.index') }}',
                                        active: {{ request()->routeIs('delivery-orders*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('down_payments'))
                                    {
                                        label: 'Uang Muka (DP)',
                                        href: '{{ route('down-payments.index') }}',
                                        active: {{ request()->routeIs('down-payments*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('sales_returns'))
                                    {
                                        label: 'Retur Penjualan',
                                        href: '{{ route('sales-returns.index') }}',
                                        active: {{ request()->routeIs('sales-returns*') ? 'true' : 'false' }}
                                    },
                                @endif
                            @endif
                            @if (($navTenant?->isModuleEnabled('crm') ?? true) && $canView('crm'))
                                {
                                    label: 'CRM & Pipeline',
                                    href: '{{ route('crm.index') }}',
                                    active: {{ request()->routeIs('crm*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('commission') ?? true) && $canView('commission'))
                                {
                                    label: 'Komisi Sales',
                                    href: '{{ route('commission.index') }}',
                                    active: {{ request()->routeIs('commission.index') ? 'true' : 'false' }}
                                }, {
                                    label: 'Rule Komisi',
                                    href: '{{ route('commission.rules') }}',
                                    active: {{ request()->routeIs('commission.rules*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('helpdesk') ?? true) && $canView('helpdesk'))
                                {
                                    label: 'Helpdesk',
                                    href: '{{ route('helpdesk.index') }}',
                                    active: {{ request()->routeIs('helpdesk.index') || request()->routeIs('helpdesk.show') ? 'true' : 'false' }}
                                }, {
                                    label: 'Knowledge Base',
                                    href: '{{ route('helpdesk.kb') }}',
                                    active: {{ request()->routeIs('helpdesk.kb*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('subscription_billing') ?? true) && $canView('subscription_billing'))
                                {
                                    label: 'Subscription Billing',
                                    href: '{{ route('subscription-billing.index') }}',
                                    active: {{ request()->routeIs('subscription-billing.index') || request()->routeIs('subscription-billing.show') ? 'true' : 'false' }}
                                }, {
                                    label: 'Plan Langganan',
                                    href: '{{ route('subscription-billing.plans') }}',
                                    active: {{ request()->routeIs('subscription-billing.plans*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('loyalty') ?? true) && $canView('loyalty'))
                                {
                                    label: 'Program Loyalitas',
                                    href: '{{ route('loyalty.index') }}',
                                    active: {{ request()->routeIs('loyalty*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos'))
                                {
                                    label: 'Kasir (POS)',
                                    href: '{{ route('pos.index') }}',
                                    active: {{ request()->routeIs('pos*') ? 'true' : 'false' }}
                                },
                            @endif
                        @endif
                    ]
                },
                inventory: {
                    title: 'Inventori',
                    items: [
                        @if (($navTenant?->isModuleEnabled('inventory') ?? true) && $canView('inventory'))
                            {
                                label: 'Inventori',
                                href: '{{ route('inventory.index') }}',
                                active: {{ request()->routeIs('inventory.index') ? 'true' : 'false' }}
                            }, {
                                label: 'Transfer Stok',
                                href: '{{ route('inventory.transfers.index') }}',
                                active: {{ request()->routeIs('inventory.transfers*') ? 'true' : 'false' }}
                            },
                        @endif
                        @if (
                            ($user?->isAdmin() || $user?->isManager()) &&
                                ($navTenant?->isModuleEnabled('purchasing') ?? true) &&
                                $canView('purchasing'))
                            {
                                section: 'Pembelian'
                            }, {
                                label: 'Pembelian',
                                href: '{{ route('purchasing.orders') }}',
                                active: {{ request()->routeIs('purchasing.orders*') ? 'true' : 'false' }}
                            }, {
                                label: 'Purchase Requisition',
                                href: '{{ route('purchasing.requisitions') }}',
                                active: {{ request()->routeIs('purchasing.requisitions*') ? 'true' : 'false' }}
                            }, {
                                label: 'RFQ',
                                href: '{{ route('purchasing.rfq') }}',
                                active: {{ request()->routeIs('purchasing.rfq*') ? 'true' : 'false' }}
                            }, {
                                label: 'Goods Receipt',
                                href: '{{ route('purchasing.goods-receipts') }}',
                                active: {{ request()->routeIs('purchasing.goods-receipts*') ? 'true' : 'false' }}
                            }, {
                                label: '3-Way Matching',
                                href: '{{ route('purchasing.matching') }}',
                                active: {{ request()->routeIs('purchasing.matching*') ? 'true' : 'false' }}
                            }, {
                                label: 'Retur Pembelian',
                                href: '{{ route('purchase-returns.index') }}',
                                active: {{ request()->routeIs('purchase-returns*') ? 'true' : 'false' }}
                            },
                            @if (($navTenant?->isModuleEnabled('landed_cost') ?? true) && $canView('landed_cost'))
                                {
                                    label: 'Landed Cost',
                                    href: '{{ route('landed-cost.index') }}',
                                    active: {{ request()->routeIs('landed-cost*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('consignment') ?? true) && $canView('consignment'))
                                {
                                    label: 'Konsinyasi',
                                    href: '{{ route('consignment.index') }}',
                                    active: {{ request()->routeIs('consignment.index') || request()->routeIs('consignment.shipments*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Partner Konsinyasi',
                                    href: '{{ route('consignment.partners') }}',
                                    active: {{ request()->routeIs('consignment.partners*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('wms') ?? true) && $canView('wms'))
                                {
                                    section: 'WMS Gudang'
                                }, {
                                    label: 'Zone & Bin',
                                    href: '{{ route('wms.index') }}',
                                    active: {{ request()->routeIs('wms.index') ? 'true' : 'false' }}
                                }, {
                                    label: 'Picking List',
                                    href: '{{ route('wms.picking') }}',
                                    active: {{ request()->routeIs('wms.picking*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Stock Opname',
                                    href: '{{ route('wms.opname') }}',
                                    active: {{ request()->routeIs('wms.opname*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Putaway Rules',
                                    href: '{{ route('wms.putaway-rules') }}',
                                    active: {{ request()->routeIs('wms.putaway-rules*') ? 'true' : 'false' }}
                                },
                            @endif {
                                section: 'IoT Devices'
                            }, {
                                label: 'ESP32 / Arduino / RPi',
                                href: '{{ route('iot.devices.index') }}',
                                active: {{ request()->routeIs('iot.devices*') ? 'true' : 'false' }}
                            },
                        @endif
                    ]
                },
                @if (!$user?->isKasir() && !$user?->isGudang())
                    operations: {
                        title: 'Operasional',
                        items: [
                            @if (($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos'))
                                {
                                    label: 'Kasir (POS)',
                                    href: '{{ route('pos.index') }}',
                                    active: {{ request()->routeIs('pos*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('production') ?? true) && $canView('production'))
                                {
                                    section: 'Manufacturing'
                                }, {
                                    label: 'Production Dashboard',
                                    href: '{{ route('production.dashboard') }}',
                                    active: {{ request()->routeIs('production.dashboard*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Gantt Chart',
                                    href: '{{ route('production.gantt.index') }}',
                                    active: {{ request()->routeIs('production.gantt*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Produksi / WO',
                                    href: '{{ route('production.index') }}',
                                    active: {{ request()->routeIs('production.*') && !request()->routeIs('production.dashboard*') && !request()->routeIs('production.gantt*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('production') ?? true) && $canView('production'))
                                {
                                    section: 'Quality Control'
                                }, {
                                    label: 'QC Inspections',
                                    href: '{{ route('qc.inspections.index') }}',
                                    active: {{ request()->routeIs('qc.inspections*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Test Templates',
                                    href: '{{ route('qc.templates.index') }}',
                                    active: {{ request()->routeIs('qc.templates*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('manufacturing') ?? true) && $canView('manufacturing'))
                                {
                                    label: 'BOM Multi-Level',
                                    href: '{{ route('manufacturing.bom') }}',
                                    active: {{ request()->routeIs('manufacturing.bom*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Mix Design Beton',
                                    href: '{{ route('manufacturing.mix-design') }}',
                                    active: {{ request()->routeIs('manufacturing.mix-design*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Work Center',
                                    href: '{{ route('manufacturing.work-centers') }}',
                                    active: {{ request()->routeIs('manufacturing.work-centers*') ? 'true' : 'false' }}
                                }, {
                                    label: 'MRP Planning',
                                    href: '{{ route('manufacturing.mrp') }}',
                                    active: {{ request()->routeIs('manufacturing.mrp*') ? 'true' : 'false' }}
                                }, {
                                    label: 'MRP Accuracy',
                                    href: '{{ route('manufacturing.mrp.accuracy') }}',
                                    active: {{ request()->routeIs('manufacturing.mrp.accuracy*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Predictive MRP (AI)',
                                    href: '{{ route('manufacturing.mrp.predictive') }}',
                                    active: {{ request()->routeIs('manufacturing.mrp.predictive*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('manufacturing') ?? true) && $canView('printing'))
                                {
                                    label: 'Printing Jobs',
                                    href: '{{ route('printing.dashboard') }}',
                                    active: {{ request()->routeIs('printing*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('manufacturing') ?? true) && auth()->user()?->tenant_id && $canView('cosmetic'))
                                {
                                    label: 'Cosmetic Formulas',
                                    href: '{{ route('cosmetic.formulas.index') }}',
                                    active: {{ request()->routeIs('cosmetic.formulas*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Batch Production',
                                    href: '{{ route('cosmetic.batches.index') }}',
                                    active: {{ request()->routeIs('cosmetic.batches*') ? 'true' : 'false' }}
                                }, {
                                    label: 'QC Laboratory',
                                    href: '{{ route('cosmetic.qc.tests') }}',
                                    active: {{ request()->routeIs('cosmetic.qc*') ? 'true' : 'false' }}
                                }, {
                                    label: 'BPOM Registrations',
                                    href: '{{ route('cosmetic.registrations.index') }}',
                                    active: {{ request()->routeIs('cosmetic.registrations*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Variants Manager',
                                    href: '{{ route('cosmetic.variants.index') }}',
                                    active: {{ request()->routeIs('cosmetic.variants*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Packaging & Labels',
                                    href: '{{ route('cosmetic.packaging.index') }}',
                                    active: {{ request()->routeIs('cosmetic.packaging*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Expiry & Recalls',
                                    href: '{{ route('cosmetic.expiry.dashboard') }}',
                                    active: {{ request()->routeIs('cosmetic.expiry*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Distribution Channels',
                                    href: '{{ route('cosmetic.distribution.index') }}',
                                    active: {{ request()->routeIs('cosmetic.distribution*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Cosmetic Analytics',
                                    href: '{{ route('cosmetic.analytics.dashboard') }}',
                                    active: {{ request()->routeIs('cosmetic.analytics*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('hotel') ?? true) && $canView('tour_travel'))
                                {
                                    section: 'Tour & Travel'
                                }, {
                                    label: 'Tour Packages',
                                    href: '{{ route('tour-travel.packages.index') }}',
                                    active: {{ request()->routeIs('tour-travel.packages*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Bookings',
                                    href: '{{ route('tour-travel.bookings.index') }}',
                                    active: {{ request()->routeIs('tour-travel.bookings*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Tour Analytics',
                                    href: '{{ route('tour-travel.analytics') }}',
                                    active: {{ request()->routeIs('tour-travel.analytics*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('livestock') ?? true) && $canView('livestock'))
                                {
                                    section: 'Livestock Enhancement'
                                }, {
                                    label: 'Dairy Management',
                                    href: '{{ route('livestock-enhancement.dairy.milk-records') }}',
                                    active: {{ request()->routeIs('livestock-enhancement.dairy*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Poultry Management',
                                    href: '{{ route('livestock-enhancement.poultry.flocks') }}',
                                    active: {{ request()->routeIs('livestock-enhancement.poultry*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Breeding',
                                    href: '{{ route('livestock-enhancement.breeding.records') }}',
                                    active: {{ request()->routeIs('livestock-enhancement.breeding*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Health & Vaccination',
                                    href: '{{ route('livestock-enhancement.health.treatments') }}',
                                    active: {{ request()->routeIs('livestock-enhancement.health*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Waste Management',
                                    href: '{{ route('livestock-enhancement.waste.logs') }}',
                                    active: {{ request()->routeIs('livestock-enhancement.waste*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('fleet') ?? true) && $canView('fleet'))
                                {
                                    label: 'Fleet Kendaraan',
                                    href: '{{ route('fleet.index') }}',
                                    active: {{ request()->routeIs('fleet.index') ? 'true' : 'false' }}
                                }, {
                                    label: 'Driver',
                                    href: '{{ route('fleet.drivers') }}',
                                    active: {{ request()->routeIs('fleet.drivers*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Trip / Penugasan',
                                    href: '{{ route('fleet.trips') }}',
                                    active: {{ request()->routeIs('fleet.trips*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Log BBM',
                                    href: '{{ route('fleet.fuel-logs') }}',
                                    active: {{ request()->routeIs('fleet.fuel-logs*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Maintenance',
                                    href: '{{ route('fleet.maintenance') }}',
                                    active: {{ request()->routeIs('fleet.maintenance*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('inventory') ?? true) && $canView('shipping'))
                                {
                                    label: 'Pengiriman',
                                    href: '{{ route('shipping.index') }}',
                                    active: {{ request()->routeIs('shipping*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('agriculture') ?? true) && $canView('agriculture'))
                                {
                                    label: 'Manajemen Lahan',
                                    href: '{{ route('farm.plots') }}',
                                    active: {{ request()->routeIs('farm.plots*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Siklus Tanam',
                                    href: '{{ route('farm.cycles') }}',
                                    active: {{ request()->routeIs('farm.cycles*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pencatatan Panen',
                                    href: '{{ route('farm.harvests') }}',
                                    active: {{ request()->routeIs('farm.harvests*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Analisis Biaya Lahan',
                                    href: '{{ route('farm.analytics') }}',
                                    active: {{ request()->routeIs('farm.analytics*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Populasi Ternak',
                                    href: '{{ route('farm.livestock') }}',
                                    active: {{ request()->routeIs('farm.livestock*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('livestock') ?? true) && $canView('livestock'))
                                {
                                    section: 'Perikanan (Fisheries)'
                                }, {
                                    label: 'Dashboard Perikanan',
                                    href: '{{ route('fisheries.index') }}',
                                    active: {{ request()->routeIs('fisheries.index') ? 'true' : 'false' }}
                                }, {
                                    label: 'Cold Chain',
                                    href: '{{ route('fisheries.cold-chain.index') }}',
                                    active: {{ request()->routeIs('fisheries.cold-chain*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Fishing Operations',
                                    href: '{{ route('fisheries.operations.index') }}',
                                    active: {{ request()->routeIs('fisheries.operations*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Aquaculture',
                                    href: '{{ route('fisheries.aquaculture.index') }}',
                                    active: {{ request()->routeIs('fisheries.aquaculture*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Species & Grading',
                                    href: '{{ route('fisheries.species.index') }}',
                                    active: {{ request()->routeIs('fisheries.species*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Export Documentation',
                                    href: '{{ route('fisheries.export.index') }}',
                                    active: {{ request()->routeIs('fisheries.export*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Analytics',
                                    href: '{{ route('fisheries.analytics') }}',
                                    active: {{ request()->routeIs('fisheries.analytics') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('contracts') ?? true) && $canView('contracts'))
                                {
                                    label: 'Kontrak',
                                    href: '{{ route('contracts.index') }}',
                                    active: {{ request()->routeIs('contracts.index') || request()->routeIs('contracts.show') ? 'true' : 'false' }}
                                }, {
                                    label: 'Template Kontrak',
                                    href: '{{ route('contracts.templates') }}',
                                    active: {{ request()->routeIs('contracts.templates*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if ($canView('approvals'))
                                {
                                    label: 'Persetujuan',
                                    href: '{{ route('approvals.index') }}',
                                    active: {{ request()->routeIs('approvals*') ? 'true' : 'false' }},
                                    badge: {{ $sidebarBadges['approvals'] ?? 0 ?: 'null' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('ecommerce') ?? true) && $canView('ecommerce'))
                                {
                                    label: 'E-Commerce',
                                    href: '{{ route('ecommerce.index') }}',
                                    active: {{ request()->routeIs('ecommerce*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if ($canView('documents'))
                                {
                                    label: 'Dokumen',
                                    href: '{{ route('documents.index') }}',
                                    active: {{ request()->routeIs('documents*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('projects') ?? true) && $canView('projects'))
                                {
                                    label: 'Manajemen Proyek',
                                    href: '{{ route('projects.index') }}',
                                    active: {{ request()->routeIs('projects*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('project_billing') ?? true) && $canView('project_billing'))
                                {
                                    label: 'Project Billing',
                                    href: '{{ route('project-billing.index') }}',
                                    active: {{ request()->routeIs('project-billing*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('projects') ?? true) && $canView('timesheets'))
                                {
                                    label: 'Timesheet',
                                    href: '{{ route('timesheets.index') }}',
                                    active: {{ request()->routeIs('timesheets*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (
                                ($navTenant?->isModuleEnabled('hrm') ?? true) ||
                                    ($navTenant?->isModuleEnabled('payroll') ?? true) ||
                                    ($navTenant?->isModuleEnabled('reimbursement') ?? true))
                                {
                                    section: 'SDM & Karyawan'
                                },
                            @endif
                            @if ($user?->isAdmin() || $user?->isManager())
                                @if (($navTenant?->isModuleEnabled('hrm') ?? true) && $canView('hrm'))
                                    {
                                        section: 'Manajemen SDM'
                                    }, {
                                        label: 'Rekrutmen',
                                        href: '{{ route('hrm.recruitment.index') }}',
                                        active: {{ request()->routeIs('hrm.recruitment*', 'hrm.onboarding*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'SDM & Karyawan',
                                        href: '{{ route('hrm.index') }}',
                                        active: {{ request()->routeIs('hrm.index', 'hrm.store', 'hrm.update', 'hrm.destroy', 'hrm.attendance*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Manajemen Cuti',
                                        href: '{{ route('hrm.leave') }}',
                                        active: {{ request()->routeIs('hrm.leave*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Penilaian Kinerja',
                                        href: '{{ route('hrm.performance') }}',
                                        active: {{ request()->routeIs('hrm.performance*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Struktur Organisasi',
                                        href: '{{ route('hrm.orgchart') }}',
                                        active: {{ request()->routeIs('hrm.orgchart') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Jadwal Shift',
                                        href: '{{ route('hrm.shifts.index') }}',
                                        active: {{ request()->routeIs('hrm.shifts*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Lembur',
                                        href: '{{ route('hrm.overtime.index') }}',
                                        active: {{ request()->routeIs('hrm.overtime*') ? 'true' : 'false' }},
                                        badge: {{ $sidebarBadges['overtime'] ?? 0 ?: 'null' }}
                                    }, {
                                        label: 'Pelatihan & Sertifikasi',
                                        href: '{{ route('hrm.training.index') }}',
                                        active: {{ request()->routeIs('hrm.training*') ? 'true' : 'false' }},
                                        badge: {{ $sidebarBadges['certifications'] ?? 0 ?: 'null' }},
                                        badgeClass: 'badge-red'
                                    }, {
                                        label: 'Surat Peringatan',
                                        href: '{{ route('hrm.disciplinary.index') }}',
                                        active: {{ request()->routeIs('hrm.disciplinary*') ? 'true' : 'false' }},
                                        badge: {{ $sidebarBadges['disciplinary'] ?? 0 ?: 'null' }}
                                    },
                                @endif
                                @if (($navTenant?->isModuleEnabled('payroll') ?? true) && $canView('payroll'))
                                    {
                                        section: 'Penggajian'
                                    }, {
                                        label: 'Penggajian',
                                        href: '{{ route('payroll.index') }}',
                                        active: {{ request()->routeIs('payroll.index', 'payroll.process', 'payroll.run*') ? 'true' : 'false' }}
                                    }, {
                                        label: 'Komponen Gaji',
                                        href: '{{ route('payroll.components.index') }}',
                                        active: {{ request()->routeIs('payroll.components*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if (($navTenant?->isModuleEnabled('reimbursement') ?? true) && $canView('reimbursement'))
                                    {
                                        section: 'Reimbursement'
                                    }, {
                                        label: 'Kelola Reimbursement',
                                        href: '{{ route('reimbursement.index') }}',
                                        active: {{ request()->routeIs('reimbursement.index', 'reimbursement.store', 'reimbursement.approve', 'reimbursement.reject', 'reimbursement.pay', 'reimbursement.destroy') ? 'true' : 'false' }}
                                    },
                                @endif
                            @endif
                            @if (!$user?->isSuperAdmin() && !$user?->isAffiliate())
                                {
                                    section: 'Self-Service'
                                }, {
                                    label: 'Portal Karyawan',
                                    href: '{{ route('self-service.dashboard') }}',
                                    active: {{ request()->routeIs('self-service.dashboard', 'self-service.profile*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Slip Gaji',
                                    href: '{{ route('payroll.slip.index') }}',
                                    active: {{ request()->routeIs('payroll.slip*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Cuti Saya',
                                    href: '{{ route('self-service.leave.index') }}',
                                    active: {{ request()->routeIs('self-service.leave*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Absensi Saya',
                                    href: '{{ route('self-service.attendance.index') }}',
                                    active: {{ request()->routeIs('self-service.attendance*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Reimbursement Saya',
                                    href: '{{ route('reimbursement.my') }}',
                                    active: {{ request()->routeIs('reimbursement.my*') ? 'true' : 'false' }}
                                },
                            @endif
                        ]
                    },
                @endif
                @if (!$user?->isKasir() && !$user?->isGudang())
                    finance: {
                        title: 'Keuangan',
                        items: [
                            @if (($navTenant?->isModuleEnabled('accounting') ?? true) && $canView('expenses'))
                                {
                                    label: 'Pengeluaran',
                                    href: '{{ route('expenses.index') }}',
                                    active: {{ request()->routeIs('expenses*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if ($navTenant?->isModuleEnabled('invoicing') ?? true)
                                @if ($canView('receivables'))
                                    {
                                        label: 'Piutang (AR)',
                                        href: '{{ route('receivables.index') }}',
                                        active: {{ request()->routeIs('receivables*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('receivables'))
                                    {
                                        label: 'Hutang (AP)',
                                        href: '{{ route('payables.index') }}',
                                        active: {{ request()->routeIs('payables*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('bulk_payments'))
                                    {
                                        label: 'Bulk Payment',
                                        href: '{{ route('bulk-payments.index') }}',
                                        active: {{ request()->routeIs('bulk-payments*') ? 'true' : 'false' }}
                                    },
                                @endif
                            @endif
                            @if (($navTenant?->isModuleEnabled('bank_reconciliation') ?? true) && $canView('bank'))
                                {
                                    label: 'Rekening Bank',
                                    href: '{{ route('bank-accounts.index') }}',
                                    active: {{ request()->routeIs('bank-accounts*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Rekonsiliasi Bank',
                                    href: '{{ route('bank.reconciliation') }}',
                                    active: {{ request()->routeIs('bank.reconciliation*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('budget') ?? true) && $canView('budget'))
                                {
                                    label: 'Anggaran',
                                    href: '{{ route('budget.index') }}',
                                    active: {{ request()->routeIs('budget*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('assets') ?? true) && $canView('assets'))
                                {
                                    label: 'Aset',
                                    href: '{{ route('assets.index') }}',
                                    active: {{ request()->routeIs('assets*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if (($navTenant?->isModuleEnabled('accounting') ?? true) && $canView('accounting'))
                                {
                                    section: 'Akuntansi'
                                },
                                @if ($canView('journals'))
                                    {
                                        label: 'Jurnal',
                                        href: '{{ route('journals.index') }}',
                                        active: {{ request()->routeIs('journals*') ? 'true' : 'false' }}
                                    },
                                @endif {
                                    label: 'Bagan Akun (COA)',
                                    href: '{{ route('accounting.coa') }}',
                                    active: {{ request()->routeIs('accounting.coa*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Neraca Saldo',
                                    href: '{{ route('accounting.trial-balance') }}',
                                    active: {{ request()->routeIs('accounting.trial-balance*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Buku Besar',
                                    href: '{{ route('accounting.general-ledger') }}',
                                    active: {{ request()->routeIs('accounting.general-ledger*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Neraca (Balance Sheet)',
                                    href: '{{ route('accounting.balance-sheet') }}',
                                    active: {{ request()->routeIs('accounting.balance-sheet*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Laba Rugi (P&L)',
                                    href: '{{ route('accounting.income-statement') }}',
                                    active: {{ request()->routeIs('accounting.income-statement*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Arus Kas',
                                    href: '{{ route('accounting.cash-flow') }}',
                                    active: {{ request()->routeIs('accounting.cash-flow*') ? 'true' : 'false' }}
                                },
                                @if ($canView('deferred'))
                                    {
                                        label: 'Amortisasi / Deferral',
                                        href: '{{ route('deferred.index') }}',
                                        active: {{ request()->routeIs('deferred*') ? 'true' : 'false' }}
                                    },
                                @endif
                                @if ($canView('writeoffs'))
                                    {
                                        label: 'Penghapusan Piutang',
                                        href: '{{ route('writeoffs.index') }}',
                                        active: {{ request()->routeIs('writeoffs*') ? 'true' : 'false' }}
                                    },
                                @endif {
                                    label: 'Periode Akuntansi',
                                    href: '{{ route('accounting.periods') }}',
                                    active: {{ request()->routeIs('accounting.periods*') ? 'true' : 'false' }}
                                },
                                @if ($user?->isAdmin())
                                    {
                                        label: 'Kunci Periode & Backup',
                                        href: '{{ route('accounting.period-lock.index') }}',
                                        active: {{ request()->routeIs('accounting.period-lock*') ? 'true' : 'false' }}
                                    },
                                @endif
                            @endif
                        ]
                    },
                    @if (($navTenant?->isModuleEnabled('hotel') ?? true) && !$user?->isKasir() && !$user?->isGudang())
                        {{-- Hotel items merged into settings group below --}}
                    @endif
                    settings: {
                        title: 'Pengaturan',
                        items: [
                            @if (($navTenant?->isModuleEnabled('hotel') ?? true) && !$user?->isKasir() && !$user?->isGudang())
                                {
                                    section: 'Hotel PMS'
                                }, {
                                    label: 'Dashboard Hotel',
                                    href: '{{ route('hotel.dashboard') }}',
                                    active: {{ request()->routeIs('hotel.dashboard') ? 'true' : 'false' }}
                                }, {
                                    label: 'Tipe Kamar',
                                    href: '{{ route('hotel.room-types.index') }}',
                                    active: {{ request()->routeIs('hotel.room-types*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Kamar',
                                    href: '{{ route('hotel.rooms.index') }}',
                                    active: {{ request()->routeIs('hotel.rooms.index', 'hotel.rooms.show', 'hotel.rooms.edit') ? 'true' : 'false' }}
                                }, {
                                    label: 'Ketersediaan Kamar',
                                    href: '{{ route('hotel.rooms.availability') }}',
                                    active: {{ request()->routeIs('hotel.rooms.availability*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Reservasi',
                                    href: '{{ route('hotel.reservations.index') }}',
                                    active: {{ request()->routeIs('hotel.reservations*') && !request()->routeIs('hotel.reservations.checkin*', 'hotel.reservations.checkout*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Tamu',
                                    href: '{{ route('hotel.guests.index') }}',
                                    active: {{ request()->routeIs('hotel.guests*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Check-in / Check-out',
                                    href: '{{ route('hotel.checkin-out.index') }}',
                                    active: {{ request()->routeIs('hotel.checkin-out*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Housekeeping',
                                    href: '{{ route('hotel.housekeeping.room-board') }}',
                                    active: {{ request()->routeIs('hotel.housekeeping*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Tarif Kamar',
                                    href: '{{ route('hotel.rates.index') }}',
                                    active: {{ request()->routeIs('hotel.rates*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Channel Distribution',
                                    href: '{{ route('hotel.channels.index') }}',
                                    active: {{ request()->routeIs('hotel.channels*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pengaturan Hotel',
                                    href: '{{ route('hotel.settings.edit') }}',
                                    active: {{ request()->routeIs('hotel.settings*') ? 'true' : 'false' }}
                                },
                            @endif
                            @if ($user?->isAdmin())
                                {
                                    label: 'Profil Perusahaan',
                                    href: '{{ route('company-profile.index') }}',
                                    active: {{ request()->routeIs('company-profile*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pengaturan Modul',
                                    href: '{{ route('settings.modules.index') }}',
                                    active: {{ request()->routeIs('settings.modules*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Kelola Pengguna',
                                    href: '{{ route('tenant.users.index') }}',
                                    active: {{ request()->routeIs('tenant.users*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pengingat',
                                    href: '{{ route('reminders.index') }}',
                                    active: {{ request()->routeIs('reminders*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Import CSV',
                                    href: '{{ route('import.index') }}',
                                    active: {{ request()->routeIs('import*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Audit Trail',
                                    href: '{{ route('audit.index') }}',
                                    active: {{ request()->routeIs('audit*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Notifikasi',
                                    href: '{{ route('notifications.index') }}',
                                    active: {{ request()->routeIs('notifications*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Bot WA/Telegram',
                                    href: '{{ route('bot.settings') }}',
                                    active: {{ request()->routeIs('bot*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Integrasi API',
                                    href: '{{ route('settings.integrations.index') }}',
                                    active: {{ request()->routeIs('settings.integrations*') ? 'true' : 'false' }}
                                }, {
                                    label: 'API & Webhook',
                                    href: '{{ route('api-settings.index') }}',
                                    active: {{ request()->routeIs('api-settings*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pusat Biaya',
                                    href: '{{ route('cost-centers.index') }}',
                                    active: {{ request()->routeIs('cost-centers*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Memori AI',
                                    href: '{{ route('ai-memory.index') }}',
                                    active: {{ request()->routeIs('ai-memory*') ? 'true' : 'false' }}
                                }, {
                                    section: 'Konfigurasi'
                                }, {
                                    label: 'Pengaturan Akuntansi',
                                    href: '{{ route('settings.accounting') }}',
                                    active: {{ request()->routeIs('settings.accounting*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Pajak',
                                    href: '{{ route('taxes.index') }}',
                                    active: {{ request()->routeIs('taxes*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Custom Fields',
                                    href: '{{ route('custom-fields.index') }}',
                                    active: {{ request()->routeIs('custom-fields*') ? 'true' : 'false' }}
                                }, {
                                    label: 'Batasan Bisnis',
                                    href: '{{ route('constraints.index') }}',
                                    active: {{ request()->routeIs('constraints*') ? 'true' : 'false' }}
                                },
                                @if (($navTenant?->isModuleEnabled('company_groups') ?? true) && $canView('company_groups'))
                                    {
                                        label: 'Grup Perusahaan',
                                        href: '{{ route('company-groups.index') }}',
                                        active: {{ request()->routeIs('company-groups*') ? 'true' : 'false' }}
                                    },
                                @endif
                            @endif {
                                label: 'Langganan',
                                href: '{{ route('subscription.index') }}',
                                active: {{ request()->routeIs('subscription.index') ? 'true' : 'false' }}
                            },
                        ]
                    },
                @endif
            @endif
            profile: {
                title: 'Akun Saya',
                items: [{
                        label: '{{ addslashes($user?->name) }}',
                        href: '{{ route('profile.edit') }}',
                        active: {{ request()->routeIs('profile*') ? 'true' : 'false' }},
                        meta: '{{ $user?->roleLabel() }}'
                    },
                    @if (!$user?->isSuperAdmin() && !$user?->isAffiliate())
                        {
                            label: 'Portal Karyawan',
                            href: '{{ route('self-service.dashboard') }}',
                            active: false
                        },
                    @endif {
                        label: 'Keluar',
                        href: '#logout',
                        active: false,
                        danger: true
                    },
                ]
            },
        };

        const ACTIVE_GROUP = '{{ $activeGroup }}';
    </script>


    <script>
        // ── Sidebar Panel Engine — Orbital Design ────────────────────────
        let currentGroup = null;
        let allPanelItems = [];

        function buildPanel(groupKey) {
            const group = NAV_GROUPS[groupKey];
            if (!group) return;

            // BUG-1.2 FIX: Sync --group-color on BOTH document root AND panel element
            // so rail button glow dot, panel accent line, and panel header all use the same color.
            const btn = document.querySelector(`.rail-btn[data-group="${groupKey}"]`);
            const color = btn?.dataset.color || '#60a5fa';
            const rgb = btn?.dataset.rgb || '96,165,250';

            // Set on root so all CSS var() consumers (rail ::before, panel accent) stay in sync
            document.documentElement.style.setProperty('--group-color', color);
            document.documentElement.style.setProperty('--group-rgb', rgb);

            const panel = document.getElementById('sidebar-panel');
            panel.style.setProperty('--group-color', color);
            panel.style.setProperty('--group-rgb', rgb);
            const accent = document.getElementById('panel-accent');
            if (accent) {
                accent.style.background = color;
                accent.style.boxShadow = `0 0 12px ${color}`;
            }

            document.getElementById('panel-title').textContent = group.title;
            const search = document.getElementById('panel-search');
            if (search) search.value = '';
            allPanelItems = group.items;
            renderPanelItems(group.items);
        }

        // Icon map — maps menu labels to emoji icons for quick visual identification
        const MENU_ICONS = {
            // Dashboard & Overview
            'Dashboard': '📊',
            'KPI Dashboard': '📈',
            'Laporan': '📋',
            'AI Forecasting': '🔮',
            'Proyeksi Arus Kas': '💹',
            'Deteksi Anomali': '🔍',
            'Input Cerdas (AI)': '🤖',
            'Simulasi Keuangan': '🧮',
            'AI Chat': '💬',
            'Analytics': '📊',
            // Contacts
            'Data Customer': '👥',
            'Data Supplier': '🏭',
            'Supplier Scorecard': '⭐',
            'Supplier Performance': '📊',
            'Strategic Sourcing': '🎯',
            // Products & Warehouse
            'Data Produk': '📦',
            'Data Gudang': '🏢',
            'Daftar Harga': '💰',
            'Kategori Produk': '🏷️',
            'Variants Manager': '🔀',
            // Sales & CRM
            'Sales Order': '🛒',
            'Penawaran (Quotation)': '📝',
            'Invoice': '🧾',
            'Surat Jalan': '🚚',
            'Uang Muka (DP)': '💵',
            'Retur Penjualan': '↩️',
            'CRM & Pipeline': '📈',
            'Komisi Sales': '💸',
            'Rule Komisi': '⚙️',
            'Helpdesk': '🎧',
            'Knowledge Base': '📚',
            'Subscription Billing': '🔄',
            'Plan Langganan': '📋',
            'Program Loyalitas': '🎁',
            'Kasir (POS)': '🖥️',
            'E-Commerce': '🛍️',
            // Inventory & Purchasing
            'Inventori': '📦',
            'Transfer Stok': '🔄',
            'Pembelian': '🛍️',
            'Purchase Requisition': '📋',
            'RFQ': '📨',
            'Goods Receipt': '📥',
            '3-Way Matching': '✅',
            'Retur Pembelian': '↩️',
            'Landed Cost': '🚢',
            'Konsinyasi': '🤝',
            'Partner Konsinyasi': '👤',
            'Bulk Payment': '💳',
            // WMS
            'Zone & Bin': '📍',
            'Picking List': '📋',
            'Stock Opname': '🔢',
            'Putaway Rules': '📐',
            'ESP32 / Arduino / RPi': '🔌',
            // Manufacturing & Production
            'Production Dashboard': '🏭',
            'Gantt Chart': '📊',
            'Produksi / WO': '⚙️',
            'QC Inspections': '🔬',
            'QC Laboratory': '🧪',
            'Test Templates': '📝',
            'BOM Multi-Level': '🧩',
            'Mix Design Beton': '🧱',
            'Work Center': '🏗️',
            'MRP Planning': '📅',
            'MRP Accuracy': '🎯',
            'Predictive MRP (AI)': '🤖',
            'Printing Jobs': '🖨️',
            'Batch Production': '🏭',
            // Finance & Accounting
            'Jurnal': '📒',
            'Bagan Akun (COA)': '📑',
            'Neraca Saldo': '📊',
            'Buku Besar': '📖',
            'Neraca (Balance Sheet)': '⚖️',
            'Laba Rugi (P&L)': '📊',
            'Arus Kas': '💧',
            'Rekonsiliasi Bank': '🏦',
            'Rekening Bank': '🏦',
            'Anggaran': '💼',
            'Kunci Periode & Backup': '🔒',
            'Pusat Biaya': '🎯',
            'Pajak': '🏛️',
            'Pengeluaran': '💸',
            'Piutang (AR)': '📥',
            'Hutang (AP)': '📤',
            'Amortisasi / Deferral': '📉',
            'Penghapusan Piutang': '✂️',
            'Periode Akuntansi': '📅',
            'Pengaturan Akuntansi': '⚙️',
            'Aset': '🏠',
            'Konsolidasi': '🔗',
            'Grup Perusahaan': '🏢',
            // HRM & Payroll
            'SDM & Karyawan': '👤',
            'Data Karyawan': '👤',
            'Absensi': '⏰',
            'Absensi Saya': '⏰',
            'Jadwal Shift': '📅',
            'Lembur': '⏱️',
            'Manajemen Cuti': '🏖️',
            'Cuti Saya': '🏖️',
            'Penilaian Kinerja': '⭐',
            'Rekrutmen': '📢',
            'Pelatihan & Sertifikasi': '🎓',
            'Timesheet': '⏱️',
            'Penggajian': '💰',
            'Komponen Gaji': '📊',
            'Slip Gaji': '🧾',
            'Struktur Organisasi': '🏛️',
            'Portal Karyawan': '👤',
            'Surat Peringatan': '⚠️',
            'Kelola Reimbursement': '💳',
            'Reimbursement Saya': '💳',
            'Kontrak': '📄',
            'Template Kontrak': '📑',
            // Documents
            'Dokumen': '📄',
            'Template Dokumen': '📑',
            'Tanda Tangan Digital': '✍️',
            'Import CSV': '📥',
            'Audit Trail': '📜',
            // Settings & Admin
            'Pengaturan': '⚙️',
            'Kelola Pengguna': '👥',
            'Izin Akses': '🔐',
            'Langganan': '💳',
            'Notifikasi': '🔔',
            'Persetujuan': '✅',
            'Approval Workflow': '✅',
            'Automation Builder': '🤖',
            'Custom Fields': '🔧',
            'Batasan Bisnis': '📏',
            'Integrasi': '🔗',
            'Integrasi API': '🌐',
            'API & Webhook': '🌐',
            'Bot WA/Telegram': '💬',
            'Memori AI': '🧠',
            'Pengaturan Modul': '📦',
            'Profil Perusahaan': '🏢',
            'Pengingat': '⏰',
            // Super Admin
            'Semua Tenant': '🏢',
            'Kelola Paket': '📦',
            'Monitoring': '📡',
            'Popup Iklan': '📢',
            'Kelola Affiliate': '🤝',
            'Komisi': '💸',
            'Payout': '💳',
            'Fraud Monitor': '🚨',
            'Pengaturan Platform': '⚙️',
            // Profile
            'Profil Saya': '👤',
            'Keluar': '🚪',
            'Logout': '🚪',
            // Hotel
            'Dashboard Hotel': '🏨',
            'Kamar': '🛏️',
            'Tipe Kamar': '🏷️',
            'Ketersediaan Kamar': '📅',
            'Reservasi': '📅',
            'Tamu': '👤',
            'Check-in / Check-out': '🔑',
            'Housekeeping': '🧹',
            'Tarif Kamar': '💰',
            'Pengaturan Hotel': '⚙️',
            'Reservations': '📅',
            'Guests': '👥',
            'Room Map': '🗺️',
            'Group Bookings': '👥',
            'Bookings': '📅',
            // Healthcare
            'EMR Dashboard': '🏥',
            'Pasien': '🩺',
            'Rawat Inap': '🛏️',
            'Laboratorium': '🔬',
            'Radiologi': '📡',
            'Farmasi': '💊',
            'Operasi': '🏥',
            'Telemedicine': '📱',
            'Antrian': '🎫',
            // Agriculture & Farming
            'Manajemen Lahan': '🌾',
            'Siklus Tanam': '🌱',
            'Pencatatan Panen': '🌽',
            'Analisis Biaya Lahan': '📊',
            'Populasi Ternak': '🐄',
            'Health & Vaccination': '💉',
            'Breeding': '🧬',
            'Dairy Management': '🥛',
            'Poultry Management': '🐔',
            // Fisheries
            'Dashboard Perikanan': '🐟',
            'Fishing Operations': '🎣',
            'Aquaculture': '🐠',
            'Cold Chain': '❄️',
            'Species & Grading': '📊',
            'Export Documentation': '📄',
            'Waste Management': '♻️',
            // Telecom
            'Internet Packages': '📡',
            'Customer Subscriptions': '📋',
            'Network Devices': '🔌',
            'Voucher Management': '🎫',
            // Tour & Travel
            'Tour Packages': '✈️',
            'Tour Bookings': '📅',
            'Tour Analytics': '📊',
            // Shipping & Fleet
            'Pengiriman': '🚚',
            'Fleet Kendaraan': '🚛',
            'Driver': '👨‍✈️',
            'Trip / Penugasan': '🗺️',
            'Log BBM': '⛽',
            'Maintenance': '🔧',
            // Cosmetic
            'Cosmetic Formulas': '🧪',
            'BPOM Registrations': '📋',
            'Channel Distribution': '🚛',
            'Cosmetic Analytics': '📊',
            'Packaging & Labels': '🏷️',
            'Expiry & Recalls': '⚠️',
            'Distribution Channels': '🚛',
            // Construction & Projects
            'Manajemen Proyek': '📐',
            'Project Billing': '💰',
        };

        function getMenuIcon(label) {
            if (MENU_ICONS[label]) return MENU_ICONS[label];
            // Fallback: try partial match for dynamic labels
            const lower = label.toLowerCase();
            if (lower.includes('dashboard')) return '📊';
            if (lower.includes('laporan') || lower.includes('report')) return '📋';
            if (lower.includes('pengaturan') || lower.includes('setting')) return '⚙️';
            if (lower.includes('data ')) return '📁';
            if (lower.includes('manajemen') || lower.includes('kelola')) return '📂';
            return '👤'; // Default: person icon (works for profile name etc.)
        }

        function renderPanelItems(items) {
            const nav = document.getElementById('panel-nav');
            nav.innerHTML = '';
            let activeEl = null;
            items.forEach(item => {
                if (item.section) {
                    const s = document.createElement('div');
                    s.className = 'panel-section';
                    s.textContent = item.section;
                    nav.appendChild(s);
                    return;
                }
                const a = document.createElement('a');
                a.href = item.href === '#logout' ? '#' : item.href;
                a.className = 'panel-link' + (item.active ? ' active' : '');
                if (item.danger) a.style.color = '#f87171';
                const icon = getMenuIcon(item.label);
                let inner = `<span class="panel-icon">${icon}</span>`;
                if (item.meta) inner +=
                    `<span style="display:block;font-size:10px;color:#64748b;margin-bottom:1px">${item.meta}</span>`;
                inner += `<span>${item.label}</span>`;
                if (item.badge && item.badge !== 'null') {
                    inner += `<span class="badge ${item.badgeClass || ''}">${item.badge}</span>`;
                }
                a.innerHTML = inner;
                if (item.href === '#logout') {
                    a.addEventListener('click', e => {
                        e.preventDefault();
                        document.getElementById('logout-form').submit();
                    });
                }
                // Auto-close sidebar on mobile after clicking a link
                if (window.innerWidth < 1024) {
                    a.addEventListener('click', () => closeMobileSidebar());
                }
                nav.appendChild(a);
                if (item.active) activeEl = a;
            });
            // Scroll active item into view so it's visible when panel opens
            if (activeEl) {
                requestAnimationFrame(() => activeEl.scrollIntoView({
                    block: 'nearest',
                    behavior: 'smooth'
                }));
            }
        }

        function filterPanel(q) {
            if (!q.trim()) {
                renderPanelItems(allPanelItems);
                return;
            }
            const filtered = allPanelItems.filter(item =>
                !item.section && item.label.toLowerCase().includes(q.toLowerCase())
            );
            renderPanelItems(filtered);
        }

        function openGroup(groupKey) {
            currentGroup = groupKey;
            buildPanel(groupKey);
            document.getElementById('sidebar-panel').classList.add('panel-open');
            // Only show backdrop on mobile — on desktop the content shifts via padding
            if (window.innerWidth < 1024) {
                document.getElementById('panel-backdrop').classList.remove('hidden');
            }
            document.querySelectorAll('.rail-btn').forEach(b => b.classList.remove('rail-active'));
            const btn = document.querySelector(`.rail-btn[data-group="${groupKey}"]`);
            if (btn) btn.classList.add('rail-active');
            // Clear closed flag when user explicitly opens a group
            sessionStorage.removeItem('sidebar_panel_closed');
        }

        function closePanel() {
            currentGroup = null;
            document.getElementById('sidebar-panel').classList.remove('panel-open');
            document.getElementById('panel-backdrop').classList.add('hidden');
            document.querySelectorAll('.rail-btn').forEach(b => b.classList.remove('rail-active'));
            // Remember that user manually closed the panel
            sessionStorage.setItem('sidebar_panel_closed', '1');
            // On mobile, also close the whole sidebar
            if (window.innerWidth < 1024) {
                document.getElementById('sidebar-rail').classList.remove('mobile-open');
                document.getElementById('sidebar-overlay')?.classList.add('hidden');
            }
        }

        function toggleGroup(groupKey) {
            if (window.innerWidth < 1024) {
                // BUG-1.5 FIX: Mobile mutual exclusion — opening panel closes rail overlay
                document.getElementById('sidebar-rail').classList.remove('mobile-open');
                document.getElementById('sidebar-overlay').classList.add('hidden');
                openGroup(groupKey);
            } else {
                if (currentGroup === groupKey) {
                    closePanel();
                } else {
                    openGroup(groupKey);
                }
            }
        }

        // Auto-open active group on page load
        document.addEventListener('DOMContentLoaded', () => {
            const isMobile = () => window.innerWidth < 1024;
            const panel = document.getElementById('sidebar-panel');
            const main = document.getElementById('main-wrap');

            function updateMainPadding() {
                if (isMobile()) {
                    main.style.paddingLeft = '0px';
                    return;
                }
                const open = panel.classList.contains('panel-open');
                main.style.paddingLeft = open ? '296px' : '56px';
            }

            // Observe panel open/close → shift main content (desktop only)
            const obs = new MutationObserver(() => {
                main.style.transition = 'padding-left 0.26s cubic-bezier(.16,1,.3,1)';
                updateMainPadding();
            });
            obs.observe(panel, {
                attributes: true,
                attributeFilter: ['class']
            });

            // Handle window resize (orientation change, desktop↔mobile)
            window.addEventListener('resize', () => {
                updateMainPadding();
                if (!isMobile()) {
                    // Reset mobile state when going back to desktop
                    document.getElementById('sidebar-rail').classList.remove('mobile-open');
                    document.getElementById('sidebar-overlay')?.classList.add('hidden');
                }
            });

            // Auto-open active group on desktop only IF user hasn't manually closed it
            const panelClosedKey = 'sidebar_panel_closed';
            const userClosed = sessionStorage.getItem(panelClosedKey) === '1';
            if (!isMobile() && ACTIVE_GROUP && NAV_GROUPS[ACTIVE_GROUP] && !userClosed) {
                openGroup(ACTIVE_GROUP);
            }
            updateMainPadding();
        });

        // Notification dropdown
        function toggleNotif() {
            document.getElementById('notif-dropdown')?.classList.toggle('hidden');
        }
        document.addEventListener('click', e => {
            const w = document.getElementById('notif-wrapper');
            if (w && !w.contains(e.target)) {
                document.getElementById('notif-dropdown')?.classList.add('hidden');
            }
        });

        function toggleMobileSidebar() {
            const rail = document.getElementById('sidebar-rail');
            const isOpen = rail.classList.contains('mobile-open');
            if (isOpen) {
                closeMobileSidebar();
            } else {
                // BUG-1.5 FIX: Mutual exclusion — opening rail overlay closes any open panel first
                closePanel();
                rail.classList.add('mobile-open');
                document.getElementById('sidebar-overlay').classList.remove('hidden');
            }
        }

        function closeMobileSidebar() {
            document.getElementById('sidebar-rail').classList.remove('mobile-open');
            document.getElementById('sidebar-overlay').classList.add('hidden');
            closePanel();
        }

        // PWA + Push Notifications
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                    try {
                        const reg = await navigator.serviceWorker.register('/sw.js');

                        // Auto-subscribe to push only when user is authenticated
                        @auth
                        if (Notification.permission === 'granted') {
                            subscribePush(reg);
                        }
                    @endauth
                } catch (e) {}
            });
        }

        // Push notification subscribe/unsubscribe
        async function subscribePush(reg) {
            if (!reg) reg = await navigator.serviceWorker.ready;

            const vapidKey = '{{ \App\Services\WebPushService::vapidPublicKey() }}';
            if (!vapidKey) return;

            try {
                let sub = await reg.pushManager.getSubscription();
                if (!sub) {
                    sub = await reg.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey),
                    });
                }

                // Send subscription to server
                await fetch('{{ route('push.subscribe') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(sub.toJSON()),
                });
            } catch (e) {}
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
            return outputArray;
        }

        // Enable push button handler (called from notification bell area)
        window.enablePushNotifications = async function() {
            const perm = await Notification.requestPermission();
            if (perm === 'granted') {
                const reg = await navigator.serviceWorker.ready;
                await subscribePush(reg);
                // Update UI
                const btn = document.getElementById('btn-enable-push');
                if (btn) {
                    btn.textContent = '✓ Push aktif';
                    btn.disabled = true;
                    btn.classList.add('opacity-50');
                }
            }
        };
    </script>

    {{-- Hidden logout form --}}
    <form id="logout-form" method="POST" action="{{ route('logout') }}" class="hidden">@csrf</form>

    {{-- TASK-015: Contextual Help System Modal --}}
    <div id="help-modal" class="fixed inset-0 z-[9999] hidden" x-data="{ show: false }"
        @show-help.window="show = true; $dispatch('help-show-topic', $event.detail)"
        @keydown.escape.window="show = false">
        <div x-show="show" x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
            x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0" class="fixed inset-0 bg-black/60 backdrop-blur-sm"
            @click="show = false">
        </div>

        <div x-show="show" x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-y-4 scale-95"
            x-transition:enter-end="opacity-100 translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 translate-y-4 scale-95" class="fixed inset-0 z-10 overflow-y-auto"
            @help-show-topic.window="$nextTick(() => loadTopic($event.detail?.topic))">
            <div class="flex min-h-full items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-2xl border border-gray-200 bg-white shadow-2xl">
                    {{-- Header --}}
                    <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-blue-100">
                                <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h3 id="help-title" class="text-lg font-semibold text-gray-900">Bantuan
                            </h3>
                        </div>
                        <button type="button" @click="show = false"
                            class="rounded-lg p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition-colors">
                            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    {{-- Content --}}
                    <div class="px-6 py-5">
                        <div id="help-content" class="prose prose-sm max-w-none">
                            <p class="text-gray-600">Pilih topik bantuan untuk melihat panduan
                                lengkap.</p>
                        </div>

                        {{-- Tips Section --}}
                        <div id="help-tips" class="mt-6 hidden">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900">💡 Tips:</h4>
                            <ul id="help-tips-list" class="space-y-2 text-sm text-gray-700">
                                <!-- Tips will be inserted here -->
                            </ul>
                        </div>

                        {{-- Video Section --}}
                        <div id="help-video" class="mt-6 hidden">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900">🎥 Video Tutorial:
                            </h4>
                            <div class="aspect-video rounded-xl bg-gray-900 flex items-center justify-center">
                                <p class="text-gray-400 text-sm">Video akan tersedia segera</p>
                            </div>
                        </div>

                        {{-- Documentation Link --}}
                        <div id="help-docs" class="mt-6 hidden">
                            <a id="help-docs-link" href="#" target="_blank"
                                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:underline">
                                <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                Lihat dokumentasi lengkap
                            </a>
                        </div>
                    </div>

                    {{-- Footer --}}
                    <div class="border-t border-gray-200 px-6 py-4 flex items-center justify-between">
                        <button type="button" onclick="window.helpSystem?.openSearch()"
                            class="text-sm text-gray-600 hover:text-blue-600 transition-colors">
                            🔍 Cari topik lain
                        </button>
                        <button type="button" @click="show = false"
                            class="rounded-xl bg-blue-600 px-4 py-2 text-sm font-medium text-white hover:bg-blue-700 transition-colors">
                            Mengerti
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @stack('scripts')
</body>

</html>
