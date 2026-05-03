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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Version History')); ?> - <?php echo e($document->title); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('documents.index')); ?>"
                    class="text-gray-600 hover:text-gray-900">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Version Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500">Current Version</p>
                    <p class="text-3xl font-semibold text-blue-600 mt-2">v<?php echo e($history['current_version']); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500">Total Versions</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2">
                        <?php echo e($history['total_versions']); ?></p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500">Avg File Size</p>
                    <p class="text-3xl font-semibold text-gray-900 mt-2">
                        <?php echo e(number_format($statistics['avg_file_size'] / 1024, 1)); ?> KB</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm font-medium text-gray-500">Last Updated</p>
                    <p class="text-lg font-semibold text-gray-900 mt-2">
                        <?php echo e($statistics['last_updated'] ?? 'N/A'); ?></p>
                </div>
            </div>

            <!-- Version Timeline -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Version Timeline</h3>

                    <div class="flow-root">
                        <ul class="-mb-8">
                            <?php $__empty_1 = true; $__currentLoopData = $history['versions']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <li>
                                    <div class="relative pb-8">
                                        <?php if(!$loop->last): ?>
                                            <span
                                                class="absolute top-4 left-4 -ml-px h-full w-0.5 bg-gray-200"
                                                aria-hidden="true"></span>
                                        <?php endif; ?>
                                        <div class="relative flex space-x-3">
                                            <div>
                                                <span
                                                    class="h-8 w-8 rounded-full bg-blue-500 flex items-center justify-center ring-8 ring-white">
                                                    <span
                                                        class="text-white text-sm font-semibold"><?php echo e($version['version']); ?></span>
                                                </span>
                                            </div>
                                            <div class="flex min-w-0 flex-1 justify-between space-x-4 pt-1.5">
                                                <div>
                                                    <p class="text-sm text-gray-900">
                                                        <span class="font-medium"><?php echo e($version['file_name']); ?></span>
                                                    </p>
                                                    <?php if($version['change_summary']): ?>
                                                        <p class="mt-1 text-sm text-gray-500">
                                                            <?php echo e($version['change_summary']); ?></p>
                                                    <?php endif; ?>
                                                    <p class="mt-1 text-xs text-gray-400">
                                                        Changed by <span
                                                            class="font-medium"><?php echo e($version['changed_by']); ?></span>
                                                    </p>
                                                </div>
                                                <div
                                                    class="whitespace-nowrap text-right text-sm text-gray-500">
                                                    <time><?php echo e($version['created_at']); ?></time>
                                                    <div class="mt-1"><?php echo e($version['file_size_human']); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </li>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <li class="text-center py-8">
                                    <p class="text-gray-500">No version history available</p>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Actions</h3>

                    <!-- Upload New Version -->
                    <form method="POST" action="<?php echo e(route('documents.versions.store', $document)); ?>"
                        enctype="multipart/form-data" class="mb-6 pb-6 border-b border-gray-200">
                        <?php echo csrf_field(); ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">New
                                    File</label>
                                <input type="file" name="file"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Change
                                    Summary *</label>
                                <input type="text" name="change_summary" required
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Describe what changed...">
                            </div>
                        </div>
                        <div class="mt-4">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Create New Version
                            </button>
                        </div>
                    </form>

                    <!-- Rollback -->
                    <?php if($history['total_versions'] > 1): ?>
                        <div
                            class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                            <h4 class="text-sm font-medium text-yellow-800 mb-2">⚠️ Rollback to
                                Previous Version</h4>
                            <form method="POST"
                                action="<?php echo e(route('documents.versions.rollback', [$document, $history['current_version'] - 1])); ?>"
                                class="space-y-2">
                                <?php echo csrf_field(); ?>
                                <label class="inline-flex items-center">
                                    <input type="checkbox" name="confirm" value="1" required
                                        class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <span class="ml-2 text-sm text-gray-700">I confirm I want to
                                        rollback to the previous version</span>
                                </label>
                                <button type="submit"
                                    class="px-4 py-2 bg-yellow-600 text-white rounded-md hover:bg-yellow-700"
                                    onclick="return confirm('Are you sure you want to rollback?')">
                                    Rollback Now
                                </button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\documents\versions.blade.php ENDPATH**/ ?>