

<?php $__env->startSection('title', 'Safety Data Sheets'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <a href="<?php echo e(route('cosmetic.registrations.index')); ?>"
                        class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                        ← Back to Registrations
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">Safety Data Sheets (SDS)</h1>
                    <p class="mt-1 text-sm text-gray-500">Product safety documentation management</p>
                </div>
                <button onclick="document.getElementById('add-sds-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white font-medium rounded-lg transition">
                    + New SDS
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total SDS</div>
                <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($stats['total_sds']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Active</div>
                <div class="mt-2 text-2xl font-bold text-green-600"><?php echo e($stats['active']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Needs Review</div>
                <div class="mt-2 text-2xl font-bold text-orange-600"><?php echo e($stats['needs_review']); ?></div>
            </div>
        </div>

        <!-- SDS List -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b border-gray-200">
                <form method="GET" action="<?php echo e(route('cosmetic.registrations.sds')); ?>" class="flex gap-4">
                    <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                        <option value="">All Status</option>
                        <option value="draft" <?php echo e(request('status') == 'draft' ? 'selected' : ''); ?>>Draft</option>
                        <option value="active" <?php echo e(request('status') == 'active' ? 'selected' : ''); ?>>Active</option>
                        <option value="outdated" <?php echo e(request('status') == 'outdated' ? 'selected' : ''); ?>>Outdated</option>
                    </select>
                    <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                        Filter
                    </button>
                </form>
            </div>

            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">SDS Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Version</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Issue Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $sdsList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sds): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($sds->sds_number); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm text-gray-900"><?php echo e($sds->product_name); ?></div>
                                <?php if($sds->formula): ?>
                                    <div class="text-xs text-gray-500"><?php echo e($sds->formula->formula_name); ?></div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-medium text-gray-900">v<?php echo e($sds->version); ?></span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-500">
                                <?php echo e($sds->issue_date->format('d M Y')); ?>

                                <?php if($sds->needsReview()): ?>
                                    <div class="text-xs text-orange-600 font-medium">⚠️ Needs Review</div>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($sds->status == 'active'): ?> bg-green-100 text-green-800
                            <?php elseif($sds->status == 'outdated'): ?> bg-gray-100 text-gray-800
                            <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                    <?php echo e($sds->status_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <div class="flex justify-end gap-2">
                                    <?php if($sds->status == 'draft'): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('cosmetic.registrations.sds.activate', $sds)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit"
                                                class="text-green-600 hover:text-green-900">Activate</button>
                                        </form>
                                    <?php endif; ?>
                                    <?php if($sds->status == 'active'): ?>
                                        <form method="POST"
                                            action="<?php echo e(route('cosmetic.registrations.sds.new-version', $sds)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <button type="submit" class="text-blue-600 hover:text-blue-900">New
                                                Version</button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No safety data sheets found. Create your first SDS!
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($sdsList->hasPages()): ?>
            <div class="mt-4"><?php echo e($sdsList->links()); ?></div>
        <?php endif; ?>
    </div>

    <!-- Add SDS Modal -->
    <div id="add-sds-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-10 mx-auto p-5 border w-[700px] shadow-lg rounded-md bg-white max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Create Safety Data Sheet</h3>
            <form method="POST" action="<?php echo e(route('cosmetic.registrations.sds.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Product Name *</label>
                            <input type="text" name="product_name" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Issue Date *</label>
                            <input type="date" name="issue_date" value="<?php echo e(date('Y-m-d')); ?>" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Review Date</label>
                        <input type="date" name="review_date" value="<?php echo e(date('Y-m-d', strtotime('+3 years'))); ?>"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">First Aid Measures</label>
                        <textarea name="first_aid_measures" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fire Fighting Measures</label>
                        <textarea name="fire_fighting_measures" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Handling & Storage</label>
                        <textarea name="handling_storage" rows="2" class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit"
                        class="flex-1 px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700">
                        Create SDS
                    </button>
                    <button type="button" onclick="document.getElementById('add-sds-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\registrations\sds.blade.php ENDPATH**/ ?>