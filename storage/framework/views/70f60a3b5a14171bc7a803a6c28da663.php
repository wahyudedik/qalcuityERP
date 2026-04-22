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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <h1 class="text-xl font-bold text-gray-900 dark:text-white">Printing Job Dashboard</h1>
            <a href="<?php echo e(route('printing.create')); ?>"
                class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition text-sm font-medium whitespace-nowrap">
                + New Print Job
            </a>
        </div>
     <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Total Jobs</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($stats['total_jobs']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Active Jobs</p>
            <p class="text-2xl font-bold text-blue-600 dark:text-blue-400 mt-1"><?php echo e($stats['active_jobs']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Completed Today</p>
            <p class="text-2xl font-bold text-green-600 dark:text-green-400 mt-1"><?php echo e($stats['completed_today']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-red-200 dark:border-red-500/30 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Overdue Jobs</p>
            <p class="text-2xl font-bold text-red-600 dark:text-red-400 mt-1"><?php echo e($stats['overdue_jobs']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-orange-200 dark:border-orange-500/30 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400">Urgent Jobs</p>
            <p class="text-2xl font-bold text-orange-600 dark:text-orange-400 mt-1"><?php echo e($stats['urgent_jobs']); ?></p>
        </div>
    </div>

    
    <?php if($overdue->count() > 0): ?>
        <div class="bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-xl p-4 mb-6">
            <h3 class="text-sm font-semibold text-red-800 dark:text-red-400 mb-2">Overdue Jobs
                (<?php echo e($overdue->count()); ?>)</h3>
            <div class="space-y-2">
                <?php $__currentLoopData = $overdue; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-red-700 dark:text-red-300"><?php echo e($job->job_number); ?> -
                            <?php echo e($job->job_name); ?></span>
                        <span class="text-red-600 dark:text-red-400">Due: <?php echo e($job->due_date->format('d M Y')); ?></span>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Active Print Jobs</h3>
            <div class="flex gap-2">
                <select
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Status</option>
                    <option value="queued">Queued</option>
                    <option value="prepress">Pre-Press</option>
                    <option value="on_press">On Press</option>
                    <option value="finishing">Finishing</option>
                </select>
                <select
                    class="text-sm border border-gray-300 dark:border-gray-600 rounded px-3 py-1.5 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                    <option value="">All Priority</option>
                    <option value="urgent">Urgent</option>
                    <option value="high">High</option>
                    <option value="normal">Normal</option>
                    <option value="low">Low</option>
                </select>
            </div>
        </div>

        <?php if($jobs->count() === 0): ?>
            <?php if (isset($component)) { $__componentOriginal074a021b9d42f490272b5eefda63257c = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal074a021b9d42f490272b5eefda63257c = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.empty-state','data' => ['icon' => 'document','title' => 'Belum ada print job','message' => 'Belum ada print job aktif. Buat print job pertama Anda.','actionText' => 'Buat Print Job','actionUrl' => ''.e(route('printing.create')).'']] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('empty-state'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['icon' => 'document','title' => 'Belum ada print job','message' => 'Belum ada print job aktif. Buat print job pertama Anda.','actionText' => 'Buat Print Job','actionUrl' => ''.e(route('printing.create')).'']); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $attributes = $__attributesOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__attributesOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal074a021b9d42f490272b5eefda63257c)): ?>
<?php $component = $__componentOriginal074a021b9d42f490272b5eefda63257c; ?>
<?php unset($__componentOriginal074a021b9d42f490272b5eefda63257c); ?>
<?php endif; ?>
        <?php else: ?>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-[#0f172a]">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Job Number</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Product</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Qty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Status</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Priority</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Due Date</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-slate-400 uppercase">
                                Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-white/5">
                        <?php $__currentLoopData = $jobs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $job): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-gray-50 dark:hover:bg-[#0f172a] transition">
                                <td class="px-6 py-4">
                                    <a href="<?php echo e(route('printing.show', $job)); ?>"
                                        class="font-medium text-indigo-600 dark:text-indigo-400 hover:underline">
                                        <?php echo e($job->job_number); ?>

                                    </a>
                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e($job->customer?->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $job->product_type))); ?>

                                </td>
                                <td class="px-6 py-4 text-gray-700 dark:text-slate-300">
                                    <?php echo e(number_format($job->quantity)); ?>

                                </td>
                                <td class="px-6 py-4">
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
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($color); ?>-100 text-<?php echo e($color); ?>-700 dark:bg-<?php echo e($color); ?>-500/20 dark:text-<?php echo e($color); ?>-400">
                                        <?php echo e(ucfirst(str_replace('_', ' ', $job->status))); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php
                                        $priorityColors = [
                                            'low' => 'gray',
                                            'normal' => 'blue',
                                            'high' => 'orange',
                                            'urgent' => 'red',
                                        ];
                                        $pColor = $priorityColors[$job->priority] ?? 'blue';
                                    ?>
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-<?php echo e($pColor); ?>-100 text-<?php echo e($pColor); ?>-700 dark:bg-<?php echo e($pColor); ?>-500/20 dark:text-<?php echo e($pColor); ?>-400">
                                        <?php echo e(ucfirst($job->priority)); ?>

                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <?php if($job->due_date): ?>
                                        <?php if($job->due_date->isPast()): ?>
                                            <span
                                                class="text-red-600 dark:text-red-400 font-medium"><?php echo e($job->due_date->format('d M Y')); ?></span>
                                        <?php else: ?>
                                            <span
                                                class="text-gray-700 dark:text-slate-300"><?php echo e($job->due_date->format('d M Y')); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="text-gray-400">-</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <a href="<?php echo e(route('printing.show', $job)); ?>"
                                            class="text-indigo-600 dark:text-indigo-400 hover:underline text-xs">View</a>
                                        <?php if($job->status === 'queued'): ?>
                                            <form action="<?php echo e(route('printing.status', $job)); ?>" method="POST"
                                                class="inline">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="status" value="prepress">
                                                <button type="submit"
                                                    class="text-blue-600 dark:text-blue-400 hover:underline text-xs">Start</button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                <?php echo e($jobs->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\printing\dashboard.blade.php ENDPATH**/ ?>