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
     <?php $__env->slot('header', null, []); ?> Detail Work Order — <?php echo e($workOrder->number); ?> <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($workOrder->number); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400"><?php echo e($workOrder->product->name); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php
                        $colors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'];
                        $labels = ['pending'=>'Pending','in_progress'=>'Sedang Dikerjakan','completed'=>'Selesai','cancelled'=>'Dibatalkan'];
                        $c = $colors[$workOrder->status] ?? 'gray';
                    ?>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($c); ?>-100 text-<?php echo e($c); ?>-700 dark:bg-<?php echo e($c); ?>-500/20 dark:text-<?php echo e($c); ?>-400">
                        <?php echo e($labels[$workOrder->status] ?? $workOrder->status); ?>

                    </span>
                    <?php if($workOrder->status === 'pending'): ?>
                    <form method="POST" action="<?php echo e(route('production.status', $workOrder)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Mulai Produksi</button>
                    </form>
                    <?php elseif($workOrder->status === 'in_progress'): ?>
                    <form method="POST" action="<?php echo e(route('production.status', $workOrder)); ?>">
                        <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesaikan</button>
                    </form>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Target</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($workOrder->target_quantity, 0, ',', '.')); ?> <?php echo e($workOrder->unit); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Output Bagus</p>
                    <p class="font-semibold text-green-500"><?php echo e($workOrder->totalGoodQty()); ?> <?php echo e($workOrder->unit); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Reject</p>
                    <p class="font-semibold text-red-500"><?php echo e($workOrder->totalRejectQty()); ?> <?php echo e($workOrder->unit); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Yield Rate</p>
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($workOrder->yieldRate() ?? '-'); ?>%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya Material</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($workOrder->material_cost, 0, ',', '.')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya Tenaga Kerja</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($workOrder->labor_cost, 0, ',', '.')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Overhead</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($workOrder->overhead_cost, 0, ',', '.')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($workOrder->total_cost, 0, ',', '.')); ?></p>
                </div>
            </div>

            <?php if($workOrder->recipe): ?>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400 mb-2">Resep: <?php echo e($workOrder->recipe->name); ?> (batch <?php echo e($workOrder->recipe->batch_size); ?> <?php echo e($workOrder->recipe->batch_unit); ?>)</p>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $workOrder->recipe->ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ing): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-2 py-1 text-xs rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-slate-300">
                        <?php echo e($ing->product->name); ?>: <?php echo e($ing->quantity_per_batch); ?> <?php echo e($ing->unit); ?>

                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($workOrder->bom): ?>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs font-medium text-gray-500 dark:text-slate-400">
                        BOM: <?php echo e($workOrder->bom->name); ?> (batch <?php echo e($workOrder->bom->batch_size); ?> <?php echo e($workOrder->bom->batch_unit); ?>)
                    </p>
                    <div class="flex items-center gap-2">
                        <?php if($workOrder->materials_consumed): ?>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400">Material Dikonsumsi</span>
                        <?php elseif($workOrder->status === 'in_progress'): ?>
                            <form method="POST" action="<?php echo e(url('manufacturing')); ?>/<?php echo e($workOrder->id); ?>/consume" onsubmit="return confirm('Konsumsi material dari stok sesuai BOM?')">
                                <?php echo csrf_field(); ?>
                                <button type="submit" class="px-3 py-1 text-xs bg-amber-600 text-white rounded-xl hover:bg-amber-700">Konsumsi Material</button>
                            </form>
                        <?php else: ?>
                            <span class="px-2 py-0.5 rounded-full text-xs bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400">Belum Dikonsumsi</span>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="flex flex-wrap gap-2">
                    <?php $__currentLoopData = $workOrder->bom->lines; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $line): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <span class="px-2 py-1 text-xs rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-slate-300">
                        <?php echo e($line->product->name); ?>: <?php echo e($line->quantity_per_batch); ?> <?php echo e($line->unit); ?>

                        <?php if($line->childBom): ?> <span class="text-purple-500">(sub-BOM)</span> <?php endif; ?>
                    </span>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($workOrder->journalEntry): ?>
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                <p class="text-xs text-gray-500 dark:text-slate-400">
                    Jurnal Material: <a href="<?php echo e(url('accounting/journals')); ?>/<?php echo e($workOrder->journalEntry->id); ?>" class="text-blue-500 hover:underline"><?php echo e($workOrder->journalEntry->number); ?></a>
                    <span class="ml-2 px-1.5 py-0.5 rounded text-xs <?php echo e($workOrder->journalEntry->status === 'posted' ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400' : 'bg-gray-100 text-gray-500'); ?>"><?php echo e($workOrder->journalEntry->status); ?></span>
                </p>
            </div>
            <?php endif; ?>
        </div>

        
        <?php if($workOrder->status === 'in_progress'): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Catat Output Produksi</h3>
            <form method="POST" action="<?php echo e(route('production.output', $workOrder)); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty Bagus *</label>
                        <input type="number" name="good_qty" required min="0" step="0.001"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty Reject</label>
                        <input type="number" name="reject_qty" min="0" step="0.001" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Reject</label>
                        <input type="text" name="reject_reason" placeholder="Opsional"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_complete" value="1" class="rounded">
                        <span class="text-sm text-gray-700 dark:text-slate-300">Selesaikan WO & tambah stok otomatis</span>
                    </label>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan Output</button>
                </div>
            </form>
        </div>
        <?php endif; ?>

        
        <?php if($workOrder->operations->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Routing / Operasi</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-center">Seq</th>
                            <th class="px-4 py-3 text-left">Operasi</th>
                            <th class="px-4 py-3 text-left">Work Center</th>
                            <th class="px-4 py-3 text-right">Est. Jam</th>
                            <th class="px-4 py-3 text-right">Aktual Jam</th>
                            <th class="px-4 py-3 text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $workOrder->operations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $op): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $opColors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','skipped'=>'gray'];
                            $opLabels = ['pending'=>'Pending','in_progress'=>'Dikerjakan','completed'=>'Selesai','skipped'=>'Dilewati'];
                            $oc = $opColors[$op->status] ?? 'gray';
                        ?>
                        <tr>
                            <td class="px-4 py-3 text-center font-mono text-xs text-gray-900 dark:text-white"><?php echo e($op->sequence); ?></td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($op->name); ?></td>
                            <td class="px-4 py-3 text-gray-500 dark:text-slate-400"><?php echo e($op->workCenter->name ?? '-'); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e($op->estimated_hours); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e($op->actual_hours ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($oc); ?>-100 text-<?php echo e($oc); ?>-700 dark:bg-<?php echo e($oc); ?>-500/20 dark:text-<?php echo e($oc); ?>-400">
                                    <?php echo e($opLabels[$op->status] ?? $op->status); ?>

                                </span>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

        
        <?php if($workOrder->outputs->isNotEmpty()): ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Output</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-right">Bagus</th>
                            <th class="px-4 py-3 text-right">Reject</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Alasan Reject</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $workOrder->outputs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $out): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400"><?php echo e($out->created_at->format('d M Y H:i')); ?></td>
                            <td class="px-4 py-3 text-right text-green-500 font-medium"><?php echo e($out->good_qty + 0); ?></td>
                            <td class="px-4 py-3 text-right text-red-400"><?php echo e($out->reject_qty + 0); ?></td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white"><?php echo e($out->output_qty + 0); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($out->reject_reason ?? '-'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\production\show.blade.php ENDPATH**/ ?>