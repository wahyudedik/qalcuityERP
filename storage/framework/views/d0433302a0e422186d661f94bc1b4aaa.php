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
        <a href="<?php echo e(route('printing.dashboard')); ?>"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Back
                </a>
        <form action="<?php echo e(route('printing.status', $job)); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="status" value="prepress">
                        <button type="submit"
                            class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition text-sm font-medium whitespace-nowrap">
                            Start Pre-Press
                        </button>
        <a href="<?php echo e(route('printing.finishing', $job)); ?>"
                        class="px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700 transition text-sm font-medium whitespace-nowrap">
                        Manage Finishing
                    </a>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
            <div>
                <p class="text-xs text-gray-500 mb-1">Job Number</p>
                <p class="text-xl font-bold text-gray-900"><?php echo e($job->job_number); ?></p>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Status</p>
                <?php
                    $statusColors = [
                        'queued' => 'gray',
                        'prepress' => 'blue',
                        'platemaking' => 'indigo',
                        'on_press' => 'purple',
                        'finishing' => 'orange',
                        'quality_check' => 'yellow',
                        'completed' => 'green',
                    ];
                    $color = $statusColors[$job->status] ?? 'gray';
                ?>
                <span
                    class="px-3 py-1.5 text-sm rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 $color }}-500/20 $color }}-400 font-medium">
                    <?php echo e(ucfirst(str_replace('_', ' ', $job->status))); ?>

                </span>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Priority</p>
                <?php
                    $priorityColors = ['low' => 'gray', 'normal' => 'blue', 'high' => 'orange', 'urgent' => 'red'];
                    $pColor = $priorityColors[$job->priority] ?? 'blue';
                ?>
                <span
                    class="px-3 py-1.5 text-sm rounded-full bg-<?php echo e($pColor); ?>-100 text-<?php echo e($pColor); ?>-700 $pColor }}-500/20 $pColor }}-400 font-medium">
                    <?php echo e(ucfirst($job->priority)); ?>

                </span>
            </div>
            <div>
                <p class="text-xs text-gray-500 mb-1">Due Date</p>
                <?php if($job->due_date): ?>
                    <?php if($job->due_date->isPast() && !$job->completed_at): ?>
                        <span
                            class="text-red-600 font-semibold"><?php echo e($job->due_date->format('d M Y')); ?></span>
                    <?php else: ?>
                        <span
                            class="text-gray-900 font-semibold"><?php echo e($job->due_date->format('d M Y')); ?></span>
                    <?php endif; ?>
                <?php else: ?>
                    <span class="text-gray-400">Not set</span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <div class="lg:col-span-2 space-y-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Job Information</h2>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Job Name</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->job_name); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Customer</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($job->customer?->name ?? 'N/A'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Product Type</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e(ucfirst(str_replace('_', ' ', $job->product_type))); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Quantity</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e(number_format($job->quantity)); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Paper Type</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($job->paper_type ?? 'Not specified'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Paper Size</p>
                        <p class="text-sm font-medium text-gray-900">
                            <?php echo e($job->paper_size ?? 'Not specified'); ?></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Colors (Front)</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->colors_front ?? 4); ?>

                            Colors</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Colors (Back)</p>
                        <p class="text-sm font-medium text-gray-900"><?php echo e($job->colors_back ?? 0); ?>

                            Colors</p>
                    </div>
                </div>

                <?php if($job->special_instructions): ?>
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <p class="text-xs text-gray-500 mb-1">Special Instructions</p>
                        <p class="text-sm text-gray-700"><?php echo e($job->special_instructions); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Production Progress</h2>

                <?php
                    $stages = [
                        ['key' => 'queued', 'label' => 'Queued'],
                        ['key' => 'prepress', 'label' => 'Pre-Press'],
                        ['key' => 'platemaking', 'label' => 'Plate Making'],
                        ['key' => 'on_press', 'label' => 'On Press'],
                        ['key' => 'finishing', 'label' => 'Finishing'],
                        ['key' => 'quality_check', 'label' => 'Quality Check'],
                        ['key' => 'completed', 'label' => 'Completed'],
                    ];

                    $currentStageIndex = array_search($job->status, array_column($stages, 'key'));
                    if ($currentStageIndex === false) {
                        $currentStageIndex = 0;
                    }
                ?>

                <div class="relative">
                    <div class="absolute top-1/2 left-0 right-0 h-1 bg-gray-200 -translate-y-1/2">
                    </div>
                    <div class="absolute top-1/2 left-0 h-1 bg-indigo-600 -translate-y-1/2 transition-all duration-500"
                        style="width: <?php echo e(($currentStageIndex / (count($stages) - 1)) * 100); ?>%"></div>

                    <div class="relative flex justify-between">
                        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $stage): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $isActive = $index <= $currentStageIndex;
                                $isCurrent = $index === $currentStageIndex;
                            ?>
                            <div class="flex flex-col items-center">
                                <div
                                    class="w-10 h-10 rounded-full flex items-center justify-center text-sm font-semibold
                                    <?php echo e($isActive ? 'bg-indigo-600 text-white' : 'bg-gray-200 text-gray-400'); ?>

                                    <?php echo e($isCurrent ? 'ring-4 ring-indigo-200' : ''); ?>">
                                    <?php echo e($index + 1); ?>

                                </div>
                                <p
                                    class="mt-2 text-xs font-medium <?php echo e($isActive ? 'text-indigo-600' : 'text-gray-500'); ?>">
                                    <?php echo e($stage['label']); ?>

                                </p>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>

            
            <?php if($job->assignedOperator): ?>
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Assigned Operator</h2>
                    <div class="flex items-center gap-3">
                        <div
                            class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-600 font-semibold text-lg">
                            <?php echo e(substr($job->assignedOperator->name, 0, 1)); ?>

                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?php echo e($job->assignedOperator->name); ?></p>
                            <p class="text-sm text-gray-500"><?php echo e($job->assignedOperator->email); ?>

                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Timeline</h2>

                <div class="space-y-3">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-gray-900">Created</p>
                            <p class="text-xs text-gray-500">
                                <?php echo e($job->created_at->format('d M Y H:i')); ?></p>
                        </div>
                    </div>

                    <?php if($job->started_at): ?>
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-blue-500 mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Production Started</p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($job->started_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <?php if($job->completed_at): ?>
                        <div class="flex items-start gap-3">
                            <div class="w-2 h-2 rounded-full bg-green-500 mt-2"></div>
                            <div class="flex-1">
                                <p class="text-sm font-medium text-gray-900">Completed</p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($job->completed_at->format('d M Y H:i')); ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="space-y-6">
            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Pricing</h2>

                <div class="space-y-3">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Estimated Cost</span>
                        <span class="font-medium text-gray-900">Rp
                            <?php echo e(number_format($job->estimated_cost ?? 0, 0, ',', '.')); ?></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Actual Cost</span>
                        <span class="font-medium text-gray-900">Rp
                            <?php echo e(number_format($job->actual_cost ?? 0, 0, ',', '.')); ?></span>
                    </div>
                    <?php if($job->quoted_price): ?>
                        <div class="pt-3 border-t border-gray-200">
                            <div class="flex justify-between">
                                <span class="text-sm font-medium text-gray-900">Quoted Price</span>
                                <span class="text-lg font-bold text-indigo-600">Rp
                                    <?php echo e(number_format($job->quoted_price, 0, ',', '.')); ?></span>
                            </div>
                            <?php if($job->quoted_price > $job->estimated_cost): ?>
                                <p class="text-xs text-green-600 mt-1">
                                    Profit: Rp
                                    <?php echo e(number_format($job->quoted_price - $job->estimated_cost, 0, ',', '.')); ?>

                                </p>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h2>

                <div class="space-y-2">
                    <?php if($job->status === 'prepress'): ?>
                        <form action="<?php echo e(route('printing.approve-proof', $job)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="w-full px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition text-sm font-medium">
                                Approve Proof
                            </button>
                        </form>
                    <?php endif; ?>

                    <?php if($job->status === 'platemaking'): ?>
                        <a href="#"
                            class="block w-full px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition text-sm font-medium text-center">
                            Start Press Run
                        </a>
                    <?php endif; ?>

                    <?php if($job->status !== 'completed'): ?>
                        <form action="<?php echo e(route('printing.assign', $job)); ?>" method="POST">
                            <?php echo csrf_field(); ?>
                            <select name="operator_id" onchange="this.form.submit()"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white text-gray-900 text-sm">
                                <option value="">Assign Operator...</option>
                                <?php $__currentLoopData = \App\Models\User::where('tenant_id', auth()->user()->tenant_id)->get(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </form>
                    <?php endif; ?>

                    <a href="#"
                        class="block w-full px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition text-sm font-medium text-center">
                        Print Job Ticket
                    </a>
                </div>
            </div>

            
            <div
                class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <h4 class="text-sm font-semibold text-yellow-800 mb-2">Notes</h4>
                <textarea placeholder="Add notes about this job..."
                    class="w-full px-3 py-2 border border-yellow-300 rounded-lg bg-white text-gray-900 text-sm focus:ring-2 focus:ring-yellow-500 focus:border-transparent"
                    rows="3"></textarea>
            </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\job-detail.blade.php ENDPATH**/ ?>