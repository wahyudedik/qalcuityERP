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
     <?php $__env->slot('title', null, []); ?> Izin Akses — <?php echo e($user->name); ?> <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Izin Akses: <?php echo e($user->name); ?> <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('tenant.users.index')); ?>"
           class="flex items-center gap-1.5 text-sm text-slate-400 hover:text-white transition px-3 py-1.5 rounded-lg hover:bg-white/10">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
     <?php $__env->endSlot(); ?>

    <?php
        $categories   = \App\Services\PermissionService::moduleCategories();
        $actionLabels = ['view' => 'Lihat', 'create' => 'Tambah', 'edit' => 'Edit', 'delete' => 'Hapus'];
    ?>

    
    <div class="mb-5 flex items-center gap-4 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-2xl px-5 py-4">
        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white font-bold text-lg shrink-0">
            <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

        </div>
        <div class="flex-1 min-w-0">
            <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($user->name); ?></p>
            <p class="text-xs text-slate-400"><?php echo e($user->email); ?> &middot; Role: <span class="text-blue-400 font-medium"><?php echo e($user->roleLabel()); ?></span></p>
        </div>
        <div class="text-xs text-slate-500 text-right hidden sm:block">
            <p>Centang = izinkan &nbsp;|&nbsp; Kosong = tolak</p>
            <p class="text-amber-400 mt-0.5">Override menggantikan default role</p>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 flex items-center gap-3 bg-green-500/10 border border-green-500/20 text-green-400 text-sm px-4 py-3 rounded-xl">
        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    <form method="POST" action="<?php echo e(route('tenant.users.permissions.save', $user)); ?>">
        <?php echo csrf_field(); ?>

        <div class="space-y-4">
            <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $catLabel => $catModules): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    // Only show categories that have at least one module in MODULES
                    $visibleModules = array_filter($catModules, fn($m) => isset($modules[$m]));
                ?>
                <?php if(count($visibleModules) === 0): ?> <?php continue; ?> <?php endif; ?>

                <div class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-2xl overflow-hidden">
                    
                    <div class="px-5 py-3 border-b border-gray-100 dark:border-white/5 bg-gray-50 dark:bg-white/[0.03]">
                        <h3 class="text-xs font-bold uppercase tracking-widest text-slate-400"><?php echo e($catLabel); ?></h3>
                    </div>

                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-white/5">
                                    <th class="text-left px-5 py-2.5 text-xs font-semibold text-slate-500 w-52">Modul</th>
                                    <?php $__currentLoopData = $actionLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $act => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <th class="text-center px-4 py-2.5 text-xs font-semibold text-slate-500 w-20"><?php echo e($lbl); ?></th>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-50 dark:divide-white/[0.03]">
                                <?php $__currentLoopData = $visibleModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $module): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php $actions = $modules[$module]; ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition">
                                        <td class="px-5 py-3 font-medium text-gray-800 dark:text-slate-200 text-sm">
                                            <?php echo e(\App\Services\PermissionService::moduleLabel($module)); ?>

                                            <?php
                                                // Show if this row has any override vs role default
                                                $hasOverride = false;
                                                foreach ($actions as $act) {
                                                    $roleVal = is_array($roleDefault) ? in_array($act, $roleDefault[$module] ?? []) : ($roleDefault === '*');
                                                    $curVal  = $userPerms[$module][$act] ?? false;
                                                    if ($curVal !== $roleVal) { $hasOverride = true; break; }
                                                }
                                            ?>
                                            <?php if($hasOverride): ?>
                                                <span class="ml-1.5 text-[10px] font-semibold px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-400">override</span>
                                            <?php endif; ?>
                                        </td>
                                        <?php $__currentLoopData = $actionLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $action => $lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <td class="text-center px-4 py-3">
                                                <?php if(in_array($action, $actions)): ?>
                                                    <?php
                                                        $key     = "{$module}.{$action}";
                                                        $current = $userPerms[$module][$action] ?? false;
                                                        $isDefault = is_array($roleDefault)
                                                            ? in_array($action, $roleDefault[$module] ?? [])
                                                            : ($roleDefault === '*');
                                                    ?>
                                                    <input type="checkbox"
                                                           name="perms[<?php echo e($key); ?>]"
                                                           value="1"
                                                           <?php echo e($current ? 'checked' : ''); ?>

                                                           title="<?php echo e($isDefault ? 'Default role: izin' : 'Default role: tolak'); ?>"
                                                           class="w-4 h-4 rounded border-gray-300 dark:border-white/20 text-blue-500 bg-white dark:bg-white/10 focus:ring-blue-500 cursor-pointer accent-blue-500">
                                                <?php else: ?>
                                                    <span class="text-slate-700 text-xs">—</span>
                                                <?php endif; ?>
                                            </td>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>

        
        <div class="flex items-center gap-3 mt-6 pb-6">
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition shadow-sm">
                Simpan Izin
            </button>
            <button type="button"
                    onclick="if(confirm('Reset semua izin ke default role <?php echo e($user->roleLabel()); ?>?')) document.getElementById('reset-form').submit()"
                    class="px-6 py-2.5 bg-white dark:bg-white/5 hover:bg-gray-100 dark:hover:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 text-sm font-semibold rounded-xl transition">
                Reset ke Default Role
            </button>
        </div>
    </form>

    <form id="reset-form" method="POST" action="<?php echo e(route('tenant.users.permissions.reset', $user)); ?>" class="hidden">
        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
    </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\tenant\users\permissions.blade.php ENDPATH**/ ?>