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
     <?php $__env->slot('header', null, []); ?> Claim Asuransi <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim Asuransi'],
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
        ['label' => 'Claim Asuransi'],
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
            
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Pending</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">
                        <?php echo e($statistics['pending'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Diproses</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">
                        <?php echo e($statistics['processing'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Disetujui</p>
                    <p class="text-2xl font-bold text-green-600 mt-1">
                        <?php echo e($statistics['approved'] ?? 0); ?></p>
                </div>
                <div class="bg-white rounded-2xl p-4 border border-gray-200">
                    <p class="text-xs text-gray-500">Ditolak</p>
                    <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($statistics['rejected'] ?? 0); ?>

                    </p>
                </div>
            </div>

            <div class="flex justify-end mb-4">
                <a href="<?php echo e(route('healthcare.insurance-claims.create')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-xl hover:bg-blue-700 text-sm font-medium">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Claim Baru
                </a>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                
                <div class="hidden md:block overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">No. Claim</th>
                                <th class="px-4 py-3 text-left">Pasien</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Asuransi</th>
                                <th class="px-4 py-3 text-right">Jumlah Claim</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-sm font-bold text-blue-600"><?php echo e($claim->claim_number); ?></span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">
                                            <?php echo e($claim->patient->name ?? 'N/A'); ?></p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                        <?php echo e($claim->insurance_provider ?? 'N/A'); ?></td>
                                    <td class="px-4 py-3 text-right">
                                        <span class="font-semibold text-gray-900">Rp
                                            <?php echo e(number_format($claim->claim_amount, 0, ',', '.')); ?></span>
                                        <?php if($claim->approved_amount): ?>
                                            <div class="text-xs text-green-600">Disetujui: Rp
                                                <?php echo e(number_format($claim->approved_amount, 0, ',', '.')); ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <?php echo e($claim->submitted_at ? $claim->submitted_at->format('d M Y') : '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg <?php echo e($claim->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($claim->status === 'processing' ? 'bg-blue-100 text-blue-700' : ($claim->status === 'approved' ? 'bg-green-100 text-green-700' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')))); ?>">
                                            <?php echo e(ucfirst($claim->status)); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="<?php echo e(route('healthcare.insurance-claims.show', $claim)); ?>"
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
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
                                            <a href="<?php echo e(route('healthcare.insurance-claims.edit', $claim)); ?>"
                                                class="p-1.5 text-amber-600 hover:bg-amber-50 rounded-lg"
                                                title="Edit">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                                    </path>
                                                </svg>
                                            </a>
                                            <button onclick="deleteClaim(<?php echo e($claim->id); ?>)"
                                                class="p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                                title="Hapus">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                                    </path>
                                                </svg>
                                            </button>
                                            <form id="delete-claim-<?php echo e($claim->id); ?>"
                                                action="<?php echo e(route('healthcare.insurance-claims.destroy', $claim)); ?>"
                                                method="POST" class="hidden">
                                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">
                                        Tidak ada data claim asuransi</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                
                <div class="md:hidden divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-4 hover:bg-gray-50 transition-colors">
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="flex-1 min-w-0">
                                    <p class="font-mono text-sm font-bold text-blue-600">
                                        <?php echo e($claim->claim_number); ?></p>
                                    <p class="font-semibold text-gray-900 truncate mt-0.5">
                                        <?php echo e($claim->patient->name ?? 'N/A'); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($claim->insurance_provider ?? 'N/A'); ?></p>
                                </div>
                                <div class="text-right">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg <?php echo e($claim->status === 'pending' ? 'bg-amber-100 text-amber-700' : ($claim->status === 'processing' ? 'bg-blue-100 text-blue-700' : ($claim->status === 'approved' ? 'bg-green-100 text-green-700' : ($claim->status === 'rejected' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700')))); ?>">
                                        <?php echo e(ucfirst($claim->status)); ?>

                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-2 text-xs mb-3">
                                <div>
                                    <p class="text-gray-500">Jumlah Claim</p>
                                    <p class="font-bold text-gray-900">Rp
                                        <?php echo e(number_format($claim->claim_amount, 0, ',', '.')); ?></p>
                                    <?php if($claim->approved_amount): ?>
                                        <p class="text-green-600 text-[10px]">Disetujui: Rp
                                            <?php echo e(number_format($claim->approved_amount, 0, ',', '.')); ?></p>
                                    <?php endif; ?>
                                </div>
                                <div>
                                    <p class="text-gray-500">Tanggal</p>
                                    <p class="font-medium text-gray-900">
                                        <?php echo e($claim->submitted_at ? $claim->submitted_at->format('d M Y') : '-'); ?></p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2 pt-2 border-t border-gray-100">
                                <a href="<?php echo e(route('healthcare.insurance-claims.show', $claim)); ?>"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-lg text-center hover:bg-blue-100">Detail</a>
                                <a href="<?php echo e(route('healthcare.insurance-claims.edit', $claim)); ?>"
                                    class="flex-1 px-3 py-2 text-xs font-medium text-amber-600 bg-amber-50 rounded-lg text-center hover:bg-amber-100">Edit</a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="p-8 text-center text-gray-500">
                            <svg class="w-16 h-16 mx-auto mb-3 text-gray-300" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            <p>Tidak ada data claim asuransi</p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if($claims->hasPages()): ?>
                    <div class="px-6 py-4 border-t border-gray-200">
                        <?php echo e($claims->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function deleteClaim(id) {
                if (confirm('Are you sure you want to delete this claim?')) {
                    document.getElementById(`delete-claim-${id}`).submit();
                }
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\insurance-claims\index.blade.php ENDPATH**/ ?>