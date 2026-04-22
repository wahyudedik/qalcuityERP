

<?php $__env->startSection('title', 'Supplier Quality Report'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900">Supplier Quality Report</h1>
                    <p class="mt-1 text-sm text-gray-500">Vendor performance and incident analysis</p>
                </div>
                <a href="<?php echo e(route('cosmetic.analytics.dashboard')); ?>" class="text-blue-600 hover:text-blue-800">← Back to
                    Analytics</a>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex gap-4">
                <input type="date" name="date_from" value="<?php echo e($dateFrom); ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <input type="date" name="date_to" value="<?php echo e($dateTo); ?>"
                    class="px-3 py-2 border border-gray-300 rounded-lg">
                <button type="submit"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg">Filter</button>
            </form>
        </div>

        <!-- Supplier Scores -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <?php $__currentLoopData = $supplierScores; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $score): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900"><?php echo e($score['supplier']->name); ?></h3>
                            <p class="text-sm text-gray-500"><?php echo e($score['supplier']->code); ?></p>
                        </div>
                        <span
                            class="px-3 py-1 text-sm font-semibold rounded-full
                            <?php echo e($score['quality_rating'] === 'Excellent'
                                ? 'bg-green-100 text-green-800'
                                : ($score['quality_rating'] === 'Good'
                                    ? 'bg-blue-100 text-blue-800'
                                    : ($score['quality_rating'] === 'Fair'
                                        ? 'bg-yellow-100 text-yellow-800'
                                        : 'bg-red-100 text-red-800'))); ?>">
                            <?php echo e($score['quality_rating']); ?>

                        </span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-600">Overall Score</span>
                            <span class="text-sm font-bold"><?php echo e(number_format($score['overall_score'], 1)); ?>%</span>
                        </div>
                        <div class="w-full bg-gray-200 rounded-full h-2">
                            <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo e($score['overall_score']); ?>%"></div>
                        </div>
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-600">Incidents</span>
                            <span
                                class="font-semibold <?php echo e($score['incident_count'] > 0 ? 'text-red-600' : 'text-green-600'); ?>">
                                <?php echo e($score['incident_count']); ?>

                            </span>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\analytics\supplier-quality.blade.php ENDPATH**/ ?>