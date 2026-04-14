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
    <?php $__env->startSection('title', 'Stock Opname — Mode Lapangan'); ?>

    
    <div class="min-h-screen bg-gray-950 pb-28" x-data>

        
        <div
            class="sticky top-0 z-20 bg-gray-900/95 backdrop-blur border-b border-white/10 px-4 py-3 flex items-center gap-3">
            <a href="<?php echo e(route('mobile.hub')); ?>"
                class="flex items-center justify-center w-10 h-10 rounded-xl bg-white/5 hover:bg-white/10 active:scale-95 transition touch-manipulation">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
            </a>
            <div class="flex-1">
                <h1 class="text-lg font-bold text-white leading-tight">Stock Opname</h1>
                <p class="text-xs text-slate-400">Sesi pencacahan stok</p>
            </div>
            <a href="<?php echo e(route('wms.opname')); ?>"
                class="flex items-center justify-center w-10 h-10 rounded-xl bg-blue-600/20 hover:bg-blue-600/30 active:scale-95 transition touch-manipulation">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </a>
        </div>

        <div class="px-4 pt-4 space-y-3">

            
            <?php
                $totalSessions = $opnameSessions->count();
                $activeSessions = $opnameSessions->where('status', 'in_progress')->count();
                $draftSessions = $opnameSessions->where('status', 'draft')->count();
            ?>

            <div class="grid grid-cols-3 gap-2 mb-1">
                <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-3 text-center">
                    <p class="text-2xl font-bold text-white"><?php echo e($totalSessions); ?></p>
                    <p class="text-xs text-slate-400 mt-0.5">Total Sesi</p>
                </div>
                <div class="bg-[#1e293b] border border-blue-500/20 rounded-2xl p-3 text-center">
                    <p class="text-2xl font-bold text-blue-400"><?php echo e($activeSessions); ?></p>
                    <p class="text-xs text-slate-400 mt-0.5">Aktif</p>
                </div>
                <div class="bg-[#1e293b] border border-amber-500/20 rounded-2xl p-3 text-center">
                    <p class="text-2xl font-bold text-amber-400"><?php echo e($draftSessions); ?></p>
                    <p class="text-xs text-slate-400 mt-0.5">Draft</p>
                </div>
            </div>

            
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider px-1 pt-1">Daftar Sesi</p>

            
            <?php $__empty_1 = true; $__currentLoopData = $opnameSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $statusConfig = match ($s->status) {
                        'in_progress' => [
                            'label' => 'Sedang Berjalan',
                            'bg' => 'bg-blue-500/20',
                            'text' => 'text-blue-400',
                            'dot' => 'bg-blue-400',
                        ],
                        'completed' => [
                            'label' => 'Selesai',
                            'bg' => 'bg-emerald-500/20',
                            'text' => 'text-emerald-400',
                            'dot' => 'bg-emerald-400',
                        ],
                        default => [
                            'label' => 'Draft',
                            'bg' => 'bg-gray-500/20',
                            'text' => 'text-gray-400',
                            'dot' => 'bg-gray-400',
                        ],
                    };

                    $totalItems = $s->items_count ?? $s->items()->count();
                    $countedItems = $s->counted_items ?? $s->items()->whereNotNull('actual_qty')->count();
                    $progressPct = $totalItems > 0 ? round(($countedItems / $totalItems) * 100) : 0;
                ?>

                <a href="<?php echo e(route('mobile.opname.show', $s)); ?>"
                    class="block bg-[#1e293b] rounded-2xl border border-white/10 p-4 mb-3 active:scale-[0.98] transition touch-manipulation min-h-[60px] no-underline">

                    
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <div class="flex-1 min-w-0">
                            <p class="text-base font-semibold text-white truncate"><?php echo e($s->number); ?></p>
                            <p class="text-sm text-slate-400 mt-0.5"><?php echo e($s->warehouse->name ?? '-'); ?></p>
                        </div>
                        <span
                            class="flex-shrink-0 inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium <?php echo e($statusConfig['bg']); ?> <?php echo e($statusConfig['text']); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($statusConfig['dot']); ?>"></span>
                            <?php echo e($statusConfig['label']); ?>

                        </span>
                    </div>

                    
                    <div class="flex items-center gap-3 text-xs text-slate-500 mb-3">
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            <?php echo e($s->opname_date->format('d/m/Y')); ?>

                        </span>
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                            <?php echo e($totalItems); ?> item
                        </span>
                    </div>

                    
                    <div>
                        <div class="flex justify-between text-xs mb-1">
                            <span class="text-slate-400">Progress cacah</span>
                            <span
                                class="<?php echo e($progressPct === 100 ? 'text-emerald-400' : 'text-slate-300'); ?> font-medium">
                                <?php echo e($countedItems); ?>/<?php echo e($totalItems); ?> (<?php echo e($progressPct); ?>%)
                            </span>
                        </div>
                        <div class="w-full h-2 bg-white/5 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-500
                        <?php echo e($progressPct === 100 ? 'bg-emerald-500' : ($progressPct > 0 ? 'bg-blue-500' : 'bg-gray-600')); ?>"
                                style="width: <?php echo e($progressPct); ?>%"></div>
                        </div>
                    </div>

                    
                    <?php if($s->status !== 'completed'): ?>
                        <div class="mt-3 flex items-center justify-end gap-1 text-xs text-blue-400/70">
                            <span>Buka untuk input</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                
                <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-10 text-center mt-4">
                    <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 flex items-center justify-center">
                        <svg class="w-8 h-8 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <p class="text-white font-semibold mb-1">Belum Ada Sesi Opname</p>
                    <p class="text-sm text-slate-400">Buat sesi baru dari halaman WMS untuk memulai pencacahan stok.</p>
                </div>
            <?php endif; ?>

            
            <?php if($opnameSessions instanceof \Illuminate\Pagination\LengthAwarePaginator && $opnameSessions->hasPages()): ?>
                <div class="py-2">
                    <?php echo e($opnameSessions->links()); ?>

                </div>
            <?php endif; ?>

        </div>
    </div>

    
    <div
        class="fixed bottom-0 left-0 right-0 z-30 bg-gray-900/95 backdrop-blur border-t border-white/10 p-4 safe-area-bottom">
        <a href="<?php echo e(route('wms.opname')); ?>"
            class="flex items-center justify-center gap-2 w-full h-14 bg-blue-600 hover:bg-blue-500 active:scale-[0.98] text-white font-semibold rounded-2xl transition touch-manipulation text-base">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Buat Opname Baru
        </a>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/mobile/opname.blade.php ENDPATH**/ ?>