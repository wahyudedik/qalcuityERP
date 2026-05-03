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
        <?php echo e(__('Generate Vouchers')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Generate Vouchers')); ?></h1>
                    <p class="text-gray-600 mt-1"><?php echo e(__('Create new voucher codes for customers')); ?>

                    </p>
                </div>
                <a href="<?php echo e(route('telecom.vouchers.index')); ?>"
                    class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                    <?php echo e(__('Back to List')); ?>

                </a>
            </div>

            <?php if($errors->any()): ?>
                <div
                    class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Generate Form -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form action="<?php echo e(route('telecom.vouchers.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>

                    <!-- Package Selection -->
                    <div class="mb-6">
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('Internet Package')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <option value=""><?php echo e(__('Select Package...')); ?></option>
                            <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($package->id); ?>"
                                    <?php echo e(old('package_id') == $package->id ? 'selected' : ''); ?>>
                                    <?php echo e($package->name); ?> -
                                    <?php echo e($package->download_speed_mbps); ?>/<?php echo e($package->upload_speed_mbps); ?> Mbps
                                    <?php if($package->quota_bytes): ?>
                                        (<?php echo e(round($package->quota_bytes / 1073741824, 2)); ?> GB)
                                    <?php else: ?>
                                        (<?php echo e(__('Unlimited')); ?>)
                                    <?php endif; ?>
                                    - Rp <?php echo e(number_format($package->price, 0, ',', '.')); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['package_id'];
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

                    <!-- Quantity -->
                    <div class="mb-6">
                        <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('Quantity')); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="quantity" id="quantity" min="1" max="1000"
                            value="<?php echo e(old('quantity', 10)); ?>" required
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            <?php echo e(__('Max 1000 vouchers per batch')); ?></p>
                        <?php $__errorArgs = ['quantity'];
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

                    <!-- Code Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="code_length"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(__('Code Length')); ?>

                            </label>
                            <input type="number" name="code_length" id="code_length" min="6" max="16"
                                value="<?php echo e(old('code_length', 8)); ?>"
                                class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500"><?php echo e(__('6-16 characters')); ?></p>
                        </div>

                        <div>
                            <label for="code_pattern"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(__('Code Pattern')); ?>

                            </label>
                            <select name="code_pattern" id="code_pattern"
                                class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="alphanumeric"
                                    <?php echo e(old('code_pattern') === 'alphanumeric' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Alphanumeric (A-Z, 0-9)')); ?>

                                </option>
                                <option value="numeric" <?php echo e(old('code_pattern') === 'numeric' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Numeric Only (0-9)')); ?>

                                </option>
                                <option value="alphabetic"
                                    <?php echo e(old('code_pattern') === 'alphabetic' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Alphabetic Only (A-Z)')); ?>

                                </option>
                            </select>
                        </div>
                    </div>

                    <!-- Validity & Usage -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label for="validity_hours"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(__('Validity Period (Hours)')); ?>

                            </label>
                            <input type="number" name="validity_hours" id="validity_hours" min="1"
                                value="<?php echo e(old('validity_hours', 24)); ?>"
                                class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500"><?php echo e(__('Default: 24 hours')); ?></p>
                        </div>

                        <div>
                            <label for="max_usage"
                                class="block text-sm font-medium text-gray-700 mb-2">
                                <?php echo e(__('Max Usage Count')); ?>

                            </label>
                            <input type="number" name="max_usage" id="max_usage" min="1"
                                value="<?php echo e(old('max_usage', 1)); ?>"
                                class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <p class="mt-1 text-xs text-gray-500">
                                <?php echo e(__('How many times can be used')); ?></p>
                        </div>
                    </div>

                    <!-- Sale Price -->
                    <div class="mb-6">
                        <label for="sale_price" class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('Sale Price (Optional)')); ?>

                        </label>
                        <div class="relative">
                            <span class="absolute left-3 top-2 text-gray-500">Rp</span>
                            <input type="number" name="sale_price" id="sale_price" min="0" step="1000"
                                value="<?php echo e(old('sale_price')); ?>"
                                class="w-full pl-12 border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                        <p class="mt-1 text-xs text-gray-500">
                            <?php echo e(__('Leave empty to use package price')); ?></p>
                    </div>

                    <!-- Batch Number -->
                    <div class="mb-6">
                        <label for="batch_number"
                            class="block text-sm font-medium text-gray-700 mb-2">
                            <?php echo e(__('Batch Number (Optional)')); ?>

                        </label>
                        <input type="text" name="batch_number" id="batch_number"
                            value="<?php echo e(old('batch_number', 'BATCH-' . now()->format('Ymd'))); ?>"
                            class="w-full border-gray-300 bg-white text-gray-900 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <p class="mt-1 text-xs text-gray-500">
                            <?php echo e(__('For grouping vouchers together')); ?></p>
                    </div>

                    <!-- Preview -->
                    <div
                        class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                        <h3 class="text-sm font-semibold text-blue-900 mb-2"><?php echo e(__('Preview:')); ?>

                        </h3>
                        <ul class="text-xs text-blue-800 space-y-1">
                            <li>• <?php echo e(__('Package')); ?>: <span id="preview_package">-</span></li>
                            <li>• <?php echo e(__('Quantity')); ?>: <span id="preview_quantity">10</span> <?php echo e(__('vouchers')); ?>

                            </li>
                            <li>• <?php echo e(__('Code Format')); ?>: <span
                                    id="preview_code"><?php echo e(__('8 chars, alphanumeric')); ?></span></li>
                            <li>• <?php echo e(__('Valid For')); ?>: <span id="preview_validity"><?php echo e(__('24 hours')); ?></span></li>
                            <li>• <?php echo e(__('Batch')); ?>: <span id="preview_batch">-</span></li>
                        </ul>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex gap-3">
                        <button type="submit"
                            class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-semibold">
                            <i class="fas fa-ticket-alt mr-2"></i> <?php echo e(__('Generate Vouchers')); ?>

                        </button>
                        <a href="<?php echo e(route('telecom.vouchers.index')); ?>"
                            class="flex-1 bg-gray-200 hover:bg-gray-300 text-gray-700 px-6 py-3 rounded-lg font-semibold text-center">
                            <?php echo e(__('Cancel')); ?>

                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            document.getElementById('package_id').addEventListener('change', function() {
                const selected = this.options[this.selectedIndex];
                document.getElementById('preview_package').textContent = selected.text.split(' - ')[0] || '-';
            });

            document.getElementById('quantity').addEventListener('input', function() {
                document.getElementById('preview_quantity').textContent = this.value;
            });

            document.getElementById('code_length').addEventListener('input', function() {
                const pattern = document.getElementById('code_pattern').value;
                document.getElementById('preview_code').textContent = `${this.value} chars, ${pattern}`;
            });

            document.getElementById('code_pattern').addEventListener('change', function() {
                const length = document.getElementById('code_length').value;
                document.getElementById('preview_code').textContent = `${length} chars, ${this.value}`;
            });

            document.getElementById('validity_hours').addEventListener('input', function() {
                document.getElementById('preview_validity').textContent = `${this.value} hours`;
            });

            document.getElementById('batch_number').addEventListener('input', function() {
                document.getElementById('preview_batch').textContent = this.value || '-';
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\vouchers\create.blade.php ENDPATH**/ ?>