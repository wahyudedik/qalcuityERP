<!DOCTYPE html>
<html lang="id" class="h-full dark" id="html-root">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? (View::hasSection('title') ? View::yieldContent('title') : config('app.name', 'Qalcuity ERP'))); ?></title>
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
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/offline-manager.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
    <script>
        if (localStorage.getItem('theme') === 'light') {
            document.getElementById('html-root')?.classList.remove('dark');
        }
    </script>
    <style>
        /* ═══════════════════════════════════════════════
           QALCUITY SIDEBAR — Orbital Design System
           Rail: 56px | Panel: 240px | Frosted glass
        ═══════════════════════════════════════════════ */

        /* Rail */
        #sidebar-rail {
            width: 56px;
            background: linear-gradient(180deg, #080f1e 0%, #0a1628 60%, #080f1e 100%);
            border-right: 1px solid rgba(255,255,255,0.04);
        }

        /* Panel */
        #sidebar-panel {
            width: 240px;
            transform: translateX(-244px);
            transition: transform 0.26s cubic-bezier(.16,1,.3,1), opacity 0.2s;
            opacity: 0;
            pointer-events: none;
            background: rgba(10,18,38,0.97);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-right: 1px solid rgba(255,255,255,0.06);
            box-shadow: 4px 0 32px rgba(0,0,0,0.5);
        }
        #sidebar-panel.panel-open {
            transform: translateX(0);
            opacity: 1;
            pointer-events: auto;
        }

        /* Rail button */
        .rail-btn {
            position: relative;
            width: 40px; height: 40px;
            border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; border: none; background: transparent;
            transition: all 0.18s cubic-bezier(.4,0,.2,1);
            color: #475569;
        }
        .rail-btn:hover { color: #e2e8f0; transform: scale(1.08); }
        .rail-btn.rail-active {
            color: var(--group-color, #60a5fa);
            background: rgba(var(--group-rgb, 96,165,250), 0.12);
        }

        /* Glow dot indicator */
        .rail-btn::before {
            content: '';
            position: absolute; left: -8px; top: 50%; transform: translateY(-50%);
            width: 3px; height: 0; border-radius: 0 3px 3px 0;
            background: var(--group-color, #60a5fa);
            transition: height 0.2s cubic-bezier(.4,0,.2,1);
            box-shadow: 0 0 8px var(--group-color, #60a5fa);
        }
        .rail-btn.rail-active::before { height: 20px; }

        /* Tooltip */
        .rail-btn .rail-tip {
            position: absolute; left: 52px; top: 50%; transform: translateY(-50%);
            background: #1e293b; color: #f1f5f9; font-size: 11px; font-weight: 600;
            padding: 5px 10px; border-radius: 8px; white-space: nowrap;
            pointer-events: none; opacity: 0; transition: opacity 0.15s, left 0.15s;
            border: 1px solid rgba(255,255,255,0.08); z-index: 200;
            box-shadow: 0 4px 16px rgba(0,0,0,0.4);
        }
        .rail-btn:hover .rail-tip { opacity: 1; left: 56px; }

        /* Badge on rail icon */
        .rail-badge {
            position: absolute; top: 4px; right: 4px;
            min-width: 14px; height: 14px; border-radius: 7px;
            background: #ef4444; color: #fff; font-size: 9px; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            padding: 0 3px; border: 1.5px solid #080f1e;
            animation: pulse-badge 2s infinite;
        }
        @keyframes pulse-badge {
            0%,100% { box-shadow: 0 0 0 0 rgba(239,68,68,0.4); }
            50% { box-shadow: 0 0 0 4px rgba(239,68,68,0); }
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
            background: rgba(255,255,255,0.05);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 10px;
            color: #e2e8f0; font-size: 12px;
            padding: 7px 12px 7px 32px;
            width: 100%; outline: none;
            transition: border-color 0.15s, background 0.15s;
        }
        #panel-search:focus {
            border-color: rgba(var(--group-rgb, 96,165,250), 0.4);
            background: rgba(255,255,255,0.07);
        }
        #panel-search::placeholder { color: #475569; }

        /* Panel nav link */
        .panel-link {
            display: flex; align-items: center; gap: 9px;
            padding: 6px 12px; border-radius: 9px; font-size: 12.5px; font-weight: 500;
            color: #64748b; transition: all 0.15s; cursor: pointer;
            text-decoration: none; position: relative; margin: 1px 0;
        }
        .panel-link:hover {
            background: rgba(255,255,255,0.06);
            color: #cbd5e1;
            padding-left: 16px;
        }
        .panel-link.active {
            background: rgba(var(--group-rgb, 96,165,250), 0.12);
            color: var(--group-color, #60a5fa);
            font-weight: 600;
        }
        .panel-link.active::before {
            content: '';
            position: absolute; left: 0; top: 50%; transform: translateY(-50%);
            width: 2px; height: 14px; border-radius: 0 2px 2px 0;
            background: var(--group-color, #60a5fa);
        }

        /* Panel section label */
        .panel-section {
            font-size: 9.5px; font-weight: 700; letter-spacing: 0.1em;
            text-transform: uppercase; color: #334155;
            padding: 12px 12px 3px; margin-top: 4px;
        }

        /* Badge */
        .panel-link .badge {
            margin-left: auto; font-size: 10px; font-weight: 700;
            padding: 1px 6px; border-radius: 20px;
            background: rgba(245,158,11,0.15); color: #fbbf24;
            border: 1px solid rgba(245,158,11,0.2);
        }
        .panel-link .badge.badge-red {
            background: rgba(239,68,68,0.15); color: #f87171;
            border-color: rgba(239,68,68,0.2);
        }

        /* Scrollbar */
        .scrollbar-thin::-webkit-scrollbar { width: 3px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.08); border-radius: 4px; }

        /* Logo glow */
        #rail-logo:hover { filter: drop-shadow(0 0 8px rgba(96,165,250,0.6)); }

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
                border-top: 1px solid rgba(255,255,255,0.06) !important;
                transform: translateY(100%) !important;
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
            }
            #main-wrap { padding-left: 0 !important; transition: none !important; }
            #panel-backdrop { display: none !important; }
            .rail-btn .rail-tip { display: none !important; }
            .rail-btn::before { display: none !important; }
        }

        /* Light mode overrides */
        html:not(.dark) #sidebar-rail {
            background: linear-gradient(180deg, #1e293b 0%, #1a2744 100%);
        }
        html:not(.dark) #sidebar-panel {
            background: rgba(248,250,252,0.98);
            border-color: #e2e8f0;
            box-shadow: 4px 0 24px rgba(0,0,0,0.08);
        }
        html:not(.dark) .panel-link { color: #64748b; }
        html:not(.dark) .panel-link:hover { background: #f1f5f9; color: #1e293b; }
        html:not(.dark) .panel-link.active { background: rgba(var(--group-rgb,59,130,246),0.08); color: var(--group-color,#2563eb); }
        html:not(.dark) .panel-section { color: #94a3b8; }
        html:not(.dark) #panel-search { background: #f1f5f9; border-color: #e2e8f0; color: #1e293b; }
        html:not(.dark) #panel-search::placeholder { color: #94a3b8; }
    </style>
</head>
<body class="h-full font-[Inter,sans-serif] antialiased bg-[#f8f8f8] dark:bg-[#0f172a] text-gray-900 dark:text-gray-100">
<div class="flex h-full">

    
    <aside id="sidebar-rail"
        class="fixed inset-y-0 left-0 z-50 flex flex-col items-center py-3 gap-0.5 shrink-0
               -translate-x-full lg:translate-x-0 transition-transform duration-300">

        
        <a href="<?php echo e(route('dashboard')); ?>" id="rail-logo"
            class="flex items-center justify-center w-9 h-9 mb-3 rounded-xl transition-all duration-200">
            <img src="/logo.png" alt="Q" class="h-6 w-auto object-contain" style="filter: brightness(0) invert(1);">
        </a>

        <?php
        $user      = auth()->user();
        $navTenant = $user?->tenant;
        // Active group detection
        // IMPORTANT: Order matters — more specific patterns must come before generic ones.
        // Each route prefix should appear in exactly ONE group to avoid conflicts.
        $activeGroup = match(true) {
            request()->routeIs('dashboard')                                                    => 'home',
            request()->routeIs('chat*')                                                        => 'ai',
            request()->routeIs('quotations*','invoices*','delivery-orders*','down-payments*',
                               'sales-returns*','crm*','loyalty*','pos*','commission*',
                               'helpdesk*','subscription-billing*','sales.*','sales.index',
                               'price-lists*')                                                 => 'sales',
            request()->routeIs('inventory*','purchasing*','purchase-returns*','landed-cost*',
                               'consignment*','wms*')                                          => 'inventory',
            request()->routeIs('customers*','suppliers*','products*','warehouses*')             => 'masterdata',
            request()->routeIs('production*','manufacturing*','fleet*','contracts*','shipping*',
                               'approvals*','ecommerce*','documents*','projects*','timesheets*',
                               'project-billing*','farm*')                                     => 'ops',
            request()->routeIs('hrm*','payroll*','self-service*','reimbursement*')              => 'hrm',
            request()->routeIs('accounting*','expenses*','bank.*','bank-accounts*',
                               'receivables*','payables*','bulk-payments*','assets*','budget*',
                               'journals*','deferred*','writeoffs*')                           => 'finance',
            request()->routeIs('reports*','kpi*','anomalies*','zero-input*','simulations*',
                               'forecast*')                                                    => 'analytics',
            request()->routeIs('company-profile*','settings*','tenant.users*','reminders*',
                               'import*','audit*','notifications*','bot*','api-settings*',
                               'subscription.index','cost-centers*','ai-memory*','taxes*',
                               'custom-fields*','constraints*','company-groups*')               => 'settings',
            request()->routeIs('super-admin*')                                                 => 'superadmin',
            default                                                                            => '',
        };
        ?>

        <?php if($user?->isSuperAdmin()): ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'home',       'icon'=>'home',       'label'=>'Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'superadmin', 'icon'=>'building',   'label'=>'Admin'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php elseif($user?->isAffiliate()): ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'home',      'icon'=>'home',      'label'=>'Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
        <?php else: ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'home',      'icon'=>'home',      'label'=>'Dashboard'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'ai',        'icon'=>'sparkle',   'label'=>'AI Chat'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'masterdata','icon'=>'database',  'label'=>'Master Data'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(!$user?->isGudang()): ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'sales',     'icon'=>'tag',       'label'=>'Penjualan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'inventory', 'icon'=>'cube',      'label'=>'Inventori'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'ops',       'icon'=>'cog',       'label'=>'Operasional'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'hrm',       'icon'=>'users',     'label'=>'SDM'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'finance',   'icon'=>'currency',  'label'=>'Keuangan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'analytics', 'icon'=>'chart',     'label'=>'Analitik'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php echo $__env->make('layouts._rail_btn', ['group'=>'settings',  'icon'=>'gear',      'label'=>'Pengaturan'], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endif; ?>
        <?php endif; ?>

        
        <div class="flex-1"></div>

        
        <button onclick="toggleGroup('profile')"
            class="rail-btn w-9 h-9 rounded-full overflow-hidden ring-2 ring-white/10 hover:ring-blue-500/50 transition mb-1 relative"
            data-group="profile" data-color="#60a5fa" data-rgb="96,165,250"
            style="--group-color:#60a5fa;--group-rgb:96,165,250">
            <img src="<?php echo e($user?->avatarUrl()); ?>" alt="<?php echo e($user?->name); ?>" class="w-full h-full object-cover">
            <span class="rail-tip"><?php echo e($user?->name); ?></span>
        </button>
    </aside>

    
    <div id="sidebar-panel"
        class="fixed inset-y-0 left-0 lg:left-14 z-40 flex flex-col overflow-hidden">

        
        <div id="panel-accent"></div>

        
        <div class="flex items-center justify-between px-4 h-14 border-b border-white/10 shrink-0">
            <span id="panel-title" class="text-xs font-bold uppercase tracking-widest text-slate-400"></span>
            <button onclick="closePanel()" class="text-slate-600 hover:text-white transition p-1.5 rounded-lg hover:bg-white/10">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        
        <div class="px-3 py-2.5 border-b border-white/5 shrink-0">
            <div class="relative">
                <svg class="absolute left-2.5 top-1/2 -translate-y-1/2 w-3.5 h-3.5 text-slate-500 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input id="panel-search" type="text" placeholder="Cari menu..." oninput="filterPanel(this.value)">
            </div>
        </div>

        
        <nav class="flex-1 overflow-y-auto scrollbar-thin py-2 px-2" id="panel-nav">
            
        </nav>
    </div>

    
    <div id="panel-backdrop" class="fixed inset-0 z-30 hidden" onclick="closePanel()"></div>

    
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-black/50 hidden lg:hidden" onclick="closeMobileSidebar()" style="pointer-events:auto"></div>


    
    <div class="flex-1 flex flex-col min-w-0 pl-0 lg:pl-14" id="main-wrap">

        
        <header class="sticky top-0 z-20 h-14 backdrop-blur border-b flex items-center px-4 sm:px-6 gap-4
                       bg-[#f0f0f0]/95 dark:bg-[#0f172a]/95 border-gray-200 dark:border-white/10">

            
            <button onclick="toggleMobileSidebar()" class="lg:hidden p-2 rounded-lg hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-gray-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
            </button>

            
            <div class="flex-1 flex items-center gap-2 min-w-0">
                <span class="text-xs text-slate-400 hidden sm:block"><?php echo e(config('app.name')); ?></span>
                <span class="text-xs text-slate-600 hidden sm:block">/</span>
                <?php if(isset($header)): ?>
                    <?php if(is_string($header) && !str_contains($header, '<')): ?>
                        <h1 class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?php echo e($header); ?></h1>
                    <?php else: ?>
                        <?php echo $header; ?>

                    <?php endif; ?>
                <?php elseif(View::hasSection('header')): ?>
                    <h1 class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?php echo $__env->yieldContent('header'); ?></h1>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-1.5 shrink-0">
                <?php if(isset($topbarActions)): ?><div class="hidden sm:flex items-center gap-1.5"><?php echo e($topbarActions); ?></div><?php endif; ?>

                
                <div id="offline-indicator" class="hidden items-center gap-1.5 px-2.5 py-1.5 rounded-xl bg-amber-500/10 border border-amber-500/20 text-amber-600 dark:text-amber-400 text-xs font-medium" data-pending="0">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-amber-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-amber-500"></span>
                    </span>
                    <span>Offline</span>
                    <span id="offline-sync-badge" class="hidden ml-1 px-1.5 py-0.5 rounded-full bg-amber-500/20 text-[10px] font-bold">0</span>
                </div>

                
                <button id="theme-toggle" title="Ganti tema"
                    class="p-2 rounded-xl transition hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-slate-400">
                    <svg id="icon-sun" class="w-5 h-5 hidden dark:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707M17.657 17.657l-.707-.707M6.343 6.343l-.707-.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <svg id="icon-moon" class="w-5 h-5 block dark:hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"/>
                    </svg>
                </button>

                
                <?php
                    $authUser      = auth()->user();
                    $notifTenantId = $authUser?->tenant_id;
                    $unreadCount   = $notifTenantId
                        ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)->whereNull('read_at')->count()
                        : ($authUser?->isSuperAdmin() ? \App\Models\ErpNotification::where('user_id', $authUser->id)->whereNull('read_at')->count() : 0);
                ?>
                <div class="relative" id="notif-wrapper">
                    <button onclick="toggleNotif()" class="relative p-2 rounded-xl hover:bg-[#e4e4e4] dark:hover:bg-white/10 text-gray-500 dark:text-gray-400 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <?php if($unreadCount > 0): ?>
                        <span class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white dark:ring-[#0f172a]"></span>
                        <?php endif; ?>
                    </button>
                    <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 rounded-2xl shadow-xl border overflow-hidden z-50
                               bg-white dark:bg-[#1e293b] border-gray-200 dark:border-white/10">
                        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100 dark:border-white/10">
                            <span class="font-semibold text-sm text-gray-900 dark:text-white">Notifikasi</span>
                            <a href="<?php echo e(route('notifications.index')); ?>" class="text-xs text-blue-500 dark:text-blue-400 hover:underline">Lihat semua</a>
                        </div>
                        <div class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-white/5">
                            <?php
                                $topbarNotifs = $notifTenantId
                                    ? \App\Models\ErpNotification::where('tenant_id', $notifTenantId)->latest()->take(5)->get()
                                    : ($authUser?->isSuperAdmin() ? \App\Models\ErpNotification::where('user_id', $authUser->id)->latest()->take(5)->get() : collect());
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $topbarNotifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="px-4 py-3 hover:bg-[#f0f0f0] dark:hover:bg-white/5 <?php echo e($notif->isRead() ? 'opacity-60' : ''); ?>">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($notif->title); ?></p>
                                <p class="text-xs text-slate-400 mt-0.5"><?php echo e(Str::limit($notif->body, 80)); ?></p>
                                <p class="text-xs text-slate-500 mt-1"><?php echo e($notif->created_at->diffForHumans()); ?></p>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi</div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="px-4 py-2.5 border-t border-gray-100 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                            <button id="btn-enable-push" onclick="enablePushNotifications()"
                                class="w-full text-xs text-center py-1.5 rounded-lg text-blue-600 dark:text-blue-400 hover:bg-blue-50 dark:hover:bg-blue-500/10 transition">
                                🔔 Aktifkan Notifikasi Push
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        
        <main class="flex-1 p-4 sm:p-6 bg-[#f8f8f8] dark:bg-[#0f172a]">
            <?php if(session('success')): ?>
            <div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-600 dark:text-green-400 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>
            <?php if(session('warning')): ?>
            <div class="mb-4 flex items-start gap-3 bg-amber-500/10 border border-amber-500/20 text-amber-700 dark:text-amber-400 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span><?php echo e(session('warning')); ?></span>
            </div>
            <?php endif; ?>
            <?php if(session('error')): ?>
            <div class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-600 dark:text-red-400 text-sm px-4 py-3 rounded-xl">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                <?php echo e(session('error')); ?>

            </div>
            <?php endif; ?>
            <?php echo e($slot ?? ''); ?>

            <?php if (! empty(trim($__env->yieldContent('content')))): ?>
                <?php echo $__env->yieldContent('content'); ?>
            <?php endif; ?>
        </main>
    </div>
</div>



<?php
    // Permission helper for sidebar — only for non-admin/non-superadmin
    $ps = app(\App\Services\PermissionService::class);
    $canView = function(string $module) use ($user, $ps): bool {
        if (!$user || $user->isAdmin() || $user->isSuperAdmin()) return true;
        return $ps->check($user, $module, 'view');
    };
?>
<script>
const NAV_GROUPS = {
<?php if($user?->isSuperAdmin()): ?>
  home: {
    title: 'Dashboard',
    items: [
      { label: 'Dashboard', href: '<?php echo e(route("dashboard")); ?>', active: <?php echo e(request()->routeIs('dashboard') ? 'true' : 'false'); ?> },
    ]
  },
  superadmin: {
    title: 'Super Admin',
    items: [
      { label: 'Semua Tenant',  href: '<?php echo e(route("super-admin.tenants.index")); ?>', active: <?php echo e(request()->routeIs('super-admin.tenants*') ? 'true' : 'false'); ?> },
      { label: 'Kelola Paket',  href: '<?php echo e(route("super-admin.plans.index")); ?>',   active: <?php echo e(request()->routeIs('super-admin.plans*') ? 'true' : 'false'); ?> },
      { section: 'Monitoring' },
      { label: 'Monitoring',    href: '<?php echo e(route("super-admin.monitoring.index")); ?>', active: <?php echo e(request()->routeIs('super-admin.monitoring*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\ErrorLog::where('is_resolved',false)->count() ?: 'null'); ?>, badgeClass: 'badge-red' },
      { section: 'Afiliasi' },
      { label: 'Kelola Affiliate', href: '<?php echo e(route("super-admin.affiliates.index")); ?>', active: <?php echo e(request()->routeIs('super-admin.affiliates.index') ? 'true' : 'false'); ?> },
      { label: 'Komisi',          href: '<?php echo e(route("super-admin.affiliates.commissions")); ?>', active: <?php echo e(request()->routeIs('super-admin.affiliates.commissions*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\AffiliateCommission::where('status','pending')->count() ?: 'null'); ?>, badgeClass: 'badge-amber' },
      { label: 'Payout',          href: '<?php echo e(route("super-admin.affiliates.payouts")); ?>', active: <?php echo e(request()->routeIs('super-admin.affiliates.payouts*') ? 'true' : 'false'); ?> },
      { label: 'Fraud Monitor',   href: '<?php echo e(route("super-admin.affiliates.audit-logs")); ?>', active: <?php echo e(request()->routeIs('super-admin.affiliates.audit-logs*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\AffiliateAuditLog::where('severity','fraud')->where('created_at','>=',now()->subDays(7))->count() ?: 'null'); ?>, badgeClass: 'badge-red' },
    ]
  },
<?php elseif($user?->isAffiliate()): ?>
  home: {
    title: 'Affiliate',
    items: [
      { label: 'Dashboard', href: '<?php echo e(route("affiliate.dashboard")); ?>', active: <?php echo e(request()->routeIs('affiliate.dashboard') ? 'true' : 'false'); ?> },
    ]
  },
<?php else: ?>
  home: {
    title: 'Dashboard',
    items: [
      { label: 'Dashboard', href: '<?php echo e(route("dashboard")); ?>', active: <?php echo e(request()->routeIs('dashboard') ? 'true' : 'false'); ?> },
    ]
  },
  ai: {
    title: 'AI Chat',
    items: [
      { label: 'AI Chat', href: '<?php echo e(route("chat.index")); ?>', active: <?php echo e(request()->routeIs('chat*') ? 'true' : 'false'); ?> },
    ]
  },
  masterdata: {
    title: 'Master Data',
    items: [
      { section: 'Kontak' },
      <?php if($canView('customers')): ?> { label: 'Data Customer',  href: '<?php echo e(route("customers.index")); ?>',  active: <?php echo e(request()->routeIs('customers*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('suppliers')): ?> { label: 'Data Supplier',  href: '<?php echo e(route("suppliers.index")); ?>',  active: <?php echo e(request()->routeIs('suppliers*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      { section: 'Produk & Gudang' },
      <?php if($canView('products')): ?>  { label: 'Data Produk',    href: '<?php echo e(route("products.index")); ?>',   active: <?php echo e(request()->routeIs('products*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('warehouses')): ?>{ label: 'Data Gudang',    href: '<?php echo e(route("warehouses.index")); ?>', active: <?php echo e(request()->routeIs('warehouses*') ? 'true' : 'false'); ?> }, <?php endif; ?>
    ]
  },
<?php if(!$user?->isGudang()): ?>
  sales: {
    title: 'Penjualan',
    items: [
      { section: 'Transaksi' },
<?php if($navTenant?->isModuleEnabled('invoicing') ?? true): ?>
      <?php if($canView('sales')): ?>      { label: 'Sales Order',           href: '<?php echo e(route("sales.index")); ?>',            active: <?php echo e(request()->routeIs('sales.index','sales.create','sales.show','sales.store') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('invoices')): ?>   { label: 'Penawaran (Quotation)', href: '<?php echo e(route("quotations.index")); ?>',       active: <?php echo e(request()->routeIs('quotations*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('invoices')): ?>   { label: 'Invoice',               href: '<?php echo e(route("invoices.index")); ?>',         active: <?php echo e(request()->routeIs('invoices*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('delivery')): ?>   { label: 'Surat Jalan',           href: '<?php echo e(route("delivery-orders.index")); ?>',  active: <?php echo e(request()->routeIs('delivery-orders*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('down_payments')): ?> { label: 'Uang Muka (DP)',     href: '<?php echo e(route("down-payments.index")); ?>',    active: <?php echo e(request()->routeIs('down-payments*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('sales_returns')): ?> { label: 'Retur Penjualan',    href: '<?php echo e(route("sales-returns.index")); ?>',    active: <?php echo e(request()->routeIs('sales-returns*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('price_lists')): ?> { label: 'Daftar Harga',         href: '<?php echo e(route("price-lists.index")); ?>',      active: <?php echo e(request()->routeIs('price-lists*') ? 'true' : 'false'); ?> }, <?php endif; ?>
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('crm') ?? true) && $canView('crm')): ?>
      { label: 'CRM & Pipeline',        href: '<?php echo e(route("crm.index")); ?>',              active: <?php echo e(request()->routeIs('crm*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('commission') ?? true) && $canView('commission')): ?>
      { label: 'Komisi Sales',          href: '<?php echo e(route("commission.index")); ?>',        active: <?php echo e(request()->routeIs('commission.index') ? 'true' : 'false'); ?> },
      { label: 'Rule Komisi',           href: '<?php echo e(route("commission.rules")); ?>',        active: <?php echo e(request()->routeIs('commission.rules*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('helpdesk') ?? true) && $canView('helpdesk')): ?>
      { label: 'Helpdesk',             href: '<?php echo e(route("helpdesk.index")); ?>',           active: <?php echo e(request()->routeIs('helpdesk.index') || request()->routeIs('helpdesk.show') ? 'true' : 'false'); ?> },
      { label: 'Knowledge Base',       href: '<?php echo e(route("helpdesk.kb")); ?>',              active: <?php echo e(request()->routeIs('helpdesk.kb*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('subscription_billing') ?? true) && $canView('subscription_billing')): ?>
      { label: 'Subscription Billing', href: '<?php echo e(route("subscription-billing.index")); ?>', active: <?php echo e(request()->routeIs('subscription-billing.index') || request()->routeIs('subscription-billing.show') ? 'true' : 'false'); ?> },
      { label: 'Plan Langganan',       href: '<?php echo e(route("subscription-billing.plans")); ?>', active: <?php echo e(request()->routeIs('subscription-billing.plans*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('loyalty') ?? true) && $canView('loyalty')): ?>
      { label: 'Program Loyalitas',     href: '<?php echo e(route("loyalty.index")); ?>',          active: <?php echo e(request()->routeIs('loyalty*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos')): ?>
      { label: 'Kasir (POS)',           href: '<?php echo e(route("pos.index")); ?>',              active: <?php echo e(request()->routeIs('pos*') ? 'true' : 'false'); ?> },
<?php endif; ?>
    ]
  },
<?php endif; ?>
  inventory: {
    title: 'Inventori',
    items: [
<?php if(($navTenant?->isModuleEnabled('inventory') ?? true) && $canView('inventory')): ?>
      { label: 'Inventori',             href: '<?php echo e(route("inventory.index")); ?>',        active: <?php echo e(request()->routeIs('inventory.index') ? 'true' : 'false'); ?> },
      { label: 'Transfer Stok',         href: '<?php echo e(route("inventory.transfers.index")); ?>', active: <?php echo e(request()->routeIs('inventory.transfers*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($user?->isAdmin() || $user?->isManager()) && ($navTenant?->isModuleEnabled('purchasing') ?? true) && $canView('purchasing')): ?>
      { section: 'Pembelian' },
      { label: 'Pembelian',             href: '<?php echo e(route("purchasing.orders")); ?>',       active: <?php echo e(request()->routeIs('purchasing.orders*') ? 'true' : 'false'); ?> },
      { label: 'Purchase Requisition',  href: '<?php echo e(route("purchasing.requisitions")); ?>',active: <?php echo e(request()->routeIs('purchasing.requisitions*') ? 'true' : 'false'); ?> },
      { label: 'RFQ',                   href: '<?php echo e(route("purchasing.rfq")); ?>',         active: <?php echo e(request()->routeIs('purchasing.rfq*') ? 'true' : 'false'); ?> },
      { label: 'Goods Receipt',         href: '<?php echo e(route("purchasing.goods-receipts")); ?>', active: <?php echo e(request()->routeIs('purchasing.goods-receipts*') ? 'true' : 'false'); ?> },
      { label: '3-Way Matching',        href: '<?php echo e(route("purchasing.matching")); ?>',    active: <?php echo e(request()->routeIs('purchasing.matching*') ? 'true' : 'false'); ?> },
      { label: 'Retur Pembelian',       href: '<?php echo e(route("purchase-returns.index")); ?>', active: <?php echo e(request()->routeIs('purchase-returns*') ? 'true' : 'false'); ?> },
<?php if(($navTenant?->isModuleEnabled('landed_cost') ?? true) && $canView('landed_cost')): ?>
      { label: 'Landed Cost',          href: '<?php echo e(route("landed-cost.index")); ?>',       active: <?php echo e(request()->routeIs('landed-cost*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('consignment') ?? true) && $canView('consignment')): ?>
      { label: 'Konsinyasi',           href: '<?php echo e(route("consignment.index")); ?>',       active: <?php echo e(request()->routeIs('consignment.index') || request()->routeIs('consignment.shipments*') ? 'true' : 'false'); ?> },
      { label: 'Partner Konsinyasi',   href: '<?php echo e(route("consignment.partners")); ?>',    active: <?php echo e(request()->routeIs('consignment.partners*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('wms') ?? true) && $canView('wms')): ?>
      { section: 'WMS Gudang' },
      { label: 'Zone & Bin',           href: '<?php echo e(route("wms.index")); ?>',               active: <?php echo e(request()->routeIs('wms.index') ? 'true' : 'false'); ?> },
      { label: 'Picking List',         href: '<?php echo e(route("wms.picking")); ?>',             active: <?php echo e(request()->routeIs('wms.picking*') ? 'true' : 'false'); ?> },
      { label: 'Stock Opname',         href: '<?php echo e(route("wms.opname")); ?>',              active: <?php echo e(request()->routeIs('wms.opname*') ? 'true' : 'false'); ?> },
      { label: 'Putaway Rules',        href: '<?php echo e(route("wms.putaway-rules")); ?>',       active: <?php echo e(request()->routeIs('wms.putaway-rules*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php endif; ?>
    ]
  },
<?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
  ops: {
    title: 'Operasional',
    items: [
<?php if(($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos')): ?>
      { label: 'Kasir (POS)',           href: '<?php echo e(route("pos.index")); ?>',              active: <?php echo e(request()->routeIs('pos*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('production') ?? true) && $canView('production')): ?>
      { label: 'Produksi / WO',         href: '<?php echo e(route("production.index")); ?>',       active: <?php echo e(request()->routeIs('production*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('manufacturing') ?? true) && $canView('manufacturing')): ?>
      { label: 'BOM Multi-Level',       href: '<?php echo e(route("manufacturing.bom")); ?>',      active: <?php echo e(request()->routeIs('manufacturing.bom*') ? 'true' : 'false'); ?> },
      { label: 'Mix Design Beton',      href: '<?php echo e(route("manufacturing.mix-design")); ?>', active: <?php echo e(request()->routeIs('manufacturing.mix-design*') ? 'true' : 'false'); ?> },
      { label: 'Work Center',           href: '<?php echo e(route("manufacturing.work-centers")); ?>', active: <?php echo e(request()->routeIs('manufacturing.work-centers*') ? 'true' : 'false'); ?> },
      { label: 'MRP Planning',          href: '<?php echo e(route("manufacturing.mrp")); ?>',      active: <?php echo e(request()->routeIs('manufacturing.mrp*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('fleet') ?? true) && $canView('fleet')): ?>
      { label: 'Fleet Kendaraan',       href: '<?php echo e(route("fleet.index")); ?>',            active: <?php echo e(request()->routeIs('fleet.index') ? 'true' : 'false'); ?> },
      { label: 'Driver',                href: '<?php echo e(route("fleet.drivers")); ?>',          active: <?php echo e(request()->routeIs('fleet.drivers*') ? 'true' : 'false'); ?> },
      { label: 'Trip / Penugasan',      href: '<?php echo e(route("fleet.trips")); ?>',            active: <?php echo e(request()->routeIs('fleet.trips*') ? 'true' : 'false'); ?> },
      { label: 'Log BBM',               href: '<?php echo e(route("fleet.fuel-logs")); ?>',        active: <?php echo e(request()->routeIs('fleet.fuel-logs*') ? 'true' : 'false'); ?> },
      { label: 'Maintenance',           href: '<?php echo e(route("fleet.maintenance")); ?>',      active: <?php echo e(request()->routeIs('fleet.maintenance*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      <?php if($canView('shipping')): ?>      { label: 'Pengiriman',            href: '<?php echo e(route("shipping.index")); ?>',         active: <?php echo e(request()->routeIs('shipping*') ? 'true' : 'false'); ?> }, <?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('agriculture') ?? true)): ?>
      { label: 'Manajemen Lahan',       href: '<?php echo e(route("farm.plots")); ?>',             active: <?php echo e(request()->routeIs('farm.plots*') ? 'true' : 'false'); ?> },
      { label: 'Siklus Tanam',          href: '<?php echo e(route("farm.cycles")); ?>',            active: <?php echo e(request()->routeIs('farm.cycles*') ? 'true' : 'false'); ?> },
      { label: 'Pencatatan Panen',      href: '<?php echo e(route("farm.harvests")); ?>',           active: <?php echo e(request()->routeIs('farm.harvests*') ? 'true' : 'false'); ?> },
      { label: 'Analisis Biaya Lahan',  href: '<?php echo e(route("farm.analytics")); ?>',          active: <?php echo e(request()->routeIs('farm.analytics*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('contracts') ?? true) && $canView('contracts')): ?>
      { label: 'Kontrak',               href: '<?php echo e(route("contracts.index")); ?>',        active: <?php echo e(request()->routeIs('contracts.index') || request()->routeIs('contracts.show') ? 'true' : 'false'); ?> },
      { label: 'Template Kontrak',      href: '<?php echo e(route("contracts.templates")); ?>',    active: <?php echo e(request()->routeIs('contracts.templates*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      <?php if($canView('approvals')): ?>     { label: 'Persetujuan',           href: '<?php echo e(route("approvals.index")); ?>',        active: <?php echo e(request()->routeIs('approvals*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\ApprovalRequest::where('tenant_id', $user?->tenant_id ?? 0)->where('status','pending')->count() ?: 'null'); ?> }, <?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('ecommerce') ?? true) && $canView('ecommerce')): ?>
      { label: 'E-Commerce',            href: '<?php echo e(route("ecommerce.index")); ?>',        active: <?php echo e(request()->routeIs('ecommerce*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      <?php if($canView('documents')): ?>     { label: 'Dokumen',               href: '<?php echo e(route("documents.index")); ?>',        active: <?php echo e(request()->routeIs('documents*') ? 'true' : 'false'); ?> }, <?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('projects') ?? true) && $canView('projects')): ?>
      { label: 'Manajemen Proyek',      href: '<?php echo e(route("projects.index")); ?>',         active: <?php echo e(request()->routeIs('projects*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('project_billing') ?? true) && $canView('project_billing')): ?>
      { label: 'Project Billing',        href: '#',                                     active: <?php echo e(request()->routeIs('project-billing*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      <?php if($canView('timesheets')): ?>    { label: 'Timesheet',             href: '<?php echo e(route("timesheets.index")); ?>',       active: <?php echo e(request()->routeIs('timesheets*') ? 'true' : 'false'); ?> }, <?php endif; ?>
    ]
  },
<?php endif; ?>
  hrm: {
    title: 'SDM & Karyawan',
    items: [
<?php if($user?->isAdmin() || $user?->isManager()): ?>
<?php if(($navTenant?->isModuleEnabled('hrm') ?? true) && $canView('hrm')): ?>
      { section: 'Manajemen SDM' },
      { label: 'Rekrutmen',             href: '<?php echo e(route("hrm.recruitment.index")); ?>',  active: <?php echo e(request()->routeIs('hrm.recruitment*','hrm.onboarding*') ? 'true' : 'false'); ?> },
      { label: 'SDM & Karyawan',        href: '<?php echo e(route("hrm.index")); ?>',              active: <?php echo e(request()->routeIs('hrm.index','hrm.store','hrm.update','hrm.destroy','hrm.attendance*') ? 'true' : 'false'); ?> },
      { label: 'Manajemen Cuti',        href: '<?php echo e(route("hrm.leave")); ?>',              active: <?php echo e(request()->routeIs('hrm.leave*') ? 'true' : 'false'); ?> },
      { label: 'Penilaian Kinerja',     href: '<?php echo e(route("hrm.performance")); ?>',        active: <?php echo e(request()->routeIs('hrm.performance*') ? 'true' : 'false'); ?> },
      { label: 'Struktur Organisasi',   href: '<?php echo e(route("hrm.orgchart")); ?>',           active: <?php echo e(request()->routeIs('hrm.orgchart') ? 'true' : 'false'); ?> },
      { label: 'Jadwal Shift',          href: '<?php echo e(route("hrm.shifts.index")); ?>',       active: <?php echo e(request()->routeIs('hrm.shifts*') ? 'true' : 'false'); ?> },
      { label: 'Lembur',                href: '<?php echo e(route("hrm.overtime.index")); ?>',     active: <?php echo e(request()->routeIs('hrm.overtime*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\OvertimeRequest::where('tenant_id',$user?->tenant_id??0)->where('status','pending')->count() ?: 'null'); ?> },
      { label: 'Pelatihan & Sertifikasi', href: '<?php echo e(route("hrm.training.index")); ?>',  active: <?php echo e(request()->routeIs('hrm.training*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\EmployeeCertification::where('tenant_id',$user?->tenant_id??0)->where('status','active')->whereNotNull('expiry_date')->where('expiry_date','<=',now()->addDays(90))->count() ?: 'null'); ?>, badgeClass: 'badge-red' },
      { label: 'Surat Peringatan',      href: '<?php echo e(route("hrm.disciplinary.index")); ?>', active: <?php echo e(request()->routeIs('hrm.disciplinary*') ? 'true' : 'false'); ?>, badge: <?php echo e(\App\Models\DisciplinaryLetter::where('tenant_id',$user?->tenant_id??0)->whereIn('status',['issued','acknowledged'])->count() ?: 'null'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('payroll') ?? true) && $canView('payroll')): ?>
      { section: 'Penggajian' },
      { label: 'Penggajian',            href: '<?php echo e(route("payroll.index")); ?>',          active: <?php echo e(request()->routeIs('payroll.index','payroll.process','payroll.run*') ? 'true' : 'false'); ?> },
      { label: 'Komponen Gaji',         href: '<?php echo e(route("payroll.components.index")); ?>', active: <?php echo e(request()->routeIs('payroll.components*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('reimbursement') ?? true) && $canView('reimbursement')): ?>
      { section: 'Reimbursement' },
      { label: 'Kelola Reimbursement', href: '<?php echo e(route("reimbursement.index")); ?>',     active: <?php echo e(request()->routeIs('reimbursement.index','reimbursement.store','reimbursement.approve','reimbursement.reject','reimbursement.pay','reimbursement.destroy') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php endif; ?>
<?php if(!$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>
      { section: 'Self-Service' },
      { label: 'Portal Karyawan',       href: '<?php echo e(route("self-service.dashboard")); ?>', active: <?php echo e(request()->routeIs('self-service.dashboard','self-service.profile*') ? 'true' : 'false'); ?> },
      { label: 'Slip Gaji',             href: '<?php echo e(route("payroll.slip.index")); ?>',     active: <?php echo e(request()->routeIs('payroll.slip*') ? 'true' : 'false'); ?> },
      { label: 'Cuti Saya',             href: '<?php echo e(route("self-service.leave.index")); ?>', active: <?php echo e(request()->routeIs('self-service.leave*') ? 'true' : 'false'); ?> },
      { label: 'Absensi Saya',          href: '<?php echo e(route("self-service.attendance.index")); ?>', active: <?php echo e(request()->routeIs('self-service.attendance*') ? 'true' : 'false'); ?> },
      { label: 'Reimbursement Saya',   href: '<?php echo e(route("reimbursement.my")); ?>', active: <?php echo e(request()->routeIs('reimbursement.my*') ? 'true' : 'false'); ?> },
<?php endif; ?>
    ]
  },
<?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
  finance: {
    title: 'Keuangan',
    items: [
      <?php if($canView('expenses')): ?>      { label: 'Pengeluaran',           href: '<?php echo e(route("expenses.index")); ?>',         active: <?php echo e(request()->routeIs('expenses*') ? 'true' : 'false'); ?> }, <?php endif; ?>
<?php if($navTenant?->isModuleEnabled('invoicing') ?? true): ?>
      <?php if($canView('receivables')): ?>   { label: 'Piutang (AR)',          href: '<?php echo e(route("receivables.index")); ?>',      active: <?php echo e(request()->routeIs('receivables*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('receivables')): ?>   { label: 'Hutang (AP)',           href: '<?php echo e(route("payables.index")); ?>',         active: <?php echo e(request()->routeIs('payables*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('bulk_payments')): ?> { label: 'Bulk Payment',          href: '<?php echo e(route("bulk-payments.index")); ?>',    active: <?php echo e(request()->routeIs('bulk-payments*') ? 'true' : 'false'); ?> }, <?php endif; ?>
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('bank_reconciliation') ?? true) && $canView('bank')): ?>
      { label: 'Rekening Bank',         href: '<?php echo e(route("bank-accounts.index")); ?>',    active: <?php echo e(request()->routeIs('bank-accounts*') ? 'true' : 'false'); ?> },
      { label: 'Rekonsiliasi Bank',     href: '<?php echo e(route("bank.reconciliation")); ?>',    active: <?php echo e(request()->routeIs('bank.reconciliation*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('budget') ?? true) && $canView('budget')): ?>
      { label: 'Anggaran',              href: '<?php echo e(route("budget.index")); ?>',           active: <?php echo e(request()->routeIs('budget*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('assets') ?? true) && $canView('assets')): ?>
      { label: 'Aset',                  href: '<?php echo e(route("assets.index")); ?>',           active: <?php echo e(request()->routeIs('assets*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php if(($navTenant?->isModuleEnabled('accounting') ?? true) && $canView('accounting')): ?>
      { section: 'Akuntansi' },
      <?php if($canView('journals')): ?>      { label: 'Jurnal',                href: '<?php echo e(route("journals.index")); ?>',         active: <?php echo e(request()->routeIs('journals*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      { label: 'Bagan Akun (COA)',      href: '<?php echo e(route("accounting.coa")); ?>',         active: <?php echo e(request()->routeIs('accounting.coa*') ? 'true' : 'false'); ?> },
      { label: 'Neraca Saldo',          href: '<?php echo e(route("accounting.trial-balance")); ?>', active: <?php echo e(request()->routeIs('accounting.trial-balance*') ? 'true' : 'false'); ?> },
      { label: 'Neraca (Balance Sheet)',href: '<?php echo e(route("accounting.balance-sheet")); ?>', active: <?php echo e(request()->routeIs('accounting.balance-sheet*') ? 'true' : 'false'); ?> },
      { label: 'Laba Rugi (P&L)',       href: '<?php echo e(route("accounting.income-statement")); ?>', active: <?php echo e(request()->routeIs('accounting.income-statement*') ? 'true' : 'false'); ?> },
      { label: 'Arus Kas',              href: '<?php echo e(route("accounting.cash-flow")); ?>',   active: <?php echo e(request()->routeIs('accounting.cash-flow*') ? 'true' : 'false'); ?> },
      <?php if($canView('deferred')): ?>      { label: 'Amortisasi / Deferral', href: '<?php echo e(route("deferred.index")); ?>',         active: <?php echo e(request()->routeIs('deferred*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('writeoffs')): ?>     { label: 'Penghapusan Piutang',   href: '<?php echo e(route("writeoffs.index")); ?>',        active: <?php echo e(request()->routeIs('writeoffs*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      { label: 'Periode Akuntansi',     href: '<?php echo e(route("accounting.periods")); ?>',     active: <?php echo e(request()->routeIs('accounting.periods*') ? 'true' : 'false'); ?> },
<?php if($user?->isAdmin()): ?>
      { label: 'Kunci Periode & Backup',href: '<?php echo e(route("accounting.period-lock.index")); ?>', active: <?php echo e(request()->routeIs('accounting.period-lock*') ? 'true' : 'false'); ?> },
<?php endif; ?>
<?php endif; ?>
    ]
  },
  analytics: {
    title: 'Analitik',
    items: [
<?php if(($navTenant?->isModuleEnabled('reports') ?? true) && $canView('reports')): ?>
      { label: 'Laporan',               href: '<?php echo e(route("reports.index")); ?>',          active: <?php echo e(request()->routeIs('reports.index','reports.sales*','reports.finance*','reports.inventory*','reports.hrm*','reports.receivables*','reports.profit-loss*','reports.income-statement*','reports.payroll*','reports.aging*','reports.balance-sheet*','reports.cash-flow*','reports.budget*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      <?php if($canView('kpi')): ?>           { label: 'KPI Dashboard',         href: '<?php echo e(route("kpi.index")); ?>',              active: <?php echo e(request()->routeIs('kpi*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('reports')): ?>       { label: 'AI Forecasting',        href: '<?php echo e(route("forecast.index")); ?>',         active: <?php echo e(request()->routeIs('forecast*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('reports')): ?>       { label: 'Proyeksi Arus Kas',     href: '<?php echo e(route("reports.cash-flow-projection")); ?>', active: <?php echo e(request()->routeIs('reports.cash-flow-projection*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      { section: 'AI & Deteksi' },
      <?php if($canView('anomalies')): ?>     { label: 'Deteksi Anomali',       href: '<?php echo e(route("anomalies.index")); ?>',        active: <?php echo e(request()->routeIs('anomalies*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('zero_input')): ?>    { label: 'Input Cerdas (AI)',      href: '<?php echo e(route("zero-input.index")); ?>',       active: <?php echo e(request()->routeIs('zero-input*') ? 'true' : 'false'); ?> }, <?php endif; ?>
      <?php if($canView('simulations')): ?>   { label: 'Simulasi Keuangan',     href: '<?php echo e(route("simulations.index")); ?>',      active: <?php echo e(request()->routeIs('simulations*') ? 'true' : 'false'); ?> }, <?php endif; ?>
    ]
  },
  settings: {
    title: 'Pengaturan',
    items: [
<?php if($user?->isAdmin()): ?>
      { label: 'Profil Perusahaan',     href: '<?php echo e(route("company-profile.index")); ?>',  active: <?php echo e(request()->routeIs('company-profile*') ? 'true' : 'false'); ?> },
      { label: 'Pengaturan Modul',      href: '<?php echo e(route("settings.modules.index")); ?>', active: <?php echo e(request()->routeIs('settings.modules*') ? 'true' : 'false'); ?> },
      { label: 'Kelola Pengguna',       href: '<?php echo e(route("tenant.users.index")); ?>',     active: <?php echo e(request()->routeIs('tenant.users*') ? 'true' : 'false'); ?> },
      { label: 'Pengingat',             href: '<?php echo e(route("reminders.index")); ?>',        active: <?php echo e(request()->routeIs('reminders*') ? 'true' : 'false'); ?> },
      { label: 'Import CSV',            href: '<?php echo e(route("import.index")); ?>',           active: <?php echo e(request()->routeIs('import*') ? 'true' : 'false'); ?> },
      { label: 'Audit Trail',           href: '<?php echo e(route("audit.index")); ?>',            active: <?php echo e(request()->routeIs('audit*') ? 'true' : 'false'); ?> },
      { label: 'Notifikasi',            href: '<?php echo e(route("notifications.index")); ?>',    active: <?php echo e(request()->routeIs('notifications*') ? 'true' : 'false'); ?> },
      { label: 'Bot WA/Telegram',       href: '<?php echo e(route("bot.settings")); ?>',           active: <?php echo e(request()->routeIs('bot*') ? 'true' : 'false'); ?> },
      { label: 'API & Webhook',         href: '<?php echo e(route("api-settings.index")); ?>',     active: <?php echo e(request()->routeIs('api-settings*') ? 'true' : 'false'); ?> },
      { label: 'Pusat Biaya',           href: '<?php echo e(route("cost-centers.index")); ?>',     active: <?php echo e(request()->routeIs('cost-centers*') ? 'true' : 'false'); ?> },
      { label: 'Memori AI',             href: '<?php echo e(route("ai-memory.index")); ?>',        active: <?php echo e(request()->routeIs('ai-memory*') ? 'true' : 'false'); ?> },
      { section: 'Konfigurasi' },
      { label: 'Pengaturan Akuntansi',  href: '<?php echo e(route("settings.accounting")); ?>',   active: <?php echo e(request()->routeIs('settings.accounting*') ? 'true' : 'false'); ?> },
      { label: 'Pajak',                 href: '<?php echo e(route("taxes.index")); ?>',            active: <?php echo e(request()->routeIs('taxes*') ? 'true' : 'false'); ?> },
      { label: 'Custom Fields',         href: '<?php echo e(route("custom-fields.index")); ?>',    active: <?php echo e(request()->routeIs('custom-fields*') ? 'true' : 'false'); ?> },
      { label: 'Batasan Bisnis',        href: '<?php echo e(route("constraints.index")); ?>',      active: <?php echo e(request()->routeIs('constraints*') ? 'true' : 'false'); ?> },
      { label: 'Grup Perusahaan',       href: '<?php echo e(route("company-groups.index")); ?>',   active: <?php echo e(request()->routeIs('company-groups*') ? 'true' : 'false'); ?> },
<?php endif; ?>
      { label: 'Langganan',             href: '<?php echo e(route("subscription.index")); ?>',     active: <?php echo e(request()->routeIs('subscription.index') ? 'true' : 'false'); ?> },
    ]
  },
<?php endif; ?>
<?php endif; ?>
  profile: {
    title: 'Akun Saya',
    items: [
      { label: '<?php echo e(addslashes($user?->name)); ?>', href: '<?php echo e(route("profile.edit")); ?>', active: <?php echo e(request()->routeIs('profile*') ? 'true' : 'false'); ?>, meta: '<?php echo e($user?->roleLabel()); ?>' },
<?php if(!$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>
      { label: 'Portal Karyawan',       href: '<?php echo e(route("self-service.dashboard")); ?>', active: false },
<?php endif; ?>
      { label: 'Keluar', href: '#logout', active: false, danger: true },
    ]
  },
};

const ACTIVE_GROUP = '<?php echo e($activeGroup); ?>';
</script>


<script>
// ── Sidebar Panel Engine — Orbital Design ────────────────────────
let currentGroup = null;
let allPanelItems = [];

function buildPanel(groupKey) {
    const group = NAV_GROUPS[groupKey];
    if (!group) return;

    // Apply per-group color
    const btn   = document.querySelector(`.rail-btn[data-group="${groupKey}"]`);
    const color = btn?.dataset.color || '#60a5fa';
    const rgb   = btn?.dataset.rgb   || '96,165,250';
    const panel = document.getElementById('sidebar-panel');
    panel.style.setProperty('--group-color', color);
    panel.style.setProperty('--group-rgb', rgb);
    const accent = document.getElementById('panel-accent');
    if (accent) { accent.style.background = color; accent.style.boxShadow = `0 0 12px ${color}`; }

    document.getElementById('panel-title').textContent = group.title;
    const search = document.getElementById('panel-search');
    if (search) search.value = '';
    allPanelItems = group.items;
    renderPanelItems(group.items);
}

function renderPanelItems(items) {
    const nav = document.getElementById('panel-nav');
    nav.innerHTML = '';
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
        let inner = '';
        if (item.meta) inner += `<span style="display:block;font-size:10px;color:#64748b;margin-bottom:1px">${item.meta}</span>`;
        inner += `<span>${item.label}</span>`;
        if (item.badge && item.badge !== 'null') {
            inner += `<span class="badge ${item.badgeClass || ''}">${item.badge}</span>`;
        }
        a.innerHTML = inner;
        if (item.href === '#logout') {
            a.addEventListener('click', e => { e.preventDefault(); document.getElementById('logout-form').submit(); });
        }
        // Auto-close sidebar on mobile after clicking a link
        if (window.innerWidth < 1024) {
            a.addEventListener('click', () => closeMobileSidebar());
        }
        nav.appendChild(a);
    });
}

function filterPanel(q) {
    if (!q.trim()) { renderPanelItems(allPanelItems); return; }
    const filtered = allPanelItems.filter(item =>
        !item.section && item.label.toLowerCase().includes(q.toLowerCase())
    );
    renderPanelItems(filtered);
}

function openGroup(groupKey) {
    currentGroup = groupKey;
    buildPanel(groupKey);
    document.getElementById('sidebar-panel').classList.add('panel-open');
    document.getElementById('panel-backdrop').classList.remove('hidden');
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
        // Mobile: open panel full screen, rail stays as bottom bar
        openGroup(groupKey);
    } else {
        if (currentGroup === groupKey) { closePanel(); }
        else { openGroup(groupKey); }
    }
}

// Auto-open active group on page load
document.addEventListener('DOMContentLoaded', () => {
    const isMobile = () => window.innerWidth < 1024;
    const panel = document.getElementById('sidebar-panel');
    const main  = document.getElementById('main-wrap');

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
    obs.observe(panel, { attributes: true, attributeFilter: ['class'] });

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
        rail.classList.add('mobile-open');
        document.getElementById('sidebar-overlay').classList.remove('hidden');
    }
}
function closeMobileSidebar() {
    document.getElementById('sidebar-rail').classList.remove('mobile-open');
    document.getElementById('sidebar-overlay').classList.add('hidden');
    closePanel();
}

// Theme toggle
document.getElementById('theme-toggle')?.addEventListener('click', () => {
    const isDark = document.getElementById('html-root').classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});

// PWA + Push Notifications
if ('serviceWorker' in navigator) {
    window.addEventListener('load', async () => {
        try {
            const reg = await navigator.serviceWorker.register('/sw.js');

            // Auto-subscribe to push if permission already granted
            if (Notification.permission === 'granted') {
                subscribePush(reg);
            }
        } catch(e) {}
    });
}

// Push notification subscribe/unsubscribe
async function subscribePush(reg) {
    if (!reg) reg = await navigator.serviceWorker.ready;

    const vapidKey = '<?php echo e(\App\Services\WebPushService::vapidPublicKey()); ?>';
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
        await fetch('<?php echo e(route("push.subscribe")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>' },
            body: JSON.stringify(sub.toJSON()),
        });
    } catch(e) {}
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
        if (btn) { btn.textContent = '✓ Push aktif'; btn.disabled = true; btn.classList.add('opacity-50'); }
    }
};
</script>


<form id="logout-form" method="POST" action="<?php echo e(route('logout')); ?>" class="hidden"><?php echo csrf_field(); ?></form>

<?php echo $__env->yieldPushContent('scripts'); ?>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/layouts/app.blade.php ENDPATH**/ ?>