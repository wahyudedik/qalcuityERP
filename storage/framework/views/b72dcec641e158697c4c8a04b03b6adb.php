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
     <?php $__env->slot('header', null, []); ?> Analisis Biaya & Produktivitas Lahan <?php $__env->endSlot(); ?>

    <div class="mb-4">
        <a href="<?php echo e(route('farm.plots')); ?>" class="text-sm text-blue-500 hover:text-blue-600">← Daftar Lahan</a>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Luas</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e(number_format($totalArea, 1)); ?> ha</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya</p>
            <p class="text-xl font-bold text-red-500">Rp <?php echo e(number_format($totalCost, 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Panen Bersih</p>
            <p class="text-xl font-bold text-emerald-600"><?php echo e(number_format($totalHarvest, 0)); ?> kg</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Rata-rata HPP/kg</p>
            <p class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($avgHpp ? 'Rp '.number_format($avgHpp, 0, ',', '.') : '-'); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Yield Rata-rata</p>
            <p class="text-xl font-bold text-blue-600"><?php echo e(number_format($avgYieldPerHa, 0)); ?> kg/ha</p>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
        <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10">
            <h3 class="font-semibold text-gray-900 dark:text-white">Perbandingan Biaya & Produktivitas per Lahan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Lahan</th>
                        <th class="px-4 py-3 text-left">Tanaman</th>
                        <th class="px-4 py-3 text-right">Luas</th>
                        <th class="px-4 py-3 text-right">Total Biaya</th>
                        <th class="px-4 py-3 text-right">Biaya/Ha</th>
                        <th class="px-4 py-3 text-right">Panen Bersih</th>
                        <th class="px-4 py-3 text-right">Yield/Ha</th>
                        <th class="px-4 py-3 text-right">HPP/kg</th>
                        <th class="px-4 py-3 text-right">Reject</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $comparison; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <a href="<?php echo e(route('farm.plots.show', $p['code'])); ?>" class="font-medium text-gray-900 dark:text-white hover:text-blue-600"><?php echo e($p['code']); ?></a>
                            <span class="text-xs text-gray-400 ml-1"><?php echo e($p['name']); ?></span>
                        </td>
                        <td class="px-4 py-3 text-gray-600 dark:text-slate-300"><?php echo e($p['crop']); ?></td>
                        <td class="px-4 py-3 text-right text-xs text-gray-500"><?php echo e($p['area']); ?></td>
                        <td class="px-4 py-3 text-right font-mono <?php echo e($p['total_cost'] > 0 ? 'text-red-500' : 'text-gray-300'); ?>">
                            <?php echo e($p['total_cost'] > 0 ? 'Rp '.number_format($p['total_cost'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono text-gray-600 dark:text-slate-300">
                            <?php echo e($p['cost_per_ha'] > 0 ? 'Rp '.number_format($p['cost_per_ha'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono font-medium text-emerald-600">
                            <?php echo e($p['total_harvest'] > 0 ? number_format($p['total_harvest'], 0).' kg' : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono text-blue-600">
                            <?php echo e($p['yield_per_ha'] > 0 ? number_format($p['yield_per_ha'], 0).' kg' : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-mono font-bold <?php echo e($p['hpp_per_kg'] !== null ? 'text-gray-900 dark:text-white' : 'text-gray-300'); ?>">
                            <?php echo e($p['hpp_per_kg'] !== null ? 'Rp '.number_format($p['hpp_per_kg'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-xs <?php echo e($p['reject_pct'] > 5 ? 'text-red-500 font-medium' : 'text-gray-400'); ?>">
                            <?php echo e($p['reject_pct'] > 0 ? $p['reject_pct'].'%' : '-'); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <?php if($ranked->isNotEmpty()): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-5">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🏆 Ranking Efisiensi (HPP Terendah = Paling Efisien)</h3>
        <div class="space-y-3">
            <?php $__currentLoopData = $ranked; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $medal = match($i) { 0 => '🥇', 1 => '🥈', 2 => '🥉', default => '#'.($i+1) };
                $barWidth = $ranked->first()['hpp_per_kg'] > 0 ? min(100, round($p['hpp_per_kg'] / $ranked->last()['hpp_per_kg'] * 100)) : 0;
            ?>
            <div class="flex items-center gap-3">
                <span class="text-lg w-8 text-center"><?php echo e($medal); ?></span>
                <div class="flex-1">
                    <div class="flex items-center justify-between mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($p['code']); ?> — <?php echo e($p['crop']); ?></span>
                        <span class="text-sm font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($p['hpp_per_kg'], 0, ',', '.')); ?>/kg</span>
                    </div>
                    <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-2">
                        <div class="h-2 rounded-full <?php echo e($i === 0 ? 'bg-emerald-500' : ($i === $ranked->count()-1 ? 'bg-red-400' : 'bg-blue-400')); ?>" style="width:<?php echo e($barWidth); ?>%"></div>
                    </div>
                    <div class="flex gap-4 mt-1 text-[10px] text-gray-400">
                        <span>Biaya/ha: Rp <?php echo e(number_format($p['cost_per_ha'], 0, ',', '.')); ?></span>
                        <span>Yield/ha: <?php echo e(number_format($p['yield_per_ha'], 0)); ?> kg</span>
                        <span>Panen: <?php echo e(number_format($p['total_harvest'], 0)); ?> kg</span>
                    </div>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\farm\analytics.blade.php ENDPATH**/ ?>