

<?php $__env->startSection('title', 'OOS Investigations'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <a href="<?php echo e(route('cosmetic.qc.tests')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                        ← Back to QC Tests
                    </a>
                    <h1 class="text-3xl font-bold text-gray-900">OOS Investigations</h1>
                    <p class="mt-1 text-sm text-gray-500">Out-of-Specification investigation management</p>
                </div>
                <button onclick="document.getElementById('add-oos-modal').classList.remove('hidden')"
                    class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                    + New OOS
                </button>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Total OOS</div>
                <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($stats['total_oos']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Open</div>
                <div class="mt-2 text-2xl font-bold text-yellow-600"><?php echo e($stats['open_oos']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">Critical</div>
                <div class="mt-2 text-2xl font-bold text-red-600"><?php echo e($stats['critical_oos']); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-4">
                <div class="text-sm font-medium text-gray-500">High Priority</div>
                <div class="mt-2 text-2xl font-bold text-orange-600"><?php echo e($stats['high_oos']); ?></div>
            </div>
        </div>

        <!-- Filters -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" action="<?php echo e(route('cosmetic.qc.oos')); ?>" class="flex gap-4">
                <select name="status" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Status</option>
                    <option value="open" <?php echo e(request('status') == 'open' ? 'selected' : ''); ?>>Open</option>
                    <option value="investigating" <?php echo e(request('status') == 'investigating' ? 'selected' : ''); ?>>Investigating
                    </option>
                    <option value="completed" <?php echo e(request('status') == 'completed' ? 'selected' : ''); ?>>Completed</option>
                    <option value="closed" <?php echo e(request('status') == 'closed' ? 'selected' : ''); ?>>Closed</option>
                </select>
                <select name="severity" class="px-3 py-2 border border-gray-300 rounded-lg">
                    <option value="">All Severity</option>
                    <option value="low" <?php echo e(request('severity') == 'low' ? 'selected' : ''); ?>>Low</option>
                    <option value="medium" <?php echo e(request('severity') == 'medium' ? 'selected' : ''); ?>>Medium</option>
                    <option value="high" <?php echo e(request('severity') == 'high' ? 'selected' : ''); ?>>High</option>
                    <option value="critical" <?php echo e(request('severity') == 'critical' ? 'selected' : ''); ?>>Critical</option>
                </select>
                <button type="submit" class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white rounded-lg">
                    Filter
                </button>
            </form>
        </div>

        <!-- OOS List -->
        <div class="space-y-4">
            <?php $__empty_1 = true; $__currentLoopData = $oosList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $oos): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    class="bg-white rounded-lg shadow p-6 border-l-4
            <?php if($oos->severity == 'critical'): ?> border-red-500
            <?php elseif($oos->severity == 'high'): ?> border-orange-500
            <?php elseif($oos->severity == 'medium'): ?> border-yellow-500
            <?php else: ?> border-blue-500 <?php endif; ?>">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-lg font-semibold text-gray-900"><?php echo e($oos->oos_number); ?></span>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($oos->severity == 'critical'): ?> bg-red-100 text-red-800
                            <?php elseif($oos->severity == 'high'): ?> bg-orange-100 text-orange-800
                            <?php elseif($oos->severity == 'medium'): ?> bg-yellow-100 text-yellow-800
                            <?php else: ?> bg-blue-100 text-blue-800 <?php endif; ?>">
                                    <?php echo e($oos->severity_label); ?>

                                </span>
                                <span
                                    class="px-2 py-1 text-xs font-medium rounded-full
                            <?php if($oos->status == 'completed'): ?> bg-green-100 text-green-800
                            <?php elseif($oos->status == 'investigating'): ?> bg-blue-100 text-blue-800
                            <?php elseif($oos->status == 'closed'): ?> bg-gray-100 text-gray-800
                            <?php else: ?> bg-yellow-100 text-yellow-800 <?php endif; ?>">
                                    <?php echo e($oos->status_label); ?>

                                </span>
                            </div>
                            <div class="text-sm text-gray-600">
                                Type: <?php echo e($oos->type_label); ?> |
                                Days Open: <?php echo e($oos->days_open); ?>

                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <strong class="text-sm text-gray-700">Description:</strong>
                            <p class="text-sm text-gray-900 mt-1"><?php echo e($oos->description); ?></p>
                        </div>
                        <?php if($oos->root_cause): ?>
                            <div>
                                <strong class="text-sm text-gray-700">Root Cause:</strong>
                                <p class="text-sm text-gray-900 mt-1"><?php echo e($oos->root_cause); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if($oos->corrective_action || $oos->preventive_action): ?>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4 p-3 bg-gray-50 rounded">
                            <?php if($oos->corrective_action): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Corrective Action:</strong>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo e($oos->corrective_action); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if($oos->preventive_action): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Preventive Action:</strong>
                                    <p class="text-sm text-gray-900 mt-1"><?php echo e($oos->preventive_action); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="flex justify-between items-center text-sm text-gray-500">
                        <div>
                            <?php if($oos->batch): ?>
                                Batch: <?php echo e($oos->batch->batch_number); ?> |
                            <?php endif; ?>
                            Discovered: <?php echo e($oos->discovery_date->format('d M Y')); ?>

                        </div>
                        <div class="flex gap-2">
                            <?php if($oos->status == 'open'): ?>
                                <form method="POST" action="<?php echo e(route('cosmetic.qc.oos.complete', $oos)); ?>" class="inline">
                                    <?php echo csrf_field(); ?>
                                    <button type="submit"
                                        class="text-green-600 hover:text-green-900 font-medium">Complete</button>
                                </form>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="bg-white rounded-lg shadow p-12 text-center text-gray-400">
                    <p class="text-lg">No OOS investigations found. Great job!</p>
                </div>
            <?php endif; ?>
        </div>

        <?php if($oosList->hasPages()): ?>
            <div class="mt-4"><?php echo e($oosList->links()); ?></div>
        <?php endif; ?>
    </div>

    <!-- Add OOS Modal -->
    <div id="add-oos-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-[600px] shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Create OOS Investigation</h3>
            <form method="POST" action="<?php echo e(route('cosmetic.qc.oos.store')); ?>">
                <?php echo csrf_field(); ?>
                <div class="space-y-3">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">OOS Type *</label>
                            <select name="oos_type" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="laboratory">Laboratory</option>
                                <option value="manufacturing">Manufacturing</option>
                                <option value="stability">Stability</option>
                                <option value="complaint">Customer Complaint</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Severity *</label>
                            <select name="severity" required class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                                <option value="critical">Critical</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Description *</label>
                        <textarea name="description" rows="3" required class="w-full px-3 py-2 border border-gray-300 rounded-lg"></textarea>
                    </div>
                </div>
                <div class="mt-4 flex gap-2">
                    <button type="submit" class="flex-1 px-4 py-2 bg-orange-600 text-white rounded-lg hover:bg-orange-700">
                        Create OOS
                    </button>
                    <button type="button" onclick="document.getElementById('add-oos-modal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\qc\oos.blade.php ENDPATH**/ ?>