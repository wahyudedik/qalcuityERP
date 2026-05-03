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
     <?php $__env->slot('title', null, []); ?> Kelola Paket — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Kelola Paket Langganan <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <form method="POST" action="<?php echo e(route('super-admin.plans.seed')); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit"
                class="flex items-center gap-2 text-sm text-gray-500 hover:text-gray-900 px-3 py-2 rounded-xl hover:bg-gray-100 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                Sync Default
            </button>
        </form>
        <a href="<?php echo e(route('super-admin.plans.create')); ?>"
           class="flex items-center gap-2 text-sm bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-xl transition font-medium">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah
        </a>
     <?php $__env->endSlot(); ?>

    
    <div class="flex sm:hidden gap-2 mb-4">
        <form method="POST" action="<?php echo e(route('super-admin.plans.seed')); ?>" class="inline">
            <?php echo csrf_field(); ?>
            <button type="submit" class="text-xs px-3 py-2 border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Sync Default</button>
        </form>
        <a href="<?php echo e(route('super-admin.plans.create')); ?>" class="text-xs px-3 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Paket</a>
    </div>

    <?php if(session('success')): ?>
        <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 text-green-700 text-sm rounded-xl"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="mb-4 px-4 py-3 bg-red-50 border border-red-200 text-red-700 text-sm rounded-xl"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $plans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
        <div class="bg-white rounded-2xl border border-gray-200 p-6 flex flex-col <?php echo e(!$plan->is_active ? 'opacity-60' : ''); ?>">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h3 class="font-bold text-gray-900 text-lg"><?php echo e($plan->name); ?></h3>
                    <p class="text-xs text-gray-400 font-mono"><?php echo e($plan->slug); ?></p>
                </div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium
                    <?php echo e($plan->is_active ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500'); ?>">
                    <span class="w-1.5 h-1.5 rounded-full <?php echo e($plan->is_active ? 'bg-green-500' : 'bg-gray-400'); ?>"></span>
                    <?php echo e($plan->is_active ? 'Aktif' : 'Nonaktif'); ?>

                </span>
            </div>

            <div class="mb-4">
                <p class="text-2xl font-bold text-gray-900">
                    Rp <?php echo e(number_format($plan->price_monthly, 0, ',', '.')); ?>

                    <span class="text-sm font-normal text-gray-400">/bln</span>
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    Rp <?php echo e(number_format($plan->price_yearly, 0, ',', '.')); ?>/tahun
                </p>
            </div>

            <div class="space-y-2 text-sm text-gray-600 mb-5 flex-1">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-blue-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span><?php echo e($plan->max_users === -1 ? 'User tak terbatas' : 'Maks. ' . $plan->max_users . ' user'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v10a2 2 0 01-2 2h-2"/></svg>
                    <span><?php echo e($plan->max_ai_messages === -1 ? 'AI tak terbatas' : 'Maks. ' . $plan->max_ai_messages . ' pesan AI/bln'); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-amber-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <span>Trial <?php echo e($plan->trial_days); ?> hari</span>
                </div>
                <?php if($plan->features): ?>
                    <?php $__currentLoopData = array_slice($plan->features, 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feature): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 text-green-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        <span class="text-xs"><?php echo e($feature); ?></span>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php if(count($plan->features) > 5): ?>
                    <p class="text-xs text-gray-400 pl-6">+<?php echo e(count($plan->features) - 5); ?> fitur lainnya</p>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <div class="flex items-center gap-2 pt-4 border-t border-gray-200">
                <span class="text-xs text-gray-400"><?php echo e($plan->tenants_count ?? $plan->tenants()->count()); ?> tenant</span>
                <div class="flex-1"></div>
                <form method="POST" action="<?php echo e(route('super-admin.plans.toggle', $plan)); ?>">
                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                    <button type="submit"
                        class="text-xs font-medium px-3 py-1.5 rounded-lg transition
                        <?php echo e($plan->is_active
                            ? 'text-amber-600 hover:bg-amber-50'
                            : 'text-green-600 hover:bg-green-50'); ?>">
                        <?php echo e($plan->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>

                    </button>
                </form>
                <a href="<?php echo e(route('super-admin.plans.edit', $plan)); ?>"
                   class="text-xs text-blue-600 hover:text-blue-700 font-medium px-3 py-1.5 rounded-lg hover:bg-blue-50 transition">
                    Edit
                </a>
                <form method="POST" action="<?php echo e(route('super-admin.plans.destroy', $plan)); ?>"
                      onsubmit="return confirm('Hapus paket <?php echo e($plan->name); ?>?')">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit"
                        class="text-xs text-red-600 hover:text-red-700 font-medium px-3 py-1.5 rounded-lg hover:bg-red-50 transition">
                        Hapus
                    </button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
        <div class="col-span-full bg-white rounded-2xl border border-gray-200 p-12 text-center">
            <p class="text-gray-400 text-sm mb-4">Belum ada paket langganan.</p>
            <form method="POST" action="<?php echo e(route('super-admin.plans.seed')); ?>" class="inline">
                <?php echo csrf_field(); ?>
                <button type="submit" class="text-sm bg-blue-600 text-white px-4 py-2 rounded-xl hover:bg-blue-700 transition">
                    Buat Paket Default
                </button>
            </form>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\plans\index.blade.php ENDPATH**/ ?>