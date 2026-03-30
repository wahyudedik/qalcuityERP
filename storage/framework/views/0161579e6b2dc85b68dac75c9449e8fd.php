

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white">Surat Jalan (Delivery Order)</h1>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Kelola pengiriman barang dari Sales Order</p>
        </div>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'delivery', 'create')): ?>
        <a href="<?php echo e(route('delivery-orders.create')); ?>"
           class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Buat Surat Jalan
        </a>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-3 gap-4">
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">Draft</p>
            <p class="text-2xl font-bold text-slate-800 dark:text-white mt-1"><?php echo e($stats['draft']); ?></p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">Dikirim</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($stats['shipped']); ?></p>
        </div>
        <div class="bg-white dark:bg-slate-800 rounded-xl p-4 border border-slate-200 dark:border-slate-700">
            <p class="text-xs text-slate-500 dark:text-slate-400">Terkirim</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($stats['delivered']); ?></p>
        </div>
    </div>

    <form method="GET" class="flex gap-3">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nomor surat jalan / SO..."
               class="flex-1 px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none">
        <select name="status" class="px-3 py-2 text-sm border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-800 text-slate-800 dark:text-white">
            <option value="">Semua Status</option>
            <option value="draft" <?php if(request('status') === 'draft'): echo 'selected'; endif; ?>>Draft</option>
            <option value="shipped" <?php if(request('status') === 'shipped'): echo 'selected'; endif; ?>>Dikirim</option>
            <option value="delivered" <?php if(request('status') === 'delivered'): echo 'selected'; endif; ?>>Terkirim</option>
            <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Dibatalkan</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-slate-700 hover:bg-slate-800 text-white text-sm rounded-lg">Filter</button>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-slate-50 dark:bg-slate-700/50 text-slate-500 dark:text-slate-400 text-xs uppercase">
                <tr>
                    <th class="px-4 py-3 text-left">Nomor</th>
                    <th class="px-4 py-3 text-left">Sales Order</th>
                    <th class="px-4 py-3 text-left">Customer</th>
                    <th class="px-4 py-3 text-left">Gudang</th>
                    <th class="px-4 py-3 text-left">Tgl Kirim</th>
                    <th class="px-4 py-3 text-left">Kurir</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-center">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $do): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/30">
                    <td class="px-4 py-3 font-mono font-medium text-slate-800 dark:text-white"><?php echo e($do->number); ?></td>
                    <td class="px-4 py-3 font-mono text-xs text-slate-500 dark:text-slate-400"><?php echo e($do->salesOrder->number ?? '-'); ?></td>
                    <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?php echo e($do->salesOrder?->customer?->name ?? '-'); ?></td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?php echo e($do->warehouse->name ?? '-'); ?></td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?php echo e($do->delivery_date->format('d/m/Y')); ?></td>
                    <td class="px-4 py-3 text-slate-500 dark:text-slate-400"><?php echo e($do->courier ?? '-'); ?></td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 text-xs font-medium rounded-full <?php echo e($do->statusColor()); ?>">
                            <?php echo e($do->statusLabel()); ?>

                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'delivery', 'edit')): ?>
                        <div class="flex items-center justify-center gap-2">
                            <?php if($do->status === 'draft'): ?>
                            <form method="POST" action="<?php echo e(route('delivery-orders.ship', $do)); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-blue-100 text-blue-700 hover:bg-blue-200 rounded">Kirim</button>
                            </form>
                            <?php endif; ?>
                            <?php if($do->status === 'shipped'): ?>
                            <form method="POST" action="<?php echo e(route('delivery-orders.deliver', $do)); ?>" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-green-100 text-green-700 hover:bg-green-200 rounded">Konfirmasi Terkirim</button>
                            </form>
                            <?php endif; ?>
                            <?php if($do->status === 'delivered'): ?>
                            <form method="POST" action="<?php echo e(route('delivery-orders.invoice', $do)); ?>" class="inline"
                                  onsubmit="return confirm('Buat invoice dari surat jalan ini?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-purple-100 text-purple-700 hover:bg-purple-200 rounded">Buat Invoice</button>
                            </form>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="8" class="px-4 py-12 text-center text-slate-400 dark:text-slate-500">Belum ada surat jalan</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php echo e($orders->links()); ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\delivery-orders\index.blade.php ENDPATH**/ ?>