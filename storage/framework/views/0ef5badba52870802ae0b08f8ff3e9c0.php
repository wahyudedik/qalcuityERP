<!DOCTYPE html>
<html lang="id" class="h-full" id="html-root">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>
        <?php echo e($title ?? (View::hasSection('title') ? View::yieldContent('title') : config('app.name', 'Qalcuity ERP'))); ?>

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
    <?php if(config('services.vapid.public_key')): ?>
        <meta name="vapid-public-key" content="<?php echo e(config('services.vapid.public_key')); ?>">
    <?php endif; ?>
    <script>
        (function() {
            try {
                // FORCE LIGHT MODE FOR ERP - Aggressive approach

                // 1. Clear any existing theme settings
                localStorage.removeItem('theme');
                localStorage.removeItem('color-theme');
                localStorage.removeItem('darkMode');
                localStorage.setItem('theme', 'light');
                localStorage.setItem('_theme_cleaned', '1');

                // 2. Force remove dark class immediately
                const html = document.documentElement;
                const body = document.body;

                html.classList.remove('dark');
                body?.classList.remove('dark');

                // 3. Set light mode attributes
                html.setAttribute('data-theme', 'light');
                html.style.colorScheme = 'light';

                // 4. Override any CSS that might force dark mode
                const style = document.createElement('style');
                style.textContent = `
                    html, html.dark, html[data-theme="dark"] {
                        color-scheme: light !important;
                    }
                    html.dark *, html[data-theme="dark"] * {
                        color-scheme: light !important;
                    }
                    /* Force light backgrounds */
                    body, .bg-gray-900, .bg-slate-900, .dark\\:bg-gray-900 {
                        background-color: #ffffff !important;
                        color: #1f2937 !important;
                    }
                `;
                document.head.appendChild(style);

                // 5. Continuous monitoring with MutationObserver
                const observer = new MutationObserver(function(mutations) {
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                            const target = mutation.target;
                            if (target.classList.contains('dark')) {
                                target.classList.remove('dark');
                            }
                        }
                    });
                });

                observer.observe(html, {
                    attributes: true,
                    attributeFilter: ['class', 'data-theme'],
                    subtree: true
                });

                // 6. Override media query detection
                if (window.matchMedia) {
                    const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                    mediaQuery.matches = false; // Force override
                }

            } catch (e) {
                console.warn('Theme override failed:', e);
            }
        })();
    </script>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js', 'resources/js/offline-manager.js', 'resources/js/conflict-resolution.js', 'resources/js/topbar-offline-indicator.js']); ?>
    <?php echo $__env->yieldPushContent('head'); ?>
    <style>
        /* ── Module color CSS custom properties ── */
        :root {
            --module-color-home: #3b82f6;
            --module-color-ai: #8b5cf6;
            --module-color-transactions: #f59e0b;
            --module-color-inventory: #10b981;
            --module-color-operations: #f97316;
            --module-color-finance: #06b6d4;
            --module-color-settings: #6b7280;
            --module-color-superadmin: #ef4444;
        }

        /* Scrollbar utilities */
        .scrollbar-thin::-webkit-scrollbar {
            width: 3px;
        }

        .scrollbar-thin::-webkit-scrollbar-track {
            background: transparent;
        }

        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(0, 0, 0, 0.12);
            border-radius: 4px;
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

        
        <?php
            $user = auth()->user();
            $navTenant = $user?->tenant;

            // resolveActiveGroup() — array-priority approach.
            // Each route pattern belongs to exactly ONE group. The first matching group wins.
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
                    // 8. Pengaturan
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
                        return $group;
                    }
                }

                return '';
            }

            $activeGroup = resolveActiveGroup();
        ?>

        
        
        <div class="flex-1 flex min-w-0 pt-14" id="main-wrap">

            
            <?php echo $__env->make('layouts._nav_sidebar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

            
            <div x-data>
                <button x-show="$store.navSystem.sidebarVisible && $store.navSystem.sidebarCollapsed"
                    @click="$store.navSystem.openSidebar()"
                    class="fixed top-[72px] left-0 z-50 flex items-center justify-center w-6 h-8 bg-white border border-l-0 border-gray-200 rounded-r-lg shadow-sm hover:bg-gray-50 text-gray-500 transition"
                    title="Buka sidebar" aria-label="Buka sidebar" style="display: none;">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                        aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                    </svg>
                </button>
            </div>

            
            <div class="flex-1 flex flex-col min-w-0">

                
                <header
                    class="fixed top-0 left-0 right-0 z-30 h-14 border-b flex items-center px-4 sm:px-6 gap-4
                       bg-[#f0f0f0] border-gray-200">

                    
                    <div x-data>
                        <button id="launcher-btn" @click="$store.navSystem.toggleLauncher()"
                            :aria-expanded="$store.navSystem.launcherOpen"
                            :class="$store.navSystem.launcherOpen ?
                                'bg-gray-200' :
                                'hover:bg-gray-100'"
                            class="relative flex items-center justify-center w-10 h-10 rounded-xl transition-colors duration-150 text-gray-600"
                            title="Buka Menu" aria-label="Buka Menu" aria-controls="launcher-overlay">
                            
                            <svg width="20" height="20" viewBox="0 0 20 20" fill="currentColor"
                                aria-hidden="true">
                                <circle cx="4" cy="4" r="1.5" />
                                <circle cx="10" cy="4" r="1.5" />
                                <circle cx="16" cy="4" r="1.5" />
                                <circle cx="4" cy="10" r="1.5" />
                                <circle cx="10" cy="10" r="1.5" />
                                <circle cx="16" cy="10" r="1.5" />
                                <circle cx="4" cy="16" r="1.5" />
                                <circle cx="10" cy="16" r="1.5" />
                                <circle cx="16" cy="16" r="1.5" />
                            </svg>

                            
                            <span x-show="$store.navSystem.activeModule !== ''"
                                class="absolute bottom-1.5 right-1.5 w-2 h-2 rounded-full ring-2 ring-[#f0f0f0]"
                                :style="`background-color: var(--module-color-${$store.navSystem.activeModule}, #60a5fa)`"
                                aria-hidden="true"></span>
                        </button>
                    </div>

                    
                    <div class="flex-1 flex items-center gap-2 min-w-0 overflow-x-auto scrollbar-hide">
                        <span
                            class="text-xs text-slate-400 hidden sm:block whitespace-nowrap"><?php echo e(config('app.name')); ?></span>
                        <span class="text-xs text-slate-600 hidden sm:block whitespace-nowrap">/</span>
                        <?php if(isset($header)): ?>
                            <?php if(is_string($header) && !str_contains($header, '<')): ?>
                                <h1 class="text-base font-semibold text-gray-900 truncate whitespace-nowrap">
                                    <?php echo e($header); ?></h1>
                            <?php else: ?>
                                <div class="flex items-center gap-2 whitespace-nowrap"><?php echo $header; ?></div>
                            <?php endif; ?>
                        <?php elseif(View::hasSection('header')): ?>
                            <div class="flex items-center gap-2 whitespace-nowrap">
                                <h1 class="text-base font-semibold text-gray-900 truncate">
                                    <?php echo $__env->yieldContent('header'); ?>
                                </h1>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="flex items-center gap-1.5 shrink-0">
                        
                        <div id="topbar-offline-indicator" class="flex items-center gap-2"></div>

                        
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

                        
                        <button onclick="toggleFullscreen()" id="btn-fullscreen-main" title="Layar penuh (F11)"
                            class="p-2 rounded-xl hover:bg-[#e4e4e4] text-gray-500 transition">
                            <svg id="icon-fs-enter" class="w-5 h-5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2" />
                            </svg>
                            <svg id="icon-fs-exit" class="w-5 h-5 hidden" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 3v3a2 2 0 01-2 2H3m18 0h-3a2 2 0 01-2-2V3m0 18v-3a2 2 0 012-2h3M3 16h3a2 2 0 012 2v3" />
                            </svg>
                        </button>

                        
                        <?php
                            // N+1 FIX: Use cached sidebarBadges from View Composer instead of direct DB query
                            $unreadCount = $sidebarBadges['notifications'] ?? 0;
                            $notifTenantId = $user->tenant_id ?? null;
                            $authUser = $user;
                        ?>
                        <div class="relative" id="notif-wrapper">
                            <button onclick="toggleNotif()"
                                class="relative p-2 rounded-xl hover:bg-[#e4e4e4] text-gray-500 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                                </svg>
                                <?php if($unreadCount > 0): ?>
                                    <span
                                        class="absolute top-1 right-1 w-2 h-2 bg-red-500 rounded-full ring-2 ring-white"></span>
                                <?php endif; ?>
                            </button>
                            <div id="notif-dropdown"
                                class="hidden absolute right-0 mt-2 w-80 rounded-2xl shadow-xl border overflow-hidden z-50
                               bg-white border-gray-200">
                                <div class="flex items-center justify-between px-4 py-3 border-b border-gray-100">
                                    <span class="font-semibold text-sm text-gray-900">Notifikasi</span>
                                    <a href="<?php echo e(route('notifications.index')); ?>"
                                        class="text-xs text-blue-500 hover:underline">Lihat semua</a>
                                </div>
                                <div class="max-h-72 overflow-y-auto divide-y divide-gray-100">
                                    <?php
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
                                    ?>
                                    <?php $__empty_1 = true; $__currentLoopData = $topbarNotifs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                        <div
                                            class="px-4 py-3 hover:bg-[#f0f0f0] <?php echo e($notif->isRead() ? 'opacity-60' : ''); ?>">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo e($notif->title); ?></p>
                                            <p class="text-xs text-slate-400 mt-0.5">
                                                <?php echo e(Str::limit($notif->body, 80)); ?>

                                            </p>
                                            <p class="text-xs text-slate-500 mt-1">
                                                <?php echo e($notif->created_at->diffForHumans()); ?></p>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                        <div class="px-4 py-6 text-center text-sm text-slate-400">Tidak ada notifikasi
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="px-4 py-2.5 border-t border-gray-100 bg-gray-50">
                                    <button id="btn-enable-push" onclick="enablePushNotifications()"
                                        class="w-full text-xs text-center py-1.5 rounded-lg text-blue-600 hover:bg-blue-50 transition">
                                        🔔 Aktifkan Notifikasi Push
                                    </button>
                                </div>
                            </div>
                        </div>

                        
                        <div class="relative" id="profile-wrapper">
                            <button onclick="toggleProfile()"
                                class="flex items-center gap-2 p-2 rounded-xl hover:bg-[#e4e4e4] text-gray-500 transition">
                                <div
                                    class="w-8 h-8 rounded-full bg-blue-500 flex items-center justify-center text-white text-sm font-semibold">
                                    <?php echo e(strtoupper(substr($user?->name ?? 'U', 0, 1))); ?>

                                </div>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 9l-7 7-7-7" />
                                </svg>
                            </button>
                            <div id="profile-dropdown"
                                class="hidden absolute right-0 mt-2 w-64 rounded-2xl shadow-xl border overflow-hidden z-50
                               bg-white border-gray-200">
                                <div class="px-4 py-3 border-b border-gray-100">
                                    <p class="font-semibold text-sm text-gray-900"><?php echo e($user?->name ?? 'User'); ?></p>
                                    <p class="text-xs text-gray-500"><?php echo e($user?->roleLabel() ?? 'User'); ?></p>
                                </div>
                                <div class="py-2">
                                    <a href="<?php echo e(route('profile.edit')); ?>"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                        </svg>
                                        Profil Saya
                                    </a>
                                    <?php if(!$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>
                                        <a href="<?php echo e(route('self-service.dashboard')); ?>"
                                            class="flex items-center gap-3 px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2-2v2m8 0V6a2 2 0 012 2v6M8 6V4a2 2 0 012-2h4a2 2 0 012 2v2m-8 0V6a2 2 0 00-2 2v6" />
                                            </svg>
                                            Portal Karyawan
                                        </a>
                                    <?php endif; ?>
                                    <div class="border-t border-gray-100 my-2"></div>
                                    <button onclick="document.getElementById('logout-form').submit()"
                                        class="flex items-center gap-3 px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition w-full text-left">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                                        </svg>
                                        Keluar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>

                
                <main class="flex-1 p-4 sm:p-6 bg-[#f8f8f8]">
                    
                    <?php if(isset($pageHeader)): ?>
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                            <div class="min-w-0">
                                <?php if(isset($pageTitle)): ?>
                                    <h1 class="text-xl font-semibold text-gray-900 truncate"><?php echo e($pageTitle); ?>

                                    </h1>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-wrap items-center gap-2 shrink-0">
                                <?php echo e($pageHeader); ?>

                            </div>
                        </div>
                    <?php endif; ?>
                    <?php if(session('success')): ?>
                        <div
                            class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-600 text-sm px-4 py-3 rounded-xl">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5 13l4 4L19 7" />
                            </svg>
                            <?php echo e(session('success')); ?>

                        </div>
                    <?php endif; ?>
                    <?php if(session('warning')): ?>
                        <div
                            class="mb-4 flex items-start gap-3 bg-amber-500/10 border border-amber-500/20 text-amber-700 text-sm px-4 py-3 rounded-xl">
                            <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span><?php echo e(session('warning')); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if(session('error')): ?>
                        <div
                            class="mb-4 flex items-center gap-3 bg-red-500/10 border border-red-500/20 text-red-600 text-sm px-4 py-3 rounded-xl">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
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
    </div>


    
    <?php
        // Permission helper for sidebar — only for non-admin/non-superadmin
        $ps = app(\App\Services\PermissionService::class);
        $canView = function (string $module) use ($user, $ps): bool {
            if (!$user || $user->isAdmin() || $user->isSuperAdmin()) {
                return true;
            }
            return $ps->check($user, $module, 'view');
        };
    ?>
    <script>
        const NAV_GROUPS = {
            <?php if($user?->isSuperAdmin()): ?>
                home: {
                    title: 'Dashboard',
                    items: [{
                        label: 'Dashboard',
                        href: '<?php echo e(route('dashboard')); ?>',
                        active: <?php echo e(request()->routeIs('dashboard') ? 'true' : 'false'); ?>

                    }, ]
                },
                superadmin: {
                    title: 'Super Admin',
                    items: [{
                            label: 'Semua Tenant',
                            href: '<?php echo e(route('super-admin.tenants.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.tenants*') ? 'true' : 'false'); ?>

                        },
                        {
                            label: 'Kelola Paket',
                            href: '<?php echo e(route('super-admin.plans.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.plans*') ? 'true' : 'false'); ?>

                        },
                        {
                            section: 'Monitoring'
                        },
                        {
                            label: 'Monitoring',
                            href: '<?php echo e(route('super-admin.monitoring.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.monitoring*') ? 'true' : 'false'); ?>,
                            badge: <?php echo e($sidebarBadges['error_logs'] ?? 0 ?: 'null'); ?>,
                            badgeClass: 'badge-red'
                        },
                        {
                            section: 'Konten'
                        },
                        {
                            label: 'Popup Iklan',
                            href: '<?php echo e(route('super-admin.popup-ads.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.popup-ads*') ? 'true' : 'false'); ?>,
                        },
                        {
                            section: 'Afiliasi'
                        },
                        {
                            label: 'Kelola Affiliate',
                            href: '<?php echo e(route('super-admin.affiliates.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.affiliates.index') ? 'true' : 'false'); ?>

                        },
                        {
                            label: 'Komisi',
                            href: '<?php echo e(route('super-admin.affiliates.commissions')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.affiliates.commissions*') ? 'true' : 'false'); ?>,
                            badge: <?php echo e($sidebarBadges['affiliate_commissions'] ?? 0 ?: 'null'); ?>,
                            badgeClass: 'badge-amber'
                        },
                        {
                            label: 'Payout',
                            href: '<?php echo e(route('super-admin.affiliates.payouts')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.affiliates.payouts*') ? 'true' : 'false'); ?>

                        },
                        {
                            label: 'Fraud Monitor',
                            href: '<?php echo e(route('super-admin.affiliates.audit-logs')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.affiliates.audit-logs*') ? 'true' : 'false'); ?>,
                            badge: <?php echo e($sidebarBadges['affiliate_fraud'] ?? 0 ?: 'null'); ?>,
                            badgeClass: 'badge-red'
                        },
                        {
                            section: 'Platform'
                        },
                        {
                            label: 'Pengaturan Platform',
                            href: '<?php echo e(route('super-admin.settings.index')); ?>',
                            active: <?php echo e(request()->routeIs('super-admin.settings*') ? 'true' : 'false'); ?>

                        },
                    ]
                },
            <?php elseif($user?->isAffiliate()): ?>
                home: {
                        title: 'Affiliate',
                        items: [{
                            label: 'Dashboard',
                            href: '<?php echo e(route('affiliate.dashboard')); ?>',
                            active: <?php echo e(request()->routeIs('affiliate.dashboard') ? 'true' : 'false'); ?>

                        }, ]
                    },
            <?php else: ?>
                home: {
                    title: 'Dashboard',
                    items: [
                        <?php if($canView('dashboard')): ?>
                            {
                                section: 'Overview'
                            }, {
                                label: 'Dashboard',
                                href: '<?php echo e(route('dashboard')); ?>',
                                active: <?php echo e(request()->routeIs('dashboard') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if(($navTenant?->isModuleEnabled('reports') ?? true) && $canView('reports')): ?>
                            {
                                section: 'Reports & Analytics'
                            }, {
                                label: 'Laporan',
                                href: '<?php echo e(route('reports.index')); ?>',
                                active: <?php echo e(request()->routeIs('reports.index', 'reports.sales*', 'reports.finance*', 'reports.inventory*', 'reports.hrm*', 'reports.receivables*', 'reports.profit-loss*', 'reports.income-statement*', 'reports.payroll*', 'reports.aging*', 'reports.balance-sheet*', 'reports.cash-flow*', 'reports.budget*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('kpi')): ?>
                            {
                                label: 'KPI Dashboard',
                                href: '<?php echo e(route('kpi.index')); ?>',
                                active: <?php echo e(request()->routeIs('kpi*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('reports')): ?>
                            {
                                label: 'AI Forecasting',
                                href: '<?php echo e(route('forecast.index')); ?>',
                                active: <?php echo e(request()->routeIs('forecast*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Proyeksi Arus Kas',
                                href: '<?php echo e(route('reports.cash-flow-projection')); ?>',
                                active: <?php echo e(request()->routeIs('reports.cash-flow-projection*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('anomalies')): ?>
                            {
                                section: 'AI & Intelligence'
                            }, {
                                label: 'Deteksi Anomali',
                                href: '<?php echo e(route('anomalies.index')); ?>',
                                active: <?php echo e(request()->routeIs('anomalies*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('zero_input')): ?>
                            {
                                label: 'Input Cerdas (AI)',
                                href: '<?php echo e(route('zero-input.index')); ?>',
                                active: <?php echo e(request()->routeIs('zero-input*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('simulations')): ?>
                            {
                                label: 'Simulasi Keuangan',
                                href: '<?php echo e(route('simulations.index')); ?>',
                                active: <?php echo e(request()->routeIs('simulations*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                    ]
                },
                ai: {
                    title: 'AI Chat',
                    items: [{
                        label: 'AI Chat',
                        href: '<?php echo e(route('chat.index')); ?>',
                        active: <?php echo e(request()->routeIs('chat*') ? 'true' : 'false'); ?>

                    }, ]
                },
                transactions: {
                    title: 'Transaksi & Master Data',
                    items: [{
                            section: 'Kontak'
                        },
                        <?php if($canView('customers')): ?>
                            {
                                label: 'Data Customer',
                                href: '<?php echo e(route('customers.index')); ?>',
                                active: <?php echo e(request()->routeIs('customers*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('suppliers')): ?>
                            {
                                label: 'Data Supplier',
                                href: '<?php echo e(route('suppliers.index')); ?>',
                                active: <?php echo e(request()->routeIs('suppliers*') && !request()->routeIs('suppliers.scorecards*') && !request()->routeIs('suppliers.scorecard*') && !request()->routeIs('suppliers.strategic-sourcing*') && !request()->routeIs('suppliers.sourcing*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('suppliers')): ?>
                            {
                                label: 'Supplier Scorecard',
                                href: '<?php echo e(route('suppliers.scorecards.index')); ?>',
                                active: <?php echo e(request()->routeIs('suppliers.scorecards*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Supplier Performance',
                                href: '<?php echo e(route('supplier-performance.dashboard')); ?>',
                                active: <?php echo e(request()->routeIs('supplier-performance*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('suppliers')): ?>
                            {
                                label: 'Strategic Sourcing',
                                href: '<?php echo e(route('suppliers.strategic-sourcing')); ?>',
                                active: <?php echo e(request()->routeIs('suppliers.strategic-sourcing*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?> {
                            section: 'Produk & Gudang'
                        },
                        <?php if($canView('products')): ?>
                            {
                                label: 'Data Produk',
                                href: '<?php echo e(route('products.index')); ?>',
                                active: <?php echo e(request()->routeIs('products*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('warehouses')): ?>
                            {
                                label: 'Data Gudang',
                                href: '<?php echo e(route('warehouses.index')); ?>',
                                active: <?php echo e(request()->routeIs('warehouses*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('price_lists')): ?>
                            {
                                label: 'Daftar Harga',
                                href: '<?php echo e(route('price-lists.index')); ?>',
                                active: <?php echo e(request()->routeIs('price-lists*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if($canView('categories')): ?>
                            {
                                label: 'Kategori Produk',
                                href: '<?php echo e(route('categories.index')); ?>',
                                active: <?php echo e(request()->routeIs('categories*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if(!$user?->isGudang()): ?>
                            {
                                section: 'Penjualan'
                            },
                            <?php if($navTenant?->isModuleEnabled('invoicing') ?? true): ?>
                                <?php if($canView('sales')): ?>
                                    {
                                        label: 'Sales Order',
                                        href: '<?php echo e(route('sales.index')); ?>',
                                        active: <?php echo e(request()->routeIs('sales.index', 'sales.create', 'sales.show', 'sales.store') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('invoices')): ?>
                                    {
                                        label: 'Penawaran (Quotation)',
                                        href: '<?php echo e(route('quotations.index')); ?>',
                                        active: <?php echo e(request()->routeIs('quotations*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('invoices')): ?>
                                    {
                                        label: 'Invoice',
                                        href: '<?php echo e(route('invoices.index')); ?>',
                                        active: <?php echo e(request()->routeIs('invoices*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('delivery')): ?>
                                    {
                                        label: 'Surat Jalan',
                                        href: '<?php echo e(route('delivery-orders.index')); ?>',
                                        active: <?php echo e(request()->routeIs('delivery-orders*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('down_payments')): ?>
                                    {
                                        label: 'Uang Muka (DP)',
                                        href: '<?php echo e(route('down-payments.index')); ?>',
                                        active: <?php echo e(request()->routeIs('down-payments*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('sales_returns')): ?>
                                    {
                                        label: 'Retur Penjualan',
                                        href: '<?php echo e(route('sales-returns.index')); ?>',
                                        active: <?php echo e(request()->routeIs('sales-returns*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('crm') ?? true) && $canView('crm')): ?>
                                {
                                    label: 'CRM & Pipeline',
                                    href: '<?php echo e(route('crm.index')); ?>',
                                    active: <?php echo e(request()->routeIs('crm*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('commission') ?? true) && $canView('commission')): ?>
                                {
                                    label: 'Komisi Sales',
                                    href: '<?php echo e(route('commission.index')); ?>',
                                    active: <?php echo e(request()->routeIs('commission.index') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Rule Komisi',
                                    href: '<?php echo e(route('commission.rules')); ?>',
                                    active: <?php echo e(request()->routeIs('commission.rules*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('helpdesk') ?? true) && $canView('helpdesk')): ?>
                                {
                                    label: 'Helpdesk',
                                    href: '<?php echo e(route('helpdesk.index')); ?>',
                                    active: <?php echo e(request()->routeIs('helpdesk.index') || request()->routeIs('helpdesk.show') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Knowledge Base',
                                    href: '<?php echo e(route('helpdesk.kb')); ?>',
                                    active: <?php echo e(request()->routeIs('helpdesk.kb*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('subscription_billing') ?? true) && $canView('subscription_billing')): ?>
                                {
                                    label: 'Subscription Billing',
                                    href: '<?php echo e(route('subscription-billing.index')); ?>',
                                    active: <?php echo e(request()->routeIs('subscription-billing.index') || request()->routeIs('subscription-billing.show') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Plan Langganan',
                                    href: '<?php echo e(route('subscription-billing.plans')); ?>',
                                    active: <?php echo e(request()->routeIs('subscription-billing.plans*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('loyalty') ?? true) && $canView('loyalty')): ?>
                                {
                                    label: 'Program Loyalitas',
                                    href: '<?php echo e(route('loyalty.index')); ?>',
                                    active: <?php echo e(request()->routeIs('loyalty*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos')): ?>
                                {
                                    label: 'Kasir (POS)',
                                    href: '<?php echo e(route('pos.index')); ?>',
                                    active: <?php echo e(request()->routeIs('pos*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                        <?php endif; ?>
                    ]
                },
                inventory: {
                    title: 'Inventori',
                    items: [
                        <?php if(($navTenant?->isModuleEnabled('inventory') ?? true) && $canView('inventory')): ?>
                            {
                                label: 'Inventori',
                                href: '<?php echo e(route('inventory.index')); ?>',
                                active: <?php echo e(request()->routeIs('inventory.index') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Transfer Stok',
                                href: '<?php echo e(route('inventory.transfers.index')); ?>',
                                active: <?php echo e(request()->routeIs('inventory.transfers*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                        <?php if(
                            ($user?->isAdmin() || $user?->isManager()) &&
                                ($navTenant?->isModuleEnabled('purchasing') ?? true) &&
                                $canView('purchasing')): ?>
                            {
                                section: 'Pembelian'
                            }, {
                                label: 'Pembelian',
                                href: '<?php echo e(route('purchasing.orders')); ?>',
                                active: <?php echo e(request()->routeIs('purchasing.orders*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Purchase Requisition',
                                href: '<?php echo e(route('purchasing.requisitions')); ?>',
                                active: <?php echo e(request()->routeIs('purchasing.requisitions*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'RFQ',
                                href: '<?php echo e(route('purchasing.rfq')); ?>',
                                active: <?php echo e(request()->routeIs('purchasing.rfq*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Goods Receipt',
                                href: '<?php echo e(route('purchasing.goods-receipts')); ?>',
                                active: <?php echo e(request()->routeIs('purchasing.goods-receipts*') ? 'true' : 'false'); ?>

                            }, {
                                label: '3-Way Matching',
                                href: '<?php echo e(route('purchasing.matching')); ?>',
                                active: <?php echo e(request()->routeIs('purchasing.matching*') ? 'true' : 'false'); ?>

                            }, {
                                label: 'Retur Pembelian',
                                href: '<?php echo e(route('purchase-returns.index')); ?>',
                                active: <?php echo e(request()->routeIs('purchase-returns*') ? 'true' : 'false'); ?>

                            },
                            <?php if(($navTenant?->isModuleEnabled('landed_cost') ?? true) && $canView('landed_cost')): ?>
                                {
                                    label: 'Landed Cost',
                                    href: '<?php echo e(route('landed-cost.index')); ?>',
                                    active: <?php echo e(request()->routeIs('landed-cost*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('consignment') ?? true) && $canView('consignment')): ?>
                                {
                                    label: 'Konsinyasi',
                                    href: '<?php echo e(route('consignment.index')); ?>',
                                    active: <?php echo e(request()->routeIs('consignment.index') || request()->routeIs('consignment.shipments*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Partner Konsinyasi',
                                    href: '<?php echo e(route('consignment.partners')); ?>',
                                    active: <?php echo e(request()->routeIs('consignment.partners*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('wms') ?? true) && $canView('wms')): ?>
                                {
                                    section: 'WMS Gudang'
                                }, {
                                    label: 'Zone & Bin',
                                    href: '<?php echo e(route('wms.index')); ?>',
                                    active: <?php echo e(request()->routeIs('wms.index') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Picking List',
                                    href: '<?php echo e(route('wms.picking')); ?>',
                                    active: <?php echo e(request()->routeIs('wms.picking*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Stock Opname',
                                    href: '<?php echo e(route('wms.opname')); ?>',
                                    active: <?php echo e(request()->routeIs('wms.opname*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Putaway Rules',
                                    href: '<?php echo e(route('wms.putaway-rules')); ?>',
                                    active: <?php echo e(request()->routeIs('wms.putaway-rules*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?> {
                                section: 'IoT Devices'
                            }, {
                                label: 'ESP32 / Arduino / RPi',
                                href: '<?php echo e(route('iot.devices.index')); ?>',
                                active: <?php echo e(request()->routeIs('iot.devices*') ? 'true' : 'false'); ?>

                            },
                        <?php endif; ?>
                    ]
                },
                <?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
                    operations: {
                        title: 'Operasional',
                        items: [
                            <?php if(($navTenant?->isModuleEnabled('pos') ?? true) && $canView('pos')): ?>
                                {
                                    label: 'Kasir (POS)',
                                    href: '<?php echo e(route('pos.index')); ?>',
                                    active: <?php echo e(request()->routeIs('pos*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('production') ?? true) && $canView('production')): ?>
                                {
                                    section: 'Manufacturing'
                                }, {
                                    label: 'Production Dashboard',
                                    href: '<?php echo e(route('production.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('production.dashboard*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Gantt Chart',
                                    href: '<?php echo e(route('production.gantt.index')); ?>',
                                    active: <?php echo e(request()->routeIs('production.gantt*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Produksi / WO',
                                    href: '<?php echo e(route('production.index')); ?>',
                                    active: <?php echo e(request()->routeIs('production.*') && !request()->routeIs('production.dashboard*') && !request()->routeIs('production.gantt*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('production') ?? true) && $canView('production')): ?>
                                {
                                    section: 'Quality Control'
                                }, {
                                    label: 'QC Inspections',
                                    href: '<?php echo e(route('qc.inspections.index')); ?>',
                                    active: <?php echo e(request()->routeIs('qc.inspections*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Test Templates',
                                    href: '<?php echo e(route('qc.templates.index')); ?>',
                                    active: <?php echo e(request()->routeIs('qc.templates*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('manufacturing') ?? true) && $canView('manufacturing')): ?>
                                {
                                    label: 'BOM Multi-Level',
                                    href: '<?php echo e(route('manufacturing.bom')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.bom*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Mix Design Beton',
                                    href: '<?php echo e(route('manufacturing.mix-design')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.mix-design*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Work Center',
                                    href: '<?php echo e(route('manufacturing.work-centers')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.work-centers*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'MRP Planning',
                                    href: '<?php echo e(route('manufacturing.mrp')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.mrp*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'MRP Accuracy',
                                    href: '<?php echo e(route('manufacturing.mrp.accuracy')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.mrp.accuracy*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Predictive MRP (AI)',
                                    href: '<?php echo e(route('manufacturing.mrp.predictive')); ?>',
                                    active: <?php echo e(request()->routeIs('manufacturing.mrp.predictive*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('manufacturing') ?? true) && $canView('printing')): ?>
                                {
                                    label: 'Printing Jobs',
                                    href: '<?php echo e(route('printing.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('printing*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('manufacturing') ?? true) && auth()->user()?->tenant_id && $canView('cosmetic')): ?>
                                {
                                    label: 'Cosmetic Formulas',
                                    href: '<?php echo e(route('cosmetic.formulas.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.formulas*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Batch Production',
                                    href: '<?php echo e(route('cosmetic.batches.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.batches*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'QC Laboratory',
                                    href: '<?php echo e(route('cosmetic.qc.tests')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.qc*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'BPOM Registrations',
                                    href: '<?php echo e(route('cosmetic.registrations.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.registrations*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Variants Manager',
                                    href: '<?php echo e(route('cosmetic.variants.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.variants*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Packaging & Labels',
                                    href: '<?php echo e(route('cosmetic.packaging.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.packaging*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Expiry & Recalls',
                                    href: '<?php echo e(route('cosmetic.expiry.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.expiry*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Distribution Channels',
                                    href: '<?php echo e(route('cosmetic.distribution.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.distribution*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Cosmetic Analytics',
                                    href: '<?php echo e(route('cosmetic.analytics.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('cosmetic.analytics*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('hotel') ?? true) && $canView('tour_travel')): ?>
                                {
                                    section: 'Tour & Travel'
                                }, {
                                    label: 'Tour Packages',
                                    href: '<?php echo e(route('tour-travel.packages.index')); ?>',
                                    active: <?php echo e(request()->routeIs('tour-travel.packages*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Bookings',
                                    href: '<?php echo e(route('tour-travel.bookings.index')); ?>',
                                    active: <?php echo e(request()->routeIs('tour-travel.bookings*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Tour Analytics',
                                    href: '<?php echo e(route('tour-travel.analytics')); ?>',
                                    active: <?php echo e(request()->routeIs('tour-travel.analytics*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('livestock') ?? true) && $canView('livestock')): ?>
                                {
                                    section: 'Livestock Enhancement'
                                }, {
                                    label: 'Dairy Management',
                                    href: '<?php echo e(route('livestock-enhancement.dairy.milk-records')); ?>',
                                    active: <?php echo e(request()->routeIs('livestock-enhancement.dairy*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Poultry Management',
                                    href: '<?php echo e(route('livestock-enhancement.poultry.flocks')); ?>',
                                    active: <?php echo e(request()->routeIs('livestock-enhancement.poultry*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Breeding',
                                    href: '<?php echo e(route('livestock-enhancement.breeding.records')); ?>',
                                    active: <?php echo e(request()->routeIs('livestock-enhancement.breeding*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Health & Vaccination',
                                    href: '<?php echo e(route('livestock-enhancement.health.treatments')); ?>',
                                    active: <?php echo e(request()->routeIs('livestock-enhancement.health*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Waste Management',
                                    href: '<?php echo e(route('livestock-enhancement.waste.logs')); ?>',
                                    active: <?php echo e(request()->routeIs('livestock-enhancement.waste*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('fleet') ?? true) && $canView('fleet')): ?>
                                {
                                    label: 'Fleet Kendaraan',
                                    href: '<?php echo e(route('fleet.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fleet.index') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Driver',
                                    href: '<?php echo e(route('fleet.drivers')); ?>',
                                    active: <?php echo e(request()->routeIs('fleet.drivers*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Trip / Penugasan',
                                    href: '<?php echo e(route('fleet.trips')); ?>',
                                    active: <?php echo e(request()->routeIs('fleet.trips*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Log BBM',
                                    href: '<?php echo e(route('fleet.fuel-logs')); ?>',
                                    active: <?php echo e(request()->routeIs('fleet.fuel-logs*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Maintenance',
                                    href: '<?php echo e(route('fleet.maintenance')); ?>',
                                    active: <?php echo e(request()->routeIs('fleet.maintenance*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('inventory') ?? true) && $canView('shipping')): ?>
                                {
                                    label: 'Pengiriman',
                                    href: '<?php echo e(route('shipping.index')); ?>',
                                    active: <?php echo e(request()->routeIs('shipping*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('agriculture') ?? true) && $canView('agriculture')): ?>
                                {
                                    label: 'Manajemen Lahan',
                                    href: '<?php echo e(route('farm.plots')); ?>',
                                    active: <?php echo e(request()->routeIs('farm.plots*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Siklus Tanam',
                                    href: '<?php echo e(route('farm.cycles')); ?>',
                                    active: <?php echo e(request()->routeIs('farm.cycles*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pencatatan Panen',
                                    href: '<?php echo e(route('farm.harvests')); ?>',
                                    active: <?php echo e(request()->routeIs('farm.harvests*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Analisis Biaya Lahan',
                                    href: '<?php echo e(route('farm.analytics')); ?>',
                                    active: <?php echo e(request()->routeIs('farm.analytics*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Populasi Ternak',
                                    href: '<?php echo e(route('farm.livestock')); ?>',
                                    active: <?php echo e(request()->routeIs('farm.livestock*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('livestock') ?? true) && $canView('livestock')): ?>
                                {
                                    section: 'Perikanan (Fisheries)'
                                }, {
                                    label: 'Dashboard Perikanan',
                                    href: '<?php echo e(route('fisheries.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.index') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Cold Chain',
                                    href: '<?php echo e(route('fisheries.cold-chain.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.cold-chain*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Fishing Operations',
                                    href: '<?php echo e(route('fisheries.operations.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.operations*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Aquaculture',
                                    href: '<?php echo e(route('fisheries.aquaculture.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.aquaculture*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Species & Grading',
                                    href: '<?php echo e(route('fisheries.species.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.species*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Export Documentation',
                                    href: '<?php echo e(route('fisheries.export.index')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.export*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Analytics',
                                    href: '<?php echo e(route('fisheries.analytics')); ?>',
                                    active: <?php echo e(request()->routeIs('fisheries.analytics') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('contracts') ?? true) && $canView('contracts')): ?>
                                {
                                    label: 'Kontrak',
                                    href: '<?php echo e(route('contracts.index')); ?>',
                                    active: <?php echo e(request()->routeIs('contracts.index') || request()->routeIs('contracts.show') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Template Kontrak',
                                    href: '<?php echo e(route('contracts.templates')); ?>',
                                    active: <?php echo e(request()->routeIs('contracts.templates*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if($canView('approvals')): ?>
                                {
                                    label: 'Persetujuan',
                                    href: '<?php echo e(route('approvals.index')); ?>',
                                    active: <?php echo e(request()->routeIs('approvals*') ? 'true' : 'false'); ?>,
                                    badge: <?php echo e($sidebarBadges['approvals'] ?? 0 ?: 'null'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('ecommerce') ?? true) && $canView('ecommerce')): ?>
                                {
                                    label: 'E-Commerce',
                                    href: '<?php echo e(route('ecommerce.index')); ?>',
                                    active: <?php echo e(request()->routeIs('ecommerce*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if($canView('documents')): ?>
                                {
                                    label: 'Dokumen',
                                    href: '<?php echo e(route('documents.index')); ?>',
                                    active: <?php echo e(request()->routeIs('documents*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('projects') ?? true) && $canView('projects')): ?>
                                {
                                    label: 'Manajemen Proyek',
                                    href: '<?php echo e(route('projects.index')); ?>',
                                    active: <?php echo e(request()->routeIs('projects*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('project_billing') ?? true) && $canView('project_billing')): ?>
                                {
                                    label: 'Project Billing',
                                    href: '<?php echo e(route('project-billing.index')); ?>',
                                    active: <?php echo e(request()->routeIs('project-billing*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('projects') ?? true) && $canView('timesheets')): ?>
                                {
                                    label: 'Timesheet',
                                    href: '<?php echo e(route('timesheets.index')); ?>',
                                    active: <?php echo e(request()->routeIs('timesheets*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(
                                ($navTenant?->isModuleEnabled('hrm') ?? true) ||
                                    ($navTenant?->isModuleEnabled('payroll') ?? true) ||
                                    ($navTenant?->isModuleEnabled('reimbursement') ?? true)): ?>
                                {
                                    section: 'SDM & Karyawan'
                                },
                            <?php endif; ?>
                            <?php if($user?->isAdmin() || $user?->isManager()): ?>
                                <?php if(($navTenant?->isModuleEnabled('hrm') ?? true) && $canView('hrm')): ?>
                                    {
                                        section: 'Manajemen SDM'
                                    }, {
                                        label: 'Rekrutmen',
                                        href: '<?php echo e(route('hrm.recruitment.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.recruitment*', 'hrm.onboarding*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'SDM & Karyawan',
                                        href: '<?php echo e(route('hrm.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.index', 'hrm.store', 'hrm.update', 'hrm.destroy', 'hrm.attendance*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Manajemen Cuti',
                                        href: '<?php echo e(route('hrm.leave')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.leave*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Penilaian Kinerja',
                                        href: '<?php echo e(route('hrm.performance')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.performance*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Struktur Organisasi',
                                        href: '<?php echo e(route('hrm.orgchart')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.orgchart') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Jadwal Shift',
                                        href: '<?php echo e(route('hrm.shifts.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.shifts*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Lembur',
                                        href: '<?php echo e(route('hrm.overtime.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.overtime*') ? 'true' : 'false'); ?>,
                                        badge: <?php echo e($sidebarBadges['overtime'] ?? 0 ?: 'null'); ?>

                                    }, {
                                        label: 'Pelatihan & Sertifikasi',
                                        href: '<?php echo e(route('hrm.training.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.training*') ? 'true' : 'false'); ?>,
                                        badge: <?php echo e($sidebarBadges['certifications'] ?? 0 ?: 'null'); ?>,
                                        badgeClass: 'badge-red'
                                    }, {
                                        label: 'Surat Peringatan',
                                        href: '<?php echo e(route('hrm.disciplinary.index')); ?>',
                                        active: <?php echo e(request()->routeIs('hrm.disciplinary*') ? 'true' : 'false'); ?>,
                                        badge: <?php echo e($sidebarBadges['disciplinary'] ?? 0 ?: 'null'); ?>

                                    },
                                <?php endif; ?>
                                <?php if(($navTenant?->isModuleEnabled('payroll') ?? true) && $canView('payroll')): ?>
                                    {
                                        section: 'Penggajian'
                                    }, {
                                        label: 'Penggajian',
                                        href: '<?php echo e(route('payroll.index')); ?>',
                                        active: <?php echo e(request()->routeIs('payroll.index', 'payroll.process', 'payroll.run*') ? 'true' : 'false'); ?>

                                    }, {
                                        label: 'Komponen Gaji',
                                        href: '<?php echo e(route('payroll.components.index')); ?>',
                                        active: <?php echo e(request()->routeIs('payroll.components*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if(($navTenant?->isModuleEnabled('reimbursement') ?? true) && $canView('reimbursement')): ?>
                                    {
                                        section: 'Reimbursement'
                                    }, {
                                        label: 'Kelola Reimbursement',
                                        href: '<?php echo e(route('reimbursement.index')); ?>',
                                        active: <?php echo e(request()->routeIs('reimbursement.index', 'reimbursement.store', 'reimbursement.approve', 'reimbursement.reject', 'reimbursement.pay', 'reimbursement.destroy') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if(!$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>
                                {
                                    section: 'Self-Service'
                                }, {
                                    label: 'Portal Karyawan',
                                    href: '<?php echo e(route('self-service.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('self-service.dashboard', 'self-service.profile*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Slip Gaji',
                                    href: '<?php echo e(route('payroll.slip.index')); ?>',
                                    active: <?php echo e(request()->routeIs('payroll.slip*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Cuti Saya',
                                    href: '<?php echo e(route('self-service.leave.index')); ?>',
                                    active: <?php echo e(request()->routeIs('self-service.leave*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Absensi Saya',
                                    href: '<?php echo e(route('self-service.attendance.index')); ?>',
                                    active: <?php echo e(request()->routeIs('self-service.attendance*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Reimbursement Saya',
                                    href: '<?php echo e(route('reimbursement.my')); ?>',
                                    active: <?php echo e(request()->routeIs('reimbursement.my*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                        ]
                    },
                <?php endif; ?>
                <?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
                    finance: {
                        title: 'Keuangan',
                        items: [
                            <?php if(($navTenant?->isModuleEnabled('accounting') ?? true) && $canView('expenses')): ?>
                                {
                                    label: 'Pengeluaran',
                                    href: '<?php echo e(route('expenses.index')); ?>',
                                    active: <?php echo e(request()->routeIs('expenses*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if($navTenant?->isModuleEnabled('invoicing') ?? true): ?>
                                <?php if($canView('receivables')): ?>
                                    {
                                        label: 'Piutang (AR)',
                                        href: '<?php echo e(route('receivables.index')); ?>',
                                        active: <?php echo e(request()->routeIs('receivables*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('receivables')): ?>
                                    {
                                        label: 'Hutang (AP)',
                                        href: '<?php echo e(route('payables.index')); ?>',
                                        active: <?php echo e(request()->routeIs('payables*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('bulk_payments')): ?>
                                    {
                                        label: 'Bulk Payment',
                                        href: '<?php echo e(route('bulk-payments.index')); ?>',
                                        active: <?php echo e(request()->routeIs('bulk-payments*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('bank_reconciliation') ?? true) && $canView('bank')): ?>
                                {
                                    label: 'Rekening Bank',
                                    href: '<?php echo e(route('bank-accounts.index')); ?>',
                                    active: <?php echo e(request()->routeIs('bank-accounts*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Rekonsiliasi Bank',
                                    href: '<?php echo e(route('bank.reconciliation')); ?>',
                                    active: <?php echo e(request()->routeIs('bank.reconciliation*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('budget') ?? true) && $canView('budget')): ?>
                                {
                                    label: 'Anggaran',
                                    href: '<?php echo e(route('budget.index')); ?>',
                                    active: <?php echo e(request()->routeIs('budget*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('assets') ?? true) && $canView('assets')): ?>
                                {
                                    label: 'Aset',
                                    href: '<?php echo e(route('assets.index')); ?>',
                                    active: <?php echo e(request()->routeIs('assets*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?>
                            <?php if(($navTenant?->isModuleEnabled('accounting') ?? true) && $canView('accounting')): ?>
                                {
                                    section: 'Akuntansi'
                                },
                                <?php if($canView('journals')): ?>
                                    {
                                        label: 'Jurnal',
                                        href: '<?php echo e(route('journals.index')); ?>',
                                        active: <?php echo e(request()->routeIs('journals*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?> {
                                    label: 'Bagan Akun (COA)',
                                    href: '<?php echo e(route('accounting.coa')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.coa*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Neraca Saldo',
                                    href: '<?php echo e(route('accounting.trial-balance')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.trial-balance*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Buku Besar',
                                    href: '<?php echo e(route('accounting.general-ledger')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.general-ledger*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Neraca (Balance Sheet)',
                                    href: '<?php echo e(route('accounting.balance-sheet')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.balance-sheet*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Laba Rugi (P&L)',
                                    href: '<?php echo e(route('accounting.income-statement')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.income-statement*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Arus Kas',
                                    href: '<?php echo e(route('accounting.cash-flow')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.cash-flow*') ? 'true' : 'false'); ?>

                                },
                                <?php if($canView('deferred')): ?>
                                    {
                                        label: 'Amortisasi / Deferral',
                                        href: '<?php echo e(route('deferred.index')); ?>',
                                        active: <?php echo e(request()->routeIs('deferred*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                                <?php if($canView('writeoffs')): ?>
                                    {
                                        label: 'Penghapusan Piutang',
                                        href: '<?php echo e(route('writeoffs.index')); ?>',
                                        active: <?php echo e(request()->routeIs('writeoffs*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?> {
                                    label: 'Periode Akuntansi',
                                    href: '<?php echo e(route('accounting.periods')); ?>',
                                    active: <?php echo e(request()->routeIs('accounting.periods*') ? 'true' : 'false'); ?>

                                },
                                <?php if($user?->isAdmin()): ?>
                                    {
                                        label: 'Kunci Periode & Backup',
                                        href: '<?php echo e(route('accounting.period-lock.index')); ?>',
                                        active: <?php echo e(request()->routeIs('accounting.period-lock*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                            <?php endif; ?>
                        ]
                    },
                    <?php if(($navTenant?->isModuleEnabled('hotel') ?? true) && !$user?->isKasir() && !$user?->isGudang()): ?>
                        
                    <?php endif; ?>
                    settings: {
                        title: 'Pengaturan',
                        items: [
                            <?php if(($navTenant?->isModuleEnabled('hotel') ?? true) && !$user?->isKasir() && !$user?->isGudang()): ?>
                                {
                                    section: 'Hotel PMS'
                                }, {
                                    label: 'Dashboard Hotel',
                                    href: '<?php echo e(route('hotel.dashboard')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.dashboard') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Tipe Kamar',
                                    href: '<?php echo e(route('hotel.room-types.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.room-types*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Kamar',
                                    href: '<?php echo e(route('hotel.rooms.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.rooms.index', 'hotel.rooms.show', 'hotel.rooms.edit') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Ketersediaan Kamar',
                                    href: '<?php echo e(route('hotel.rooms.availability')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.rooms.availability*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Reservasi',
                                    href: '<?php echo e(route('hotel.reservations.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.reservations*') && !request()->routeIs('hotel.reservations.checkin*', 'hotel.reservations.checkout*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Tamu',
                                    href: '<?php echo e(route('hotel.guests.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.guests*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Check-in / Check-out',
                                    href: '<?php echo e(route('hotel.checkin-out.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.checkin-out*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Housekeeping',
                                    href: '<?php echo e(route('hotel.housekeeping.room-board')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.housekeeping*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Tarif Kamar',
                                    href: '<?php echo e(route('hotel.rates.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.rates*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Channel Distribution',
                                    href: '<?php echo e(route('hotel.channels.index')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.channels*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pengaturan Hotel',
                                    href: '<?php echo e(route('hotel.settings.edit')); ?>',
                                    active: <?php echo e(request()->routeIs('hotel.settings*') ? 'true' : 'false'); ?>

                                },
                            <?php endif; ?> {
                                section: 'Akun & Notifikasi'
                            }, {
                                label: 'Notifikasi',
                                href: '<?php echo e(route('notifications.index')); ?>',
                                active: <?php echo e(request()->routeIs('notifications*') ? 'true' : 'false'); ?>

                            },
                            <?php if($user?->isAdmin()): ?>
                                {
                                    section: 'Pengaturan Perusahaan'
                                }, {
                                    label: 'Profil Perusahaan',
                                    href: '<?php echo e(route('company-profile.index')); ?>',
                                    active: <?php echo e(request()->routeIs('company-profile*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pengaturan Modul',
                                    href: '<?php echo e(route('settings.modules.index')); ?>',
                                    active: <?php echo e(request()->routeIs('settings.modules*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Kelola Pengguna',
                                    href: '<?php echo e(route('tenant.users.index')); ?>',
                                    active: <?php echo e(request()->routeIs('tenant.users*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pengingat',
                                    href: '<?php echo e(route('reminders.index')); ?>',
                                    active: <?php echo e(request()->routeIs('reminders*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Import CSV',
                                    href: '<?php echo e(route('import.index')); ?>',
                                    active: <?php echo e(request()->routeIs('import*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Audit Trail',
                                    href: '<?php echo e(route('audit.index')); ?>',
                                    active: <?php echo e(request()->routeIs('audit*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Bot WA/Telegram',
                                    href: '<?php echo e(route('bot.settings')); ?>',
                                    active: <?php echo e(request()->routeIs('bot*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Integrasi API',
                                    href: '<?php echo e(route('settings.integrations.index')); ?>',
                                    active: <?php echo e(request()->routeIs('settings.integrations*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'API & Webhook',
                                    href: '<?php echo e(route('api-settings.index')); ?>',
                                    active: <?php echo e(request()->routeIs('api-settings*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pusat Biaya',
                                    href: '<?php echo e(route('cost-centers.index')); ?>',
                                    active: <?php echo e(request()->routeIs('cost-centers*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Memori AI',
                                    href: '<?php echo e(route('ai-memory.index')); ?>',
                                    active: <?php echo e(request()->routeIs('ai-memory*') ? 'true' : 'false'); ?>

                                }, {
                                    section: 'Konfigurasi'
                                }, {
                                    label: 'Pengaturan Akuntansi',
                                    href: '<?php echo e(route('settings.accounting')); ?>',
                                    active: <?php echo e(request()->routeIs('settings.accounting*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Pajak',
                                    href: '<?php echo e(route('taxes.index')); ?>',
                                    active: <?php echo e(request()->routeIs('taxes*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Custom Fields',
                                    href: '<?php echo e(route('custom-fields.index')); ?>',
                                    active: <?php echo e(request()->routeIs('custom-fields*') ? 'true' : 'false'); ?>

                                }, {
                                    label: 'Batasan Bisnis',
                                    href: '<?php echo e(route('constraints.index')); ?>',
                                    active: <?php echo e(request()->routeIs('constraints*') ? 'true' : 'false'); ?>

                                },
                                <?php if(($navTenant?->isModuleEnabled('company_groups') ?? true) && $canView('company_groups')): ?>
                                    {
                                        label: 'Grup Perusahaan',
                                        href: '<?php echo e(route('company-groups.index')); ?>',
                                        active: <?php echo e(request()->routeIs('company-groups*') ? 'true' : 'false'); ?>

                                    },
                                <?php endif; ?>
                            <?php endif; ?> {
                                label: 'Langganan',
                                href: '<?php echo e(route('subscription.index')); ?>',
                                active: <?php echo e(request()->routeIs('subscription.index') ? 'true' : 'false'); ?>

                            },
                        ]
                    },
                <?php endif; ?>
            <?php endif; ?>
            profile: {
                title: 'Akun Saya',
                items: [{
                        label: '<?php echo e(addslashes($user?->name)); ?>',
                        href: '<?php echo e(route('profile.edit')); ?>',
                        active: <?php echo e(request()->routeIs('profile*') ? 'true' : 'false'); ?>,
                        meta: '<?php echo e($user?->roleLabel()); ?>'
                    },
                    <?php if(!$user?->isSuperAdmin() && !$user?->isAffiliate()): ?>
                        {
                            label: 'Portal Karyawan',
                            href: '<?php echo e(route('self-service.dashboard')); ?>',
                            active: false
                        },
                    <?php endif; ?> {
                        label: 'Keluar',
                        href: '#logout',
                        active: false,
                        danger: true
                    },
                ]
            },
        };

        const ACTIVE_GROUP = '<?php echo e($activeGroup); ?>';

        const MODULE_LIST = [
            <?php if($user?->isSuperAdmin()): ?>
                {
                    key: 'home',
                    label: 'Dashboard',
                    icon: 'home'
                }, {
                    key: 'superadmin',
                    label: 'Super Admin',
                    icon: 'building'
                },
            <?php elseif($user?->isAffiliate()): ?> {
                    key: 'home',
                    label: 'Dashboard',
                    icon: 'home'
                },
            <?php else: ?>
                {
                    key: 'home',
                    label: 'Dashboard',
                    icon: 'home'
                }, {
                    key: 'ai',
                    label: 'AI Chat',
                    icon: 'sparkle'
                }, {
                    key: 'transactions',
                    label: 'Transaksi',
                    icon: 'tag'
                }, {
                    key: 'inventory',
                    label: 'Inventori',
                    icon: 'cube'
                },
                <?php if(!$user?->isKasir() && !$user?->isGudang()): ?>
                    {
                        key: 'operations',
                        label: 'Operasional',
                        icon: 'cog'
                    }, {
                        key: 'finance',
                        label: 'Keuangan',
                        icon: 'currency'
                    }, {
                        key: 'settings',
                        label: 'Pengaturan',
                        icon: 'gear'
                    },
                <?php endif; ?>
            <?php endif; ?>
        ];

        // ── Task 1.3 & 1.4: Alpine.js navSystem Store ──
        document.addEventListener('alpine:init', () => {
            Alpine.store('navSystem', {
                // Launcher state
                launcherOpen: false,

                // Sidebar state
                activeModule: '<?php echo e($activeGroup); ?>',
                sidebarVisible: <?php echo e($activeGroup !== '' ? 'true' : 'false'); ?>,
                sidebarCollapsed: false,

                // Search
                launcherQuery: '',
                sidebarQuery: '',

                // Recently Visited (from localStorage)
                recentlyVisited: [],

                init() {
                    // Restore collapse state from localStorage
                    try {
                        this.sidebarCollapsed = localStorage.getItem('nav_sidebar_collapsed') === 'true';
                    } catch (e) {
                        this.sidebarCollapsed = false;
                    }

                    // Restore recently visited
                    try {
                        const stored = localStorage.getItem('nav_recently_visited');
                        this.recentlyVisited = stored ? JSON.parse(stored) : [];
                    } catch (e) {
                        this.recentlyVisited = [];
                    }

                    // Persist collapse state on change.
                    // NOTE: Alpine stores do NOT have $watch — use Alpine.effect() instead.
                    Alpine.effect(() => {
                        const val = this.sidebarCollapsed;
                        try {
                            localStorage.setItem('nav_sidebar_collapsed', val);
                        } catch (e) {}
                    });
                },

                toggleLauncher() {
                    this.launcherOpen = !this.launcherOpen;
                    if (this.launcherOpen) {
                        this.launcherQuery = '';
                        // NOTE: Alpine stores do NOT have $nextTick — use Alpine.nextTick() global.
                        Alpine.nextTick(() => {
                            document.getElementById('launcher-search')?.focus();
                        });
                    }
                },

                closeLauncher() {
                    this.launcherOpen = false;
                    // NOTE: Alpine stores do NOT have $nextTick — use Alpine.nextTick() global.
                    Alpine.nextTick(() => {
                        document.getElementById('launcher-btn')?.focus();
                    });
                },

                selectModule(moduleKey) {
                    if (moduleKey === this.activeModule) {
                        // Same module — just close launcher (Req 7.2)
                        this.closeLauncher();
                        return;
                    }
                    this.activeModule = moduleKey;
                    this.sidebarVisible = true;
                    this.sidebarQuery = '';
                    this.closeLauncher();
                    // Collapse state is preserved (Req 7.5)
                },

                toggleCollapse() {
                    this.sidebarCollapsed = !this.sidebarCollapsed;
                },

                openSidebar() {
                    this.sidebarCollapsed = false;
                },

                closeSidebar() {
                    // Used for mobile drawer close
                    this.sidebarVisible = false;
                },

                get filteredModules() {
                    const q = this.launcherQuery.toLowerCase().trim();
                    if (!q) return MODULE_LIST;
                    return MODULE_LIST.filter(m =>
                        m.label.toLowerCase().includes(q)
                    );
                },

                get filteredNavItems() {
                    const group = NAV_GROUPS[this.activeModule];
                    if (!group) return [];
                    const q = this.sidebarQuery.toLowerCase().trim();
                    if (!q) return group.items;
                    return group.items.filter(item =>
                        item.section || (item.label && item.label.toLowerCase().includes(q))
                    );
                }
            });
        });
    </script>


    <script>
        // ── Notification dropdown ──────────────────────────────────────────────
        function toggleNotif() {
            document.getElementById('notif-dropdown')?.classList.toggle('hidden');
        }
        document.addEventListener('click', e => {
            const w = document.getElementById('notif-wrapper');
            if (w && !w.contains(e.target)) {
                document.getElementById('notif-dropdown')?.classList.add('hidden');
            }
        });

        // ── Profile dropdown ──────────────────────────────────────────────
        function toggleProfile() {
            document.getElementById('profile-dropdown')?.classList.toggle('hidden');
        }
        document.addEventListener('click', e => {
            const w = document.getElementById('profile-wrapper');
            if (w && !w.contains(e.target)) {
                document.getElementById('profile-dropdown')?.classList.add('hidden');
            }
        });

        // ── DOMContentLoaded: track recently visited ───────────────────────────
        document.addEventListener('DOMContentLoaded', () => {
            // Task 5.3: Track recently visited page
            try {
                trackRecentlyVisited();
            } catch (e) {}
        });

        // Task 5.1 & 5.2: Recently Visited Tracking
        function trackRecentlyVisited() {
            const key = 'nav_recently_visited';
            const maxEntries = 5;
            const currentUrl = window.location.href;

            // Task 5.2: Extract clean page title (strip after " — " or " | ")
            const rawTitle = document.title || '';
            const currentTitle = rawTitle.split(/\s[—|]\s/)[0].trim() || rawTitle;

            // Guard: only track if Alpine store is available and activeModule is set
            if (typeof Alpine === 'undefined') return;
            const store = Alpine.store('navSystem');
            if (!store || !store.activeModule) return;

            const currentModule = store.activeModule;

            let entries = [];
            try {
                entries = JSON.parse(localStorage.getItem(key) || '[]');
            } catch (e) {
                entries = [];
            }

            // Task 5.1: Dedup by URL — remove existing entry for same URL
            entries = entries.filter(e => e.url !== currentUrl);

            // Add new entry at front
            entries.unshift({
                url: currentUrl,
                title: currentTitle,
                module: currentModule,
                visitedAt: Date.now()
            });

            // Slice to max 5 entries
            entries = entries.slice(0, maxEntries);

            try {
                localStorage.setItem(key, JSON.stringify(entries));
                // Update Alpine store so launcher shows updated list immediately
                store.recentlyVisited = entries;
            } catch (e) {
                // localStorage unavailable (private browsing / storage full) — fail silently
            }
        }

        // PWA + Push Notifications
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', async () => {
                    try {
                        const reg = await navigator.serviceWorker.register('/sw.js');

                        // Auto-subscribe to push only when user is authenticated
                        <?php if(auth()->guard()->check()): ?>
                        if (Notification.permission === 'granted') {
                            subscribePush(reg);
                        }
                    <?php endif; ?>
                } catch (e) {}
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
                await fetch('<?php echo e(route('push.subscribe')); ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>'
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

    
    <form id="logout-form" method="POST" action="<?php echo e(route('logout')); ?>" class="hidden"><?php echo csrf_field(); ?></form>

    
    <div id="help-modal" class="fixed inset-0 z-[9999] hidden" x-data="{ show: false }"
        <?php echo $__env->yieldSection(); ?>-help.window="show = true; $dispatch('help-show-topic', $event.detail)"
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

                    
                    <div class="px-6 py-5">
                        <div id="help-content" class="prose prose-sm max-w-none">
                            <p class="text-gray-600">Pilih topik bantuan untuk melihat panduan
                                lengkap.</p>
                        </div>

                        
                        <div id="help-tips" class="mt-6 hidden">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900">💡 Tips:</h4>
                            <ul id="help-tips-list" class="space-y-2 text-sm text-gray-700">
                                <!-- Tips will be inserted here -->
                            </ul>
                        </div>

                        
                        <div id="help-video" class="mt-6 hidden">
                            <h4 class="mb-3 text-sm font-semibold text-gray-900">🎥 Video Tutorial:
                            </h4>
                            <div class="aspect-video rounded-xl bg-gray-900 flex items-center justify-center">
                                <p class="text-gray-400 text-sm">Video akan tersedia segera</p>
                            </div>
                        </div>

                        
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

    
    <?php echo $__env->make('layouts._nav_launcher', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <?php echo $__env->yieldPushContent('scripts'); ?>

    <script>
        // ── Fullscreen (global, persisten antar halaman) ──────────────────────────
        const FS_KEY = 'qalcuity_fullscreen';

        function toggleFullscreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                localStorage.setItem(FS_KEY, '1');
            } else {
                document.exitFullscreen().catch(() => {});
                localStorage.removeItem(FS_KEY);
            }
        }

        function _updateFsIcon() {
            const isFs = !!document.fullscreenElement;
            const enter = document.getElementById('icon-fs-enter');
            const exit = document.getElementById('icon-fs-exit');
            const btn = document.getElementById('btn-fullscreen-main');
            if (enter) enter.classList.toggle('hidden', isFs);
            if (exit) exit.classList.toggle('hidden', !isFs);
            if (btn) btn.title = isFs ? 'Keluar layar penuh (Esc)' : 'Layar penuh (F11)';
        }

        // Saat user keluar fullscreen manual (Esc / tombol browser), hapus preferensi
        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                localStorage.removeItem(FS_KEY);
                _hideFsOverlay();
            }
            _updateFsIcon();
        });

        // Shortcut F11
        document.addEventListener('keydown', function(e) {
            if (e.key === 'F11') {
                e.preventDefault();
                toggleFullscreen();
            }
        });

        // ── Overlay "klik untuk fullscreen" ──────────────────────────────────────
        function _showFsOverlay() {
            const el = document.getElementById('fs-restore-overlay');
            if (el) el.classList.remove('hidden');
        }

        function _hideFsOverlay() {
            const el = document.getElementById('fs-restore-overlay');
            if (el) el.classList.add('hidden');
        }

        function _doRestoreFs() {
            _hideFsOverlay();
            document.documentElement.requestFullscreen().catch(function() {
                localStorage.removeItem(FS_KEY);
            });
        }

        // Auto-restore: tampilkan overlay jika preferensi aktif, restore saat diklik
        if (localStorage.getItem(FS_KEY) === '1' && !document.fullscreenElement) {
            // Tampilkan overlay setelah DOM siap
            document.addEventListener('DOMContentLoaded', _showFsOverlay);
            // Fallback jika DOMContentLoaded sudah lewat
            if (document.readyState !== 'loading') _showFsOverlay();
        }
    </script>

    
    <div id="fs-restore-overlay" onclick="_doRestoreFs()" class="hidden fixed inset-0 z-[9999] cursor-pointer"
        style="background: rgba(0,0,0,0.7);">
        <div
            style="position:absolute;top:50%;left:50%;transform:translate(-50%,-50%);text-align:center;color:#fff;pointer-events:none;">
            <div
                style="width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);display:flex;align-items:center;justify-content:center;margin:0 auto 16px;">
                <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 8V6a2 2 0 012-2h2M4 16v2a2 2 0 002 2h2m8-16h2a2 2 0 012 2v2m0 8v2a2 2 0 01-2 2h-2" />
                </svg>
            </div>
            <p style="font-size:16px;font-weight:600;margin-bottom:6px;">Klik untuk melanjutkan layar penuh</p>
            <p style="font-size:13px;opacity:0.6;">atau tekan tombol mana saja</p>
        </div>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/layouts/app.blade.php ENDPATH**/ ?>