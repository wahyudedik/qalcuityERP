

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Breeding Records</h1>
            <p class="mt-2 text-gray-600">Track breeding programs and genetics</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Records</div>
                <div class="mt-2 text-3xl font-bold text-gray-900"><?php echo e($stats['total_records']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Pregnant</div>
                <div class="mt-2 text-3xl font-bold text-blue-600"><?php echo e($stats['pregnant_count']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Upcoming Births</div>
                <div class="mt-2 text-3xl font-bold text-yellow-600"><?php echo e($stats['upcoming_births']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Success Rate</div>
                <div class="mt-2 text-3xl font-bold text-green-600"><?php echo e($stats['success_rate']); ?>%</div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold text-gray-900">Breeding Records</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Mating Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Dam ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Sire ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $records; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $record): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($record->mating_date->format('d M Y')); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    <?php echo e($record->dam_id); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($record->sire_id); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $record->mating_type))); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                    <?php if($record->status === 'pregnant'): ?> bg-blue-100 text-blue-800
                                    <?php elseif($record->status === 'born'): ?> bg-green-100 text-green-800
                                    <?php elseif($record->status === 'failed'): ?> bg-red-100 text-red-800
                                    <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                        <?php echo e(ucfirst($record->status)); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">No records found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($records->links()); ?>

            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\livestock\breeding\records.blade.php ENDPATH**/ ?>