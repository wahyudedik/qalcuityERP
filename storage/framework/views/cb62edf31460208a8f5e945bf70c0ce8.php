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
     <?php $__env->slot('header', null, []); ?> Withdraw Affiliate <?php $__env->endSlot(); ?>

    <div class="flex gap-2 mb-4">
        <?php $__currentLoopData = ['' => 'Semua', 'pending' => 'Pending', 'completed' => 'Completed', 'rejected' => 'Rejected']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="?status=<?php echo e($v); ?>"
                class="px-3 py-1.5 text-xs rounded-xl <?php echo e(request('status') === $v ? 'bg-blue-600 text-white' : 'bg-white/5 border border-white/10 text-slate-300 hover:bg-white/10'); ?>"><?php echo e($l); ?></a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-right">Jumlah</th>
                        <th class="px-4 py-3 text-left">Rekening</th>
                        <th class="px-4 py-3 text-center">Diajukan</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $payouts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $sc =
                                ['pending' => 'amber', 'completed' => 'green', 'rejected' => 'red'][$p->status] ??
                                'gray';
                            $aff = $p->affiliate;
                        ?>
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-3">
                                <p class="text-white"><?php echo e($aff->user->name ?? '-'); ?></p>
                                <p class="text-xs text-slate-500"><?php echo e($aff->user->email ?? ''); ?></p>
                            </td>
                            <td class="px-4 py-3 text-right text-amber-400 font-bold">Rp
                                <?php echo e(number_format($p->amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-xs text-slate-400">
                                <?php echo e($aff->bank_name ?? '-'); ?> <?php echo e($aff->bank_account ?? ''); ?><br>
                                a/n <?php echo e($aff->bank_holder ?? '-'); ?>

                            </td>
                            <td class="px-4 py-3 text-center text-xs text-slate-400">
                                <?php echo e($p->requested_at?->format('d/m/Y H:i') ?? $p->created_at->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-500/20 text-<?php echo e($sc); ?>-400"><?php echo e(ucfirst($p->status)); ?></span>
                                <?php if($p->reject_reason): ?>
                                    <p class="text-xs text-red-400 mt-1"><?php echo e($p->reject_reason); ?></p>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($p->status === 'pending'): ?>
                                    <div class="flex items-center justify-center gap-1">
                                        <form method="POST"
                                            action="<?php echo e(route('super-admin.affiliates.payouts.approve', $p)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700"
                                                onclick="return confirm('Approve withdraw ini? Saldo affiliate akan dikurangi.')">Approve</button>
                                        </form>
                                        <form method="POST"
                                            action="<?php echo e(route('super-admin.affiliates.payouts.reject', $p)); ?>"
                                            onsubmit="const r=prompt('Alasan reject:'); if(!r) return false; this.querySelector('[name=reason]').value=r;">
                                            <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                            <input type="hidden" name="reason" value="">
                                            <button type="submit"
                                                class="text-xs px-2 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">✕
                                                Reject</button>
                                        </form>
                                    </div>
                                <?php elseif($p->processor): ?>
                                    <span class="text-xs text-slate-500"><?php echo e($p->processor->name ?? ''); ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-slate-500">Belum ada withdraw.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($payouts->hasPages()): ?>
            <div class="px-4 py-3 border-t border-white/5"><?php echo e($payouts->links()); ?></div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\affiliates\payouts.blade.php ENDPATH**/ ?>