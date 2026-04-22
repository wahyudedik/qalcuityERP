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
     <?php $__env->slot('header', null, []); ?> Helpdesk <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Open</p>
            <p class="text-2xl font-bold text-blue-500"><?php echo e($stats['open']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">In Progress</p>
            <p class="text-2xl font-bold text-amber-500"><?php echo e($stats['in_progress']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Overdue SLA</p>
            <p class="text-2xl font-bold text-red-500"><?php echo e($stats['overdue']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Resolved Bulan Ini</p>
            <p class="text-2xl font-bold text-green-500"><?php echo e($stats['resolved_month']); ?></p>
        </div>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex flex-wrap gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari tiket..."
                class="flex-1 min-w-[120px] px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <?php $__currentLoopData = ['open'=>'Open','in_progress'=>'In Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('status')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <select name="priority" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Prioritas</option>
                <?php $__currentLoopData = ['low'=>'Low','medium'=>'Medium','high'=>'High','urgent'=>'Urgent']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if(request('priority')===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
        <div class="flex gap-2">
            <a href="<?php echo e(route('helpdesk.kb')); ?>" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Knowledge Base</a>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'helpdesk', 'create')): ?>
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tiket</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Tiket</th>
                        <th class="px-4 py-3 text-left">Subjek</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Customer</th>
                        <th class="px-4 py-3 text-center">Prioritas</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Assigned</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center hidden md:table-cell">SLA</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $tickets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $pc = ['low'=>'gray','medium'=>'blue','high'=>'amber','urgent'=>'red'][$t->priority] ?? 'gray';
                        $sc = ['open'=>'blue','in_progress'=>'amber','waiting'=>'purple','resolved'=>'green','closed'=>'gray'][$t->status] ?? 'gray';
                        $sl = ['open'=>'Open','in_progress'=>'Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed'][$t->status] ?? $t->status;
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 <?php echo e($t->isOverdue() ? 'bg-red-50/50 dark:bg-red-500/5' : ''); ?>">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="<?php echo e(route('helpdesk.show', $t)); ?>" class="hover:text-blue-500"><?php echo e($t->ticket_number); ?></a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300"><?php echo e(Str::limit($t->subject, 35)); ?></td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->customer->name ?? $t->contact_name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 dark:bg-<?php echo e($pc); ?>-500/20 dark:text-<?php echo e($pc); ?>-400"><?php echo e(ucfirst($t->priority)); ?></span></td>
                        <td class="px-4 py-3 text-center hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400"><?php echo e($t->assignee->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e($sl); ?></span></td>
                        <td class="px-4 py-3 text-center hidden md:table-cell">
                            <?php if($t->isOverdue()): ?><span class="text-red-500 text-xs">⏰ Overdue</span>
                            <?php elseif($t->sla_resolve_met === true): ?><span class="text-green-500 text-xs">✅</span>
                            <?php elseif($t->sla_resolve_met === false): ?><span class="text-red-500 text-xs">❌</span>
                            <?php else: ?><span class="text-gray-400 text-xs">—</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <a href="<?php echo e(route('helpdesk.show', $t)); ?>" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Detail</a>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada tiket.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($tickets->hasPages()): ?><div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($tickets->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Tiket</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('helpdesk.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white'; ?>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Subjek *</label><input type="text" name="subject" required class="<?php echo e($cls); ?>"></div>
                <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Deskripsi *</label><textarea name="description" required rows="3" class="<?php echo e($cls); ?>"></textarea></div>
                <div class="grid grid-cols-2 gap-4">
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Customer</label>
                        <select name="customer_id" class="<?php echo e($cls); ?>"><option value="">-- Pilih --</option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Prioritas *</label>
                        <select name="priority" required class="<?php echo e($cls); ?>">
                            <option value="low">Low</option><option value="medium" selected>Medium</option><option value="high">High</option><option value="urgent">Urgent</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori *</label>
                        <select name="category" required class="<?php echo e($cls); ?>">
                            <option value="general">Umum</option><option value="billing">Billing</option><option value="technical">Teknis</option><option value="delivery">Pengiriman</option><option value="product">Produk</option><option value="complaint">Komplain</option>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Assign ke</label>
                        <select name="assigned_to" class="<?php echo e($cls); ?>"><option value="">-- Auto --</option>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>"><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Kontak</label><input type="text" name="contact_name" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Email Kontak</label><input type="email" name="contact_email" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Tiket</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\helpdesk\index.blade.php ENDPATH**/ ?>