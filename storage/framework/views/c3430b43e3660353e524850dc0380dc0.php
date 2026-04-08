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
     <?php $__env->slot('header', null, []); ?> Subscription Billing <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Aktif</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['active']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Trial</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['trial']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">MRR</p>
            <p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($stats['mrr'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Past Due</p>
            <p class="text-2xl font-bold text-red-500"><?php echo e($stats['past_due']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Jatuh Tempo Hari Ini</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['due_today']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari subscription / customer..."
                class="flex-1 min-w-[150px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['trial'=>'Trial','active'=>'Aktif','past_due'=>'Past Due','cancelled'=>'Batal','expired'=>'Expired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('subscription-billing.plans')); ?>" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Plans</a>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'subscription_billing', 'create')): ?>
            <form method="POST" action="<?php echo e(route('subscription-billing.bulk-generate')); ?>"><?php echo csrf_field(); ?>
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700" onclick="return confirm('Generate invoice untuk semua subscription jatuh tempo?')">⚡ Bulk Generate</button>
            </form>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Subscription</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No.</th>
                        <th class="px-4 py-3 text-left">Customer</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Plan</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Harga</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">Next Billing</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $subscriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $sc = ['trial'=>'blue','active'=>'green','past_due'=>'red','cancelled'=>'gray','expired'=>'gray'][$s->status] ?? 'gray';
                        $sl = ['trial'=>'Trial','active'=>'Aktif','past_due'=>'Past Due','cancelled'=>'Batal','expired'=>'Expired'][$s->status] ?? $s->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="<?php echo e(route('subscription-billing.show', $s)); ?>" class="hover:text-blue-500"><?php echo e($s->subscription_number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e($s->customer->name ?? '-'); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400 text-xs"><?php echo e($s->plan->name ?? '-'); ?> (<?php echo e($s->plan->cycleLabel() ?? ''); ?>)</td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900 dark:text-white">Rp <?php echo e(number_format($s->effectivePrice(), 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center hidden md:table-cell text-xs <?php echo e($s->next_billing_date->isPast() ? 'text-red-500 font-semibold' : 'text-gray-500 dark:text-slate-400'); ?>"><?php echo e($s->next_billing_date->format('d/m/Y')); ?></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span></td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="<?php echo e(route('subscription-billing.show', $s)); ?>" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300">Detail</a>
                                <?php if(in_array($s->status, ['active', 'trial']) && $s->next_billing_date->lte(today())): ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'subscription_billing', 'create')): ?>
                                <form method="POST" action="<?php echo e(route('subscription-billing.generate', $s)); ?>" class="inline"><?php echo csrf_field(); ?>
                                    <button type="submit" class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Invoice</button>
                                </form>
                                <?php endif; ?>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada subscription.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($subscriptions->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($subscriptions->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Subscription</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('subscription-billing.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer *</label>
                    <select name="customer_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                        <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Plan *</label>
                    <select name="plan_id" required class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                        <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?> — Rp <?php echo e(number_format($p->price, 0, ',', '.')); ?>/<?php echo e($p->cycleLabel()); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mulai *</label><input type="date" name="start_date" required value="<?php echo e(date('Y-m-d')); ?>" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Diskon (%)</label><input type="number" name="discount_pct" min="0" max="100" step="0.01" value="0" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Harga Override</label><input type="number" name="price_override" min="0" step="1000" placeholder="Kosong = pakai harga plan" class="<?php echo e($cls); ?>"></div>
                    <div class="flex items-end"><label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" name="auto_renew" value="1" checked class="rounded"><span class="text-sm text-gray-700 dark:text-slate-300">Auto Renew</span></label></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/subscription-billing/index.blade.php ENDPATH**/ ?>