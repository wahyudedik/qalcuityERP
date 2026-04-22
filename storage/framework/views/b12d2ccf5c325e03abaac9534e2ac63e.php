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
     <?php $__env->slot('header', null, []); ?> Claim BPJS <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <?php if(session('success')): ?>
                <div
                    class="mb-4 bg-green-100 dark:bg-green-900/30 border border-green-200 dark:border-green-800 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl">
                    <?php echo e(session('success')); ?>

                </div>
            <?php endif; ?>

            
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <?php if (isset($component)) { $__componentOriginal88e528801c974d273571b7e2f6d38871 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal88e528801c974d273571b7e2f6d38871 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.stats-card','data' => ['label' => 'Total Claim','value' => $statistics['total'],'color' => 'blue','icon' => 'clipboard']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Total Claim','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['total']),'color' => 'blue','icon' => 'clipboard']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $attributes = $__attributesOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__attributesOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $component = $__componentOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__componentOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal88e528801c974d273571b7e2f6d38871 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal88e528801c974d273571b7e2f6d38871 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.stats-card','data' => ['label' => 'Pending','value' => $statistics['pending'],'color' => 'amber','icon' => 'clock']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Pending','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['pending']),'color' => 'amber','icon' => 'clock']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $attributes = $__attributesOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__attributesOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $component = $__componentOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__componentOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal88e528801c974d273571b7e2f6d38871 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal88e528801c974d273571b7e2f6d38871 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.stats-card','data' => ['label' => 'Disetujui','value' => $statistics['approved'],'color' => 'green','icon' => 'check']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Disetujui','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['approved']),'color' => 'green','icon' => 'check']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $attributes = $__attributesOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__attributesOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $component = $__componentOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__componentOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
                <?php if (isset($component)) { $__componentOriginal88e528801c974d273571b7e2f6d38871 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal88e528801c974d273571b7e2f6d38871 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.healthcare.stats-card','data' => ['label' => 'Ditolak','value' => $statistics['rejected'],'color' => 'red','icon' => 'x']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('healthcare.stats-card'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['label' => 'Ditolak','value' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute($statistics['rejected']),'color' => 'red','icon' => 'x']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $attributes = $__attributesOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__attributesOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal88e528801c974d273571b7e2f6d38871)): ?>
<?php $component = $__componentOriginal88e528801c974d273571b7e2f6d38871; ?>
<?php unset($__componentOriginal88e528801c974d273571b7e2f6d38871); ?>
<?php endif; ?>
            </div>

            <div class="flex justify-end mb-4">
                <a href="<?php echo e(route('healthcare.bpjs-claims.create')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Claim Baru
                </a>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">No. Claim</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">No. BPJS</th>
                                <th class="px-4 py-3 text-right">Jumlah Claim</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400"><?php echo e($claim->claim_number); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($claim->patient->name ?? 'N/A'); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 dark:text-slate-300 hidden lg:table-cell">
                                        <?php echo e($claim->bpjs_number); ?></td>
                                    <td class="px-4 py-3 text-right font-semibold text-gray-900 dark:text-white">Rp
                                        <?php echo e(number_format($claim->claim_amount, 0, ',', '.')); ?></td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <?php echo e($claim->submission_date ? $claim->submission_date->format('d M Y') : '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg <?php echo e($claim->status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400')); ?>">
                                            <?php echo e(ucfirst($claim->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?php echo e(route('healthcare.bpjs-claims.show', $claim)); ?>"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 dark:text-blue-400 dark:hover:bg-blue-900/30 rounded-lg"
                                                title="Detail">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </a>
                                            <a href="<?php echo e(route('healthcare.bpjs-claims.edit', $claim)); ?>"
                                                class="p-1.5 text-amber-600 hover:bg-amber-50 dark:text-amber-400 dark:hover:bg-amber-900/30 rounded-lg"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                            <form action="<?php echo e(route('healthcare.bpjs-claims.destroy', $claim)); ?>"
                                                method="POST" class="inline"
                                                onsubmit="return confirm('Yakin ingin menghapus?')">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                                <button type="submit"
                                                    class="p-1.5 text-red-600 hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30 rounded-lg"
                                                    title="Hapus">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                        viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            stroke-width="1.5"
                                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                        </path>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500 dark:text-slate-400">
                                        Tidak ada data claim BPJS</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="md:hidden divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 hover:bg-gray-50 dark:hover:bg-white/5 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400">
                                        <?php echo e($claim->claim_number); ?></p>
                                    <p class="font-semibold text-gray-900 dark:text-white truncate mt-0.5">
                                        <?php echo e($claim->patient->name ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400">BPJS:
                                        <?php echo e($claim->bpjs_number); ?></p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg <?php echo e($claim->status === 'approved' ? 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400' : 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400')); ?>">
                                        <?php echo e(ucfirst($claim->status)); ?>

                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Jumlah Claim</p>
                                    <p class="font-bold text-gray-900 dark:text-white">Rp
                                        <?php echo e(number_format($claim->claim_amount, 0, ',', '.')); ?></p>
                                </div>
                                <div>
                                    <p class="text-gray-500 dark:text-slate-400">Tanggal</p>
                                    <p class="font-medium text-gray-900 dark:text-white">
                                        <?php echo e($claim->submission_date ? $claim->submission_date->format('d M Y') : '-'); ?>

                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/5">
                                <a href="<?php echo e(route('healthcare.bpjs-claims.show', $claim)); ?>"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-900/20 rounded-lg text-center hover:bg-blue-100 dark:hover:bg-blue-900/30">Detail</a>
                                <a href="<?php echo e(route('healthcare.bpjs-claims.edit', $claim)); ?>"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-900/20 rounded-lg text-center hover:bg-amber-100 dark:hover:bg-amber-900/30">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center text-gray-500 dark:text-slate-400">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300 dark:text-gray-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p>Tidak ada data claim BPJS</p>
                        </div>
                    <?php endif; ?>
                </div>

                
                <?php if($claims->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-200 dark:border-white/10">
                        <?php echo e($claims->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\bpjs-claims\index.blade.php ENDPATH**/ ?>