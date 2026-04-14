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
     <?php $__env->slot('header', null, []); ?> Neraca (Balance Sheet) <?php $__env->endSlot(); ?>

    <div class="space-y-5">

        
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div>
                <label class="text-xs text-gray-400 mb-1 block">Per Tanggal</label>
                <input type="date" name="as_of" value="<?php echo e($asOf); ?>"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Tampilkan</button>
            <a href="<?php echo e(route('accounting.balance-sheet.pdf')); ?>?as_of=<?php echo e($asOf); ?>"
               class="bg-red-600 hover:bg-red-700 text-white text-sm px-4 py-2 rounded-lg flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Export PDF
            </a>
        </form>

        <?php
            $fmt = fn($n) => number_format(abs($n), 0, ',', '.');
        ?>

        
        <div class="flex flex-wrap items-center gap-3">
            <?php if($data['is_balanced']): ?>
                <span class="inline-flex items-center gap-1.5 bg-green-500/10 text-green-400 text-xs px-3 py-1.5 rounded-full border border-green-500/20">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Neraca Balance
                </span>
            <?php else: ?>
                <span class="inline-flex items-center gap-1.5 bg-red-500/10 text-red-400 text-xs px-3 py-1.5 rounded-full border border-red-500/20">
                    ⚠ Neraca Tidak Balance — selisih Rp <?php echo e($fmt($data['total_assets'] - $data['total_l_e'])); ?>

                </span>
            <?php endif; ?>
            <?php if(isset($data['gl_integrity'])): ?>
                <?php if($data['gl_integrity']['is_balanced']): ?>
                <span class="inline-flex items-center gap-1.5 bg-blue-500/10 text-blue-400 text-xs px-3 py-1.5 rounded-full border border-blue-500/20">
                    ✓ GL Integrity OK (<?php echo e($data['gl_integrity']['journal_count']); ?> jurnal)
                </span>
                <?php else: ?>
                <span class="inline-flex items-center gap-1.5 bg-red-500/10 text-red-400 text-xs px-3 py-1.5 rounded-full border border-red-500/20">
                    ⚠ <?php echo e($data['gl_integrity']['unbalanced_count']); ?> jurnal tidak balance — selisih Rp <?php echo e(number_format($data['gl_integrity']['difference'], 0, ',', '.')); ?>

                </span>
                <?php endif; ?>
            <?php endif; ?>
            <span class="text-xs text-gray-500">Per <?php echo e(\Carbon\Carbon::parse($asOf)->translatedFormat('d F Y')); ?></span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">

            
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">ASET</h3>

                
                <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Aset Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $data['assets']['current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2.5 text-gray-300">
                                    <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span>
                                    <?php echo e($acc['name']); ?>

                                </td>
                                <td class="px-4 py-2.5 text-right text-white font-medium"><?php echo e($fmt($acc['balance'])); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($data['assets']['current']->isEmpty()): ?>
                            <tr><td colspan="2" class="px-4 py-4 text-center text-gray-500 text-xs">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-white/5">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-400">Total Aset Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-white"><?php echo e($fmt($data['assets']['current']->sum('balance'))); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Aset Tidak Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $data['assets']['non_current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2.5 text-gray-300">
                                    <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span>
                                    <?php echo e($acc['name']); ?>

                                </td>
                                <td class="px-4 py-2.5 text-right text-white font-medium"><?php echo e($fmt($acc['balance'])); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($data['assets']['non_current']->isEmpty()): ?>
                            <tr><td colspan="2" class="px-4 py-4 text-center text-gray-500 text-xs">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-white/5">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-400">Total Aset Tidak Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-white"><?php echo e($fmt($data['assets']['non_current']->sum('balance'))); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <div class="bg-indigo-600/20 border border-indigo-500/30 rounded-xl px-4 py-3 flex justify-between items-center">
                    <span class="font-bold text-white">TOTAL ASET</span>
                    <span class="font-bold text-indigo-300 text-lg">Rp <?php echo e($fmt($data['total_assets'])); ?></span>
                </div>
            </div>

            
            <div class="space-y-4">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">KEWAJIBAN & EKUITAS</h3>

                
                <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Kewajiban Lancar</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $data['liabilities']['current']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2.5 text-gray-300">
                                    <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span>
                                    <?php echo e($acc['name']); ?>

                                </td>
                                <td class="px-4 py-2.5 text-right text-white font-medium"><?php echo e($fmt($acc['balance'])); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            <?php if($data['liabilities']['current']->isEmpty()): ?>
                            <tr><td colspan="2" class="px-4 py-4 text-center text-gray-500 text-xs">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                        <tfoot class="bg-white/5">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-400">Total Kewajiban Lancar</td>
                                <td class="px-4 py-2.5 text-right font-bold text-white"><?php echo e($fmt($data['liabilities']['current']->sum('balance'))); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <?php if($data['liabilities']['long_term']->isNotEmpty()): ?>
                <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Kewajiban Jangka Panjang</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $data['liabilities']['long_term']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2.5 text-gray-300">
                                    <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span>
                                    <?php echo e($acc['name']); ?>

                                </td>
                                <td class="px-4 py-2.5 text-right text-white font-medium"><?php echo e($fmt($acc['balance'])); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                        <tfoot class="bg-white/5">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-400">Total Kewajiban Jangka Panjang</td>
                                <td class="px-4 py-2.5 text-right font-bold text-white"><?php echo e($fmt($data['liabilities']['long_term']->sum('balance'))); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                <?php endif; ?>

                
                <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
                    <div class="px-4 py-2.5 bg-white/5 border-b border-white/10">
                        <span class="text-xs font-semibold text-gray-400 uppercase">Ekuitas</span>
                    </div>
                    <table class="w-full text-sm">
                        <tbody class="divide-y divide-white/5">
                            <?php $__currentLoopData = $data['equity']['items']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-white/5">
                                <td class="px-4 py-2.5 text-gray-300">
                                    <span class="font-mono text-xs text-gray-500 mr-2"><?php echo e($acc['code']); ?></span>
                                    <?php echo e($acc['name']); ?>

                                </td>
                                <td class="px-4 py-2.5 text-right text-white font-medium"><?php echo e($fmt($acc['balance'])); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            
                            <tr class="hover:bg-white/5 border-t border-white/10">
                                <td class="px-4 py-2.5 text-gray-300 italic">Laba/Rugi Tahun Berjalan</td>
                                <td class="px-4 py-2.5 text-right font-medium <?php echo e($data['net_income'] >= 0 ? 'text-green-400' : 'text-red-400'); ?>">
                                    <?php echo e($data['net_income'] < 0 ? '(' : ''); ?><?php echo e($fmt($data['net_income'])); ?><?php echo e($data['net_income'] < 0 ? ')' : ''); ?>

                                </td>
                            </tr>
                        </tbody>
                        <tfoot class="bg-white/5">
                            <tr>
                                <td class="px-4 py-2.5 text-xs font-semibold text-gray-400">Total Ekuitas</td>
                                <td class="px-4 py-2.5 text-right font-bold text-white"><?php echo e($fmt($data['equity']['total'] + $data['net_income'])); ?></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                
                <div class="bg-indigo-600/20 border border-indigo-500/30 rounded-xl px-4 py-3 flex justify-between items-center">
                    <span class="font-bold text-white">TOTAL KEWAJIBAN & EKUITAS</span>
                    <span class="font-bold text-indigo-300 text-lg">Rp <?php echo e($fmt($data['total_l_e'])); ?></span>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/accounting/balance-sheet.blade.php ENDPATH**/ ?>