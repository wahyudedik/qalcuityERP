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
     <?php $__env->slot('header', null, []); ?> 🐟 Dashboard Perikanan <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div
            class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400">
            <?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Unit Cold Storage</p>
            <p class="text-2xl font-bold text-blue-600" x-data="{ count: <?php echo e($stats['cold_storage_units'] ?? 0); ?> }" x-text="count">0</p>
            <p class="text-xs mt-1" :class="<?php echo e($stats['temp_alerts'] ?? 0 > 0 ? 'text-red-500' : 'text-green-500'); ?>">
                <?php echo e($stats['temp_alerts'] ?? 0); ?> alert suhu
            </p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Trip Aktif</p>
            <p class="text-2xl font-bold text-emerald-600"><?php echo e($stats['active_trips'] ?? 0); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['total_catches'] ?? 0); ?> total tangkapan</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Kolam Budidaya</p>
            <p class="text-2xl font-bold text-cyan-600"><?php echo e($stats['ponds'] ?? 0); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['avg_pond_utilization'] ?? 0); ?>% utilisasi</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Spesies Terdaftar</p>
            <p class="text-2xl font-bold text-purple-600"><?php echo e($stats['species_count'] ?? 0); ?></p>
            <p class="text-xs text-gray-400 mt-1"><?php echo e($stats['export_shipments'] ?? 0); ?> pengiriman</p>
        </div>
    </div>

    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        
        <a href="<?php echo e(route('fisheries.cold-chain.index')); ?>"
            class="block bg-gradient-to-br from-blue-50 to-blue-100 dark:from-blue-900/20 dark:to-blue-800/20 rounded-2xl border border-blue-200 dark:border-blue-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-blue-600 rounded-xl flex items-center justify-center text-2xl">❄️</div>
                <span
                    class="text-xs px-2 py-1 bg-blue-200 dark:bg-blue-500/30 text-blue-700 dark:text-blue-300 rounded-full">Monitoring</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Cold Chain Management</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Pantau suhu cold storage, kelola alert, dan
                pastikan kualitas produk tetap terjaga</p>
            <div
                class="flex items-center text-sm text-blue-600 dark:text-blue-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        
        <a href="<?php echo e(route('fisheries.operations.index')); ?>"
            class="block bg-gradient-to-br from-emerald-50 to-emerald-100 dark:from-emerald-900/20 dark:to-emerald-800/20 rounded-2xl border border-emerald-200 dark:border-emerald-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-emerald-600 rounded-xl flex items-center justify-center text-2xl">⚓</div>
                <span
                    class="text-xs px-2 py-1 bg-emerald-200 dark:bg-emerald-500/30 text-emerald-700 dark:text-emerald-300 rounded-full">Operasional</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Fishing Operations</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Kelola trip penangkapan, catat hasil tangkapan,
                dan tracking armada kapal</p>
            <div
                class="flex items-center text-sm text-emerald-600 dark:text-emerald-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        
        <a href="<?php echo e(route('fisheries.aquaculture.index')); ?>"
            class="block bg-gradient-to-br from-cyan-50 to-cyan-100 dark:from-cyan-900/20 dark:to-cyan-800/20 rounded-2xl border border-cyan-200 dark:border-cyan-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-cyan-600 rounded-xl flex items-center justify-center text-2xl">🐠</div>
                <span
                    class="text-xs px-2 py-1 bg-cyan-200 dark:bg-cyan-500/30 text-cyan-700 dark:text-cyan-300 rounded-full">Budidaya</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Aquaculture Management</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Monitor kualitas air kolam, jadwal pemberian
                pakan, dan kesehatan ikan</p>
            <div
                class="flex items-center text-sm text-cyan-600 dark:text-cyan-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        
        <a href="<?php echo e(route('fisheries.species.index')); ?>"
            class="block bg-gradient-to-br from-purple-50 to-purple-100 dark:from-purple-900/20 dark:to-purple-800/20 rounded-2xl border border-purple-200 dark:border-purple-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-purple-600 rounded-xl flex items-center justify-center text-2xl">📋</div>
                <span
                    class="text-xs px-2 py-1 bg-purple-200 dark:bg-purple-500/30 text-purple-700 dark:text-purple-300 rounded-full">Katalog</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Species & Grading</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Kelola katalog spesies ikan, sistem grading
                kualitas, dan penilaian kesegaran</p>
            <div
                class="flex items-center text-sm text-purple-600 dark:text-purple-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        
        <a href="<?php echo e(route('fisheries.export.index')); ?>"
            class="block bg-gradient-to-br from-orange-50 to-orange-100 dark:from-orange-900/20 dark:to-orange-800/20 rounded-2xl border border-orange-200 dark:border-orange-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-orange-600 rounded-xl flex items-center justify-center text-2xl">📦</div>
                <span
                    class="text-xs px-2 py-1 bg-orange-200 dark:bg-orange-500/30 text-orange-700 dark:text-orange-300 rounded-full">Ekspor</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Export Documentation</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Urus perizinan ekspor, sertifikat kesehatan, dan
                dokumen kepabeanan</p>
            <div
                class="flex items-center text-sm text-orange-600 dark:text-orange-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>

        
        <a href="<?php echo e(route('fisheries.analytics')); ?>"
            class="block bg-gradient-to-br from-indigo-50 to-indigo-100 dark:from-indigo-900/20 dark:to-indigo-800/20 rounded-2xl border border-indigo-200 dark:border-indigo-500/30 p-6 hover:shadow-lg transition group">
            <div class="flex items-start justify-between mb-4">
                <div class="w-12 h-12 bg-indigo-600 rounded-xl flex items-center justify-center text-2xl">📊</div>
                <span
                    class="text-xs px-2 py-1 bg-indigo-200 dark:bg-indigo-500/30 text-indigo-700 dark:text-indigo-300 rounded-full">Analitik</span>
            </div>
            <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-2">Analytics & Reports</h3>
            <p class="text-sm text-gray-600 dark:text-slate-400 mb-3">Laporan produksi, analisis efisiensi, dan insight
                bisnis perikanan</p>
            <div
                class="flex items-center text-sm text-indigo-600 dark:text-indigo-400 font-medium group-hover:translate-x-1 transition">
                Lihat Detail →
            </div>
        </a>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
        <h3 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Aktivitas Terbaru</h3>

        <?php if(empty($recent_activities) || count($recent_activities) === 0): ?>
            <div class="text-center py-8">
                <p class="text-3xl mb-2">🐟</p>
                <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada aktivitas. Mulai dengan menambahkan data
                    perikanan Anda.</p>
            </div>
        <?php else: ?>
            <div class="space-y-3">
                <?php $__currentLoopData = $recent_activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-[#0f172a]">
                        <div
                            class="w-8 h-8 rounded-full bg-<?php echo e($activity['color'] ?? 'blue'); ?>-100 dark:bg-<?php echo e($activity['color'] ?? 'blue'); ?>-500/20 flex items-center justify-center text-sm">
                            <?php echo $activity['icon'] ?? '📌'; ?>

                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white truncate">
                                <?php echo e($activity['title']); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($activity['description']); ?></p>
                            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($activity['time']); ?></p>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fisheries\index.blade.php ENDPATH**/ ?>