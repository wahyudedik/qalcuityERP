

<?php $__env->startSection('title', 'Detail Sesi Kasir'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 sm:p-6 space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
        <div>
            <a href="<?php echo e(route('pos.sessions.index')); ?>"
                class="inline-flex items-center gap-1 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 mb-2 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Semua Sesi
            </a>
            <h1 class="text-xl font-semibold text-gray-900 dark:text-white">
                Detail Sesi Kasir
                <?php if($session->isOpen()): ?>
                    <span class="ml-2 inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span>
                        Terbuka
                    </span>
                <?php else: ?>
                    <span class="ml-2 inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400">
                        Ditutup
                    </span>
                <?php endif; ?>
            </h1>
            <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">
                <?php echo e($session->register_name ?? 'Kasir Utama'); ?>

                &bull; <?php echo e($session->cashier?->name); ?>

            </p>
        </div>

        <?php if($session->isOpen()): ?>
        <div class="flex gap-2">
            <a href="<?php echo e(route('pos.index')); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Lanjut Transaksi
            </a>
            <a href="<?php echo e(route('pos.sessions.close-form', $session)); ?>"
                class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-xl transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Tutup Sesi
            </a>
        </div>
        <?php endif; ?>
    </div>

    
    <?php if(session('success')): ?>
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 text-green-800 dark:text-green-300 px-4 py-3 rounded-xl text-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if(session('warning')): ?>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 text-amber-800 dark:text-amber-300 px-4 py-3 rounded-xl text-sm">
            ⚠ <?php echo e(session('warning')); ?>

        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu Buka</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($session->opened_at?->format('d/m/Y H:i')); ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Waktu Tutup</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                <?php echo e($session->closed_at?->format('d/m/Y H:i') ?? '—'); ?>

            </p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Modal Awal</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($session->opening_balance, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
            <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Gudang</p>
            <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($session->warehouse?->name ?? '—'); ?></p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-6">
        <h2 class="text-base font-semibold text-gray-900 dark:text-white mb-4">Rekap Penjualan</h2>

        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Transaksi</p>
                <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($recap['total_transactions'])); ?></p>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 text-center col-span-2">
                <p class="text-xs text-blue-600 dark:text-blue-400 mb-1">Total Penjualan</p>
                <p class="text-xl font-bold text-blue-700 dark:text-blue-300">Rp <?php echo e(number_format($recap['total_sales'], 0, ',', '.')); ?></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Diskon</p>
                <p class="text-lg font-bold text-red-600 dark:text-red-400">Rp <?php echo e(number_format($recap['total_discount'], 0, ',', '.')); ?></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Pajak</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($recap['total_tax'], 0, ',', '.')); ?></p>
            </div>
            <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Total Bersih</p>
                <p class="text-lg font-bold text-gray-900 dark:text-white">
                    Rp <?php echo e(number_format($recap['total_sales'] - $recap['total_discount'], 0, ',', '.')); ?>

                </p>
            </div>
        </div>

        
        <div class="border-t border-gray-100 dark:border-gray-700 pt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Per Metode Pembayaran</h3>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                <div class="flex items-center gap-3 bg-green-50 dark:bg-green-900/20 rounded-xl p-3">
                    <span class="w-3 h-3 bg-green-500 rounded-full shrink-0"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Tunai</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($recap['total_cash'], 0, ',', '.')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3">
                    <span class="w-3 h-3 bg-blue-500 rounded-full shrink-0"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Kartu</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($recap['total_card'], 0, ',', '.')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-purple-50 dark:bg-purple-900/20 rounded-xl p-3">
                    <span class="w-3 h-3 bg-purple-500 rounded-full shrink-0"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">QRIS</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($recap['total_qris'], 0, ',', '.')); ?></p>
                    </div>
                </div>
                <div class="flex items-center gap-3 bg-orange-50 dark:bg-orange-900/20 rounded-xl p-3">
                    <span class="w-3 h-3 bg-orange-500 rounded-full shrink-0"></span>
                    <div>
                        <p class="text-xs text-gray-500 dark:text-gray-400">Transfer</p>
                        <p class="text-sm font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($recap['total_transfer'], 0, ',', '.')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        
        <?php if($session->isClosed()): ?>
        <div class="border-t border-gray-100 dark:border-gray-700 pt-4 mt-4">
            <h3 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">Rekonsiliasi Kas</h3>
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kas yang Diharapkan</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($session->expected_balance, 0, ',', '.')); ?></p>
                </div>
                <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-3">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Kas Aktual</p>
                    <p class="text-base font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($session->closing_balance, 0, ',', '.')); ?></p>
                </div>
                <div class="rounded-xl p-3 <?php echo e($session->balance_difference == 0 ? 'bg-green-50 dark:bg-green-900/20' : ($session->balance_difference > 0 ? 'bg-blue-50 dark:bg-blue-900/20' : 'bg-red-50 dark:bg-red-900/20')); ?>">
                    <p class="text-xs mb-1 <?php echo e($session->balance_difference == 0 ? 'text-green-600 dark:text-green-400' : ($session->balance_difference > 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400')); ?>">
                        Selisih
                    </p>
                    <p class="text-base font-semibold <?php echo e($session->balance_difference == 0 ? 'text-green-700 dark:text-green-300' : ($session->balance_difference > 0 ? 'text-blue-700 dark:text-blue-300' : 'text-red-700 dark:text-red-300')); ?>">
                        <?php echo e($session->balance_difference >= 0 ? '+' : ''); ?>Rp <?php echo e(number_format($session->balance_difference, 0, ',', '.')); ?>

                    </p>
                </div>
            </div>
            <?php if($session->closedByUser): ?>
            <p class="text-xs text-gray-500 dark:text-gray-400 mt-3">
                Ditutup oleh <span class="font-medium text-gray-700 dark:text-gray-300"><?php echo e($session->closedByUser->name); ?></span>
                pada <?php echo e($session->closed_at?->format('d/m/Y H:i')); ?>

            </p>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 dark:border-gray-700">
            <h2 class="text-base font-semibold text-gray-900 dark:text-white">
                Daftar Transaksi
                <span class="ml-2 text-sm font-normal text-gray-500 dark:text-gray-400">(<?php echo e($transactions->count()); ?> transaksi)</span>
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 dark:bg-gray-700 border-b border-gray-200 dark:border-gray-600">
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-300">No. Transaksi</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Pelanggan</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Waktu</th>
                        <th class="text-left px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Metode</th>
                        <th class="text-right px-4 py-3 font-medium text-gray-600 dark:text-gray-300">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $transactions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $trx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700/50 transition">
                        <td class="px-4 py-3 font-mono text-xs text-gray-700 dark:text-gray-300"><?php echo e($trx->number); ?></td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400"><?php echo e($trx->customer?->name ?? 'Umum'); ?></td>
                        <td class="px-4 py-3 text-gray-600 dark:text-gray-400"><?php echo e($trx->created_at?->format('H:i')); ?></td>
                        <td class="px-4 py-3">
                            <?php
                                $methodLabels = [
                                    'cash' => ['label' => 'Tunai', 'class' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'],
                                    'card' => ['label' => 'Kartu', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
                                    'credit' => ['label' => 'Kartu', 'class' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400'],
                                    'qris' => ['label' => 'QRIS', 'class' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400'],
                                    'transfer' => ['label' => 'Transfer', 'class' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
                                    'bank_transfer' => ['label' => 'Transfer', 'class' => 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400'],
                                ];
                                $method = $methodLabels[$trx->payment_method] ?? ['label' => $trx->payment_method ?? '-', 'class' => 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-400'];
                            ?>
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium <?php echo e($method['class']); ?>">
                                <?php echo e($method['label']); ?>

                            </span>
                        </td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-gray-100">
                            Rp <?php echo e(number_format($trx->total, 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-gray-400">
                            Belum ada transaksi dalam sesi ini.
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
                <?php if($transactions->isNotEmpty()): ?>
                <tfoot>
                    <tr class="bg-gray-50 dark:bg-gray-700 border-t border-gray-200 dark:border-gray-600">
                        <td colspan="4" class="px-4 py-3 text-sm font-semibold text-gray-700 dark:text-gray-300">Total</td>
                        <td class="px-4 py-3 text-right text-sm font-bold text-gray-900 dark:text-white">
                            Rp <?php echo e(number_format($transactions->sum('total'), 0, ',', '.')); ?>

                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <?php if($session->notes): ?>
    <div class="bg-white dark:bg-gray-800 rounded-2xl border border-gray-200 dark:border-gray-700 p-4">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 mb-1">Catatan</p>
        <p class="text-sm text-gray-700 dark:text-gray-300"><?php echo e($session->notes); ?></p>
    </div>
    <?php endif; ?>

</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pos\sessions\show.blade.php ENDPATH**/ ?>