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
     <?php $__env->slot('header', null, []); ?> Log Operasi <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalOperations = \App\Models\OperationLog::where('tenant_id', $tid)->count();
            $completedOperations = \App\Models\OperationLog::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
            $avgDuration = \App\Models\OperationLog::where('tenant_id', $tid)
                ->whereNotNull('duration_minutes')
                ->avg('duration_minutes');
            $complicationRate =
                $totalOperations > 0
                    ? (\App\Models\OperationLog::where('tenant_id', $tid)->where('has_complications', true)->count() /
                            $totalOperations) *
                        100
                    : 0;
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Operasi</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($totalOperations)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($completedOperations); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Durasi Rata-rata</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">
                <?php echo e($avgDuration ? round($avgDuration) . ' min' : '-'); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Komplikasi</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e(number_format($complicationRate, 1)); ?>%
            </p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Prosedur</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Durasi</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Komplikasi</th>
                        <th class="px-4 py-3 text-center">Outcome</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $operations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    <?php echo e($operation->patient ? $operation->patient->full_name : '-'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($operation->patient ? $operation->patient->medical_record_number : '-'); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900"><?php echo e($operation->procedure_name ?? '-'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($operation->surgery_type ?? '-'); ?></p>
                            </td>
                            <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                <?php echo e($operation->surgeon ? $operation->surgeon->name : '-'); ?></td>
                            <td class="px-4 py-3 hidden sm:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($operation->operation_date ? \Carbon\Carbon::parse($operation->operation_date)->format('d M Y') : '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($operation->operation_date ? \Carbon\Carbon::parse($operation->operation_date)->format('H:i') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <span
                                    class="font-medium text-gray-900"><?php echo e($operation->duration_minutes ?? '-'); ?>

                                    min</span>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <?php if($operation->has_complications): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Yes</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">No</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <?php if($operation->outcome === 'successful'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Successful</span>
                                <?php elseif($operation->outcome === 'partial'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Partial</span>
                                <?php elseif($operation->outcome === 'failed'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Failed</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="<?php echo e(route('healthcare.surgery.operations.show', $operation)); ?>"
                                    class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                    title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                        </path>
                                    </svg>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada log operasi</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if(isset($operations) && $operations->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($operations->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\surgery\operations.blade.php ENDPATH**/ ?>