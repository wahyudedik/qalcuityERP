<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('title', null, []); ?> Dashboard — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Dashboard <?php $__env->endSlot(); ?>

    <?php $__env->startPush('head'); ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.6/Sortable.min.js"></script>
        <style>
            .widget-item {
                transition: transform 0.2s, box-shadow 0.2s;
            }

            .widget-item.sortable-ghost {
                opacity: 0.4;
            }

            .widget-item.sortable-drag {
                transform: scale(1.02);
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.25);
                z-index: 50;
            }

            .widget-handle {
                cursor: grab;
            }

            .widget-handle:active {
                cursor: grabbing;
            }
        </style>
    <?php $__env->stopPush(); ?>

    
    <?php if (isset($component)) { $__componentOriginal010d8294a36ca9a8c5721c79868d801f = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal010d8294a36ca9a8c5721c79868d801f = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.offline-sync-status','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('offline-sync-status'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal010d8294a36ca9a8c5721c79868d801f)): ?>
<?php $attributes = $__attributesOriginal010d8294a36ca9a8c5721c79868d801f; ?>
<?php unset($__attributesOriginal010d8294a36ca9a8c5721c79868d801f); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal010d8294a36ca9a8c5721c79868d801f)): ?>
<?php $component = $__componentOriginal010d8294a36ca9a8c5721c79868d801f; ?>
<?php unset($__componentOriginal010d8294a36ca9a8c5721c79868d801f); ?>
<?php endif; ?>

    
    <div id="mobile-field-banner" class="mb-4 rounded-2xl overflow-hidden" style="display:none;">
        <div style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%); padding: 14px 16px;">
            <div style="display:flex; align-items:flex-start; justify-content:space-between; gap:10px;">
                <div style="flex:1; min-width:0;">
                    <p style="color:#fff; font-size:13px; font-weight:500; line-height:1.5; margin:0;">
                        📱 Anda menggunakan perangkat mobile. Beralih ke <strong>Mode Lapangan</strong> untuk pengalaman
                        yang lebih baik.
                    </p>
                    <a href="<?php echo e(route('mobile.hub')); ?>"
                        style="display:inline-block; margin-top:8px; padding:6px 14px; background:rgba(255,255,255,0.2); color:#fff; border:1px solid rgba(255,255,255,0.35); border-radius:10px; font-size:12px; font-weight:600; text-decoration:none; backdrop-filter:blur(4px);">
                        🚀 Buka Mode Lapangan
                    </a>
                </div>
                <button onclick="dismissMobileFieldBanner()" aria-label="Tutup"
                    style="flex-shrink:0; width:28px; height:28px; background:rgba(255,255,255,0.15); border:none; border-radius:8px; color:#fff; cursor:pointer; display:flex; align-items:center; justify-content:center; transition:background 0.15s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.25)'"
                    onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>
    <script>
        (function() {
            var BANNER_KEY = 'qalcuity_mobile_banner_dismissed';
            var banner = document.getElementById('mobile-field-banner');
            if (banner && window.innerWidth < 1024 && !localStorage.getItem(BANNER_KEY)) {
                banner.style.display = 'block';
            }
            window.dismissMobileFieldBanner = function() {
                localStorage.setItem(BANNER_KEY, '1');
                if (banner) banner.style.display = 'none';
            };
        })();
    </script>

    
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Selamat datang, <?php echo e(auth()->user()->name); ?>

                👋
            </h2>
            <p class="text-sm text-gray-500 mt-0.5"><?php echo e(now()->translatedFormat('l, d F Y')); ?></p>
        </div>
        <button onclick="document.getElementById('widgetModal').classList.remove('hidden')"
            class="flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-500 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" />
            </svg>
            Kustomisasi
        </button>
    </div>

    
    <?php
        $tenant = auth()->user()->tenant;
        $checkSteps = [
            'profile' => [
                'label' => 'Lengkapi profil perusahaan',
                'done' => !empty($tenant?->phone) && !empty($tenant?->address),
                'url' => route('company-profile.index'),
                'icon' => '🏢',
            ],
            'coa' => [
                'label' => 'Load Chart of Accounts',
                'done' => \App\Models\ChartOfAccount::where('tenant_id', $tenant?->id)->count() >= 10,
                'url' => route('settings.accounting') . '?tab=coa',
                'icon' => '📊',
            ],
            'warehouse' => [
                'label' => 'Tambah gudang pertama',
                'done' => \App\Models\Warehouse::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('warehouses.index'),
                'icon' => '🏭',
            ],
            'product' => [
                'label' => 'Tambah produk pertama',
                'done' => \App\Models\Product::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('products.index'),
                'icon' => '📦',
            ],
            'customer' => [
                'label' => 'Tambah customer pertama',
                'done' => \App\Models\Customer::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('customers.index'),
                'icon' => '👤',
            ],
            'so' => [
                'label' => 'Buat Sales Order pertama',
                'done' => \App\Models\SalesOrder::where('tenant_id', $tenant?->id)->exists(),
                'url' => route('sales.create'),
                'icon' => '🧾',
            ],
        ];
        $doneCount = collect($checkSteps)->where('done', true)->count();
        $totalSteps = count($checkSteps);
        $allDone = $doneCount === $totalSteps;
        $pct = $totalSteps > 0 ? round(($doneCount / $totalSteps) * 100) : 0;
    ?>
    <?php if(!$allDone && $tenant): ?>
        <div class="mb-6 bg-white rounded-2xl border border-gray-200 p-5"
            id="setup-checklist">
            <div class="flex items-center justify-between mb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-xl bg-blue-500/20 flex items-center justify-center text-lg">🚀</div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Setup Bisnis Anda</p>
                        <p class="text-xs text-gray-500"><?php echo e($doneCount); ?>/<?php echo e($totalSteps); ?>

                            langkah selesai</p>
                    </div>
                </div>
                <button onclick="document.getElementById('setup-checklist').remove()"
                    class="text-xs text-gray-400 hover:text-gray-600"
                    title="Sembunyikan">✕</button>
            </div>
            <div class="w-full h-2 bg-gray-100 rounded-full mb-4 overflow-hidden">
                <div class="h-full rounded-full transition-all duration-500 <?php echo e($pct >= 100 ? 'bg-green-500' : ($pct >= 50 ? 'bg-blue-500' : 'bg-amber-500')); ?>"
                    style="width:<?php echo e($pct); ?>%"></div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2">
                <?php $__currentLoopData = $checkSteps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e($step['done'] ? '#' : $step['url']); ?>"
                        class="flex items-center gap-3 px-3 py-2.5 rounded-xl transition
                    <?php echo e($step['done']
                        ? 'bg-green-50 border border-green-200'
                        : 'bg-gray-50 border border-gray-200 hover:border-blue-300 hover:bg-blue-50'); ?>">
                        <span class="text-lg shrink-0"><?php echo e($step['done'] ? '✅' : $step['icon']); ?></span>
                        <span
                            class="text-sm <?php echo e($step['done'] ? 'text-green-700 line-through' : 'text-gray-700 font-medium'); ?>">
                            <?php echo e($step['label']); ?>

                        </span>
                        <?php if(!$step['done']): ?>
                            <svg class="w-4 h-4 ml-auto text-gray-400 shrink-0" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        <?php endif; ?>
                    </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div id="widget-grid" class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php $__currentLoopData = collect($userWidgets)->where('visible', true)->sortBy('order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php if(isset($registry[$w['key']])): ?>
                <?php
                    $meta = $registry[$w['key']];
                    // cols_override allows the user to resize a widget
                    $colsVal = (int) ($w['cols_override'] ?? ($meta['cols'] ?? 1));
                    $colClass = match ($colsVal) {
                        1 => 'col-span-1',
                        2 => 'col-span-2',
                        4 => 'col-span-2 lg:col-span-4',
                        default => 'col-span-1',
                    };
                    // Resolve partial from controller-provided allowlist (security: prevents arbitrary view inclusion)
                    $partial = $allowedPartials[$w['key']] ?? null;
                ?>
                <div class="widget-item <?php echo e($colClass); ?> relative group" data-key="<?php echo e($w['key']); ?>">
                    
                    <div
                        class="widget-handle absolute -top-1.5 left-1/2 -translate-x-1/2 z-10 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                        <div
                            class="flex items-center gap-0.5 bg-gray-200 rounded-full px-2.5 py-0.5 shadow-sm">
                            <svg class="w-3 h-3 text-gray-400" viewBox="0 0 24 24"
                                fill="currentColor">
                                <circle cx="9" cy="6" r="1.5" />
                                <circle cx="15" cy="6" r="1.5" />
                                <circle cx="9" cy="12" r="1.5" />
                                <circle cx="15" cy="12" r="1.5" />
                                <circle cx="9" cy="18" r="1.5" />
                                <circle cx="15" cy="18" r="1.5" />
                            </svg>
                        </div>
                    </div>
                    <?php if($partial): ?>
                        <?php echo $__env->make($partial, ['data' => $widgetData[$w['key']] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div id="widgetModal"
        class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div
            class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg max-h-[85vh] flex flex-col">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-200">
                <div>
                    <h3 class="text-base font-semibold text-gray-900">Kustomisasi Dashboard</h3>
                    <p class="text-xs text-gray-500 mt-0.5">Aktifkan, urutkan, dan ubah ukuran
                        widget. Drag untuk mengubah urutan.</p>
                </div>
                <button onclick="document.getElementById('widgetModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="overflow-y-auto flex-1 px-4 py-3" id="widget-toggle-list">
                <?php $__currentLoopData = $availableKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(isset($registry[$key])): ?>
                        <?php
                            $meta = $registry[$key];
                            $currentWidget = collect($userWidgets)->firstWhere('key', $key);
                            $isVisible = $currentWidget && ($currentWidget['visible'] ?? false);
                            $colsOverride = $currentWidget['cols_override'] ?? null;
                            $defaultCols = (int) $meta['cols'];
                            $activeCols = $colsOverride ?? $defaultCols;
                            $isCustom = !empty($meta['is_custom']);
                        ?>
                        <div class="modal-widget-item flex items-center gap-2 p-2.5 rounded-xl mb-1.5 cursor-grab
                            bg-gray-50 border border-gray-200 hover:bg-gray-100 transition"
                            data-widget-key="<?php echo e($key); ?>">
                            
                            <div class="modal-drag-handle shrink-0 text-gray-300 cursor-grab">
                                <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                    <circle cx="9" cy="6" r="1.5" />
                                    <circle cx="15" cy="6" r="1.5" />
                                    <circle cx="9" cy="12" r="1.5" />
                                    <circle cx="15" cy="12" r="1.5" />
                                    <circle cx="9" cy="18" r="1.5" />
                                    <circle cx="15" cy="18" r="1.5" />
                                </svg>
                            </div>
                            
                            <input type="checkbox"
                                class="widget-toggle rounded border-gray-300 text-blue-600 shrink-0"
                                data-widget-key="<?php echo e($key); ?>" <?php echo e($isVisible ? 'checked' : ''); ?>>
                            <div
                                class="w-7 h-7 rounded-lg <?php echo e($meta['icon_bg']); ?> flex items-center justify-center shrink-0">
                                <svg class="w-3.5 h-3.5 <?php echo e($meta['icon_color']); ?>" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                                        d="<?php echo e($meta['icon']); ?>" />
                                </svg>
                            </div>
                            
                            <div class="flex-1 min-w-0">
                                <p class="text-xs font-medium text-gray-900 truncate">
                                    <?php echo e($meta['title']); ?></p>
                            </div>
                            
                            <div class="flex items-center gap-0.5 shrink-0">
                                <?php $__currentLoopData = [1 => 'S', 2 => 'M', 4 => 'L']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sz => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($defaultCols <= $sz || $sz === 1): ?>
                                        <button
                                            onclick="setWidgetSize('<?php echo e($key); ?>', <?php echo e($sz); ?>, this)"
                                            data-widget-key="<?php echo e($key); ?>" data-size="<?php echo e($sz); ?>"
                                            class="size-btn px-1.5 py-0.5 rounded text-xs font-medium transition
                                            <?php echo e($activeCols === $sz ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200'); ?>">
                                            <?php echo e($label); ?>

                                        </button>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            
                            <?php if($isCustom): ?>
                                <button onclick="openWidgetBuilder(<?php echo e($meta['custom_id'] ?? 0); ?>)"
                                    class="shrink-0 p-1 rounded-lg text-gray-400 hover:text-blue-400 hover:bg-blue-500/10 transition"
                                    title="Edit widget">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                                    </svg>
                                </button>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
            <div class="flex items-center justify-between px-6 py-4 border-t border-gray-200">
                <div class="flex items-center gap-2">
                    <button onclick="resetWidgets()"
                        class="text-xs text-red-400 hover:text-red-300 font-medium transition">Reset Default</button>
                    <?php if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])): ?>
                        <span class="text-gray-200">|</span>
                        <button onclick="openWidgetBuilder(null)"
                            class="flex items-center gap-1 text-xs text-blue-500 hover:text-blue-400 font-medium transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4v16m8-8H4" />
                            </svg>
                            Buat Widget Baru
                        </button>
                    <?php endif; ?>
                </div>
                <div class="flex gap-2">
                    <button onclick="document.getElementById('widgetModal').classList.add('hidden')"
                        class="px-4 py-2 rounded-lg text-sm border border-gray-200 text-gray-700 hover:bg-gray-50 transition">Batal</button>
                    <button onclick="saveWidgetConfig()"
                        class="px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition"
                        id="btn-save-widgets">Simpan</button>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(in_array(auth()->user()->role, ['admin', 'manager', 'super_admin'])): ?>
        <div id="modal-widget-builder"
            class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-black/60 backdrop-blur-sm p-4">
            <div
                class="bg-white rounded-2xl border border-gray-200 w-full max-w-lg max-h-[90vh] flex flex-col shadow-xl">
                <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                    <div>
                        <h3 class="font-semibold text-gray-900 text-sm" id="builder-title">Buat Widget
                            Baru</h3>
                        <p class="text-xs text-gray-400 mt-0.5">Kartu metrik kustom berbasis data
                            ERP</p>
                    </div>
                    <button onclick="closeWidgetBuilder()"
                        class="text-gray-400 hover:text-gray-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="overflow-y-auto flex-1 p-6 space-y-4">
                    <input type="hidden" id="builder-id" value="">

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Widget
                            <span class="text-red-400">*</span></label>
                        <input type="text" id="builder-title-input" maxlength="60"
                            placeholder="Contoh: Total Invoice Lunas"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Subtitle
                            (opsional)</label>
                        <input type="text" id="builder-subtitle" maxlength="100" placeholder="Contoh: Bulan ini"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tipe Metrik
                                <span class="text-red-400">*</span></label>
                            <select id="builder-metric-type" onchange="toggleBuilderFields()"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="count">Count (hitung baris)</option>
                                <option value="sum">Sum (jumlahkan kolom)</option>
                                <option value="avg">Avg (rata-rata kolom)</option>
                                <option value="static">Static (nilai tetap)</option>
                            </select>
                        </div>

                        
                        <div id="builder-model-wrap">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Sumber
                                Data</label>
                            <select id="builder-model-class"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih --</option>
                                <option value="SalesOrder">Sales Order</option>
                                <option value="PurchaseOrder">Purchase Order</option>
                                <option value="Transaction">Transaksi</option>
                                <option value="Invoice">Invoice</option>
                                <option value="Customer">Customer</option>
                                <option value="Employee">Karyawan</option>
                                <option value="EcommerceOrder">Order Marketplace</option>
                                <option value="Attendance">Kehadiran</option>
                            </select>
                        </div>
                    </div>

                    
                    <div id="builder-column-wrap" class="hidden">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama
                            Kolom</label>
                        <input type="text" id="builder-metric-column" placeholder="Contoh: total atau amount"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div id="builder-static-wrap" class="hidden">
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nilai</label>
                        <input type="number" id="builder-static-value" placeholder="100"
                            class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        
                        <div id="builder-date-wrap">
                            <label
                                class="block text-xs font-medium text-gray-600 mb-1">Periode</label>
                            <select id="builder-date-scope"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="this_month">Bulan Ini</option>
                                <option value="today">Hari Ini</option>
                                <option value="this_year">Tahun Ini</option>
                                <option value="all_time">Semua Waktu</option>
                            </select>
                        </div>

                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Format
                                Nilai</label>
                            <select id="builder-value-format"
                                class="w-full px-3 py-2 rounded-xl border border-gray-200 bg-gray-50 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="number">Angka (1.234)</option>
                                <option value="currency">Mata Uang (Rp 1.234)</option>
                                <option value="percent">Persen (12.3%)</option>
                            </select>
                        </div>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Ukuran
                            Widget</label>
                        <div class="flex gap-2">
                            <?php $__currentLoopData = [1 => 'Kecil (1 kolom)', 2 => 'Sedang (2 kolom)', 4 => 'Lebar (4 kolom)']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sz => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label
                                    class="flex-1 flex items-center gap-2 p-2.5 rounded-xl border border-gray-200 cursor-pointer hover:border-blue-400 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-50">
                                    <input type="radio" name="builder-cols" value="<?php echo e($sz); ?>"
                                        <?php echo e($sz === 1 ? 'checked' : ''); ?> class="text-blue-600">
                                    <span class="text-xs text-gray-700"><?php echo e($lbl); ?></span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    
                    <div class="bg-gray-50 rounded-xl p-3 flex items-center justify-between">
                        <span class="text-xs text-gray-500">Preview nilai:</span>
                        <div class="flex items-center gap-2">
                            <span id="builder-preview-value"
                                class="text-lg font-bold text-gray-900">—</span>
                            <button onclick="previewWidget()"
                                class="text-xs text-blue-500 hover:text-blue-400 underline">Hitung</button>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between px-6 py-4 border-t border-gray-100">
                    <div id="builder-delete-wrap" class="hidden">
                        <button onclick="deleteCustomWidget()"
                            class="text-xs text-red-400 hover:text-red-300 font-medium">Hapus Widget</button>
                    </div>
                    <div class="ml-auto flex gap-2">
                        <button onclick="closeWidgetBuilder()"
                            class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-700">Batal</button>
                        <button onclick="saveCustomWidget()" id="builder-save-btn"
                            class="px-4 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // ── Constants ──────────────────────────────────────────────────
            const CSRF = '<?php echo e(csrf_token()); ?>';
            const SAVE_URL = "<?php echo e(route('dashboard.widgets.save')); ?>";
            const RESET_URL = "<?php echo e(route('dashboard.widgets.reset')); ?>";
            const REFRESH_URL = "<?php echo e(route('dashboard.refresh-insights')); ?>";
            const ACK_URL_BASE = "<?php echo e(url('/dashboard/anomalies')); ?>";

            // ── Chart global defaults ─────────────────────────────────────
            const isDark = document.getElementById('html-root')?.classList.contains('dark');
            window._chartDefaults = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                }
            };
            window._chartGridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
            window._chartTickColor = isDark ? '#94a3b8' : '#6b7280';
            window._chartTickFont = {
                size: 10,
                family: 'Inter'
            };

            // ── SortableJS — drag-and-drop reorder (grid) ─────────────────
            const grid = document.getElementById('widget-grid');
            if (grid && typeof Sortable !== 'undefined') {
                Sortable.create(grid, {
                    handle: '.widget-handle',
                    animation: 200,
                    ghostClass: 'sortable-ghost',
                    dragClass: 'sortable-drag',
                    onEnd: function() {
                        autoSaveOrder();
                    }
                });
            }

            // ── SortableJS — modal list reorder ───────────────────────────
            const modalList = document.getElementById('widget-toggle-list');
            if (modalList && typeof Sortable !== 'undefined') {
                Sortable.create(modalList, {
                    handle: '.modal-drag-handle',
                    animation: 150,
                    ghostClass: 'opacity-40',
                });
            }

            // ── Size toggle ───────────────────────────────────────────────
            // colsOverrides: key -> cols value (persisted on save)
            const colsOverrides = {};

            function setWidgetSize(key, size, btn) {
                colsOverrides[key] = size;
                // Update UI state for all size buttons of this widget
                document.querySelectorAll(`.size-btn[data-widget-key="${key}"]`).forEach(b => {
                    const active = parseInt(b.dataset.size) === size;
                    b.className = b.className
                        .replace(/bg-blue-500 text-white/g, '')
                        .replace(
                            /bg-gray-100\/10 text-gray-500 hover:bg-gray-200\/20/g,
                            '');
                    if (active) {
                        b.classList.add('bg-blue-500', 'text-white');
                    } else {
                        b.classList.add('bg-gray-100', 'text-gray-500',
                            'hover:bg-gray-200');
                    }
                });
            }

            function getColsOverrideForKey(key) {
                if (colsOverrides[key] !== undefined) return colsOverrides[key];
                // Read from active size btn
                const activeBtn = document.querySelector(`.size-btn[data-widget-key="${key}"].bg-blue-500`);
                return activeBtn ? parseInt(activeBtn.dataset.size) : null;
            }

            function autoSaveOrder() {
                const items = grid.querySelectorAll('.widget-item');
                const widgets = [];
                items.forEach((el, i) => {
                    widgets.push({
                        key: el.dataset.key,
                        order: i,
                        visible: true,
                        cols_override: getColsOverrideForKey(el.dataset.key),
                    });
                });
                // Also include hidden widgets
                document.querySelectorAll('.widget-toggle').forEach(cb => {
                    const key = cb.dataset.widgetKey;
                    if (!cb.checked && !widgets.find(w => w.key === key)) {
                        widgets.push({
                            key,
                            order: 999,
                            visible: false,
                            cols_override: getColsOverrideForKey(key),
                        });
                    }
                });
                fetch(SAVE_URL, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': CSRF,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        widgets
                    }),
                }).catch(e => console.error('Auto-save failed', e));
            }

            // ── Widget toggle save ────────────────────────────────────────
            async function saveWidgetConfig() {
                const btn = document.getElementById('btn-save-widgets');
                btn.disabled = true;
                btn.textContent = 'Menyimpan...';

                const items = grid.querySelectorAll('.widget-item');
                const visibleKeys = new Set();
                items.forEach(el => visibleKeys.add(el.dataset.key));

                const widgets = [];
                let order = 0;

                // Visible widgets in current grid order
                items.forEach(el => {
                    widgets.push({
                        key: el.dataset.key,
                        order: order++,
                        visible: true,
                        cols_override: getColsOverrideForKey(el.dataset.key),
                    });
                });

                // Determine order from modal list (for newly toggled-on widgets)
                const modalOrder = [];
                document.querySelectorAll('#widget-toggle-list [data-widget-key]').forEach(item => {
                    modalOrder.push(item.dataset.widgetKey);
                });

                // Toggled on/off widgets from modal
                modalOrder.forEach(key => {
                    const cb = document.querySelector(`.widget-toggle[data-widget-key="${key}"]`);
                    if (!cb) return;
                    if (cb.checked && !visibleKeys.has(key)) {
                        widgets.push({
                            key,
                            order: order++,
                            visible: true,
                            cols_override: getColsOverrideForKey(key),
                        });
                    } else if (!cb.checked) {
                        const idx = widgets.findIndex(w => w.key === key);
                        if (idx !== -1) widgets[idx].visible = false;
                        else widgets.push({
                            key,
                            order: 999,
                            visible: false,
                            cols_override: getColsOverrideForKey(key),
                        });
                    }
                });

                try {
                    await fetch(SAVE_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({
                            widgets
                        }),
                    });
                    window.location.reload();
                } catch (e) {
                    console.error('Save failed', e);
                    btn.disabled = false;
                    btn.textContent = 'Simpan';
                }
            }

            async function resetWidgets() {
                if (!confirm('Reset dashboard ke layout default untuk role Anda?')) return;
                try {
                    await fetch(RESET_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    window.location.reload();
                } catch (e) {
                    console.error('Reset failed', e);
                }
            }

            // ── Widget Builder ────────────────────────────────────────────
            const CUSTOM_STORE_URL = "<?php echo e(route('dashboard.custom-widgets.store')); ?>";
            const CUSTOM_PREVIEW_URL = "<?php echo e(route('dashboard.custom-widgets.preview')); ?>";
            const CUSTOM_UPDATE_BASE = "<?php echo e(url('/dashboard/custom-widgets')); ?>";

            function openWidgetBuilder(id) {
                const modal = document.getElementById('modal-widget-builder');
                if (!modal) return;

                // Close customize modal
                document.getElementById('widgetModal')?.classList.add('hidden');

                document.getElementById('builder-id').value = id || '';
                document.getElementById('builder-title').textContent = id ? 'Edit Widget' : 'Buat Widget Baru';
                document.getElementById('builder-preview-value').textContent = '—';

                const deleteWrap = document.getElementById('builder-delete-wrap');
                deleteWrap?.classList.toggle('hidden', !id);

                if (id) {
                    // Fetch existing widget data and populate form
                    fetch(`${CUSTOM_UPDATE_BASE}/${id}`, {
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': CSRF
                            }
                        })
                        .then(r => r.json())
                        .then(d => {
                            document.getElementById('builder-title-input').value = d.title ?? '';
                            document.getElementById('builder-subtitle').value = d.subtitle ?? '';
                            document.getElementById('builder-metric-type').value = d.metric_type ?? 'count';
                            document.getElementById('builder-model-class').value = d.model_class ?? '';
                            document.getElementById('builder-metric-column').value = d.metric_column ?? '';
                            document.getElementById('builder-static-value').value = d.static_value ?? '';
                            document.getElementById('builder-date-scope').value = d.date_scope ?? 'this_month';
                            document.getElementById('builder-value-format').value = d.value_format ?? 'number';
                            const radios = document.querySelectorAll('input[name="builder-cols"]');
                            radios.forEach(r => {
                                r.checked = parseInt(r.value) === (d.cols ?? 1);
                            });
                            toggleBuilderFields();
                        })
                        .catch(() => {});
                } else {
                    // Clear form for new widget
                    document.getElementById('builder-title-input').value = '';
                    document.getElementById('builder-subtitle').value = '';
                    document.getElementById('builder-metric-type').value = 'count';
                    document.getElementById('builder-model-class').value = '';
                    document.getElementById('builder-metric-column').value = '';
                    document.getElementById('builder-static-value').value = '';
                    document.getElementById('builder-date-scope').value = 'this_month';
                    document.getElementById('builder-value-format').value = 'number';
                    const radios = document.querySelectorAll('input[name="builder-cols"]');
                    radios.forEach(r => {
                        r.checked = r.value === '1';
                    });
                    toggleBuilderFields();
                }

                modal.classList.remove('hidden');
            }

            function closeWidgetBuilder() {
                document.getElementById('modal-widget-builder')?.classList.add('hidden');
                // Re-open customize modal
                document.getElementById('widgetModal')?.classList.remove('hidden');
            }

            function toggleBuilderFields() {
                const type = document.getElementById('builder-metric-type')?.value;
                document.getElementById('builder-column-wrap')?.classList.toggle('hidden', !['sum', 'avg'].includes(type));
                document.getElementById('builder-static-wrap')?.classList.toggle('hidden', type !== 'static');
                document.getElementById('builder-date-wrap')?.classList.toggle('hidden', type === 'static');
                document.getElementById('builder-model-wrap')?.classList.toggle('hidden', type === 'static');
            }

            async function previewWidget() {
                const btn = document.querySelector('[onclick="previewWidget()"]');
                const previewEl = document.getElementById('builder-preview-value');
                if (btn) btn.textContent = '...';

                const payload = {
                    metric_type: document.getElementById('builder-metric-type')?.value,
                    model_class: document.getElementById('builder-model-class')?.value,
                    metric_column: document.getElementById('builder-metric-column')?.value,
                    static_value: document.getElementById('builder-static-value')?.value,
                    date_scope: document.getElementById('builder-date-scope')?.value,
                    value_format: document.getElementById('builder-value-format')?.value,
                };

                try {
                    const res = await fetch(CUSTOM_PREVIEW_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload),
                    });
                    const data = await res.json();
                    previewEl.textContent = data.display ?? data.value ?? '—';
                } catch (e) {
                    previewEl.textContent = 'Error';
                } finally {
                    if (btn) btn.textContent = 'Hitung';
                }
            }

            async function saveCustomWidget() {
                const titleVal = document.getElementById('builder-title-input')?.value?.trim();
                if (!titleVal) {
                    alert('Nama widget wajib diisi.');
                    return;
                }

                const saveBtn = document.getElementById('builder-save-btn');
                saveBtn.disabled = true;
                saveBtn.textContent = 'Menyimpan...';

                const id = document.getElementById('builder-id')?.value;
                const cols = parseInt(document.querySelector('input[name="builder-cols"]:checked')?.value ?? '1');
                const payload = {
                    title: titleVal,
                    subtitle: document.getElementById('builder-subtitle')?.value?.trim(),
                    metric_type: document.getElementById('builder-metric-type')?.value,
                    model_class: document.getElementById('builder-model-class')?.value,
                    metric_column: document.getElementById('builder-metric-column')?.value?.trim(),
                    static_value: document.getElementById('builder-static-value')?.value,
                    date_scope: document.getElementById('builder-date-scope')?.value,
                    value_format: document.getElementById('builder-value-format')?.value,
                    cols,
                };

                try {
                    const url = id ? `${CUSTOM_UPDATE_BASE}/${id}` : CUSTOM_STORE_URL;
                    const method = id ? 'PUT' : 'POST';
                    const res = await fetch(url, {
                        method,
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify(payload),
                    });

                    if (!res.ok) {
                        const err = await res.json().catch(() => ({}));
                        alert(err.message ?? 'Gagal menyimpan widget.');
                        saveBtn.disabled = false;
                        saveBtn.textContent = 'Simpan';
                        return;
                    }

                    window.location.reload();
                } catch (e) {
                    alert('Gagal menyimpan widget. Periksa koneksi.');
                    saveBtn.disabled = false;
                    saveBtn.textContent = 'Simpan';
                }
            }

            async function deleteCustomWidget() {
                const id = document.getElementById('builder-id')?.value;
                if (!id || !confirm('Hapus widget ini? Tindakan tidak dapat dibatalkan.')) return;

                try {
                    const res = await fetch(`${CUSTOM_UPDATE_BASE}/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    if (!res.ok) throw new Error('Delete failed');
                    window.location.reload();
                } catch (e) {
                    alert('Gagal menghapus widget.');
                }
            }

            // ── AI Insight Refresh ────────────────────────────────────────
            async function refreshDashboardInsights() {
                const btn = document.getElementById('btn-refresh-insights');
                const icon = document.getElementById('refresh-icon');
                if (!btn) return;

                btn.disabled = true;
                icon.classList.add('animate-spin');

                try {
                    const res = await fetch(REFRESH_URL, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    const data = await res.json();

                    const grid = document.getElementById('insights-grid');
                    if (grid && data.insights?.length) {
                        grid.innerHTML = data.insights.map(insight => insightCard(insight)).join('');
                    }

                    const list = document.getElementById('anomaly-list');
                    if (list && data.anomalies?.length) {
                        list.innerHTML = data.anomalies.map(a => anomalyRow(a)).join('');
                        document.getElementById('anomaly-section')?.classList.remove('hidden');
                    } else if (list && !data.anomalies?.length) {
                        document.getElementById('anomaly-section')?.classList.add('hidden');
                    }

                    const ts = document.getElementById('insights-updated-at');
                    if (ts) ts.textContent = `— diperbarui pukul ${data.updated_at}`;
                } catch (e) {
                    console.error('Refresh insights failed', e);
                } finally {
                    btn.disabled = false;
                    icon.classList.remove('animate-spin');
                }
            }

            async function acknowledgeAnomaly(id, btn) {
                btn.disabled = true;
                btn.textContent = '...';
                try {
                    await fetch(`${ACK_URL_BASE}/${id}/acknowledge`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json'
                        },
                    });
                    const el = document.getElementById(`anomaly-${id}`);
                    if (el) {
                        el.style.opacity = '0';
                        el.style.transition = 'opacity 0.3s';
                        setTimeout(() => el.remove(), 300);
                    }
                } catch (e) {
                    btn.disabled = false;
                    btn.textContent = '✓ Tinjau';
                }
            }

            function insightCard(insight) {
                const borders = {
                    critical: 'border-red-500/40 bg-red-500/5',
                    warning: 'border-yellow-500/40 bg-yellow-500/5',
                    info: 'border-blue-500/20 bg-blue-500/5'
                };
                const badges = {
                    critical: 'bg-red-500/20 text-red-400',
                    warning: 'bg-yellow-500/20 text-yellow-400',
                    info: 'bg-blue-500/20 text-blue-400'
                };
                const labels = {
                    critical: 'Kritis',
                    warning: 'Perhatian',
                    info: 'Info'
                };
                const sev = insight.severity || 'info';
                const action = insight.action ?
                    `<a href="/chat?q=${encodeURIComponent(insight.action)}" class="text-xs text-indigo-400 hover:text-indigo-300 font-medium mt-auto">Tanya AI → ${insight.action}</a>` :
                    '';
                return `<div class="rounded-xl border ${borders[sev] || borders.info} p-4 flex flex-col gap-2">
            <div class="flex items-start justify-between gap-2">
                <p class="text-sm font-semibold text-gray-900 leading-snug">${insight.title}</p>
                <span class="text-xs font-medium px-2 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
            </div>
            <p class="text-xs text-gray-500 leading-relaxed">${insight.body}</p>
            ${action}
        </div>`;
            }

            function anomalyRow(a) {
                const borders = {
                    critical: 'border-red-500/40 bg-red-500/5',
                    warning: 'border-yellow-500/40 bg-yellow-500/5',
                    info: 'border-orange-500/20 bg-orange-500/5'
                };
                const badges = {
                    critical: 'bg-red-500/20 text-red-400',
                    warning: 'bg-yellow-500/20 text-yellow-400',
                    info: 'bg-orange-500/20 text-orange-400'
                };
                const icons = {
                    critical: 'text-red-400',
                    warning: 'text-yellow-400',
                    info: 'text-orange-400'
                };
                const labels = {
                    critical: 'Kritis',
                    warning: 'Perhatian',
                    info: 'Info'
                };
                const sev = a.severity || 'info';
                return `<div class="rounded-xl border ${borders[sev] || borders.info} p-3.5 flex items-start gap-3" id="anomaly-${a.id}">
            <svg class="w-4 h-4 ${icons[sev]} shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-0.5">
                    <p class="text-sm font-semibold text-gray-900 truncate">${a.title}</p>
                    <span class="text-xs font-medium px-1.5 py-0.5 rounded-full shrink-0 ${badges[sev] || badges.info}">${labels[sev] || 'Info'}</span>
                </div>
                <p class="text-xs text-gray-500 leading-relaxed">${a.description}</p>
                <p class="text-xs text-gray-400 mt-1">${a.age}</p>
            </div>
            <button onclick="acknowledgeAnomaly(${a.id}, this)" class="text-xs text-gray-400 hover:text-green-400 transition shrink-0 font-medium" title="Tandai sudah ditinjau">Tinjau</button>
        </div>`;
            }

            // ── Init chart widgets after DOM ready ─────────────────────────
            requestAnimationFrame(() => setTimeout(() => {
                document.dispatchEvent(new Event('widgets-ready'));
            }, 50));

            // ── Offline: cache dashboard stats for offline viewing ─────────
            if (window.ErpOffline) {
                const statsEls = document.querySelectorAll('[data-stat-key]');
                const statsData = {};
                statsEls.forEach(el => {
                    statsData[el.dataset.statKey] = el.textContent.trim();
                });
                if (Object.keys(statsData).length > 0) {
                    window.ErpOffline.cacheData('dashboard:stats', 'dashboard', statsData);
                }
            }
        </script>
    <?php $__env->stopPush(); ?>

    
    <?php if(!empty($popupAd)): ?>
        <div x-data="{ open: true }" x-show="open" x-cloak
            class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
            x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 scale-95"
            x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95">
            <div
                class="bg-white rounded-2xl w-full max-w-lg shadow-2xl overflow-hidden border border-gray-200">
                
                <?php if($popupAd->image_path): ?>
                    <div class="relative">
                        <img src="<?php echo e(Storage::url($popupAd->image_path)); ?>" class="w-full max-h-64 object-cover"
                            alt="<?php echo e($popupAd->title); ?>">
                    </div>
                <?php endif; ?>

                
                <div class="p-6">
                    <div class="flex items-start justify-between gap-4 mb-3">
                        <h3 class="text-lg font-bold text-gray-900 leading-snug">
                            <?php echo e($popupAd->title); ?>

                        </h3>
                        <button
                            @click="open=false; fetch('<?php echo e(route('popup-ads.dismiss', $popupAd)); ?>', {method:'POST',headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>','Content-Type':'application/json'}})"
                            class="shrink-0 p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition"
                            aria-label="Tutup">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                    d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <?php if($popupAd->body): ?>
                        <p class="text-sm text-gray-500 leading-relaxed mb-5">
                            <?php echo e($popupAd->body); ?>

                        </p>
                    <?php endif; ?>

                    <div class="flex items-center justify-between gap-3">
                        <?php if($popupAd->button_label && $popupAd->button_url): ?>
                            <a href="<?php echo e($popupAd->button_url); ?>" target="_blank" rel="noopener"
                                @click="fetch('<?php echo e(route('popup-ads.dismiss', $popupAd)); ?>', {method:'POST',headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>','Content-Type':'application/json'}})"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                                <?php echo e($popupAd->button_label); ?>

                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" />
                                </svg>
                            </a>
                        <?php endif; ?>
                        <button
                            @click="open=false; fetch('<?php echo e(route('popup-ads.dismiss', $popupAd)); ?>', {method:'POST',headers:{'X-CSRF-TOKEN':'<?php echo e(csrf_token()); ?>','Content-Type':'application/json'}})"
                            class="ml-auto text-sm text-gray-400 hover:text-gray-700 transition">
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\tenant.blade.php ENDPATH**/ ?>