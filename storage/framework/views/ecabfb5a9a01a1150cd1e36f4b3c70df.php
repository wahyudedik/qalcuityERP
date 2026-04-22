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
     <?php $__env->slot('header', null, []); ?> Analisis Aging Piutang <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        
        <div class="flex gap-3 text-sm">
            <a href="<?php echo e(route('receivables.index')); ?>" class="text-gray-400 hover:text-white">← Piutang</a>
            <span class="text-gray-600">|</span>
            <span class="text-white font-medium">Aging Analysis</span>
        </div>

        
        <div class="grid grid-cols-2 lg:grid-cols-6 gap-3">
            <?php
                $buckets = [
                    'current' => ['label' => 'Belum Jatuh Tempo', 'color' => 'green'],
                    '1-30'    => ['label' => '1-30 Hari', 'color' => 'yellow'],
                    '31-60'   => ['label' => '31-60 Hari', 'color' => 'orange'],
                    '61-90'   => ['label' => '61-90 Hari', 'color' => 'red'],
                    '90+'     => ['label' => '> 90 Hari', 'color' => 'red'],
                    'total'   => ['label' => 'Total Outstanding', 'color' => 'indigo'],
                ];
            ?>
            <?php $__currentLoopData = $buckets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $b): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="bg-white/5 border border-white/10 rounded-xl p-4">
                <p class="text-xs text-gray-400 mb-1"><?php echo e($b['label']); ?></p>
                <p class="text-lg font-bold text-<?php echo e($b['color']); ?>-400">
                    Rp <?php echo e(number_format($summary[$key], 0, ',', '.')); ?>

                </p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-x-auto">
            <table class="w-full text-sm text-gray-300 min-w-[800px]">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-right">Limit Kredit</th>
                        <th class="px-4 py-3 text-right text-green-400">Belum JT</th>
                        <th class="px-4 py-3 text-right text-yellow-400">1-30 Hari</th>
                        <th class="px-4 py-3 text-right text-orange-400">31-60 Hari</th>
                        <th class="px-4 py-3 text-right text-red-400">61-90 Hari</th>
                        <th class="px-4 py-3 text-right text-red-500">90+ Hari</th>
                        <th class="px-4 py-3 text-right text-white">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $aging; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-white"><?php echo e($row['customer']); ?></td>
                        <td class="px-4 py-3 text-right text-gray-500 text-xs">
                            <?php echo e($row['credit_limit'] > 0 ? 'Rp ' . number_format($row['credit_limit'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-green-400">
                            <?php echo e($row['current'] > 0 ? number_format($row['current'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-yellow-400">
                            <?php echo e($row['1-30'] > 0 ? number_format($row['1-30'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-orange-400">
                            <?php echo e($row['31-60'] > 0 ? number_format($row['31-60'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-red-400">
                            <?php echo e($row['61-90'] > 0 ? number_format($row['61-90'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-red-500 font-semibold">
                            <?php echo e($row['90+'] > 0 ? number_format($row['90+'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-bold text-white">
                            Rp <?php echo e(number_format($row['total'], 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">Tidak ada piutang outstanding.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if(count($aging) > 0): ?>
                <tfoot class="bg-white/5 font-semibold text-white text-sm">
                    <tr>
                        <td colspan="2" class="px-4 py-3 text-right">TOTAL</td>
                        <td class="px-4 py-3 text-right text-green-400"><?php echo e(number_format($summary['current'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-yellow-400"><?php echo e(number_format($summary['1-30'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-orange-400"><?php echo e(number_format($summary['31-60'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-red-400"><?php echo e(number_format($summary['61-90'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-red-500"><?php echo e(number_format($summary['90+'], 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right">Rp <?php echo e(number_format($summary['total'], 0, ',', '.')); ?></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>

        
        <div class="bg-blue-500/10 border border-blue-500/20 rounded-xl px-4 py-3 text-xs text-blue-400">
            Aging dihitung berdasarkan tanggal jatuh tempo invoice. "Belum JT" = invoice yang belum melewati due date.
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\receivables\aging.blade.php ENDPATH**/ ?>