

<?php $__env->startSection('title', 'Ingredient Restrictions'); ?>

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
                    <h1 class="text-3xl font-bold text-gray-900">Ingredient Restrictions</h1>
                    <p class="mt-1 text-sm text-gray-500">BPOM banned & restricted ingredients list</p>
                </div>
                <button onclick="document.getElementById('add-restriction-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                    + Add Restriction
                </button>
            </div>
        </div>

        <!-- Info Banner -->
        <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
            <div class="flex">
                <svg class="w-5 h-5 text-orange-600 mr-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd"
                        d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                        clip-rule="evenodd" />
                </svg>
                <div class="text-sm text-orange-800">
                    <strong>BPOM Compliance:</strong>
                    <p class="mt-1">
                        These restrictions are based on BPOM (Badan Pengawas Obat dan Makanan) regulations.
                        Products containing banned ingredients cannot be registered. Restricted ingredients must comply with
                        maximum limits.
                    </p>
                </div>
            </div>
        </div>

        <!-- Restrictions Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ingredient Name</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">CAS Number</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Restriction Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Max Limit</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Regulation</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $restrictions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $restriction): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($restriction->ingredient_name); ?></div>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($restriction->cas_number): ?>
                                    <span class="text-sm text-gray-900 font-mono"><?php echo e($restriction->cas_number); ?></span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($restriction->restriction_type == 'banned'): ?> bg-red-100 text-red-800
                            <?php elseif($restriction->restriction_type == 'restricted'): ?> bg-orange-100 text-orange-800
                            <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                    <?php echo e($restriction->type_label); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($restriction->max_limit): ?>
                                    <span class="text-sm font-medium text-gray-900"><?php echo e($restriction->max_limit); ?>%</span>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4">
                                <?php if($restriction->regulation_reference): ?>
                                    <div class="text-sm text-gray-900">
                                        <?php echo e(Str::limit($restriction->regulation_reference, 30)); ?></div>
                                <?php else: ?>
                                    <span class="text-sm text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <button class="text-blue-600 hover:text-blue-900">View</button>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-400">
                                No ingredient restrictions defined yet. Add BPOM restrictions to ensure compliance.
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($restrictions->hasPages()): ?>
            <div class="mt-4"><?php echo e($restrictions->links()); ?></div>
        <?php endif; ?>
    </div>

    <!-- Add Restriction Modal -->
    <div id="add-restriction-modal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Add Ingredient Restriction</h3>
            <form method="POST" action="<?php echo e(route('cosmetic.registrations.restrictions.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-3">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Ingredient Name (INCI) *</label>
                        <input type="text" name="ingredient_name" required placeholder="e.g., Hydroquinone"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">CAS Number</label>
                        <input type="text" name="cas_number" placeholder="e.g., 123-45-6"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Restriction Type *</label>
                        <select name="restriction_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            <option value="banned">Banned - Cannot be used</option>
                            <option value="restricted">Restricted - Limited use</option>
                            <option value="limited">Limited - Maximum concentration</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Maximum Limit (%)</label>
                        <input type="number" name="max_limit" step="0.01" min="0" max="100"
                            placeholder="e.g., 2.00" class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Regulation Reference</label>
                        <input type="text" name="regulation_reference" placeholder="e.g., BPOM Regulation No. 23/2019"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" placeholder="Additional information..."
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Add Restriction
                    </button>
                    <button type="button"
                        onclick="document.getElementById('add-restriction-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\registrations\restrictions.blade.php ENDPATH**/ ?>