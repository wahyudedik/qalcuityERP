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
     <?php $__env->slot('header', null, []); ?> Neraca Saldo (Trial Balance) <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Dari Tanggal</label>
                <input type="date" name="from" value="<?php echo e($from); ?>"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Sampai Tanggal</label>
                <input type="date" name="to" value="<?php echo e($to); ?>"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Tampilkan</button>
        </form>

        
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Akun</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                        <th class="px-4 py-3 text-right">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $totalDebit = 0; $totalCredit = 0; ?>
                    <?php $__empty_1 = true; $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $totalDebit += $acc['debit']; $totalCredit += $acc['credit']; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs"><?php echo e($acc['code']); ?></td>
                        <td class="px-4 py-3"><?php echo e($acc['name']); ?></td>
                        <td class="px-4 py-3 text-gray-500 text-xs"><?php echo e($acc['type']); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo e($acc['debit'] > 0 ? number_format($acc['debit'], 0, ',', '.') : '-'); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo e($acc['credit'] > 0 ? number_format($acc['credit'], 0, ',', '.') : '-'); ?></td>
                        <td class="px-4 py-3 text-right font-medium <?php echo e($acc['balance'] >= 0 ? 'text-white' : 'text-red-400'); ?>">
                            <?php echo e(number_format(abs($acc['balance']), 0, ',', '.')); ?>

                            <?php echo e($acc['balance'] < 0 ? '(K)' : ''); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Tidak ada data untuk periode ini.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if($accounts->count() > 0): ?>
                <tfoot class="bg-white/5 font-semibold text-white">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right">TOTAL</td>
                        <td class="px-4 py-3 text-right"><?php echo e(number_format($totalDebit, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right"><?php echo e(number_format($totalCredit, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right <?php echo e(abs($totalDebit - $totalCredit) < 0.01 ? 'text-green-400' : 'text-red-400'); ?>">
                            <?php echo e(abs($totalDebit - $totalCredit) < 0.01 ? '✓ Balance' : 'TIDAK BALANCE'); ?>

                        </td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/accounting/trial-balance.blade.php ENDPATH**/ ?>