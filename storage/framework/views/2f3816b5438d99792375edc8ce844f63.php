

<?php $__env->startSection('title', 'Defect Records'); ?>

<?php $__env->startSection('content'); ?>
    <div class="p-6">
        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-gray-900">Defect Records</h1>
            <p class="text-gray-600 mt-1">Track and resolve quality defects</p>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Severity</label>
                    <select name="severity" class="w-full rounded-md border-gray-300">
                        <option value="">All Severities</option>
                        <option value="critical" <?php echo e(request('severity') == 'critical' ? 'selected' : ''); ?>>Critical</option>
                        <option value="major" <?php echo e(request('severity') == 'major' ? 'selected' : ''); ?>>Major</option>
                        <option value="minor" <?php echo e(request('severity') == 'minor' ? 'selected' : ''); ?>>Minor</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                    <select name="status" class="w-full rounded-md border-gray-300">
                        <option value="">All Status</option>
                        <option value="open" <?php echo e(request('status') == 'open' ? 'selected' : ''); ?>>Open</option>
                        <option value="resolved" <?php echo e(request('status') == 'resolved' ? 'selected' : ''); ?>>Resolved</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Defect Type</label>
                    <select name="defect_type" class="w-full rounded-md border-gray-300">
                        <option value="">All Types</option>
                        <option value="cosmetic" <?php echo e(request('defect_type') == 'cosmetic' ? 'selected' : ''); ?>>Cosmetic
                        </option>
                        <option value="functional" <?php echo e(request('defect_type') == 'functional' ? 'selected' : ''); ?>>Functional
                        </option>
                        <option value="dimensional" <?php echo e(request('defect_type') == 'dimensional' ? 'selected' : ''); ?>>
                            Dimensional</option>
                        <option value="material" <?php echo e(request('defect_type') == 'material' ? 'selected' : ''); ?>>Material
                        </option>
                        <option value="other" <?php echo e(request('defect_type') == 'other' ? 'selected' : ''); ?>>Other</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg w-full">Filter</button>
                </div>
            </form>
        </div>

        <!-- Defects Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Defect Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Product</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Severity</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty Defected</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Disposition</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cost Impact</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php $__empty_1 = true; $__currentLoopData = $defects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $defect): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?php echo e($defect->defect_code); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-gray-900"><?php echo e($defect->product?->name ?? 'N/A'); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">
                                    <?php echo e(ucfirst($defect->defect_type)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($defect->severity === 'critical'): ?>
                                    <span
                                        class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">CRITICAL</span>
                                <?php elseif($defect->severity === 'major'): ?>
                                    <span
                                        class="px-2 py-1 bg-orange-100 text-orange-800 text-xs font-semibold rounded">MAJOR</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 text-xs font-semibold rounded">MINOR</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900"><?php echo e($defect->quantity_defected); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    <?php echo e(ucfirst(str_replace('_', ' ', $defect->disposition))); ?>

                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if($defect->resolved_at): ?>
                                    <span
                                        class="px-2 py-1 bg-green-100 text-green-800 text-xs font-semibold rounded">RESOLVED</span>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 bg-red-100 text-red-800 text-xs font-semibold rounded">OPEN</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-semibold text-gray-900">Rp
                                    <?php echo e(number_format($defect->cost_impact, 0, ',', '.')); ?></div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <?php if(!$defect->resolved_at): ?>
                                    <button onclick="openResolveModal(<?php echo e($defect->id); ?>, '<?php echo e($defect->defect_code); ?>')"
                                        class="text-blue-600 hover:text-blue-900">Resolve</button>
                                <?php else: ?>
                                    <span class="text-gray-500">Resolved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="9" class="px-6 py-8 text-center text-gray-500">
                                No defects found
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            <?php echo e($defects->links()); ?>

        </div>
    </div>

    <!-- Resolve Defect Modal -->
    <div id="resolveModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900 mb-4">Resolve Defect: <span id="defectCode"></span></h3>
                <form id="resolveForm" method="POST">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Root Cause *</label>
                            <textarea name="root_cause" required rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="What caused this defect?"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Corrective Action *</label>
                            <textarea name="corrective_action" required rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="What action was taken to fix this?"></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Preventive Action</label>
                            <textarea name="preventive_action" rows="3" class="w-full rounded-md border-gray-300"
                                placeholder="How to prevent this in the future?"></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 mt-6">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">Resolve</button>
                        <button type="button" onclick="closeResolveModal()"
                            class="flex-1 bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">Cancel</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openResolveModal(defectId, defectCode) {
            document.getElementById('defectCode').textContent = defectCode;
            document.getElementById('resolveForm').action = `/manufacturing/quality/defects/${defectId}/resolve`;
            document.getElementById('resolveModal').classList.remove('hidden');
        }

        function closeResolveModal() {
            document.getElementById('resolveModal').classList.add('hidden');
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\quality\defects.blade.php ENDPATH**/ ?>