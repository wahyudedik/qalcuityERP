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
     <?php $__env->slot('header', null, []); ?> Tiket — <?php echo e($helpdeskTicket->ticket_number); ?> <?php $__env->endSlot(); ?>

    <?php $t = $helpdeskTicket; ?>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <div class="flex items-start justify-between gap-4 mb-4">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white"><?php echo e($t->subject); ?></h2>
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-1"><?php echo e($t->ticket_number); ?> · <?php echo e($t->created_at->format('d/m/Y H:i')); ?> · oleh <?php echo e($t->creator->name ?? '-'); ?></p>
                    </div>
                    <?php
                        $pc = ['low'=>'gray','medium'=>'blue','high'=>'amber','urgent'=>'red'][$t->priority] ?? 'gray';
                        $sc = ['open'=>'blue','in_progress'=>'amber','waiting'=>'purple','resolved'=>'green','closed'=>'gray'][$t->status] ?? 'gray';
                    ?>
                    <div class="flex gap-2">
                        <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($pc); ?>-100 text-<?php echo e($pc); ?>-700 dark:bg-<?php echo e($pc); ?>-500/20 dark:text-<?php echo e($pc); ?>-400"><?php echo e(ucfirst($t->priority)); ?></span>
                        <span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst(str_replace('_', ' ', $t->status))); ?></span>
                    </div>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-700 dark:text-slate-300">
                    <?php echo nl2br(e($t->description)); ?>

                </div>
            </div>

            
            <div class="space-y-3">
                <?php $__currentLoopData = $t->replies->sortBy('created_at'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $reply): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border <?php echo e($reply->is_internal ? 'border-amber-200 dark:border-amber-500/30' : 'border-gray-200 dark:border-white/10'); ?> p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($reply->user->name ?? 'System'); ?></span>
                        <div class="flex items-center gap-2">
                            <?php if($reply->is_internal): ?><span class="text-xs text-amber-500">🔒 Internal</span><?php endif; ?>
                            <span class="text-xs text-gray-400"><?php echo e($reply->created_at->format('d/m H:i')); ?></span>
                        </div>
                    </div>
                    <div class="text-sm text-gray-700 dark:text-slate-300"><?php echo nl2br(e($reply->body)); ?></div>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <?php if(!in_array($t->status, ['closed'])): ?>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'helpdesk', 'create')): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Balas</h3>
                <form method="POST" action="<?php echo e(route('helpdesk.reply', $t)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <textarea name="body" required rows="3" placeholder="Tulis balasan..."
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></textarea>
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="is_internal" value="1" class="rounded">
                            <span class="text-xs text-gray-500 dark:text-slate-400">Internal note (tidak terlihat customer)</span>
                        </label>
                        <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Kirim</button>
                    </div>
                </form>
            </div>
            <?php endif; ?>
            <?php endif; ?>
        </div>

        
        <div class="space-y-6">
            
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'helpdesk', 'edit')): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-3">Kelola Tiket</h4>
                <form method="POST" action="<?php echo e(route('helpdesk.status', $t)); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <div><label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Status</label>
                        <select name="status" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <?php $__currentLoopData = ['open'=>'Open','in_progress'=>'In Progress','waiting'=>'Waiting','resolved'=>'Resolved','closed'=>'Closed']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($v); ?>" <?php if($t->status===$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div><label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Assign ke</label>
                        <select name="assigned_to" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="">-- Unassigned --</option>
                            <?php $__currentLoopData = $agents; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $a): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($a->id); ?>" <?php if($t->assigned_to==$a->id): echo 'selected'; endif; ?>><?php echo e($a->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Update</button>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 space-y-2 text-sm">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">Detail</h4>
                <div><span class="text-gray-500 dark:text-slate-400">Customer:</span> <span class="text-gray-900 dark:text-white"><?php echo e($t->customer->name ?? $t->contact_name ?? '-'); ?></span></div>
                <div><span class="text-gray-500 dark:text-slate-400">Email:</span> <span class="text-gray-900 dark:text-white"><?php echo e($t->contact_email ?? $t->customer->email ?? '-'); ?></span></div>
                <div><span class="text-gray-500 dark:text-slate-400">Kategori:</span> <span class="text-gray-900 dark:text-white"><?php echo e(ucfirst($t->category)); ?></span></div>
                <?php if($t->contract): ?><div><span class="text-gray-500 dark:text-slate-400">Kontrak:</span> <a href="<?php echo e(route('contracts.show', $t->contract)); ?>" class="text-blue-500 hover:underline"><?php echo e($t->contract->contract_number); ?></a></div><?php endif; ?>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4 space-y-2 text-sm">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">SLA</h4>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Response Due:</span>
                    <span class="<?php echo e($t->sla_response_due && $t->sla_response_due->isPast() && !$t->first_responded_at ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>"><?php echo e($t->sla_response_due?->format('d/m H:i') ?? '-'); ?></span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">First Response:</span>
                    <span class="text-gray-900 dark:text-white"><?php echo e($t->first_responded_at?->format('d/m H:i') ?? '-'); ?>

                        <?php if($t->sla_response_met === true): ?> ✅ <?php elseif($t->sla_response_met === false): ?> ❌ <?php endif; ?>
                    </span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Resolve Due:</span>
                    <span class="<?php echo e($t->isOverdue() ? 'text-red-500 font-semibold' : 'text-gray-900 dark:text-white'); ?>"><?php echo e($t->sla_resolve_due?->format('d/m H:i') ?? '-'); ?></span>
                </div>
                <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Resolved:</span>
                    <span class="text-gray-900 dark:text-white"><?php echo e($t->resolved_at?->format('d/m H:i') ?? '-'); ?>

                        <?php if($t->sla_resolve_met === true): ?> ✅ <?php elseif($t->sla_resolve_met === false): ?> ❌ <?php endif; ?>
                    </span>
                </div>
            </div>

            
            <?php if($kbArticles->isNotEmpty()): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">Artikel Terkait</h4>
                <div class="space-y-1">
                    <?php $__currentLoopData = $kbArticles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kb): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="#" class="block text-sm text-blue-500 hover:underline">📄 <?php echo e($kb->title); ?></a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>

            
            <?php if($t->status === 'resolved' || $t->status === 'closed'): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-2">Kepuasan</h4>
                <?php if($t->satisfaction_rating): ?>
                <p class="text-2xl"><?php echo e(str_repeat('⭐', (int) $t->satisfaction_rating)); ?></p>
                <?php else: ?>
                <form method="POST" action="<?php echo e(route('helpdesk.rate', $t)); ?>" class="flex items-center gap-2">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <select name="satisfaction_rating" class="px-2 py-1 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                        <?php for($i=1;$i<=5;$i++): ?><option value="<?php echo e($i); ?>"><?php echo e($i); ?> ⭐</option><?php endfor; ?>
                    </select>
                    <button type="submit" class="px-3 py-1 text-xs bg-blue-600 text-white rounded-lg">Rate</button>
                </form>
                <?php endif; ?>
            </div>
            <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\helpdesk\show.blade.php ENDPATH**/ ?>