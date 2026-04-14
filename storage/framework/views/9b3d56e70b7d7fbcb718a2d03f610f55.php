

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Bulk Payment</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">1 pembayaran untuk banyak invoice sekaligus</p>
        </div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'bulk_payments', 'create')): ?>
        <a href="<?php echo e(route('bulk-payments.create')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Bulk Payment
        </a>
        <?php endif; ?>
    </div>

    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor..."
               class="flex-1 px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
        <select name="status" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            <option value="">Semua Status</option>
            <option value="applied" <?php if(request('status') === 'applied'): echo 'selected'; endif; ?>>Diterapkan</option>
            <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm rounded-lg">Filter</button>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Tanggal</th>
                    <th class="px-4 py-3 text-right">Total Bayar</th>
                    <th class="px-4 py-3 text-right">Diterapkan</th>
                    <th class="px-4 py-3 text-right">Overpayment</th>
                    <th class="px-4 py-3 text-left">Metode</th>
                    <th class="px-4 py-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php $__empty_1 = true; $__currentLoopData = $payments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-3 font-mono font-medium text-slate-800 dark:text-white"><?php echo e($bp->number); ?></td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?php echo e($bp->party?->name ?? '-'); ?></td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?php echo e($bp->payment_date->format('d/m/Y')); ?></td>
                    <td class="px-4 py-3 text-right font-medium text-slate-800 dark:text-white">Rp <?php echo e(number_format($bp->total_amount, 0, ',', '.')); ?></td>
                    <td class="px-4 py-3 text-right text-slate-600 dark:text-slate-300">Rp <?php echo e(number_format($bp->applied_amount, 0, ',', '.')); ?></td>
                    <td class="px-4 py-3 text-right <?php echo e($bp->overpayment > 0 ? 'text-amber-600 font-medium' : 'text-slate-400'); ?>">
                        <?php echo e($bp->overpayment > 0 ? 'Rp ' . number_format($bp->overpayment, 0, ',', '.') : '-'); ?>

                    </td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400 capitalize"><?php echo e($bp->payment_method); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($bp->statusColor()); ?>">
                            <?php echo e($bp->statusLabel()); ?>

                        </span>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">Belum ada bulk payment</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($payments->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/bulk-payments/index.blade.php ENDPATH**/ ?>