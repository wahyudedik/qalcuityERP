

<?php $__env->startSection('title', 'Create Batch'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6">
            <a href="<?php echo e(route('cosmetic.batches.index')); ?>" class="text-blue-600 hover:text-blue-900 mb-2 inline-block">
                ← Back to Batches
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Create New Batch</h1>
            <p class="mt-1 text-sm text-gray-500">Record a new production batch</p>
        </div>

        <!-- Success/Error Messages -->
        <?php if(session('success')): ?>
            <div class="mb-4 bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if(session('error')): ?>
            <div class="mb-4 bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg">
                <?php echo e(session('error')); ?>

            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('cosmetic.batches.store')); ?>">
            <?php echo csrf_field(); ?>

            <div class="space-y-6">
                <!-- Batch Information -->
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-900 mb-4">Batch Information</h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Formula <span class="text-red-500">*</span>
                            </label>
                            <select name="formula_id" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['formula_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                onchange="updateFormulaInfo(this.value)">
                                <option value="">Select Formula</option>
                                <?php $__currentLoopData = $formulas; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $formula): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($formula->id); ?>"
                                        <?php echo e(old('formula_id') == $formula->id || (isset($selectedFormula) && $selectedFormula->id == $formula->id) ? 'selected' : ''); ?>

                                        data-code="<?php echo e($formula->formula_code); ?>" data-type="<?php echo e($formula->product_type); ?>"
                                        data-batch-size="<?php echo e($formula->batch_size); ?>"
                                        data-batch-unit="<?php echo e($formula->batch_unit); ?>">
                                        <?php echo e($formula->formula_name); ?> (<?php echo e($formula->formula_code); ?>)
                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['formula_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

                            <!-- Formula Info Display -->
                            <div id="formula-info" class="mt-2 p-3 bg-blue-50 rounded-lg hidden">
                                <div class="text-sm text-blue-900">
                                    <div><strong>Code:</strong> <span id="info-code">-</span></div>
                                    <div><strong>Type:</strong> <span id="info-type">-</span></div>
                                    <div><strong>Standard Batch:</strong> <span id="info-batch">-</span></div>
                                </div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Batch Number
                            </label>
                            <input type="text" name="batch_number" value="<?php echo e(old('batch_number', 'Auto-generated')); ?>"
                                readonly
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg bg-gray-50 text-gray-500">
                            <p class="mt-1 text-xs text-gray-500">Auto-generated (BMR-YYYY-NNNN)</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Production Date <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="production_date"
                                value="<?php echo e(old('production_date', date('Y-m-d'))); ?>" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['production_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['production_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Expiry Date
                            </label>
                            <input type="date" name="expiry_date" value="<?php echo e(old('expiry_date')); ?>"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">Optional - based on shelf life</p>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">
                                Planned Quantity <span class="text-red-500">*</span>
                            </label>
                            <input type="number" name="planned_quantity" value="<?php echo e(old('planned_quantity')); ?>"
                                step="0.01" min="0" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['planned_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                placeholder="e.g., 1000">
                            <?php $__errorArgs = ['planned_quantity'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 mb-1">
                            Production Notes
                        </label>
                        <textarea name="production_notes" rows="4"
                            class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500"
                            placeholder="Any special instructions, equipment used, operator notes..."><?php echo e(old('production_notes')); ?></textarea>
                    </div>
                </div>

                <!-- Quick Info -->
                <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                    <div class="flex">
                        <svg class="w-5 h-5 text-yellow-600 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                clip-rule="evenodd" />
                        </svg>
                        <div class="text-sm text-yellow-800">
                            <strong>Next Steps After Creation:</strong>
                            <ul class="mt-1 ml-4 list-disc space-y-1">
                                <li>Update status to "In Progress" when starting production</li>
                                <li>Record actual quantity produced</li>
                                <li>Add quality checks at each checkpoint</li>
                                <li>Handle any rework if needed</li>
                                <li>Release batch when all QC passed</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Submit Buttons -->
                <div class="flex gap-4">
                    <button type="submit"
                        class="flex-1 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                        Create Batch Record
                    </button>
                    <a href="<?php echo e(route('cosmetic.batches.index')); ?>"
                        class="px-6 py-3 bg-gray-300 hover:bg-gray-400 text-gray-700 font-medium rounded-lg text-center transition">
                        Cancel
                    </a>
                </div>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function updateFormulaInfo(formulaId) {
                const select = document.querySelector('select[name="formula_id"]');
                const selectedOption = select.options[select.selectedIndex];
                const infoDiv = document.getElementById('formula-info');

                if (formulaId && selectedOption.dataset.code) {
                    document.getElementById('info-code').textContent = selectedOption.dataset.code;
                    document.getElementById('info-type').textContent = selectedOption.dataset.type;
                    document.getElementById('info-batch').textContent = selectedOption.dataset.batchSize + ' ' + selectedOption
                        .dataset.batchUnit;
                    infoDiv.classList.remove('hidden');
                } else {
                    infoDiv.classList.add('hidden');
                }
            }

            // Initialize on page load if formula is pre-selected
            document.addEventListener('DOMContentLoaded', function() {
                const select = document.querySelector('select[name="formula_id"]');
                if (select.value) {
                    updateFormulaInfo(select.value);
                }
            });
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\batches\create.blade.php ENDPATH**/ ?>