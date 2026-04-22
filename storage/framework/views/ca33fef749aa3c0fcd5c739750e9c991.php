

<?php $__env->startSection('title', 'Label ' . $label->label_code); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('cosmetic.packaging.labels')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Labels
            </a>
            <div class="flex justify-between items-start">
                <div>
                    <div class="flex items-center gap-3">
                        <h1 class="text-3xl font-bold text-gray-900"><?php echo e($label->label_code); ?></h1>
                        <span
                            class="px-3 py-1 text-sm font-medium rounded-full
                        <?php if($label->status == 'active'): ?> bg-green-100 text-green-800
                        <?php elseif($label->status == 'approved'): ?> bg-blue-100 text-blue-800
                        <?php elseif($label->status == 'in_review'): ?> bg-orange-100 text-orange-800
                        <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                            <?php echo e($label->status_label); ?>

                        </span>
                    </div>
                    <p class="mt-1 text-sm text-gray-500"><?php echo e($label->product->formula_name ?? 'No Product'); ?> -
                        <?php echo e($label->type_label); ?></p>
                </div>
                <div class="flex gap-2">
                    <?php if($label->status == 'draft'): ?>
                        <form method="POST" action="<?php echo e(route('cosmetic.packaging.labels.submit', $label->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                                Submit for Review
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($label->status == 'approved'): ?>
                        <form method="POST" action="<?php echo e(route('cosmetic.packaging.labels.activate', $label->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white font-medium rounded-lg transition">
                                Activate Label
                            </button>
                        </form>
                    <?php endif; ?>
                    <?php if($label->status == 'active'): ?>
                        <form method="POST" action="<?php echo e(route('cosmetic.packaging.labels.archive', $label->id)); ?>">
                            <?php echo csrf_field(); ?>
                            <button type="submit"
                                class="px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                                Archive
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Success/Error Messages -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left Column - Label Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Label Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📋 Label Information</h2>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm text-gray-500">Version</label>
                            <p class="text-lg font-semibold text-gray-900"><?php echo e($label->version_number); ?></p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Type</label>
                            <p class="text-lg font-semibold text-gray-900"><?php echo e($label->type_label); ?></p>
                        </div>
                        <?php if($label->barcode): ?>
                            <div>
                                <label class="text-sm text-gray-500">Barcode</label>
                                <p class="text-lg font-mono font-semibold text-gray-900"><?php echo e($label->barcode); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if($label->qr_code): ?>
                            <div>
                                <label class="text-sm text-gray-500">QR Code</label>
                                <p class="text-lg font-mono text-gray-900"><?php echo e($label->qr_code); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if($label->effective_date): ?>
                            <div>
                                <label class="text-sm text-gray-500">Effective Date</label>
                                <p class="text-lg font-semibold text-gray-900">
                                    <?php echo e($label->effective_date->format('d M Y')); ?></p>
                            </div>
                        <?php endif; ?>
                        <?php if($label->expiry_date): ?>
                            <div>
                                <label class="text-sm text-gray-500">Expiry Date</label>
                                <p
                                    class="text-lg font-semibold <?php echo e($label->expiry_date->isPast() ? 'text-red-600' : 'text-gray-900'); ?>">
                                    <?php echo e($label->expiry_date->format('d M Y')); ?>

                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php if($label->label_content): ?>
                        <div class="mt-4 pt-4 border-t border-gray-200">
                            <label class="text-sm text-gray-500">Label Content</label>
                            <div class="mt-2 p-4 bg-gray-50 rounded-lg">
                                <pre class="text-sm text-gray-900 whitespace-pre-wrap"><?php echo e($label->label_content); ?></pre>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Compliance Checks -->
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-lg font-semibold text-gray-900">✅ Compliance Checks</h2>
                        <button onclick="document.getElementById('add-check-modal').classList.remove('hidden')"
                            class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded transition">
                            Add Check
                        </button>
                    </div>

                    <?php
                        $compliance = $label->compliance_status;
                    ?>

                    <!-- Compliance Summary -->
                    <div class="grid grid-cols-4 gap-3 mb-4">
                        <div class="p-3 bg-blue-50 rounded-lg">
                            <p class="text-xs text-blue-600">Total</p>
                            <p class="text-2xl font-bold text-blue-900"><?php echo e($compliance['total']); ?></p>
                        </div>
                        <div class="p-3 bg-green-50 rounded-lg">
                            <p class="text-xs text-green-600">Compliant</p>
                            <p class="text-2xl font-bold text-green-900"><?php echo e($compliance['compliant']); ?></p>
                        </div>
                        <div class="p-3 bg-red-50 rounded-lg">
                            <p class="text-xs text-red-600">Non-Compliant</p>
                            <p class="text-2xl font-bold text-red-900"><?php echo e($compliance['non_compliant']); ?></p>
                        </div>
                        <div class="p-3 bg-orange-50 rounded-lg">
                            <p class="text-xs text-orange-600">Pending</p>
                            <p class="text-2xl font-bold text-orange-900"><?php echo e($compliance['pending']); ?></p>
                        </div>
                    </div>

                    <!-- Compliance Checks List -->
                    <div class="space-y-3">
                        <?php $__empty_1 = true; $__currentLoopData = $label->complianceChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <div class="border border-gray-200 rounded-lg p-4">
                                <div class="flex justify-between items-start">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2">
                                            <h3 class="font-medium text-gray-900"><?php echo e($check->check_name); ?></h3>
                                            <span
                                                class="px-2 py-0.5 text-xs rounded 
                                            <?php if($check->check_category == 'mandatory'): ?> bg-red-100 text-red-800
                                            <?php elseif($check->check_category == 'regulatory'): ?> bg-orange-100 text-orange-800
                                            <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                                <?php echo e($check->category_label); ?>

                                            </span>
                                        </div>
                                        <p class="text-sm text-gray-600 mt-1"><?php echo e($check->requirement); ?></p>
                                        <?php if($check->findings): ?>
                                            <p class="text-sm text-gray-700 mt-2">
                                                <span class="font-medium">Findings:</span> <?php echo e($check->findings); ?>

                                            </p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <?php if($check->is_compliant === null): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-gray-100 text-gray-800">Not
                                                Checked</span>
                                        <?php elseif($check->is_compliant): ?>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-green-100 text-green-800">Compliant</span>
                                        <?php else: ?>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full bg-red-100 text-red-800">Non-Compliant</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php if($check->is_compliant === null): ?>
                                    <div class="mt-3 pt-3 border-t border-gray-200 flex gap-2">
                                        <form method="POST"
                                            action="<?php echo e(route('cosmetic.packaging.compliance.update', $check->id)); ?>"
                                            class="inline">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="is_compliant" value="1">
                                            <button type="submit"
                                                class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded transition">
                                                ✓ Pass
                                            </button>
                                        </form>
                                        <button
                                            onclick="document.getElementById('fail-check-<?php echo e($check->id); ?>').classList.remove('hidden')"
                                            class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded transition">
                                            ✗ Fail
                                        </button>
                                    </div>
                                    <!-- Fail Modal -->
                                    <div id="fail-check-<?php echo e($check->id); ?>" class="hidden mt-3 p-3 bg-red-50 rounded-lg">
                                        <form method="POST"
                                            action="<?php echo e(route('cosmetic.packaging.compliance.update', $check->id)); ?>">
                                            <?php echo csrf_field(); ?>
                                            <input type="hidden" name="is_compliant" value="0">
                                            <textarea name="findings" required placeholder="Reason for failure..." rows="2"
                                                class="w-full rounded-lg border-gray-300 focus:border-red-500 focus:ring-red-500 mb-2"></textarea>
                                            <div class="flex gap-2 justify-end">
                                                <button type="button"
                                                    onclick="document.getElementById('fail-check-<?php echo e($check->id); ?>').classList.add('hidden')"
                                                    class="px-3 py-1 bg-gray-200 hover:bg-gray-300 text-gray-800 text-sm font-medium rounded transition">
                                                    Cancel
                                                </button>
                                                <button type="submit"
                                                    class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded transition">
                                                    Mark as Failed
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <div class="text-center text-gray-500 py-8">
                                <p>No compliance checks added yet</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Column - Metadata -->
            <div class="space-y-6">
                <!-- Approval Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">👤 Approval Info</h2>
                    <?php if($label->approved_by): ?>
                        <div class="space-y-2">
                            <div>
                                <label class="text-sm text-gray-500">Approved By</label>
                                <p class="text-sm font-medium text-gray-900"><?php echo e($label->approved_by); ?></p>
                            </div>
                            <div>
                                <label class="text-sm text-gray-500">Approved At</label>
                                <p class="text-sm font-medium text-gray-900">
                                    <?php echo e($label->approved_at->format('d M Y, H:i')); ?></p>
                            </div>
                            <?php if($label->approval_notes): ?>
                                <div>
                                    <label class="text-sm text-gray-500">Notes</label>
                                    <p class="text-sm text-gray-900"><?php echo e($label->approval_notes); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php else: ?>
                        <p class="text-gray-500 text-sm">Not yet approved</p>
                    <?php endif; ?>
                </div>

                <!-- Created Info -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">📅 Created</h2>
                    <div class="space-y-2">
                        <div>
                            <label class="text-sm text-gray-500">Created At</label>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($label->created_at->format('d M Y, H:i')); ?>

                            </p>
                        </div>
                        <div>
                            <label class="text-sm text-gray-500">Last Updated</label>
                            <p class="text-sm font-medium text-gray-900"><?php echo e($label->updated_at->format('d M Y, H:i')); ?>

                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Compliance Check Modal -->
    <div id="add-check-modal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-full max-w-2xl shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Add Compliance Check</h3>
                <button onclick="document.getElementById('add-check-modal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="<?php echo e(route('cosmetic.packaging.compliance.store', $label->id)); ?>"
                class="space-y-4">
                <?php echo csrf_field(); ?>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Check Name *</label>
                    <input type="text" name="check_name" required
                        placeholder="e.g., Ingredient List, Net Weight, BPOM Number"
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Category *</label>
                    <select name="check_category" required
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Select Category</option>
                        <option value="mandatory">Mandatory</option>
                        <option value="regulatory">Regulatory</option>
                        <option value="optional">Optional</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Requirement *</label>
                    <textarea name="requirement" required rows="3" placeholder="What must be included/complied with..."
                        class="w-full rounded-lg border-gray-300 focus:border-blue-500 focus:ring-blue-500"></textarea>
                </div>

                <div class="flex gap-2 justify-end">
                    <button type="button" onclick="document.getElementById('add-check-modal').classList.add('hidden')"
                        class="px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Add Check
                    </button>
                </div>
            </form>
        </div>
    </div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\packaging\label-show.blade.php ENDPATH**/ ?>