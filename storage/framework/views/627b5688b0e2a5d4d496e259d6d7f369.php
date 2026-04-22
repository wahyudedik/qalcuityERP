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
     <?php $__env->slot('header', null, []); ?> Supplier Scorecard <?php $__env->endSlot(); ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-4">
        <form method="GET" id="period-form">
            <select name="period" onchange="document.getElementById('period-form').submit()"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                <option value="monthly"   <?php if(request('period', 'monthly') === 'monthly'): echo 'selected'; endif; ?>>Bulanan</option>
                <option value="quarterly" <?php if(request('period') === 'quarterly'): echo 'selected'; endif; ?>>Kuartalan</option>
                <option value="yearly"    <?php if(request('period') === 'yearly'): echo 'selected'; endif; ?>>Tahunan</option>
            </select>
        </form>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('suppliers.scorecards.export')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <button type="button" onclick="document.getElementById('generateModal').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Generate Scorecard
            </button>
        </div>
    </div>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Supplier</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400"><?php echo e($dashboard['total_suppliers']); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Scorecard aktif</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Rata-rata Skor</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($dashboard['average_score'], 1)); ?><span class="text-sm font-normal text-gray-400">/100</span></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Performa keseluruhan</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Top Performer</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400"><?php echo e($dashboard['top_performers']); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Grade A</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Perlu Perhatian</p>
            <p class="text-2xl font-bold text-red-500 dark:text-red-400"><?php echo e($dashboard['at_risk']); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Grade D/F</p>
        </div>
    </div>

    
    <?php if(count($dashboard['by_category']) > 0): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-4">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Performa per Kategori</h3>
        </div>
        <div class="p-4 grid grid-cols-2 md:grid-cols-4 gap-3">
            <?php $__currentLoopData = $dashboard['by_category']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category => $stats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-gray-50 dark:bg-[#0f172a] rounded-xl p-3">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1 truncate"><?php echo e($category); ?></p>
                <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($stats['avg_score']); ?></p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5"><?php echo e($stats['count']); ?> supplier</p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-100 dark:border-white/5 flex items-center justify-between gap-3">
            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">Daftar Scorecard</h3>
            <input type="text" placeholder="Cari supplier..."
                class="px-3 py-1.5 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 w-48">
        </div>

        <?php if(count($dashboard['scorecards']) === 0): ?>
            <div class="py-16 text-center">
                <svg class="mx-auto w-10 h-10 text-gray-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada scorecard. Klik <strong>Generate Scorecard</strong> untuk membuatnya.</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Supplier</th>
                            <th class="px-4 py-3 text-center">Rating</th>
                            <th class="px-4 py-3 text-right">Skor</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Kualitas</th>
                            <th class="px-4 py-3 text-right hidden md:table-cell">Pengiriman</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Harga</th>
                            <th class="px-4 py-3 text-right hidden lg:table-cell">Layanan</th>
                            <th class="px-4 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $dashboard['scorecards']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $scorecard): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $ratingColor = match(true) {
                                str_starts_with($scorecard->rating ?? '', 'A') => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400',
                                str_starts_with($scorecard->rating ?? '', 'B') => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400',
                                str_starts_with($scorecard->rating ?? '', 'C') => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400',
                                str_starts_with($scorecard->rating ?? '', 'D') => 'bg-orange-100 text-orange-700 dark:bg-orange-500/20 dark:text-orange-400',
                                default => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400',
                            };
                            $barColor = match(true) {
                                $scorecard->overall_score >= 80 => 'bg-green-500',
                                $scorecard->overall_score >= 60 => 'bg-amber-500',
                                default => 'bg-red-500',
                            };
                        ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900 dark:text-white"><?php echo e($scorecard->supplier->name); ?></p>
                                <?php if($scorecard->supplier->company): ?>
                                    <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($scorecard->supplier->company); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs font-bold <?php echo e($ratingColor); ?>">
                                    <?php echo e($scorecard->rating ?? 'N/A'); ?>

                                </span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <div class="w-16 bg-gray-200 dark:bg-white/10 rounded-full h-1.5 hidden sm:block">
                                        <div class="<?php echo e($barColor); ?> h-1.5 rounded-full" style="width: <?php echo e(min($scorecard->overall_score, 100)); ?>%"></div>
                                    </div>
                                    <span class="font-bold text-gray-900 dark:text-white text-sm"><?php echo e(number_format($scorecard->overall_score, 1)); ?></span>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e(number_format($scorecard->quality_score, 1)); ?></td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e(number_format($scorecard->delivery_score, 1)); ?></td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 dark:text-slate-400"><?php echo e(number_format($scorecard->cost_score, 1)); ?></td>
                            <td class="px-4 py-3 text-right hidden lg:table-cell text-gray-500 dark:text-slate-400"><?php echo e(number_format($scorecard->service_score, 1)); ?></td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo e(route('suppliers.scorecard.detail', $scorecard->supplier_id)); ?>"
                                    class="inline-flex items-center gap-1 px-2.5 py-1 text-xs rounded-lg bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 hover:bg-blue-100 dark:hover:bg-blue-500/20">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    Detail
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    
    <div id="generateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Generate Scorecard</h3>
                <button onclick="document.getElementById('generateModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="<?php echo e(route('suppliers.scorecard.generate')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Periode</label>
                    <select name="period"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="monthly">Bulanan</option>
                        <option value="quarterly">Kuartalan</option>
                        <option value="yearly">Tahunan</option>
                    </select>
                </div>
                <p class="text-xs text-gray-500 dark:text-slate-400">Akan membuat atau memperbarui scorecard untuk semua supplier aktif berdasarkan data performa mereka.</p>
                <div class="flex gap-3 pt-1">
                    <button type="submit"
                        class="flex-1 px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        Generate Sekarang
                    </button>
                    <button type="button" onclick="document.getElementById('generateModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                        Batal
                    </button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\suppliers\scorecard-dashboard.blade.php ENDPATH**/ ?>