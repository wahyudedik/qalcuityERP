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
     <?php $__env->slot('header', null, []); ?> Komisi Sales <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Penjualan</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($stats['total_sales'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Total Komisi</p>
            <p class="text-lg font-bold text-green-500">Rp <?php echo e(number_format($stats['total_commission'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Salesperson</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['salespeople']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Approved</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['approved']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="period" value="<?php echo e($period); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Lihat</button>
        </form>
        <div class="flex-1"></div>
        <div class="flex gap-2">
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'commission', 'create')): ?>
            <form method="POST" action="<?php echo e(route('commission.calculate')); ?>">
                <?php echo csrf_field(); ?> <input type="hidden" name="period" value="<?php echo e($period); ?>">
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Hitung Komisi</button>
            </form>
            <button onclick="document.getElementById('modal-target').classList.remove('hidden')"
                class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Set Target</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Salesperson</th>
                        <th class="px-4 py-3 text-right">Target</th>
                        <th class="px-4 py-3 text-right">Penjualan</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Achievement</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Bonus</th>
                        <th class="px-4 py-3 text-right">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $calculations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $target = $targets[$c->user_id] ?? null;
                        $ach = $target ? $target->achievement_pct : null;
                        $cc = ['draft'=>'gray','approved'=>'amber','paid'=>'green'][$c->status] ?? 'gray';
                        $cl = ['draft'=>'Draft','approved'=>'Approved','paid'=>'Dibayar'][$c->status] ?? $c->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 text-gray-900 dark:text-white"><?php echo e($c->user->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right text-gray-500 dark:text-slate-400"><?php echo e($target ? 'Rp ' . number_format($target->target_amount, 0, ',', '.') : '-'); ?></td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp <?php echo e(number_format($c->total_sales, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell">
                            <?php if($ach !== null): ?>
                            <div class="flex items-center gap-2">
                                <div class="flex-1 h-2 bg-gray-200 dark:bg-white/10 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full <?php echo e($ach >= 100 ? 'bg-green-500' : ($ach >= 80 ? 'bg-amber-500' : 'bg-red-500')); ?>" style="width: <?php echo e(min($ach, 100)); ?>%"></div>
                                </div>
                                <span class="text-xs font-medium <?php echo e($ach >= 100 ? 'text-green-500' : 'text-gray-500'); ?>"><?php echo e($ach); ?>%</span>
                            </div>
                            <?php else: ?> <span class="text-gray-400">—</span> <?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp <?php echo e(number_format($c->commission_amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell <?php echo e($c->bonus_amount > 0 ? 'text-green-500' : 'text-gray-400'); ?>"><?php echo e($c->bonus_amount > 0 ? 'Rp ' . number_format($c->bonus_amount, 0, ',', '.') : '—'); ?></td>
                        <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($c->total_payout, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($cc); ?>-100 text-<?php echo e($cc); ?>-700 dark:bg-<?php echo e($cc); ?>-500/20 dark:text-<?php echo e($cc); ?>-400"><?php echo e($cl); ?></span></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'commission', 'edit')): ?>
                                <?php if($c->status === 'draft'): ?>
                                <form method="POST" action="<?php echo e(url('commission')); ?>/<?php echo e($c->id); ?>/approve"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Approve</button>
                                </form>
                                <?php elseif($c->status === 'approved'): ?>
                                <form method="POST" action="<?php echo e(url('commission')); ?>/<?php echo e($c->id); ?>/pay" onsubmit="return confirm('Bayar komisi?')"><?php echo csrf_field(); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Bayar</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="9" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada data komisi. Klik "Hitung Komisi" untuk generate.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    
    <div id="modal-target" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Set Target Sales</h3>
                <button onclick="document.getElementById('modal-target').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('commission.targets.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Salesperson *</label>
                    <select name="user_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                        <?php $__currentLoopData = $salespeople; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($sp->id); ?>"><?php echo e($sp->name); ?> (<?php echo e($sp->role); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode *</label>
                    <input type="month" name="period" required value="<?php echo e($period); ?>" class="<?php echo e($cls); ?>">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target (Rp) *</label>
                    <input type="number" name="target_amount" required min="0" step="100000" class="<?php echo e($cls); ?>">
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Rule Komisi</label>
                    <select name="commission_rule_id" class="<?php echo e($cls); ?>"><option value="">-- Default --</option>
                        <?php $__currentLoopData = $rules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($r->id); ?>"><?php echo e($r->name); ?> (<?php echo e($r->type === 'flat_pct' ? $r->rate . '%' : ($r->type === 'flat_amount' ? 'Rp ' . number_format($r->rate, 0, ',', '.') : 'Tiered')); ?>)</option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-target').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\commission\index.blade.php ENDPATH**/ ?>