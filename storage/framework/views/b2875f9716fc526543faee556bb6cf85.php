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
     <?php $__env->slot('title', null, []); ?> <?php echo e($tenant->name); ?> — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Detail Tenant <?php $__env->endSlot(); ?>
     <?php $__env->slot('topbarActions', null, []); ?> 
        <a href="<?php echo e(route('super-admin.tenants.index')); ?>"
           class="flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-white px-3 py-2 rounded-xl hover:bg-[#f8f8f8] dark:bg-white/10 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
     <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 bg-green-500/20 border border-green-500/30 text-green-400 text-sm rounded-xl"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    <div class="max-w-3xl space-y-4">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex items-start justify-between mb-5 flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($tenant->name); ?></h2>
                    <p class="text-sm text-gray-400 dark:text-slate-500"><?php echo e($tenant->slug); ?></p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <?php
                        $planColors = [
                            'trial'      => 'bg-amber-500/20 text-amber-400',
                            'basic'      => 'bg-blue-500/20 text-blue-400',
                            'pro'        => 'bg-purple-500/20 text-purple-400',
                            'enterprise' => 'bg-indigo-500/20 text-indigo-400',
                        ];
                    ?>
                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium <?php echo e($planColors[$tenant->plan] ?? 'bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400'); ?>">
                        <?php echo e(ucfirst($tenant->plan)); ?>

                    </span>
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                        <?php echo e($tenant->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                        <span class="w-1.5 h-1.5 rounded-full <?php echo e($tenant->is_active ? 'bg-green-500' : 'bg-red-500'); ?>"></span>
                        <?php echo e($tenant->is_active ? 'Aktif' : 'Nonaktif'); ?>

                    </span>
                    <?php if($tenant->isTrialExpired()): ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                            Trial Expired
                        </span>
                    <?php elseif($tenant->isPlanExpired()): ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium bg-red-500/20 text-red-400">
                            Plan Expired
                        </span>
                    <?php endif; ?>
                </div>
            </div>

            <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-4 text-sm">
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Email</dt>
                    <dd class="text-white"><?php echo e($tenant->email); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Telepon</dt>
                    <dd class="text-white"><?php echo e($tenant->phone ?? '—'); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Trial Berakhir</dt>
                    <dd class="<?php echo e($tenant->isTrialExpired() ? 'text-red-400 font-medium' : 'text-white'); ?>">
                        <?php echo e($tenant->trial_ends_at?->format('d M Y') ?? '—'); ?>

                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Langganan Berakhir</dt>
                    <dd class="<?php echo e($tenant->isPlanExpired() ? 'text-red-400 font-medium' : 'text-white'); ?>">
                        <?php echo e($tenant->plan_expires_at?->format('d M Y') ?? '—'); ?>

                    </dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Paket Aktif</dt>
                    <dd class="text-white"><?php echo e($tenant->subscriptionPlan?->name ?? ucfirst($tenant->plan)); ?></dd>
                </div>
                <div>
                    <dt class="text-xs font-medium text-gray-400 dark:text-slate-500 uppercase tracking-wide mb-0.5">Terdaftar</dt>
                    <dd class="text-white"><?php echo e($tenant->created_at->format('d M Y, H:i')); ?></dd>
                </div>
            </dl>

            <div class="flex flex-wrap items-center gap-2 mt-5 pt-5 border-t border-gray-200 dark:border-white/10">
                <form method="POST" action="<?php echo e(route('super-admin.tenants.toggle', $tenant)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button type="submit"
                        class="text-sm px-4 py-2 rounded-xl border transition font-medium
                            <?php echo e($tenant->is_active
                                ? 'border-red-500/30 text-red-400 hover:bg-red-500/10'
                                : 'border-green-500/30 text-green-400 hover:bg-green-500/10'); ?>">
                        <?php echo e($tenant->is_active ? 'Nonaktifkan Tenant' : 'Aktifkan Tenant'); ?>

                    </button>
                </form>
                <form method="POST" action="<?php echo e(route('super-admin.tenants.destroy', $tenant)); ?>"
                      onsubmit="return confirm('Hapus tenant <?php echo e($tenant->name); ?> beserta semua datanya?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                        class="text-sm px-4 py-2 rounded-xl border border-red-500/30 text-red-400 hover:bg-red-500/10 transition font-medium">
                        Hapus Tenant
                    </button>
                </form>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white">Atur Paket Langganan</p>
                <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Ubah paket, pilih definisi paket, dan atur tanggal kedaluwarsa</p>
            </div>
            <form method="POST" action="<?php echo e(route('super-admin.tenants.update-plan', $tenant)); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Tipe Plan</label>
                        <select name="plan" required
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-white">
                            <?php $__currentLoopData = ['trial', 'basic', 'pro', 'enterprise']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p); ?>" <?php echo e($tenant->plan === $p ? 'selected' : ''); ?>>
                                    <?php echo e(ucfirst($p)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Definisi Paket</label>
                        <select name="subscription_plan_id"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-white">
                            <option value="">— Tidak terikat definisi —</option>
                            <?php $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($p->id); ?>" <?php echo e($tenant->subscription_plan_id == $p->id ? 'selected' : ''); ?>>
                                    <?php echo e($p->name); ?> — Rp <?php echo e(number_format($p->price_monthly, 0, ',', '.')); ?>/bln
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Trial Berakhir</label>
                        <input type="date" name="trial_ends_at"
                            value="<?php echo e(old('trial_ends_at', $tenant->trial_ends_at?->format('Y-m-d'))); ?>"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-white">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Hanya berlaku jika plan = Trial</p>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide mb-1.5">Langganan Berakhir</label>
                        <input type="date" name="plan_expires_at"
                            value="<?php echo e(old('plan_expires_at', $tenant->plan_expires_at?->format('Y-m-d'))); ?>"
                            class="w-full px-3.5 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-400 transition bg-gray-50 dark:bg-[#0f172a] text-white">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">Berlaku untuk plan berbayar</p>
                    </div>
                </div>

                
                <div class="flex flex-wrap gap-2 pt-1">
                    <p class="text-xs text-gray-400 dark:text-slate-500 w-full">Perpanjang cepat (dari hari ini):</p>
                    <?php $__currentLoopData = [
                        ['label' => '+1 Bulan', 'days' => 30],
                        ['label' => '+3 Bulan', 'days' => 90],
                        ['label' => '+6 Bulan', 'days' => 180],
                        ['label' => '+1 Tahun', 'days' => 365],
                    ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="button"
                        onclick="setExpiry(<?php echo e($opt['days']); ?>)"
                        class="text-xs px-3 py-1.5 rounded-lg border border-gray-200 dark:border-white/10 text-gray-500 dark:text-slate-400 hover:bg-gray-50 dark:hover:bg-white/5 hover:border-white/20 transition">
                        <?php echo e($opt['label']); ?>

                    </button>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                <div class="flex justify-end pt-2 border-t border-gray-200 dark:border-white/10">
                    <button type="submit"
                        class="text-sm bg-blue-600 hover:bg-blue-700 text-white font-semibold px-5 py-2.5 rounded-xl transition">
                        Simpan Paket
                    </button>
                </div>
            </form>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <p class="font-semibold text-gray-900 dark:text-white">Pengguna</p>
                <span class="text-xs bg-[#f8f8f8] dark:bg-white/10 text-gray-500 dark:text-slate-400 font-medium px-2.5 py-1 rounded-lg"><?php echo e($tenant->users->count()); ?></span>
            </div>
            <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Nama</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $tenant->users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                        <td class="px-6 py-3.5">
                            <div class="flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-xs font-bold shrink-0">
                                    <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                                </div>
                                <span class="text-sm font-medium text-white"><?php echo e($user->name); ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-3.5 text-sm text-gray-500 dark:text-slate-400 hidden sm:table-cell"><?php echo e($user->email); ?></td>
                        <td class="px-6 py-3.5">
                            <span class="text-xs font-medium text-gray-500 dark:text-slate-400 capitalize"><?php echo e($user->role); ?></span>
                        </td>
                        <td class="px-6 py-3.5">
                            <span class="inline-flex items-center gap-1.5 text-xs font-medium <?php echo e($user->is_active ? 'text-green-400' : 'text-red-400'); ?>">
                                <span class="w-1.5 h-1.5 rounded-full <?php echo e($user->is_active ? 'bg-green-500' : 'bg-red-500'); ?>"></span>
                                <?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?>

                            </span>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            </div>
        </div>
    </div>

    <script>
    function setExpiry(days) {
        const d = new Date();
        d.setDate(d.getDate() + days);
        const val = d.toISOString().split('T')[0];
        document.querySelector('input[name="plan_expires_at"]').value = val;
    }
    </script>
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



<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\tenants\show.blade.php ENDPATH**/ ?>