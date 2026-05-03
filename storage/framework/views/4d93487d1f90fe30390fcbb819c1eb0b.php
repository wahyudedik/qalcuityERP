

<?php $__env->startSection('title', 'Riwayat Sesi Kasir'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 sm:p-6 space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Sesi Kasir</h1>
            <p class="text-sm text-gray-500 mt-0.5">Riwayat buka dan tutup sesi kasir</p>
        </div>
        <a href="<?php echo e(route('pos.sessions.create')); ?>"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buka Sesi Baru
        </a>
    </div>

    
    <?php if(session('success')): ?>
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-xl text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('info')): ?>
        <div class="bg-blue-50 border border-blue-200 text-blue-800 px-4 py-3 rounded-xl text-sm">
            <?php echo e(session('info')); ?>

        </div>
    <?php endif; ?>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-200">
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Kasir</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Register</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Dibuka</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600">Ditutup</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600">Total Penjualan</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Status</th>
                        <th class="text-center px-4 py-3 font-medium text-gray-600">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 transition">
                        <td class="px-4 py-3 text-gray-900 font-medium">
                            <?php echo e($session->cashier?->name ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($session->register_name ?? 'Kasir Utama'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($session->opened_at?->format('d/m/Y H:i') ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            <?php echo e($session->closed_at?->format('d/m/Y H:i') ?? '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-gray-900 font-medium">
                            Rp <?php echo e(number_format($session->total_sales, 0, ',', '.')); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if($session->isOpen()): ?>
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 text-green-700">
                                    <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                                    Terbuka
                                </span>
                            <?php else: ?>
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                                    Ditutup
                                </span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="<?php echo e(route('pos.sessions.show', $session)); ?>"
                                    class="text-blue-600 hover:underline text-xs font-medium">
                                    Lihat
                                </a>
                                <?php if($session->isOpen()): ?>
                                    <a href="<?php echo e(route('pos.sessions.close-form', $session)); ?>"
                                        class="text-red-600 hover:underline text-xs font-medium">
                                        Tutup
                                    </a>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                            Belum ada sesi kasir. Klik "Buka Sesi Baru" untuk memulai.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($sessions->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-200">
            <?php echo e($sessions->links()); ?>

        </div>
        <?php endif; ?>
    </div>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pos\sessions\index.blade.php ENDPATH**/ ?>