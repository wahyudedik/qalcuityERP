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
     <?php $__env->slot('header', null, []); ?> Laporan Laba Rugi <?php $__env->endSlot(); ?>

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
            <a href="<?php echo e(route('accounting.income-statement.pdf')); ?>?from=<?php echo e($from); ?>&to=<?php echo e($to); ?>"
               class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </form>

        <?php
            $fmt = fn($n) => 'Rp ' . number_format(abs($n), 0, ',', '.');
            $pct = fn($part, $total) => $total > 0 ? round(($part / $total) * 100, 1) . '%' : '-';
        ?>

        <div class="max-w-2xl space-y-4">

            
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-green-500/10 border-b border-white/10 flex justify-between">
                    <span class="text-xs font-semibold text-green-400 uppercase">Pendapatan</span>
                    <span class="text-xs text-gray-500">% dari total</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $data['revenue']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?>

                            </td>
                            <td class="px-4 py-2.5 text-right text-white"><?php echo e($fmt($acc['balance'])); ?></td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16"><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php if(empty($data['revenue']['items']->toArray())): ?>
                        <tr><td colspan="3" class="px-4 py-4 text-center text-gray-500 text-xs">Tidak ada data pendapatan</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot class="bg-green-500/10">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-green-400">Total Pendapatan</td>
                            <td class="px-4 py-2.5 text-right font-bold text-green-400"><?php echo e($fmt($data['revenue']['total'])); ?></td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            
            <?php if($data['cogs']['items']->isNotEmpty()): ?>
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Harga Pokok Penjualan (HPP)</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $data['cogs']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?>

                            </td>
                            <td class="px-4 py-2.5 text-right text-white">(<?php echo e($fmt($acc['balance'])); ?>)</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16"><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="bg-white/5">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-gray-400">Total HPP</td>
                            <td class="px-4 py-2.5 text-right font-bold text-white">(<?php echo e($fmt($data['cogs']['total'])); ?>)</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="font-semibold text-gray-300">Laba Kotor</span>
                <span class="font-bold <?php echo e($data['gross_profit'] >= 0 ? 'text-green-400' : 'text-red-400'); ?>">
                    <?php echo e($data['gross_profit'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['gross_profit'])); ?><?php echo e($data['gross_profit'] < 0 ? ')' : ''); ?>

                </span>
            </div>

            
            <?php if($data['opex']['items']->isNotEmpty()): ?>
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Beban Operasional</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $data['opex']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?>

                            </td>
                            <td class="px-4 py-2.5 text-right text-white">(<?php echo e($fmt($acc['balance'])); ?>)</td>
                            <td class="px-4 py-2.5 text-right text-gray-500 text-xs w-16"><?php echo e($pct($acc['balance'], $data['revenue']['total'])); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                    <tfoot class="bg-white/5">
                        <tr>
                            <td class="px-4 py-2.5 font-semibold text-gray-400">Total Beban Operasional</td>
                            <td class="px-4 py-2.5 text-right font-bold text-white">(<?php echo e($fmt($data['opex']['total'])); ?>)</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            <?php endif; ?>

            
            <div class="bg-white/5 border border-white/10 rounded-xl px-4 py-3 flex justify-between items-center">
                <span class="font-semibold text-gray-300">Laba Operasi</span>
                <span class="font-bold <?php echo e($data['operating_income'] >= 0 ? 'text-blue-400' : 'text-red-400'); ?>">
                    <?php echo e($data['operating_income'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['operating_income'])); ?><?php echo e($data['operating_income'] < 0 ? ')' : ''); ?>

                </span>
            </div>

            
            <?php if($data['other_expense']['items']->isNotEmpty()): ?>
            <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                    <span class="text-xs font-semibold text-gray-400 uppercase">Beban / Pendapatan Lain-lain</span>
                </div>
                <table class="w-full text-sm">
                    <tbody class="divide-y divide-white/5">
                        <?php $__currentLoopData = $data['other_expense']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-2.5 text-gray-300">
                                <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span><?php echo e($acc['name']); ?>

                            </td>
                            <td class="px-4 py-2.5 text-right text-white">(<?php echo e($fmt($acc['balance'])); ?>)</td>
                            <td></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>

            
            <div class="border-2 rounded-xl px-4 py-4 flex justify-between items-center
                <?php echo e($data['net_income'] >= 0 ? 'bg-green-500/10 border-green-500/30' : 'bg-red-500/10 border-red-500/30'); ?>">
                <div>
                    <div class="font-bold text-white text-base"><?php echo e($data['net_income'] >= 0 ? 'LABA BERSIH' : 'RUGI BERSIH'); ?></div>
                    <div class="text-xs text-gray-400 mt-0.5">
                        <?php echo e(\Carbon\Carbon::parse($from)->translatedFormat('d M Y')); ?> s/d <?php echo e(\Carbon\Carbon::parse($to)->translatedFormat('d M Y')); ?>

                    </div>
                </div>
                <span class="font-bold text-xl <?php echo e($data['net_income'] >= 0 ? 'text-green-400' : 'text-red-400'); ?>">
                    <?php echo e($data['net_income'] < 0 ? '(' : ''); ?>Rp <?php echo e(number_format(abs($data['net_income']), 0, ',', '.')); ?><?php echo e($data['net_income'] < 0 ? ')' : ''); ?>

                </span>
            </div>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/accounting/income-statement.blade.php ENDPATH**/ ?>