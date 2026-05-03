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
     <?php $__env->slot('header', null, []); ?> status == 'draft') bg-gray-100 text-gray-800
                        <?php elseif($formula->status == 'testing'): ?> bg-yellow-100 text-yellow-800
                        <?php elseif($formula->status == 'approved'): ?> bg-green-100 text-green-800
                        <?php elseif($formula->status == 'production'): ?> bg-blue-100 text-blue-800
                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                        <i class="fas fa-circle text-xs mr-1"></i><?php echo e($formula->status_label); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('cosmetic.formulas.index')); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 hover:bg-gray-700 text-white font-medium rounded-lg transition">
                <i class="fas fa-arrow-left mr-2"></i>Back to Formulas
            </a>
    </div>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Status Update -->
            <div class="mb-4 flex justify-end">
                <?php if($formula->isDraft() || $formula->isTesting()): ?>
                    <form method="POST" action="<?php echo e(route('cosmetic.formulas.update-status', $formula)); ?>"
                        class="inline-block">
                        <?php echo csrf_field(); ?>
                        <select name="status" onchange="this.form.submit()"
                            class="px-3 py-2 border border-gray-300 rounded-lg text-sm">
                            <option value="draft" <?php echo e($formula->status == 'draft' ? 'selected' : ''); ?>>Draft</option>
                            <option value="testing" <?php echo e($formula->status == 'testing' ? 'selected' : ''); ?>>In Testing
                            </option>
                            <option value="approved" <?php echo e($formula->status == 'approved' ? 'selected' : ''); ?>>Approved
                            </option>
                            <option value="production" <?php echo e($formula->status == 'production' ? 'selected' : ''); ?>>
                                Production</option>
                            <option value="discontinued">Discontinued</option>
                        </select>
                    </form>
                <?php endif; ?>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">
                        <i class="fas fa-weight mr-1"></i>Batch Size
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900">
                        <?php echo e(number_format($formula->batch_size, 2)); ?> <?php echo e($formula->batch_unit); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">
                        <i class="fas fa-flask mr-1"></i>Total Ingredients
                    </div>
                    <div class="mt-2 text-2xl font-bold text-gray-900"><?php echo e($ingredients->count()); ?>

                        items</div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">
                        <i class="fas fa-money-bill mr-1"></i>Total Cost
                    </div>
                    <div class="mt-2 text-2xl font-bold text-green-600">Rp
                        <?php echo e(number_format($totalCost, 0, ',', '.')); ?></div>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <div class="text-sm font-medium text-gray-500">
                        <i class="fas fa-calculator mr-1"></i>Cost per Unit
                    </div>
                    <div class="mt-2 text-2xl font-bold text-blue-600">Rp
                        <?php echo e(number_format($formula->cost_per_unit ?? 0, 0, ',', '.')); ?></div>
                </div>
            </div>

            <!-- Tabs -->
            <div x-data="{ activeTab: 'ingredients' }" class="space-y-6">
                <!-- Tab Navigation -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200">
                        <nav class="flex -mb-px">
                            <button @click="activeTab = 'ingredients'"
                                :class="activeTab === 'ingredients' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-flask mr-2"></i>Ingredients
                            </button>
                            <button @click="activeTab = 'versions'"
                                :class="activeTab === 'versions' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-code-branch mr-2"></i>Versions (<?php echo e($versions->count()); ?>)
                            </button>
                            <button @click="activeTab = 'stability'"
                                :class="activeTab === 'stability' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-vial mr-2"></i>Stability Tests
                            </button>
                            <button @click="activeTab = 'info'"
                                :class="activeTab === 'info' ? 'border-blue-500 text-blue-600' :
                                    'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'"
                                class="w-1/4 py-4 px-1 text-center border-b-2 font-medium text-sm transition">
                                <i class="fas fa-info-circle mr-2"></i>Information
                            </button>
                        </nav>
                    </div>

                    <!-- Ingredients Tab -->
                    <div x-show="activeTab === 'ingredients'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-flask mr-2 text-purple-600"></i>Formula Ingredients
                        </h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            #</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            INCI Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Common Name</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Quantity</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            %</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Function</th>
                                        <th
                                            class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase">
                                            Phase</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php $__currentLoopData = $ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $ingredient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <tr>
                                            <td class="px-4 py-3 text-sm text-gray-500">
                                                <?php echo e($ingredient->sort_order); ?></td>
                                            <td class="px-4 py-3">
                                                <div class="text-sm font-medium text-gray-900">
                                                    <?php echo e($ingredient->inci_name); ?></div>
                                                <?php if($ingredient->cas_number): ?>
                                                    <a href="<?php echo e($ingredient->cas_number_link); ?>" target="_blank"
                                                        class="text-xs text-blue-600 hover:underline">
                                                        CAS: <?php echo e($ingredient->cas_number); ?>

                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <?php echo e($ingredient->common_name ?? '-'); ?></td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <?php echo e(number_format($ingredient->quantity, 3)); ?> <?php echo e($ingredient->unit); ?>

                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-900">
                                                <?php echo e($ingredient->percentage ? number_format($ingredient->percentage, 2) . '%' : '-'); ?>

                                            </td>
                                            <td class="px-4 py-3">
                                                <span
                                                    class="px-2 py-1 text-xs font-medium rounded-full
                                                    <?php if($ingredient->function == 'active'): ?> bg-red-100 text-red-800
                                                    <?php elseif($ingredient->function == 'preservative'): ?> bg-yellow-100 text-yellow-800
                                                    <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                                    <?php echo e($ingredient->function_label); ?>

                                                </span>
                                            </td>
                                            <td class="px-4 py-3 text-sm text-gray-600">
                                                <?php echo e($ingredient->phase_label); ?></td>
                                        </tr>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </tbody>
                            </table>
                        </div>
                        <div
                            class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                            <div class="text-sm text-blue-900">
                                <i class="fas fa-weight-hanging mr-2"></i><strong>Total Quantity:</strong>
                                <?php echo e(number_format($totalQuantity, 3)); ?> <?php echo e($formula->batch_unit); ?>

                            </div>
                        </div>
                    </div>

                    <!-- Versions Tab -->
                    <div x-show="activeTab === 'versions'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-code-branch mr-2 text-blue-600"></i>Formula Versions
                        </h3>
                        <?php if($versions->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $versions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $version): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <h4 class="text-lg font-semibold text-blue-600">
                                                <?php echo e($version->version_formatted); ?></h4>
                                            <span
                                                class="text-xs text-gray-500"><?php echo e($version->created_at->format('d M Y H:i')); ?></span>
                                        </div>
                                        <?php if($version->changes_summary): ?>
                                            <div class="mb-2">
                                                <strong
                                                    class="text-sm text-gray-700">Changes:</strong>
                                                <p class="text-sm text-gray-600">
                                                    <?php echo e($version->changes_summary); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($version->reason_for_change): ?>
                                            <div class="mb-2">
                                                <strong
                                                    class="text-sm text-gray-700">Reason:</strong>
                                                <p class="text-sm text-gray-600">
                                                    <?php echo e($version->reason_for_change); ?></p>
                                            </div>
                                        <?php endif; ?>
                                        <?php if($version->changer): ?>
                                            <div class="text-xs text-gray-500">
                                                <i class="fas fa-user mr-1"></i>Changed by:
                                                <?php echo e($version->changer->name); ?>

                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-code-branch text-4xl mb-2"></i>
                                <p>No versions yet. Versions are created when formula is approved.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Stability Tests Tab -->
                    <div x-show="activeTab === 'stability'" class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">
                                <i class="fas fa-vial mr-2 text-green-600"></i>Stability Tests
                            </h3>
                            <?php if($formula->isTesting() || $formula->isApproved()): ?>
                                <button onclick="document.getElementById('add-test-modal').classList.remove('hidden')"
                                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                                    <i class="fas fa-plus mr-1"></i>Add Test
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if($stabilityTests->count() > 0): ?>
                            <div class="space-y-4">
                                <?php $__currentLoopData = $stabilityTests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <div
                                        class="border border-gray-200 rounded-lg p-4">
                                        <div class="flex justify-between items-start mb-2">
                                            <div>
                                                <span
                                                    class="text-sm font-medium text-gray-900"><?php echo e($test->test_code); ?></span>
                                                <span
                                                    class="ml-2 px-2 py-1 text-xs font-medium rounded-full
                                                    <?php if($test->test_type == 'accelerated'): ?> bg-orange-100 text-orange-800
                                                    <?php elseif($test->test_type == 'real_time'): ?> bg-blue-100 text-blue-800
                                                    <?php else: ?> bg-purple-100 text-purple-800 <?php endif; ?>">
                                                    <?php echo e($test->test_type_label); ?>

                                                </span>
                                            </div>
                                            <span
                                                class="px-2 py-1 text-xs font-medium rounded-full
                                                <?php if($test->status == 'in_progress'): ?> bg-blue-100 text-blue-800
                                                <?php elseif($test->status == 'completed'): ?> bg-green-100 text-green-800
                                                <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                                <?php echo e($test->status_label); ?>

                                            </span>
                                        </div>
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-2 text-sm mt-3">
                                            <div>
                                                <span class="text-gray-500">Start:</span>
                                                <span
                                                    class="text-gray-900"><?php echo e($test->start_date->format('d M Y')); ?></span>
                                            </div>
                                            <?php if($test->expected_end_date): ?>
                                                <div>
                                                    <span class="text-gray-500">Expected End:</span>
                                                    <span
                                                        class="text-gray-900"><?php echo e($test->expected_end_date->format('d M Y')); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($test->initial_ph): ?>
                                                <div>
                                                    <span class="text-gray-500">Initial pH:</span>
                                                    <span
                                                        class="text-gray-900"><?php echo e($test->initial_ph); ?></span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if($test->overall_result): ?>
                                                <div>
                                                    <span class="text-gray-500">Result:</span>
                                                    <span
                                                        class="font-medium
                                                        <?php if($test->overall_result == 'Pass'): ?> text-green-600
                                                        <?php else: ?> text-red-600 <?php endif; ?>">
                                                        <?php echo e($test->overall_result); ?>

                                                    </span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-8 text-gray-400">
                                <i class="fas fa-vial text-4xl mb-2"></i>
                                <p>No stability tests yet. Add a test to begin stability monitoring.</p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Information Tab -->
                    <div x-show="activeTab === 'info'" class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-info-circle mr-2 text-blue-600"></i>Formula Information
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <strong class="text-sm text-gray-700">Product Type:</strong>
                                <p class="text-gray-900"><?php echo e(ucfirst($formula->product_type)); ?></p>
                            </div>
                            <?php if($formula->target_ph): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Target pH:</strong>
                                    <p class="text-gray-900"><?php echo e($formula->target_ph); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if($formula->actual_ph): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Actual pH:</strong>
                                    <p
                                        class="text-gray-900 <?php echo e($formula->isPhWithinRange() ? 'text-green-600' : 'text-red-600'); ?>">
                                        <?php echo e($formula->actual_ph); ?>

                                        <?php if($formula->isPhWithinRange()): ?>
                                            <span class="text-xs text-green-600"><i
                                                    class="fas fa-check ml-1"></i>Within range</span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                            <?php if($formula->shelf_life_months): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Shelf Life:</strong>
                                    <p class="text-gray-900"><?php echo e($formula->shelf_life_months); ?> months
                                    </p>
                                </div>
                            <?php endif; ?>
                            <div>
                                <strong class="text-sm text-gray-700">Created By:</strong>
                                <p class="text-gray-900"><?php echo e($formula->creator->name ?? 'Unknown'); ?>

                                </p>
                            </div>
                            <?php if($formula->approved_by): ?>
                                <div>
                                    <strong class="text-sm text-gray-700">Approved By:</strong>
                                    <p class="text-gray-900">
                                        <?php echo e($formula->approver->name ?? 'Unknown'); ?></p>
                                    <p class="text-xs text-gray-500">
                                        <?php echo e($formula->approved_at->format('d M Y H:i')); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>
                        <?php if($formula->notes): ?>
                            <div class="mt-4 p-4 bg-gray-50 rounded-lg">
                                <strong class="text-sm text-gray-700">Notes:</strong>
                                <p class="text-gray-900 mt-1"><?php echo e($formula->notes); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Add Test Modal -->
            <div id="add-test-modal"
                class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900">
                        <i class="fas fa-vial mr-2 text-green-600"></i>Add Stability Test
                    </h3>
                    <form method="POST" action="<?php echo e(route('cosmetic.formulas.stability-test.add', $formula)); ?>">
                        <?php echo csrf_field(); ?>
                        <div class="space-y-3">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Test
                                    Type</label>
                                <select name="test_type" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                                    <option value="accelerated">Accelerated Stability</option>
                                    <option value="real_time">Real-Time Stability</option>
                                    <option value="freeze_thaw">Freeze-Thaw Cycle</option>
                                    <option value="photostability">Photostability</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Start
                                    Date</label>
                                <input type="date" name="start_date" value="<?php echo e(date('Y-m-d')); ?>" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-1">Storage
                                    Conditions</label>
                                <input type="text" name="storage_conditions"
                                    placeholder="e.g., 40°C ± 2°C / 75% RH" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg">
                            </div>
                        </div>
                        <div class="mt-4 flex gap-2">
                            <button type="submit"
                                class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Create Test
                            </button>
                            <button type="button"
                                onclick="document.getElementById('add-test-modal').classList.add('hidden')"
                                class="flex-1 px-4 py-2 bg-gray-300 text-gray-700 rounded-lg hover:bg-gray-400">
                                <i class="fas fa-times mr-2"></i>Cancel
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\formulas\show.blade.php ENDPATH**/ ?>