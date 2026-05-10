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
     <?php $__env->slot('header', null, []); ?> Memori AI <?php $__env->endSlot(); ?>

    <div class="py-6 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
        <?php if(session('success')): ?>
            <div
                class="p-3 bg-green-100 border border-green-200 text-green-800 rounded-xl text-sm">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <!-- Stats Bar -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($memories->count()); ?></div>
                <div class="text-xs text-gray-500">Total Preferensi</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-2xl font-bold text-gray-900">
                    <?php echo e(number_format($memories->avg('confidence_score') ?? 0, 2)); ?>

                </div>
                <div class="text-xs text-gray-500">Rata-rata Keyakinan</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-2xl font-bold text-gray-900"><?php echo e($patterns->count()); ?></div>
                <div class="text-xs text-gray-500">Pola Dipelajari</div>
            </div>
            <div class="bg-white rounded-2xl border border-gray-200 p-4">
                <div class="text-2xl font-bold text-gray-900">
                    <?php echo e($memories->where('confidence_score', '<', 0.3)->count()); ?>

                </div>
                <div class="text-xs text-gray-500">Memori Usang</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-3">
            <form method="POST" action="<?php echo e(route('ai-memory.prune')); ?>"
                onsubmit="return confirm('Hapus semua memori usang (keyakinan < 30%)?')">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-amber-100 text-amber-800 rounded-xl text-sm font-medium hover:bg-amber-200 transition-colors border border-amber-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    Bersihkan Memori Usang
                </button>
            </form>
            <form method="POST" action="<?php echo e(route('ai-memory.reset')); ?>"
                onsubmit="return confirm('Reset semua memori AI? Preferensi yang dipelajari akan dihapus.')">
                <?php echo csrf_field(); ?>
                <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 bg-red-100 text-red-800 rounded-xl text-sm font-medium hover:bg-red-200 transition-colors border border-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Reset Semua
                </button>
            </form>
        </div>

        <!-- Suggestions -->
        <?php if(!empty($suggestions)): ?>
            <div
                class="bg-indigo-50 border border-indigo-200 rounded-2xl p-5">
                <h3 class="font-semibold text-indigo-800 text-sm mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                    Saran Berdasarkan Kebiasaan Anda
                </h3>
                <ul class="space-y-2">
                    <?php $__currentLoopData = $suggestions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li class="text-sm text-indigo-700 flex items-start gap-2">
                            <span class="mt-0.5 text-indigo-500">→</span>
                            <span><?php echo e($s); ?></span>
                        </li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Grouped Preferences Tabs -->
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="border-b border-gray-100 px-5 py-3 flex items-center justify-between">
                <h3 class="font-semibold text-gray-900">Preferensi yang Dipelajari</h3>
            </div>

            <!-- Tab Navigation -->
            <div
                class="flex overflow-x-auto border-b border-gray-100 bg-gray-50">
                <?php
                    $categoryLabels = [
                        'pelanggan' => [
                            'label' => 'Pelanggan',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                        ],
                        'supplier' => [
                            'label' => 'Supplier',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/></svg>',
                        ],
                        'produk' => [
                            'label' => 'Produk',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
                        ],
                        'pembayaran' => [
                            'label' => 'Pembayaran',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>',
                        ],
                        'umum' => [
                            'label' => 'Umum',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
                        ],
                        'lainnya' => [
                            'label' => 'Lainnya',
                            'icon' =>
                                '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h.01M12 12h.01M19 12h.01M6 12a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0zm7 0a1 1 0 11-2 0 1 1 0 012 0z"/></svg>',
                        ],
                    ];
                ?>
                <?php $__currentLoopData = $categoryLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $info): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php if(($groupedMemories[$key] ?? collect())->count() > 0): ?>
                        <button type="button" data-tab="<?php echo e($key); ?>"
                            class="tab-btn flex items-center gap-2 px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors <?php echo e($loop->first ? 'text-indigo-600 border-b-2 border-indigo-600' : 'text-gray-500 hover:text-gray-700'); ?>">
                            <?php echo $info['icon']; ?>

                            <?php echo e($info['label']); ?>

                            <span
                                class="text-xs bg-gray-200 rounded-full px-2 py-0.5"><?php echo e(($groupedMemories[$key] ?? collect())->count()); ?></span>
                        </button>
                    <?php endif; ?>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <?php
                $keyLabels = [
                    'frequent_customers' => 'Pelanggan Sering Digunakan',
                    'preferred_delivery_address' => 'Alamat Pengiriman Utama',
                    'frequent_suppliers' => 'Supplier Sering Digunakan',
                    'preferred_payment_terms' => 'Termin Pembayaran Utama',
                    'frequent_products' => 'Produk Sering Digunakan',
                    'typical_order_quantity' => 'Kuantitas Pesanan Khas',
                    'preferred_discount' => 'Diskon Utama',
                    'preferred_payment_method' => 'Metode Pembayaran Utama',
                    'preferred_currency' => 'Mata Uang Utama',
                    'tax_preference' => 'Preferensi Pajak',
                    'default_warehouse' => 'Gudang Default',
                    'default_cost_center' => 'Cost Center Default',
                    'skipped_steps' => 'Langkah Dilewati',
                    'preferred_report_period' => 'Periode Laporan Utama',
                ];
            ?>

            <?php $__currentLoopData = $groupedMemories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $memoriesInCategory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($memoriesInCategory->count() > 0): ?>
                    <div data-content="<?php echo e($category); ?>"
                        class="tab-content <?php echo e($loop->first ? '' : 'hidden'); ?> divide-y divide-gray-100">
                        <?php $__currentLoopData = $memoriesInCategory; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $memory): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="p-4">
                                <div class="flex items-start justify-between gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap">
                                            <p class="text-sm font-medium text-gray-900">
                                                <?php echo e($keyLabels[$memory->key] ?? str_replace('_', ' ', $memory->key)); ?>

                                            </p>
                                            <span
                                                class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                                <?php echo e($memory->frequency); ?>x
                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1 truncate">
                                            <?php if(is_array($memory->value)): ?>
                                                <?php echo e(implode(', ', array_slice($memory->value, 0, 3))); ?>

                                                <?php if(count($memory->value) > 3): ?>
                                                    <span class="text-gray-400">+<?php echo e(count($memory->value) - 3); ?>

                                                        lainnya</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <?php echo e($memory->value); ?>

                                            <?php endif; ?>
                                        </p>

                                        <!-- Confidence Bar -->
                                        <div class="mt-2 flex items-center gap-2">
                                            <div
                                                class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden">
                                                <div class="h-full rounded-full <?php echo e(($memory->confidence_score ?? 0) < 0.3 ? 'bg-red-500' : (($memory->confidence_score ?? 0) < 0.6 ? 'bg-amber-500' : 'bg-green-500')); ?>"
                                                    style="width: <?php echo e(($memory->confidence_score ?? 0) * 100); ?>%"></div>
                                            </div>
                                            <span class="text-xs text-gray-500 w-10 text-right">
                                                <?php echo e(number_format(($memory->confidence_score ?? 0) * 100, 0)); ?>%
                                            </span>
                                        </div>

                                        <!-- Dates -->
                                        <div
                                            class="flex items-center gap-4 mt-2 text-xs text-gray-400">
                                            <span>Dilihat: <?php echo e($memory->last_seen_at?->diffForHumans() ?? '-'); ?></span>
                                            <span>Pertama:
                                                <?php echo e($memory->first_observed_at?->diffForHumans() ?? '-'); ?></span>
                                        </div>

                                        <!-- Metadata (Expandable) -->
                                        <?php if($memory->metadata && is_array($memory->metadata) && count($memory->metadata) > 0): ?>
                                            <div class="mt-2">
                                                <button type="button"
                                                    class="text-xs text-gray-400 hover:text-gray-600 underline"
                                                    onclick="this.nextElementSibling.classList.toggle('hidden')">
                                                    Lihat Detail
                                                </button>
                                                <div
                                                    class="hidden mt-2 p-2 bg-gray-50 rounded-lg text-xs">
                                                    <?php $__currentLoopData = $memory->metadata; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $metaKey => $metaVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div class="flex gap-2">
                                                            <span
                                                                class="text-gray-400"><?php echo e($metaKey); ?>:</span>
                                                            <span
                                                                class="text-gray-600"><?php echo e(is_array($metaVal) ? json_encode($metaVal) : $metaVal); ?></span>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Actions -->
                                    <div class="flex items-center gap-2">
                                        <?php if(($memory->confidence_score ?? 0) < 1.0): ?>
                                            <form method="POST" action="<?php echo e(route('ai-memory.lock', $memory)); ?>">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit"
                                                    class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 hover:bg-green-200 rounded-lg border border-green-200 transition-colors"
                                                    title="Konfirmasi preferensi ini">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="2"
                                                            d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                                                    </svg>
                                                    Konfirmasi
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-green-700 bg-green-100 rounded-lg border border-green-200">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Dikonfirmasi
                                            </span>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('ai-memory.destroy', $memory)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-medium text-red-700 bg-red-100 hover:bg-red-200 rounded-lg border border-red-200 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="2"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            <?php if($memories->isEmpty()): ?>
                <div class="p-8 text-center text-gray-500">
                    <div class="text-4xl mb-3">🧠</div>
                    <p class="text-sm">AI belum mempelajari preferensi Anda.</p>
                    <p class="text-xs mt-1">Preferensi akan dipelajari secara otomatis saat Anda menggunakan sistem.
                    </p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Learned Patterns Section -->
        <?php if($patterns->count() > 0): ?>
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="border-b border-gray-100 px-5 py-3">
                    <h3 class="font-semibold text-gray-900">Pola yang Dipelajari</h3>
                </div>

                <?php
                    $patternTypeLabels = [
                        'customer_behavior' => 'Perilaku Pelanggan',
                        'supplier_preference' => 'Preferensi Supplier',
                        'product_affinity' => 'Afinitas Produk',
                        'order_pattern' => 'Pola Pemesanan',
                    ];
                    $groupedPatterns = $patterns->groupBy('pattern_type');
                ?>

                <?php $__currentLoopData = $groupedPatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $typePatterns): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border-b border-gray-100 last:border-b-0">
                        <div class="px-5 py-2 bg-gray-50">
                            <h4 class="text-sm font-medium text-gray-700">
                                <?php echo e($patternTypeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type))); ?>

                            </h4>
                        </div>
                        <div class="divide-y divide-gray-100">
                            <?php $__currentLoopData = $typePatterns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $pattern): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="p-4">
                                    <div class="flex items-start justify-between gap-4">
                                        <div class="flex-1">
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm font-medium text-gray-900">
                                                    <?php echo e($pattern->entity_type ?? 'Pola'); ?>

                                                    <?php if($pattern->entity_id): ?>
                                                        #<?php echo e($pattern->entity_id); ?>

                                                    <?php endif; ?>
                                                </span>
                                            </div>

                                            <!-- Pattern Data -->
                                            <?php if($pattern->pattern_data && is_array($pattern->pattern_data)): ?>
                                                <div class="mt-2 grid grid-cols-2 md:grid-cols-3 gap-2">
                                                    <?php $__currentLoopData = $pattern->pattern_data; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dataKey => $dataVal): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                        <div
                                                            class="px-2 py-1 bg-gray-100 rounded-lg text-xs">
                                                            <span
                                                                class="text-gray-500"><?php echo e($dataKey); ?>:</span>
                                                            <span
                                                                class="text-gray-700 ml-1"><?php echo e(is_array($dataVal) ? json_encode($dataVal) : $dataVal); ?></span>
                                                        </div>
                                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                </div>
                                            <?php endif; ?>

                                            <!-- Confidence Bar -->
                                            <div class="mt-2 flex items-center gap-2">
                                                <div
                                                    class="flex-1 h-2 bg-gray-200 rounded-full overflow-hidden max-w-32">
                                                    <div class="h-full rounded-full <?php echo e($pattern->confidence < 0.3 ? 'bg-red-500' : ($pattern->confidence < 0.6 ? 'bg-amber-500' : 'bg-green-500')); ?>"
                                                        style="width: <?php echo e($pattern->confidence * 100); ?>%"></div>
                                                </div>
                                                <span class="text-xs text-gray-500">
                                                    <?php echo e(number_format($pattern->confidence * 100, 0)); ?>% keyakinan
                                                </span>
                                            </div>

                                            <?php if($pattern->analyzed_at): ?>
                                                <p class="text-xs text-gray-400 mt-1">
                                                    Dianalisis: <?php echo e($pattern->analyzed_at->diffForHumans()); ?>

                                                </p>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <!-- Info Box -->
        <div
            class="bg-gray-50 rounded-2xl p-5 text-sm text-gray-500 border border-gray-200">
            <p class="font-medium mb-2 text-gray-700">Tentang Memori AI</p>
            <p>AI mempelajari kebiasaan Anda secara otomatis: metode pembayaran favorit, gudang default, customer yang
                sering digunakan, dan langkah yang sering dilewati. Data ini digunakan untuk memberikan saran yang lebih
                relevan dan mempercepat alur kerja Anda.</p>
            <div class="mt-3 flex flex-wrap gap-4 text-xs">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-green-500"></span>
                    <span>Keyakinan tinggi (>60%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                    <span>Keyakinan sedang (30-60%)</span>
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-red-500"></span>
                    <span>Keyakinan rendah (&lt;30%)</span>
                </div>
            </div>
        </div>
    </div>
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

<?php $__env->startPush('scripts'); ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabContents = document.querySelectorAll('.tab-content');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', function() {
                    const tab = this.dataset.tab;

                    // Update button styles
                    tabBtns.forEach(b => {
                        b.classList.remove('text-indigo-600',
                            'border-b-2', 'border-indigo-600');
                        b.classList.add('text-gray-500');
                    });
                    this.classList.remove('text-gray-500');
                    this.classList.add('text-indigo-600', 'border-b-2',
                        'border-indigo-600');

                    // Show/hide content
                    tabContents.forEach(content => {
                        if (content.dataset.content === tab) {
                            content.classList.remove('hidden');
                        } else {
                            content.classList.add('hidden');
                        }
                    });
                });
            });
        });
    </script>
<?php $__env->stopPush(); ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/settings/ai-memory.blade.php ENDPATH**/ ?>