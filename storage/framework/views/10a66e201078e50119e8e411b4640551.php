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
     <?php $__env->slot('header', null, []); ?> Subscription — <?php echo e($customerSubscription->subscription_number); ?> <?php $__env->endSlot(); ?>

    <?php $sub = $customerSubscription; ?>
    <div class="space-y-6">
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900"><?php echo e($sub->plan->name ?? '-'); ?></h2>
                    <p class="text-sm text-gray-500"><?php echo e($sub->subscription_number); ?> · 👤 <?php echo e($sub->customer->name ?? '-'); ?></p>
                </div>
                <div class="flex items-center gap-2">
                    <?php $sc = ['trial'=>'blue','active'=>'green','past_due'=>'red','cancelled'=>'gray','expired'=>'gray'][$sub->status] ?? 'gray'; ?>
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 $sc }}-500/20 $sc }}-400"><?php echo e(ucfirst(str_replace('_', ' ', $sub->status))); ?></span>
                    <?php if(in_array($sub->status, ['active', 'trial'])): ?>
                    <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'subscription_billing', 'edit')): ?>
                    <form method="POST" action="<?php echo e(route('subscription-billing.cancel', $sub)); ?>" onsubmit="return confirm('Batalkan subscription ini?')"><?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                        <button type="submit" class="px-3 py-1 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">Cancel</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 text-sm">
                <div><p class="text-xs text-gray-500">Mulai</p><p class="text-gray-900"><?php echo e($sub->start_date->format('d/m/Y')); ?></p></div>
                <div><p class="text-xs text-gray-500">Siklus</p><p class="text-gray-900"><?php echo e($sub->plan->cycleLabel() ?? '-'); ?></p></div>
                <div><p class="text-xs text-gray-500">Harga Efektif</p><p class="font-semibold text-gray-900">Rp <?php echo e(number_format($sub->effectivePrice(), 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500">MRR</p><p class="font-semibold text-green-500">Rp <?php echo e(number_format($sub->mrr(), 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500">Next Billing</p><p class="<?php echo e($sub->next_billing_date->isPast() ? 'text-red-500' : 'text-gray-900'); ?>"><?php echo e($sub->next_billing_date->format('d/m/Y')); ?></p></div>
                <div><p class="text-xs text-gray-500">Auto Renew</p><p class="text-gray-900"><?php echo e($sub->auto_renew ? '✅ Ya' : '❌ Tidak'); ?></p></div>
                <?php if($sub->discount_pct > 0): ?><div><p class="text-xs text-gray-500">Diskon</p><p class="text-gray-900"><?php echo e($sub->discount_pct); ?>%</p></div><?php endif; ?>
                <?php if($sub->trial_ends_at): ?><div><p class="text-xs text-gray-500">Trial Ends</p><p class="text-gray-900"><?php echo e($sub->trial_ends_at->format('d/m/Y')); ?></p></div><?php endif; ?>
            </div>
        </div>

        
        <?php if(in_array($sub->status, ['active']) && $sub->next_billing_date->lte(today())): ?>
        <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'subscription_billing', 'create')): ?>
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 flex items-center justify-between">
            <p class="text-sm text-amber-700">Billing jatuh tempo: <?php echo e($sub->next_billing_date->format('d/m/Y')); ?></p>
            <form method="POST" action="<?php echo e(route('subscription-billing.generate', $sub)); ?>"><?php echo csrf_field(); ?>
                <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Generate Invoice</button>
            </form>
        </div>
        <?php endif; ?>
        <?php endif; ?>

        
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Riwayat Invoice</h3>
            </div>
            <?php if($sub->invoices->isNotEmpty()): ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr><th class="px-4 py-3 text-left">Invoice</th><th class="px-4 py-3 text-center">Periode</th><th class="px-4 py-3 text-right">Jumlah</th><th class="px-4 py-3 text-right">Diskon</th><th class="px-4 py-3 text-right">Net</th><th class="px-4 py-3 text-center">Status</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__currentLoopData = $sub->invoices->sortByDesc('billing_date'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $si): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $ic = ['pending'=>'gray','invoiced'=>'blue','paid'=>'green','failed'=>'red'][$si->status] ?? 'gray'; ?>
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs text-gray-900"><?php echo e($si->invoice->number ?? '-'); ?></td>
                            <td class="px-4 py-3 text-center text-xs text-gray-500"><?php echo e($si->period_start->format('d/m')); ?> — <?php echo e($si->period_end->format('d/m/Y')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-700">Rp <?php echo e(number_format($si->amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-right text-gray-500"><?php echo e($si->discount > 0 ? 'Rp ' . number_format($si->discount, 0, ',', '.') : '-'); ?></td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp <?php echo e(number_format($si->net_amount, 0, ',', '.')); ?></td>
                            <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($ic); ?>-100 text-<?php echo e($ic); ?>-700 $ic }}-500/20 $ic }}-400"><?php echo e(ucfirst($si->status)); ?></span></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-400 text-sm">Belum ada invoice.</div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\subscription-billing\show.blade.php ENDPATH**/ ?>