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
     <?php $__env->slot('header', null, []); ?> Program Loyalitas <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-sm text-red-600 dark:text-red-400"><?php echo e($errors->first()); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        
        <div class="lg:col-span-2 space-y-6">

            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <?php $__currentLoopData = [
                    ['label'=>'Total Member','value'=>number_format($stats['total_members']),'color'=>'text-gray-900 dark:text-white'],
                    ['label'=>'Total Poin Aktif','value'=>number_format($stats['total_points']),'color'=>'text-blue-600 dark:text-blue-400'],
                    ['label'=>'Poin Diperoleh (Bln)','value'=>number_format($stats['earned_month']),'color'=>'text-green-600 dark:text-green-400'],
                    ['label'=>'Poin Ditukar (Bln)','value'=>number_format($stats['redeemed_month']),'color'=>'text-amber-600 dark:text-amber-400'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
                    <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($s['label']); ?></p>
                    <p class="text-xl font-bold <?php echo e($s['color']); ?> mt-1"><?php echo e($s['value']); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                <form method="GET" class="flex gap-2 flex-1">
                    <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama pelanggan..."
                        class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <select name="tier" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                        <option value="">Semua Tier</option>
                        <?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($tier->name); ?>" <?php if(request('tier')===$tier->name): echo 'selected'; endif; ?>><?php echo e($tier->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
                </form>
                <button onclick="document.getElementById('modal-add-points').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700 shrink-0">+ Tambah Poin</button>
                <button onclick="document.getElementById('modal-redeem').classList.remove('hidden')"
                    class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700 shrink-0">Tukar Poin</button>
            </div>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Pelanggan</th>
                                <th class="px-4 py-3 text-center">Tier</th>
                                <th class="px-4 py-3 text-right">Poin Aktif</th>
                                <th class="px-4 py-3 text-right hidden md:table-cell">Lifetime</th>
                                <?php if($program): ?>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Nilai Poin</th>
                                <?php endif; ?>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $points; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $lp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php
                                $tierColors = ['Bronze'=>'amber','Silver'=>'gray','Gold'=>'yellow','Platinum'=>'blue'];
                                $tc = $tierColors[$lp->tier] ?? 'gray';
                            ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 dark:text-white"><?php echo e($lp->customer->name); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($lp->customer->phone ?? $lp->customer->email ?? ''); ?></p>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($tc); ?>-100 text-<?php echo e($tc); ?>-700 dark:bg-<?php echo e($tc); ?>-500/20 dark:text-<?php echo e($tc); ?>-400">
                                        <?php echo e($lp->tier); ?>

                                    </span>
                                </td>
                                <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white"><?php echo e(number_format($lp->total_points)); ?></td>
                                <td class="px-4 py-3 text-right hidden md:table-cell text-gray-500 dark:text-slate-400"><?php echo e(number_format($lp->lifetime_points)); ?></td>
                                <?php if($program): ?>
                                <td class="px-4 py-3 text-right hidden sm:table-cell text-blue-600 dark:text-blue-400 font-medium">
                                    Rp <?php echo e(number_format($lp->total_points * $program->idr_per_point, 0, ',', '.')); ?>

                                </td>
                                <?php endif; ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada member. Tambahkan poin ke pelanggan untuk memulai.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($points->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5"><?php echo e($points->links()); ?></div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Pengaturan Program</h3>
                <form method="POST" action="<?php echo e(route('loyalty.program.save')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Program</label>
                        <input type="text" name="name" value="<?php echo e($program?->name ?? 'Program Poin Setia'); ?>" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Poin per Rp 1</label>
                        <input type="number" name="points_per_idr" value="<?php echo e($program?->points_per_idr ?? 0.01); ?>" step="0.001" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">0.01 = 1 poin per Rp 100</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nilai 1 Poin (Rp)</label>
                        <input type="number" name="idr_per_point" value="<?php echo e($program?->idr_per_point ?? 100); ?>" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Minimum Redeem (poin)</label>
                        <input type="number" name="min_redeem_points" value="<?php echo e($program?->min_redeem_points ?? 100); ?>" min="1" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Masa Berlaku Poin (hari, 0=∞)</label>
                        <input type="number" name="expiry_days" value="<?php echo e($program?->expiry_days ?? 365); ?>" min="0" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Program</button>
                </form>
            </div>

            
            <?php if($tiers->count() > 0): ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-3">Tier Member</h3>
                <div class="space-y-2">
                    <?php $__currentLoopData = $tiers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tier): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50 dark:bg-white/5">
                        <div class="flex items-center gap-2">
                            <div class="w-3 h-3 rounded-full" style="background:<?php echo e($tier->color); ?>"></div>
                            <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($tier->name); ?></span>
                        </div>
                        <div class="text-right text-xs text-gray-500 dark:text-slate-400">
                            <p>≥ <?php echo e(number_format($tier->min_points)); ?> poin</p>
                            <p><?php echo e($tier->multiplier); ?>x multiplier</p>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="modal-add-points" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tambah Poin</h3>
                <button onclick="document.getElementById('modal-add-points').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('loyalty.add-points')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pelanggan *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php $__currentLoopData = \App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Transaksi (Rp) *</label>
                    <input type="number" name="transaction_amount" min="0" step="1000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php if($program): ?><p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Poin otomatis: Rp 100 = 1 poin</p><?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Override Poin (opsional)</label>
                    <input type="number" name="points_override" min="1" placeholder="Kosongkan untuk kalkulasi otomatis" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Referensi</label>
                    <input type="text" name="reference" placeholder="No. invoice / transaksi" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-points').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Tambah Poin</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-redeem" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Tukar Poin</h3>
                <button onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('loyalty.redeem')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pelanggan *</label>
                    <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">-- Pilih Pelanggan --</option>
                        <?php $__currentLoopData = \App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->orderBy('name')->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($c->id); ?>"><?php echo e($c->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Poin *</label>
                    <input type="number" name="points" min="<?php echo e($program?->min_redeem_points ?? 100); ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php if($program): ?><p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Min. <?php echo e($program->min_redeem_points); ?> poin · 1 poin = Rp <?php echo e(number_format($program->idr_per_point,0,',','.')); ?></p><?php endif; ?>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Referensi</label>
                    <input type="text" name="reference" placeholder="No. transaksi" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-redeem').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-amber-600 text-white rounded-xl hover:bg-amber-700">Tukar Poin</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\loyalty\index.blade.php ENDPATH**/ ?>