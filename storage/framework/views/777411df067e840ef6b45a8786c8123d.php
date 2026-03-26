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
     <?php $__env->slot('header', null, []); ?> Kelola Affiliate <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
        <?php $__currentLoopData = [
            ['label'=>'Total','value'=>$stats['total'],'color'=>'text-white'],
            ['label'=>'Aktif','value'=>$stats['active'],'color'=>'text-green-400'],
            ['label'=>'Referral','value'=>$stats['total_referrals'],'color'=>'text-blue-400'],
            ['label'=>'Total Earned','value'=>'Rp '.number_format($stats['total_earned'],0,',','.'),'color'=>'text-amber-400'],
            ['label'=>'Pending Withdraw','value'=>'Rp '.number_format($stats['pending_withdraw'],0,',','.'),'color'=>'text-red-400'],
            ['label'=>'Fraud Alerts','value'=>$stats['fraud_alerts'],'color'=>$stats['fraud_alerts'] > 0 ? 'text-red-400' : 'text-green-400'],
        ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-4">
            <p class="text-[10px] text-slate-500 uppercase tracking-wider mb-1"><?php echo e($s['label']); ?></p>
            <p class="text-lg font-bold <?php echo e($s['color']); ?>"><?php echo e($s['value']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex-1 flex gap-2">
            <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama / email..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-white/10 bg-white/5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Affiliate</button>
    </div>

    
    <div class="bg-[#1e293b] border border-white/10 rounded-2xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-white/5 text-xs text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Affiliate</th>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-center">Referral</th>
                        <th class="px-4 py-3 text-right">Earned</th>
                        <th class="px-4 py-3 text-right">Balance</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $affiliates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $aff): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="text-white font-medium"><?php echo e($aff->user->name ?? '-'); ?></p>
                            <p class="text-xs text-slate-500"><?php echo e($aff->user->email ?? '-'); ?></p>
                        </td>
                        <td class="px-4 py-3 font-mono text-xs text-blue-400"><?php echo e($aff->code); ?></td>
                        <td class="px-4 py-3 text-center text-white"><?php echo e($aff->referrals_count); ?></td>
                        <td class="px-4 py-3 text-right text-white">Rp <?php echo e(number_format($aff->total_earned, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-right <?php echo e($aff->balance > 0 ? 'text-amber-400' : 'text-slate-500'); ?>">Rp <?php echo e(number_format($aff->balance, 0, ',', '.')); ?></td>
                        <td class="px-4 py-3 text-center">
                            <?php if($aff->is_active): ?><span class="px-2 py-0.5 rounded-full text-xs bg-green-500/20 text-green-400">Aktif</span>
                            <?php else: ?><span class="px-2 py-0.5 rounded-full text-xs bg-red-500/20 text-red-400">Nonaktif</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <form method="POST" action="<?php echo e(route('super-admin.affiliates.toggle', $aff)); ?>" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" class="text-xs px-2 py-1 border border-white/10 rounded-lg text-slate-300 hover:bg-white/10"><?php echo e($aff->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?></button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="7" class="px-4 py-12 text-center text-slate-500">Belum ada affiliate.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($affiliates->hasPages()): ?><div class="px-4 py-3 border-t border-white/5"><?php echo e($affiliates->links()); ?></div><?php endif; ?>
    </div>

    
    <div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70">
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-white/10">
                <h3 class="font-semibold text-white">Buat Affiliate Baru</h3>
                <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('super-admin.affiliates.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <?php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-white/10 bg-white/5 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500'; ?>
                <div class="grid grid-cols-2 gap-4">
                    <div class="col-span-2"><label class="block text-xs text-slate-400 mb-1">Nama *</label><input type="text" name="name" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Email *</label><input type="email" name="email" required class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Password *</label><input type="password" name="password" required minlength="8" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Telepon</label><input type="text" name="phone" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Perusahaan</label><input type="text" name="company_name" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Bank</label><input type="text" name="bank_name" placeholder="BCA" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">No. Rekening</label><input type="text" name="bank_account" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Atas Nama</label><input type="text" name="bank_holder" class="<?php echo e($cls); ?>"></div>
                    <div><label class="block text-xs text-slate-400 mb-1">Komisi (%)</label><input type="number" name="commission_rate" min="0" max="50" step="0.5" value="10" class="<?php echo e($cls); ?>"></div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')" class="px-4 py-2 text-sm border border-white/10 rounded-xl text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat Affiliate</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/super-admin/affiliates/index.blade.php ENDPATH**/ ?>