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
     <?php $__env->slot('title', null, []); ?> Kelola Pengguna — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> Kelola Pengguna <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('tenant.users.create')); ?>"
           class="flex items-center gap-2 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold px-4 py-2 rounded-xl transition shadow-sm shadow-blue-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
     <?php $__env->endSlot(); ?>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
        <table class="min-w-full w-full">
            <thead>
                <tr class="border-b border-gray-200 bg-gray-50">
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Pengguna</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Role</th>
                    <th class="px-4 sm:px-6 py-3.5 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider hidden sm:table-cell">Status</th>
                    <th class="px-4 sm:px-6 py-3.5 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php $__empty_1 = true; $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 sm:px-6 py-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-blue-400 to-indigo-500 flex items-center justify-center text-white text-sm font-bold shrink-0">
                                <?php echo e(strtoupper(substr($user->name, 0, 1))); ?>

                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-white"><?php echo e($user->name); ?></p>
                                <p class="text-xs text-gray-500 truncate"><?php echo e($user->email); ?></p>
                                
                                <div class="flex items-center gap-1.5 mt-1 sm:hidden">
                                    <?php
                                    $roleStyle = ['admin' => 'bg-purple-500/20 text-purple-400', 'manager' => 'bg-blue-500/20 text-blue-400', 'staff' => 'bg-gray-100 text-gray-600'];
                                    $roleLabel = ['admin' => 'Admin', 'manager' => 'Manager', 'staff' => 'Staff'];
                                    ?>
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-md text-xs font-medium <?php echo e($roleStyle[$user->role] ?? ''); ?>"><?php echo e($roleLabel[$user->role] ?? $user->role); ?></span>
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md text-xs font-medium <?php echo e($user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                                        <?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?>

                                    </span>
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                        <?php
                        $roleStyle = ['admin' => 'bg-purple-500/20 text-purple-400', 'manager' => 'bg-blue-500/20 text-blue-400', 'staff' => 'bg-gray-100 text-gray-600'];
                        $roleLabel = ['admin' => 'Admin', 'manager' => 'Manager', 'staff' => 'Staff'];
                        ?>
                        <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-medium <?php echo e($roleStyle[$user->role] ?? 'bg-[#f8f8f8] text-slate-300'); ?>">
                            <?php echo e($roleLabel[$user->role] ?? $user->role); ?>

                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4 hidden sm:table-cell">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg text-xs font-medium <?php echo e($user->is_active ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400'); ?>">
                            <span class="w-1.5 h-1.5 rounded-full <?php echo e($user->is_active ? 'bg-green-400' : 'bg-red-400'); ?>"></span>
                            <?php echo e($user->is_active ? 'Aktif' : 'Nonaktif'); ?>

                        </span>
                    </td>
                    <td class="px-4 sm:px-6 py-4">
                        <div class="flex items-center justify-end gap-1">
                            <?php if (! ($user->isAdmin())): ?>
                            <a href="<?php echo e(route('tenant.users.permissions', $user)); ?>"
                               class="p-2 rounded-lg text-gray-500 hover:text-green-400 hover:bg-green-500/10 transition" title="Izin Akses">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                            </a>
                            <a href="<?php echo e(route('tenant.users.edit', $user)); ?>"
                               class="p-2 rounded-lg text-gray-500 hover:text-blue-400 hover:bg-blue-500/10 transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </a>
                            <form method="POST" action="<?php echo e(route('tenant.users.toggle', $user)); ?>" class="inline">
                                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                <button type="submit" title="<?php echo e($user->is_active ? 'Nonaktifkan' : 'Aktifkan'); ?>"
                                    class="p-2 rounded-lg text-gray-500 hover:text-amber-400 hover:bg-amber-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <?php if($user->is_active): ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        <?php else: ?>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        <?php endif; ?>
                                    </svg>
                                </button>
                            </form>
                            <form method="POST" action="<?php echo e(route('tenant.users.destroy', $user)); ?>" class="inline"
                                  onsubmit="return confirm('Hapus pengguna <?php echo e($user->name); ?>?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" title="Hapus"
                                    class="p-2 rounded-lg text-gray-500 hover:text-red-400 hover:bg-red-500/10 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                            <?php else: ?>
                            <span class="text-xs text-slate-600 px-2">Admin Utama</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <tr>
                    <td colspan="4" class="px-6 py-12 text-center">
                        <div class="flex flex-col items-center text-gray-400">
                            <svg class="w-10 h-10 mb-2 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <p class="text-sm">Belum ada pengguna</p>
                        </div>
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
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


<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\tenant\users\index.blade.php ENDPATH**/ ?>