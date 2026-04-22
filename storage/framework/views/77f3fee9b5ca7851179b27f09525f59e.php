

<?php $__env->startSection('title', 'Project Billing Dashboard'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Project Billing Dashboard</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Kelola billing dan invoice untuk semua project
            </p>
        </div>

        <?php if($projects->count() > 0): ?>
            <!-- Projects Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div
                        class="bg-white dark:bg-slate-800 rounded-lg shadow-sm border border-gray-200 dark:border-white/10 hover:shadow-md transition-shadow">
                        <div class="p-6">
                            <!-- Project Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex-1">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white truncate">
                                        <?php echo e($project->name); ?>

                                    </h3>
                                    <?php if($project->customer): ?>
                                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                                            <?php echo e($project->customer->name); ?>

                                        </p>
                                    <?php endif; ?>
                                </div>
                                <span
                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                <?php echo e($project->status === 'active'
                                    ? 'bg-green-100 text-green-800 dark:bg-green-900/30 dark:text-green-400'
                                    : ($project->status === 'completed'
                                        ? 'bg-blue-100 text-blue-800 dark:bg-blue-900/30 dark:text-blue-400'
                                        : 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300')); ?>">
                                    <?php echo e(ucfirst($project->status ?? 'draft')); ?>

                                </span>
                            </div>

                            <!-- Billing Info -->
                            <div class="space-y-3 mb-4">
                                <?php if($project->billingConfig): ?>
                                    <div class="flex items-center justify-between text-sm">
                                        <span class="text-gray-500 dark:text-gray-400">Billing Type:</span>
                                        <span class="font-medium text-gray-900 dark:text-white capitalize">
                                            <?php echo e(str_replace('_', ' ', $project->billingConfig->billing_type)); ?>

                                        </span>
                                    </div>

                                    <?php if($project->billingConfig->hourly_rate): ?>
                                        <div class="flex items-center justify-between text-sm">
                                            <span class="text-gray-500 dark:text-gray-400">Hourly Rate:</span>
                                            <span class="font-medium text-gray-900 dark:text-white">
                                                Rp <?php echo e(number_format($project->billingConfig->hourly_rate, 0, ',', '.')); ?>

                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <div
                                        class="text-sm text-yellow-600 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 p-2 rounded">
                                        ⚠️ Billing config belum disetup
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Quick Stats -->
                            <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-200 dark:border-white/10">
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Total Invoices</p>
                                    <p class="text-lg font-semibold text-gray-900 dark:text-white">
                                        <?php echo e($project->projectInvoices->count()); ?>

                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-gray-500 dark:text-gray-400">Unbilled Hours</p>
                                    <p class="text-lg font-semibold text-orange-600 dark:text-orange-400">
                                        <?php echo e($project->timesheets()->where('billing_status', 'unbilled')->sum('hours')); ?>h
                                    </p>
                                </div>
                            </div>

                            <!-- Action Button -->
                            <div class="mt-4 pt-4 border-t border-gray-200 dark:border-white/10">
                                <a href="<?php echo e(route('project-billing.show', $project)); ?>"
                                    class="block w-full text-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                                    Manage Billing
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <!-- Pagination -->
            <div class="mt-8">
                <?php echo e($projects->links()); ?>

            </div>
        <?php else: ?>
            <!-- Empty State -->
            <div
                class="text-center py-12 bg-white dark:bg-slate-800 rounded-lg border border-gray-200 dark:border-white/10">
                <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">No projects found</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Belum ada project yang dibuat. Mulai dengan membuat project baru.
                </p>
                <div class="mt-6">
                    <a href="<?php echo e(route('projects.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                        Create First Project
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\project-billing\index.blade.php ENDPATH**/ ?>