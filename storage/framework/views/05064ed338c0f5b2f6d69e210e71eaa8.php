

<?php $__env->startSection('title', 'Integration Marketplace'); ?>

<?php $__env->startSection('content'); ?>
    <div class="min-h-screen bg-gray-50">
        <!-- Header -->
        <div class="bg-white shadow">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900">Integration Marketplace</h1>
                        <p class="mt-2 text-sm text-gray-600">Connect your favorite tools and marketplaces</p>
                    </div>
                    <div class="flex space-x-3">
                        <a href="<?php echo e(route('integrations.webhook-logs')); ?>"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                            </svg>
                            Webhook Logs
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Tabs -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="border-b border-gray-200">
                <nav class="-mb-px flex space-x-8">
                    <a href="<?php echo e(route('integrations.index', ['type' => 'all'])); ?>"
                        class="<?php echo e($type === 'all' ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm">
                        All (<?php echo e(count($availableIntegrations)); ?>)
                    </a>
                    <?php $__currentLoopData = $availableIntegrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $integrations): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('integrations.index', ['type' => $typeKey])); ?>"
                            class="<?php echo e($type === $typeKey ? 'border-blue-500 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'); ?> whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm capitalize">
                            <?php echo e(str_replace('-', ' ', $typeKey)); ?>

                        </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </nav>
            </div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
            <!-- Your Integrations -->
            <?php if($integrations->count() > 0): ?>
                <div class="mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Your Integrations</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        <?php $__currentLoopData = $integrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $integration): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow">
                                <div class="p-6">
                                    <div class="flex items-center justify-between mb-4">
                                        <div class="flex items-center">
                                            <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                                                <span
                                                    class="text-2xl font-bold text-blue-600"><?php echo e(strtoupper(substr($integration->slug, 0, 1))); ?></span>
                                            </div>
                                            <div class="ml-3">
                                                <h3 class="text-lg font-semibold text-gray-900"><?php echo e($integration->name); ?>

                                                </h3>
                                                <p class="text-sm text-gray-500 capitalize"><?php echo e($integration->type); ?></p>
                                            </div>
                                        </div>
                                        <span
                                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium <?php echo e($integration->status === 'active' ? 'bg-green-100 text-green-800' : ($integration->status === 'error' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800')); ?>">
                                            <?php echo e(ucfirst($integration->status)); ?>

                                        </span>
                                    </div>

                                    <?php if($integration->last_sync_at): ?>
                                        <div class="text-sm text-gray-600 mb-4">
                                            <p>Last sync: <?php echo e($integration->last_sync_at->diffForHumans()); ?></p>
                                            <p>Frequency: <?php echo e(ucfirst($integration->sync_frequency)); ?></p>
                                        </div>
                                    <?php endif; ?>

                                    <div class="flex space-x-2">
                                        <a href="<?php echo e(route('integrations.show', $integration)); ?>"
                                            class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                                            View Details
                                        </a>
                                        <?php if($integration->isConnected()): ?>
                                            <button onclick="triggerSync('<?php echo e($integration->id); ?>')"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                Sync Now
                                            </button>
                                        <?php else: ?>
                                            <a href="<?php echo e(route('integrations.setup', $integration)); ?>"
                                                class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                Setup
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>

                    <div class="mt-6">
                        <?php echo e($integrations->links()); ?>

                    </div>
                </div>
            <?php endif; ?>

            <!-- Available Integrations -->
            <div>
                <h2 class="text-xl font-semibold text-gray-900 mb-4">Available Integrations</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php $__currentLoopData = $availableIntegrations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $typeKey => $availableList): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($type === 'all' || $type === $typeKey): ?>
                            <?php $__currentLoopData = $availableList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $available): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div
                                    class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow <?php echo e(isset($available['coming_soon']) ? 'opacity-75' : ''); ?>">
                                    <div class="p-6">
                                        <div class="flex items-center justify-between mb-4">
                                            <div class="flex items-center">
                                                <div
                                                    class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center">
                                                    <span
                                                        class="text-2xl font-bold text-white"><?php echo e(strtoupper(substr($available['slug'], 0, 1))); ?></span>
                                                </div>
                                                <div class="ml-3">
                                                    <h3 class="text-lg font-semibold text-gray-900">
                                                        <?php echo e($available['name']); ?></h3>
                                                    <p class="text-sm text-gray-500 capitalize">
                                                        <?php echo e(str_replace('-', ' ', $typeKey)); ?></p>
                                                </div>
                                            </div>
                                            <?php if(isset($available['coming_soon'])): ?>
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                    Coming Soon
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <p class="text-sm text-gray-600 mb-4"><?php echo e($available['description']); ?></p>

                                        <?php if(!isset($available['coming_soon'])): ?>
                                            <form action="<?php echo e(route('integrations.store')); ?>" method="POST">
                                                <?php echo csrf_field(); ?>
                                                <input type="hidden" name="name" value="<?php echo e($available['name']); ?>">
                                                <input type="hidden" name="slug" value="<?php echo e($available['slug']); ?>">
                                                <input type="hidden" name="type" value="<?php echo e($typeKey); ?>">
                                                <input type="hidden" name="sync_frequency" value="hourly">

                                                <button type="submit"
                                                    class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700">
                                                    Install Integration
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <button disabled
                                                class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-400 bg-gray-100 cursor-not-allowed">
                                                Coming Soon
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function triggerSync(integrationId) {
                if (!confirm('Trigger sync for this integration?')) return;

                fetch(`/integrations/${integrationId}/sync`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '<?php echo e(csrf_token()); ?>',
                        },
                        body: JSON.stringify({
                            type: 'all'
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            alert('Sync job queued successfully!');
                        } else {
                            alert('Failed to trigger sync: ' + data.error);
                        }
                    })
                    .catch(error => {
                        alert('Error: ' + error.message);
                    });
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\integrations\index.blade.php ENDPATH**/ ?>