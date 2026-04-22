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
     <?php $__env->slot('header', null, []); ?> Landed Cost — <?php echo e($landedCost->number); ?> <?php $__env->endSlot(); ?>

    <div class="space-y-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($landedCost->number); ?></h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400">
                        PO: <?php echo e($landedCost->purchaseOrder->number ?? '-'); ?>

                        <?php if($landedCost->purchaseOrder?->supplier): ?>
                            · <?php echo e($landedCost->purchaseOrder->supplier->name); ?>

                        <?php endif; ?>
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <?php
                        $sc =
                            ['draft' => 'gray', 'allocated' => 'amber', 'posted' => 'green'][$landedCost->status] ??
                            'gray';
                        $sl =
                            ['draft' => 'Draft', 'allocated' => 'Dialokasi', 'posted' => 'Diposting'][
                                $landedCost->status
                            ] ?? $landedCost->status;
                    ?>
                    <span
                        class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span>

                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'landed_cost', 'edit')): ?>
                    <?php if($landedCost->status === 'draft' || $landedCost->status === 'allocated'): ?>
                        <form method="POST" action="<?php echo e(route('landed-cost.allocate', $landedCost)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-3 py-1 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">
                                <?php echo e($landedCost->status === 'draft' ? 'Alokasi' : 'Re-alokasi'); ?>

                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($landedCost->status === 'allocated'): ?>
                        <form method="POST" action="<?php echo e(route('landed-cost.post', $landedCost)); ?>"
                            onsubmit="return confirm('Posting akan update HPP produk. Lanjutkan?')">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Posting
                                & Update HPP</button>
                        </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal</p>
                    <p class="text-gray-900 dark:text-white"><?php echo e($landedCost->date->format('d/m/Y')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Metode Alokasi</p>
                    <p class="text-gray-900 dark:text-white">
                        <?php echo e(['by_value' => 'Berdasarkan Nilai', 'by_quantity' => 'Berdasarkan Qty', 'by_weight' => 'Berdasarkan Berat', 'equal' => 'Rata (Equal)'][$landedCost->allocation_method] ?? $landedCost->allocation_method); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya Tambahan</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp
                        <?php echo e(number_format($landedCost->total_additional_cost, 0, ',', '.')); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Jurnal</p>
                    <?php if($landedCost->journalEntry): ?>
                        <a href="<?php echo e(url('accounting/journals')); ?>/<?php echo e($landedCost->journalEntry->id); ?>"
                            class="text-blue-500 hover:underline"><?php echo e($landedCost->journalEntry->number); ?></a>
                    <?php else: ?>
                        <p class="text-gray-400">—</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Komponen Biaya</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tipe</th>
                            <th class="px-4 py-3 text-left">Nama</th>
                            <th class="px-4 py-3 text-right">Jumlah</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Vendor</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Referensi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $landedCost->components; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-3 text-xs">
                                    <span
                                        class="px-2 py-0.5 rounded-full <?php echo e(['freight' => 'bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-400', 'customs' => 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400', 'insurance' => 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400', 'handling' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400'][$comp->type] ?? 'bg-gray-100 text-gray-500'); ?>">
                                        <?php echo e(['freight' => 'Freight', 'customs' => 'Bea Masuk', 'insurance' => 'Asuransi', 'handling' => 'Handling', 'other' => 'Lainnya'][$comp->type] ?? $comp->type); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($comp->name); ?></td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp
                                    <?php echo e(number_format($comp->amount, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400">
                                    <?php echo e($comp->vendor ?? '-'); ?></td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 text-xs">
                                    <?php echo e($comp->reference ?? '-'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <tr class="bg-gray-50 dark:bg-white/5 font-semibold">
                            <td colspan="2" class="px-4 py-3 text-gray-900 dark:text-white">Total</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp
                                <?php echo e(number_format($landedCost->components->sum('amount'), 0, ',', '.')); ?></td>
                            <td colspan="2"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Alokasi per Produk</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Produk</th>
                            <th class="px-4 py-3 text-right">Qty</th>
                            <th class="px-4 py-3 text-right">Harga Asli</th>
                            <th class="px-4 py-3 text-right">Biaya Dialokasi</th>
                            <th class="px-4 py-3 text-right">Landed Unit Cost</th>
                            <?php if($landedCost->allocation_method === 'by_weight'): ?>
                                <th class="px-4 py-3 text-right">Berat</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $landedCost->allocations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alloc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($alloc->product->name ?? '-'); ?>

                                </td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($alloc->quantity, 0, ',', '.')); ?></td>
                                <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">Rp
                                    <?php echo e(number_format($alloc->original_cost, 0, ',', '.')); ?></td>
                                <td
                                    class="px-4 py-3 text-right font-medium <?php echo e($alloc->allocated_cost > 0 ? 'text-amber-500' : 'text-gray-400'); ?>">
                                    <?php echo e($alloc->allocated_cost > 0 ? 'Rp ' . number_format($alloc->allocated_cost, 0, ',', '.') : '—'); ?>

                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">
                                    <?php echo e($alloc->landed_unit_cost > 0 ? 'Rp ' . number_format($alloc->landed_unit_cost, 0, ',', '.') : '—'); ?>

                                </td>
                                <?php if($landedCost->allocation_method === 'by_weight'): ?>
                                    <td class="px-4 py-3 text-right">
                                        <?php if($landedCost->status !== 'posted'): ?>
                                            <form method="POST" action="<?php echo e(route('landed-cost.weight', $alloc)); ?>"
                                                class="flex items-center justify-end gap-1">
                                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                <input type="number" name="weight" value="<?php echo e($alloc->weight); ?>"
                                                    min="0.001" step="0.001"
                                                    class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                                <button type="submit"
                                                    class="text-xs text-blue-500 hover:underline">Confirm</button>
                                            </form>
                                        <?php else: ?>
                                            <?php echo e($alloc->weight ?? '-'); ?>

                                        <?php endif; ?>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\landed-cost\show.blade.php ENDPATH**/ ?>