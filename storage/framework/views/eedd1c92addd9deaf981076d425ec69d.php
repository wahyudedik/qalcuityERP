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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Create QC Test Template')); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('qc.templates.index')); ?>"
            class="bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg">
            <i class="fas fa-arrow-left mr-2"></i>Back to List
        </a>
    </div>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="<?php echo e(route('qc.templates.store')); ?>" method="POST" x-data="templateForm()">
                    <?php echo csrf_field(); ?>

                    <!-- Template Name -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Template Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" value="<?php echo e(old('name')); ?>" required
                            class="w-full rounded-md border-gray-300 <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                            placeholder="e.g., Incoming Material Inspection">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Product Type -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Product Type
                        </label>
                        <input type="text" name="product_type" value="<?php echo e(old('product_type')); ?>"
                            class="w-full rounded-md border-gray-300"
                            placeholder="e.g., Electronics, Textiles (leave blank for all types)">
                    </div>

                    <!-- Stage -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Inspection Stage <span class="text-red-500">*</span>
                        </label>
                        <select name="stage" required
                            class="w-full rounded-md border-gray-300 <?php $__errorArgs = ['stage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="">Select Stage</option>
                            <option value="incoming" <?php echo e(old('stage') == 'incoming' ? 'selected' : ''); ?>>Incoming
                                Material</option>
                            <option value="in-process" <?php echo e(old('stage') == 'in-process' ? 'selected' : ''); ?>>In-Process
                            </option>
                            <option value="final" <?php echo e(old('stage') == 'final' ? 'selected' : ''); ?>>Final Inspection
                            </option>
                        </select>
                        <?php $__errorArgs = ['stage'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Sample Size Formula -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Sample Size Formula <span class="text-red-500">*</span>
                        </label>
                        <select name="sample_size_formula" required
                            class="w-full rounded-md border-gray-300 <?php $__errorArgs = ['sample_size_formula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="1" <?php echo e(old('sample_size_formula', 1) == 1 ? 'selected' : ''); ?>>√n
                                (Square Root of Lot Size)</option>
                            <option value="2" <?php echo e(old('sample_size_formula') == 2 ? 'selected' : ''); ?>>10% of Lot
                                Size</option>
                            <option value="3" <?php echo e(old('sample_size_formula') == 3 ? 'selected' : ''); ?>>5% of Lot
                                Size (min 3)</option>
                        </select>
                        <?php $__errorArgs = ['sample_size_formula'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- AQL -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Acceptance Quality Limit (AQL %) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="acceptance_quality_limit" step="0.01" min="0"
                            max="100" value="<?php echo e(old('acceptance_quality_limit', '2.50')); ?>" required
                            class="w-full rounded-md border-gray-300 <?php $__errorArgs = ['acceptance_quality_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['acceptance_quality_limit'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Test Parameters -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Test Parameters <span class="text-red-500">*</span>
                        </label>

                        <template x-for="(param, index) in parameters" :key="index">
                            <div class="mb-3 p-4 border rounded-lg relative">
                                <button type="button" x-show="parameters.length > 1" @click="removeParameter(index)"
                                    class="absolute top-2 right-2 text-red-500 hover:text-red-700">
                                    <i class="fas fa-times"></i>
                                </button>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Parameter
                                            Name *</label>
                                        <input type="text" :name="`test_parameters[${index}][name]`"
                                            x-model="param.name" required
                                            class="w-full rounded-md border-gray-300 text-sm"
                                            placeholder="e.g., Dimension, Weight">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Unit</label>
                                        <input type="text" :name="`test_parameters[${index}][unit]`"
                                            x-model="param.unit"
                                            class="w-full rounded-md border-gray-300 text-sm"
                                            placeholder="e.g., mm, kg, °C">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Min
                                            Value</label>
                                        <input type="number" step="any" :name="`test_parameters[${index}][min]`"
                                            x-model="param.min"
                                            class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs text-gray-600 mb-1">Max
                                            Value</label>
                                        <input type="number" step="any" :name="`test_parameters[${index}][max]`"
                                            x-model="param.max"
                                            class="w-full rounded-md border-gray-300 text-sm">
                                    </div>
                                </div>
                                <div class="mt-2">
                                    <label class="inline-flex items-center">
                                        <input type="hidden" :name="`test_parameters[${index}][critical]`"
                                            value="0">
                                        <input type="checkbox" :name="`test_parameters[${index}][critical]`"
                                            value="1" x-model="param.critical"
                                            class="rounded border-gray-300 text-blue-600">
                                        <span class="ml-2 text-sm text-gray-700">Critical
                                            parameter</span>
                                    </label>
                                </div>
                            </div>
                        </template>

                        <button type="button" @click="addParameter()"
                            class="text-blue-600 hover:text-blue-800 text-sm">
                            <i class="fas fa-plus mr-1"></i>Add Parameter
                        </button>

                        <?php $__errorArgs = ['test_parameters'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Instructions -->
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Instructions
                        </label>
                        <textarea name="instructions" rows="3"
                            class="w-full rounded-md border-gray-300"
                            placeholder="Step-by-step inspection instructions (optional)"><?php echo e(old('instructions')); ?></textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="mb-6">
                        <label class="inline-flex items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" checked
                                class="rounded border-gray-300 text-blue-600">
                            <span class="ml-2 text-sm text-gray-700">Active</span>
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg flex-1">
                            <i class="fas fa-save mr-2"></i>Create Template
                        </button>
                        <a href="<?php echo e(route('qc.templates.index')); ?>"
                            class="bg-gray-200 hover:bg-gray-300 px-6 py-2 rounded-lg text-center">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function templateForm() {
                return {
                    parameters: [{
                        name: '',
                        unit: '',
                        min: '',
                        max: '',
                        critical: false
                    }],
                    addParameter() {
                        this.parameters.push({
                            name: '',
                            unit: '',
                            min: '',
                            max: '',
                            critical: false
                        });
                    },
                    removeParameter(index) {
                        this.parameters.splice(index, 1);
                    }
                };
            }
        </script>
    <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\qc\templates\create.blade.php ENDPATH**/ ?>