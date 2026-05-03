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
     <?php $__env->slot('header', null, []); ?> | <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('printing.show', $job)); ?>"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Kembali ke Job
                </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Operasi Finishing</h2>

                <?php if($job->finishingOperations->count() === 0): ?>
                    <div class="text-center py-8">
                        <svg class="w-12 h-12 mx-auto text-gray-300 mb-3" fill="none"
                            stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                        <p class="text-gray-500 text-sm">Belum ada operasi finishing untuk job ini.
                        </p>
                    </div>
                <?php else: ?>
                    <div class="space-y-4">
                        <?php $__currentLoopData = $job->finishingOperations->sortBy('sequence_order'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $operation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="border border-gray-200 rounded-xl p-4">
                                <div class="flex items-center justify-between mb-3">
                                    <div class="flex items-center gap-3">
                                        <span
                                            class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 text-sm font-semibold">
                                            <?php echo e($operation->sequence_order); ?>

                                        </span>
                                        <div>
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $operation->operation_type))); ?>

                                            </h3>
                                            <p class="text-xs text-gray-500">
                                                Operator: <?php echo e($operation->operator?->name ?? 'Belum ditugaskan'); ?>

                                            </p>
                                        </div>
                                    </div>
                                    <?php
                                        $opStatusColors = [
                                            'pending' => 'gray',
                                            'in_progress' => 'blue',
                                            'completed' => 'green',
                                            'failed' => 'red',
                                        ];
                                        $opColor = $opStatusColors[$operation->status] ?? 'gray';
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($opColor); ?>-100 text-<?php echo e($opColor); ?>-700 $opColor }}-500/20 $opColor }}-400">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $operation->status))); ?>

                                    </span>
                                </div>

                                
                                <div class="mb-2">
                                    <div class="flex justify-between text-xs mb-1">
                                        <span class="text-gray-500">Progress</span>
                                        <span class="text-gray-900">
                                            <?php echo e(number_format($operation->completed_quantity ?? 0)); ?> /
                                            <?php echo e(number_format($operation->target_quantity ?? 0)); ?>

                                        </span>
                                    </div>
                                    <div class="w-full bg-gray-200 rounded-full h-2">
                                        <div class="bg-indigo-600 h-2 rounded-full transition-all"
                                            style="width: <?php echo e($operation->completion_percentage ?? 0); ?>%"></div>
                                    </div>
                                </div>

                                
                                <?php if($operation->waste_quantity > 0): ?>
                                    <p class="text-xs text-orange-600">
                                        Waste: <?php echo e(number_format($operation->waste_quantity)); ?> lembar
                                    </p>
                                <?php endif; ?>

                                
                                <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                    <?php if($operation->started_at): ?>
                                        <span>Mulai: <?php echo e($operation->started_at->format('d M H:i')); ?></span>
                                    <?php endif; ?>
                                    <?php if($operation->completed_at): ?>
                                        <span>Selesai: <?php echo e($operation->completed_at->format('d M H:i')); ?></span>
                                    <?php endif; ?>
                                </div>

                                
                                <?php if($operation->quality_notes): ?>
                                    <div
                                        class="mt-2 p-2 bg-yellow-50 rounded text-xs text-yellow-800">
                                        <?php echo e($operation->quality_notes); ?>

                                    </div>
                                <?php endif; ?>

                                
                                <?php if($operation->issues): ?>
                                    <div
                                        class="mt-2 p-2 bg-red-50 rounded text-xs text-red-800">
                                        <?php echo e($operation->issues); ?>

                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Info Job</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-xs text-gray-500">Job Number</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->job_number); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Nama Job</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->job_name); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <?php
                            $statusColors = [
                                'queued' => 'gray',
                                'prepress' => 'blue',
                                'platemaking' => 'indigo',
                                'on_press' => 'purple',
                                'finishing' => 'orange',
                                'quality_check' => 'yellow',
                                'completed' => 'green',
                                'cancelled' => 'red',
                            ];
                            $color = $statusColors[$job->status] ?? 'gray';
                        ?>
                        <span
                            class="px-2 py-1 text-xs rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400">
                            <?php echo e(ucfirst(str_replace('_', ' ', $job->status))); ?>

                        </span>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Quantity</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e(number_format($job->quantity)); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Finishing Type</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->finishing_type ?? '-'); ?>

                        </p>
                    </div>
                </div>
            </div>

            
            <?php if($job->status === 'finishing'): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Aksi</h2>
                    <form action="<?php echo e(route('printing.status', $job)); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="status" value="quality_check">
                        <button type="submit"
                            class="w-full px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition text-sm font-medium">
                            Kirim ke Quality Check
                        </button>
                    </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\finishing-operations.blade.php ENDPATH**/ ?>