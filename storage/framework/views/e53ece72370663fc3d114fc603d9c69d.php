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
     <?php $__env->slot('header', null, []); ?> Komisi Affiliate <?php $__env->endSlot(); ?>

    <div class="flex gap-2 mb-4">
        <?php $__currentLoopData = [''=>'Semua','pending'=>'Pending','approved'=>'Approved','paid'=>'Paid']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="?status=<?php echo e($v); ?>" class="px-3 py-1.5 text-xs rounded-xl <?php echo e(request('status')===$v ? 'bg-blue-600 text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10'); ?>"><?php echo e($l); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Tenant</th>
                        <th class="px-4 py-3 text-left">Plan</th>
                        <th class="px-4 py-3 text-right">Pembayaran</th>
                        <th class="px-4 py-3 text-right">Komisi</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $commissions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php $sc = ['pending'=>'amber','approved'=>'blue','paid'=>'green','rejected'=>'red'][$c->status] ?? 'gray'; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 text-white"><?php echo e($c->affiliate->user->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-300 text-xs"><?php echo e($c->tenant->name ?? '-'); ?></td>
                        <td class="px-4 py-3 text-slate-400 text-xs"><?php echo e($c->plan_name); ?></td>
                        <td class="px-4 py-3 text-right text-white">Rp <?php echo e(number_format($c->payment_amount, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right text-amber-400 font-medium">Rp <?php echo e(number_format($c->commission_amount, 0, ',', '.')); ?> <span class="text-xs text-slate-500">(<?php echo e($c->commission_rate); ?>%)</span></td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-500/20 text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst($c->status)); ?></span></td>
                        <td class="px-4 py-3 text-center">
                            <?php if($c->status === 'pending'): ?>
                            <form method="POST" action="<?php echo e(route('super-admin.affiliates.commissions.approve', $c)); ?>" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Approve</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada komisi.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($commissions->hasPages()): ?><div class="px-4 py-3 border-t border-white/5"><?php echo e($commissions->links()); ?></div><?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\affiliates\commissions.blade.php ENDPATH**/ ?>