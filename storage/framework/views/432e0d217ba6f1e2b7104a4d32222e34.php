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
     <?php $__env->slot('header', null, []); ?> Buku Besar (General Ledger) <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        
        <form method="GET" class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Pilih Akun</label>
                    <select name="account_id" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white dark:text-white focus:outline-none focus:border-indigo-500">
                        <option value="">-- Pilih Akun --</option>
                        <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($acc->id); ?>" <?php echo e(request('account_id') == $acc->id ? 'selected' : ''); ?>>
                                <?php echo e($acc->code); ?> - <?php echo e($acc->name); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Dari Tanggal</label>
                    <input type="date" name="from" value="<?php echo e($from); ?>"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Sampai Tanggal</label>
                    <input type="date" name="to" value="<?php echo e($to); ?>"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white dark:text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg transition">
                        Tampilkan
                    </button>
                    <?php if($account): ?>
                    <a href="<?php echo e(route('accounting.general-ledger.pdf')); ?>?account_id=<?php echo e($account->id); ?>&from=<?php echo e($from); ?>&to=<?php echo e($to); ?>"
                       class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        PDF
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </form>

        <?php if($account): ?>
        
        <div class="bg-white/5 border border-white/10 rounded-xl p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <span class="text-gray-400">Kode Akun:</span>
                    <p class="font-semibold text-white font-mono"><?php echo e($account->code); ?></p>
                </div>
                <div>
                    <span class="text-gray-400">Nama Akun:</span>
                    <p class="font-semibold text-white"><?php echo e($account->name); ?></p>
                </div>
                <div>
                    <span class="text-gray-400">Tipe:</span>
                    <p class="font-semibold text-white"><?php echo e($account->getTypeLabel()); ?></p>
                </div>
                <div>
                    <span class="text-gray-400">Saldo Normal:</span>
                    <p class="font-semibold text-white capitalize"><?php echo e($account->normal_balance); ?></p>
                </div>
            </div>
        </div>

        
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Referensi</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    
                    <tr class="bg-indigo-500/10 font-semibold">
                        <td class="px-4 py-3" colspan="3">Saldo Awal (per <?php echo e(\Carbon\Carbon::parse($from)->translatedFormat('d F Y')); ?>)</td>
                        <td class="px-4 py-3 text-right">-</td>
                        <td class="px-4 py-3 text-right">-</td>
                        <td class="px-4 py-3 text-right text-white">
                            <?php echo e(number_format(abs($openingBalance), 0, ',', '.')); ?>

                            <?php if($openingBalance < 0): ?> (K) <?php endif; ?>
                        </td>
                    </tr>

                    <?php $__empty_1 = true; $__currentLoopData = $entries; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $entry): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 text-xs"><?php echo e(\Carbon\Carbon::parse($entry['date'])->translatedFormat('d M Y')); ?></td>
                        <td class="px-4 py-3 font-mono text-xs"><?php echo e($entry['reference']); ?></td>
                        <td class="px-4 py-3 text-xs">
                            <?php echo e($entry['description']); ?>

                            <?php if(isset($entry['created_by'])): ?>
                            <span class="text-gray-500 text-xs ml-2">(<?php echo e($entry['created_by']); ?>)</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php echo e($entry['debit'] > 0 ? number_format($entry['debit'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right">
                            <?php echo e($entry['credit'] > 0 ? number_format($entry['credit'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-medium <?php echo e($entry['balance'] >= 0 ? 'text-white' : 'text-red-400'); ?>">
                            <?php echo e(number_format(abs($entry['balance']), 0, ',', '.')); ?>

                            <?php if($entry['balance'] < 0): ?> (K) <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada transaksi untuk periode ini.</td></tr>
                    <?php endif; ?>

                    <?php if($entries->count() > 0): ?>
                    
                    <tr class="bg-indigo-500/10 font-semibold">
                        <td class="px-4 py-3" colspan="3">Saldo Akhir (per <?php echo e(\Carbon\Carbon::parse($to)->translatedFormat('d F Y')); ?>)</td>
                        <td class="px-4 py-3 text-right"><?php echo e(number_format($entries->sum('debit'), 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo e(number_format($entries->sum('credit'), 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-white">
                            <?php
                                $closingBalance = $entries->last()['balance'] ?? $openingBalance;
                            ?>
                            <?php echo e(number_format(abs($closingBalance), 0, ',', '.')); ?>

                            <?php if($closingBalance < 0): ?> (K) <?php endif; ?>
                        </td>
                    </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php else: ?>
        
        <div class="bg-white/5 border border-white/10 rounded-xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <h3 class="text-lg font-semibold text-white mb-2">Pilih Akun untuk Melihat Buku Besar</h3>
            <p class="text-gray-400">Gunakan filter di atas untuk memilih akun dan periode yang ingin ditampilkan.</p>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\general-ledger.blade.php ENDPATH**/ ?>