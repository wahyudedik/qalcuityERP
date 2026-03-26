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
     <?php $__env->slot('title', null, []); ?> Semua Tenant — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Panel Super Admin <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <?php
        $statCards = [
            ['label' => 'Total Tenant', 'value' => $stats['total'],    'color' => 'text-blue-400',  'bg' => 'bg-blue-500/10'],
            ['label' => 'Aktif',        'value' => $stats['active'],   'color' => 'text-green-400', 'bg' => 'bg-green-500/10'],
            ['label' => 'Nonaktif',     'value' => $stats['inactive'], 'color' => 'text-red-400',   'bg' => 'bg-red-500/10'],
            ['label' => 'Trial',        'value' => $stats['trial'],    'color' => 'text-amber-400', 'bg' => 'bg-amber-500/10'],
        ];
        ?>
        <?php $__currentLoopData = $statCards; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 text-center">
            <p class="text-2xl font-bold <?php echo e($sc['color']); ?>"><?php echo e($sc['value']); ?></p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1"><?php echo e($sc['label']); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <form method="GET" action="<?php echo e(route('super-admin.tenants.index')); ?>" class="flex flex-wrap gap-3 mb-4">
        <input type="text" name="search" value="<?php echo e(request('search')); ?>" placeholder="Cari nama, slug, email..."
            class="flex-1 min-w-[200px] px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 text-sm text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-blue-500">
        <select name="status" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Status</option>
            <option value="active"   <?php if(request('status')==='active'): echo 'selected'; endif; ?>>Aktif</option>
            <option value="inactive" <?php if(request('status')==='inactive'): echo 'selected'; endif; ?>>Nonaktif</option>
            <option value="expired"  <?php if(request('status')==='expired'): echo 'selected'; endif; ?>>Expired</option>
        </select>
        <select name="plan" class="px-4 py-2 rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-slate-800 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Plan</option>
            <option value="trial"      <?php if(request('plan')==='trial'): echo 'selected'; endif; ?>>Trial</option>
            <option value="basic"      <?php if(request('plan')==='basic'): echo 'selected'; endif; ?>>Basic</option>
            <option value="pro"        <?php if(request('plan')==='pro'): echo 'selected'; endif; ?>>Pro</option>
            <option value="enterprise" <?php if(request('plan')==='enterprise'): echo 'selected'; endif; ?>>Enterprise</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">Filter</button>
        <?php if(request()->hasAny(['search','status','plan'])): ?>
        <a href="<?php echo e(route('super-admin.tenants.index')); ?>" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 hover:bg-gray-200 dark:hover:bg-white/20 text-gray-700 dark:text-slate-300 text-sm font-medium transition">Reset</a>
        <?php endif; ?>
    </form>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full">
            <thead>
                <tr class="border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Perusahaan</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Admin</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Plan</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden lg:table-cell">Expired</th>
                    <th class="px-4 sm:px-6 py-3.5 text-center text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Users</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                <?php $__empty_1 = true; $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                $expiryDate = $tenant->plan === 'trial' ? $tenant->trial_ends_at : $tenant->plan_expires_at;
                $isExpiringSoon = $expiryDate && $expiryDate->isFuture() && $expiryDate->diffInDays(now()) <= 7;
                ?>
                <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    <td class="px-4 sm:px-6 py-4">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($tenant->name); ?></p>
                        <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($tenant->slug); ?></p>
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-sm text-gray-500 dark:text-slate-400 hidden md:table-cell"><?php echo e($tenant->admins->first()?->email ?? '—'); ?></td>
                    <td class="px-4 sm:px-6 py-4">
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium
                            <?php echo e(match($tenant->plan) { 'trial'=>'bg-amber-500/20 text-amber-400', 'pro'=>'bg-purple-500/20 text-purple-400', 'enterprise'=>'bg-green-500/20 text-green-400', default=>'bg-blue-500/20 text-blue-400' }); ?>">
                            <?php echo e(ucfirst($tenant->plan)); ?>

                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4 hidden lg:table-cell">
                        <?php if($expiryDate): ?>
                        <span class="text-sm <?php echo e($isExpiringSoon ? 'text-red-400 font-semibold' : 'text-gray-500 dark:text-slate-400'); ?>">
                            <?php echo e($expiryDate->format('d M Y')); ?>

                            <?php if($isExpiringSoon): ?><span class="text-xs ml-1">(<?php echo e($expiryDate->diffForHumans()); ?>)</span><?php endif; ?>
                        </span>
                        <?php else: ?>
                        <span class="text-sm text-gray-400 dark:text-slate-500">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 sm:px-6 py-4 text-center hidden sm:table-cell">
                        <span class="text-sm font-semibold text-gray-900 dark:text-white"><?php echo e($tenant->users_count); ?></span>
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                            <?php echo e($tenant->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($tenant->is_active ? 'bg-green-500' : 'bg-red-500'); ?>"></span>
                            <?php echo e($tenant->is_active ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <a href="<?php echo e(route('super-admin.tenants.show', $tenant)); ?>"
                               class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            <form method="POST" action="<?php echo e(route('super-admin.tenants.toggle', $tenant)); ?>" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" title="<?php echo e($tenant->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>"
                                    class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-amber-400 hover:bg-amber-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="<?php echo e($tenant->is_active ? 'M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636' : 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z'); ?>"/>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('super-admin.tenants.destroy', $tenant)); ?>" class="inline"
                                  onsubmit="return confirm('Hapus tenant <?php echo e(addslashes($tenant->name)); ?> beserta semua datanya?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Hapus"
                                    class="p-2 rounded-lg text-gray-500 dark:text-slate-400 hover:text-red-400 hover:bg-red-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center">
                        <p class="text-sm text-gray-400 dark:text-slate-500">Tidak ada tenant ditemukan.</p>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <?php if($tenants->hasPages()): ?>
        <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
            <?php echo e($tenants->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/super-admin/tenants/index.blade.php ENDPATH**/ ?>