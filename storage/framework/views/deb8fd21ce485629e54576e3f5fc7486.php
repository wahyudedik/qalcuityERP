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
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            <?php echo e(__('Cloud Storage Configuration')); ?>

        </h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Storage Configurations -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Storage Providers</h3>

                    <?php $__empty_1 = true; $__currentLoopData = $configs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $config): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="border border-gray-200 dark:border-gray-700 rounded-lg p-4 mb-4 <?php if($config['is_default']): ?> bg-blue-50 dark:bg-blue-900/20 border-blue-300 dark:border-blue-700 <?php endif; ?>">
                            <div class="flex items-center justify-between mb-3">
                                <div class="flex items-center space-x-3">
                                    <?php if($config['provider'] === 's3'): ?>
                                        <svg class="h-8 w-8 text-yellow-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5" />
                                        </svg>
                                    <?php elseif($config['provider'] === 'gcs'): ?>
                                        <svg class="h-8 w-8 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                                        </svg>
                                    <?php else: ?>
                                        <svg class="h-8 w-8 text-blue-500" fill="currentColor" viewBox="0 0 24 24">
                                            <path
                                                d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z" />
                                        </svg>
                                    <?php endif; ?>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-white">
                                            <?php echo e(strtoupper($config['provider'])); ?>

                                            <?php if($config['is_default']): ?>
                                                <span
                                                    class="ml-2 px-2 py-0.5 text-xs bg-blue-600 text-white rounded">Default</span>
                                            <?php endif; ?>
                                        </h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($config['bucket_name']); ?>

                                            - <?php echo e($config['region']); ?></p>
                                    </div>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full <?php echo e($config['is_active'] ? 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200' : 'bg-gray-100 text-gray-800 dark:bg-gray-600 dark:text-gray-200'); ?>">
                                    <?php echo e($config['is_active'] ? 'Active' : 'Inactive'); ?>

                                </span>
                            </div>
                            <div class="flex space-x-2 text-sm">
                                <button class="text-blue-600 hover:text-blue-900">Edit</button>
                                <button class="text-green-600 hover:text-green-900">Test Connection</button>
                                <?php if(!$config['is_default']): ?>
                                    <button class="text-purple-600 hover:text-purple-900">Set as Default</button>
                                    <button class="text-red-600 hover:text-red-900">Delete</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">No cloud storage configured</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Add New Configuration -->
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Add New Storage Provider</h3>
                    <form method="POST" action="<?php echo e(route('documents.cloud-storage.store')); ?>" class="space-y-4">
                        <?php echo csrf_field(); ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Provider
                                    *</label>
                                <select name="provider" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Provider</option>
                                    <option value="s3">Amazon S3</option>
                                    <option value="gcs">Google Cloud Storage</option>
                                    <option value="azure">Azure Blob Storage</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Bucket/Container
                                    Name *</label>
                                <input type="text" name="bucket_name" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Region</label>
                                <input type="text" name="region"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., us-east-1">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Endpoint
                                    (Optional)</label>
                                <input type="url" name="endpoint"
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Access
                                    Key *</label>
                                <input type="text" name="access_key" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Secret
                                    Key *</label>
                                <input type="password" name="secret_key" required
                                    class="w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="space-y-2">
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_active" value="1" checked
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Active</span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="is_default" value="1"
                                    class="rounded border-gray-300 text-blue-600 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300">Set as default
                                    storage</span>
                            </label>
                        </div>
                        <div class="flex justify-end space-x-3 pt-4">
                            <button type="submit"
                                class="px-6 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                Add Storage Provider
                            </button>
                        </div>
                    </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\documents\cloud-storage-config.blade.php ENDPATH**/ ?>