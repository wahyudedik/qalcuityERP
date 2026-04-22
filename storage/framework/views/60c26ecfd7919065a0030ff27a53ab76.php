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
        <?php echo e(__('Create Internet Package')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Create Internet Package')); ?>

                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            <?php echo e(__('Define a new internet service package for customers')); ?></p>
                    </div>
                    <a href="<?php echo e(route('telecom.packages.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php echo e(__('Back to Packages')); ?>

                    </a>
                </div>
            </div>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form action="<?php echo e(route('telecom.packages.store')); ?>" method="POST" class="p-6 space-y-6">
                    <?php echo csrf_field(); ?>

                    <!-- Package Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Package Name')); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="<?php echo e(old('name')); ?>"
                            placeholder="<?php echo e(__('e.g., Premium 50Mbps')); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                        <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Speed Configuration -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Download Speed -->
                        <div>
                            <label for="download_speed_mbps"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Download Speed (Mbps)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="download_speed_mbps" id="download_speed_mbps" required
                                    min="1" max="10000" value="<?php echo e(old('download_speed_mbps', 10)); ?>"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['download_speed_mbps'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            <?php $__errorArgs = ['download_speed_mbps'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Upload Speed -->
                        <div>
                            <label for="upload_speed_mbps"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Upload Speed (Mbps)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="upload_speed_mbps" id="upload_speed_mbps" required
                                    min="1" max="10000" value="<?php echo e(old('upload_speed_mbps', 5)); ?>"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['upload_speed_mbps'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Mbps</span>
                                </div>
                            </div>
                            <?php $__errorArgs = ['upload_speed_mbps'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Quota Configuration -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            <?php echo e(__('Monthly Quota')); ?>

                        </label>
                        <div class="flex items-center space-x-4">
                            <label class="inline-flex items-center">
                                <input type="radio" name="quota_type" value="unlimited"
                                    <?php echo e(old('quota_type') === 'unlimited' || !old('quota_type') ? 'checked' : ''); ?>

                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                    onchange="toggleQuotaInput(false)">
                                <span
                                    class="ml-2 text-sm text-gray-700 dark:text-gray-300"><?php echo e(__('Unlimited')); ?></span>
                            </label>
                            <label class="inline-flex items-center">
                                <input type="radio" name="quota_type" value="limited"
                                    <?php echo e(old('quota_type') === 'limited' ? 'checked' : ''); ?>

                                    class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300"
                                    onchange="toggleQuotaInput(true)">
                                <span class="ml-2 text-sm text-gray-700 dark:text-gray-300"><?php echo e(__('Limited')); ?></span>
                            </label>
                        </div>

                        <div id="quota_input" class="mt-3 <?php echo e(old('quota_type') === 'limited' ? '' : 'hidden'); ?>">
                            <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                                <input type="number" name="quota_gb" id="quota_gb" min="1" max="10000"
                                    value="<?php echo e(old('quota_gb', 100)); ?>"
                                    <?php echo e(old('quota_type') === 'limited' ? 'required' : ''); ?>

                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-12 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['quota_gb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">GB</span>
                                </div>
                            </div>
                            <?php $__errorArgs = ['quota_gb'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Price -->
                        <div>
                            <label for="price"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Monthly Price (IDR)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="price" id="price" required min="0"
                                    step="1000" value="<?php echo e(old('price', 100000)); ?>"
                                    class="block w-full pl-12 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            </div>
                            <?php $__errorArgs = ['price'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <!-- Billing Cycle -->
                        <div>
                            <label for="billing_cycle"
                                class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Billing Cycle')); ?> <span class="text-red-500">*</span>
                            </label>
                            <select name="billing_cycle" id="billing_cycle" required
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['billing_cycle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="monthly" <?php echo e(old('billing_cycle') === 'monthly' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Monthly')); ?></option>
                                <option value="quarterly"
                                    <?php echo e(old('billing_cycle') === 'quarterly' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Quarterly (3 months)')); ?></option>
                                <option value="yearly" <?php echo e(old('billing_cycle') === 'yearly' ? 'selected' : ''); ?>>
                                    <?php echo e(__('Yearly')); ?></option>
                            </select>
                            <?php $__errorArgs = ['billing_cycle'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description"
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Description')); ?>

                        </label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="<?php echo e(__('Describe the package features and benefits...')); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('description')); ?></textarea>
                        <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Features (Optional) -->
                    <div>
                        <label for="features" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Features (one per line)')); ?>

                        </label>
                        <textarea name="features" id="features" rows="4"
                            placeholder="24/7 Support&#10;<?php echo e(__('Free Installation')); ?>&#10;<?php echo e(__('Static IP Available')); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['features'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"><?php echo e(old('features')); ?></textarea>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            <?php echo e(__('Enter each feature on a new line')); ?></p>
                        <?php $__errorArgs = ['features'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active"
                                class="font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('Activate Package')); ?></label>
                            <p class="text-gray-500 dark:text-gray-400">
                                <?php echo e(__('Make this package available for new subscriptions immediately')); ?></p>
                        </div>
                    </div>

                    <!-- Live Preview -->
                    <div class="border-t border-gray-200 dark:border-gray-700 pt-6">
                        <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4"><?php echo e(__('Package Preview')); ?>

                        </h3>
                        <div
                            class="bg-gradient-to-br from-indigo-500 to-purple-600 dark:from-indigo-600 dark:to-purple-700 rounded-lg p-6 text-white max-w-md">
                            <div class="flex items-center justify-between mb-4">
                                <h4 id="preview_name" class="text-xl font-bold"><?php echo e(__('Premium 50Mbps')); ?></h4>
                                <span
                                    class="bg-white bg-opacity-20 px-3 py-1 rounded-full text-sm font-medium"><?php echo e(__('POPULAR')); ?></span>
                            </div>

                            <div class="space-y-2 mb-4">
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_download"><?php echo e(__('10 Mbps Download')); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_upload"><?php echo e(__('5 Mbps Upload')); ?></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <span id="preview_quota"><?php echo e(__('Unlimited Quota')); ?></span>
                                </div>
                            </div>

                            <div class="border-t border-white border-opacity-20 pt-4">
                                <div class="flex items-baseline">
                                    <span class="text-3xl font-bold" id="preview_price">Rp 100.000</span>
                                    <span class="ml-2 text-sm opacity-80">/<?php echo e(__('month')); ?></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div
                        class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?php echo e(route('telecom.packages.index')); ?>"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <?php echo e(__('Cancel')); ?>

                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                            <i class="fas fa-check mr-2"></i>
                            <?php echo e(__('Create Package')); ?>

                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function toggleQuotaInput(show) {
                const quotaInput = document.getElementById('quota_input');
                const quotaField = document.getElementById('quota_gb');

                if (show) {
                    quotaInput.classList.remove('hidden');
                    quotaField.setAttribute('required', 'required');
                } else {
                    quotaInput.classList.add('hidden');
                    quotaField.removeAttribute('required');
                }
            }

            document.getElementById('name').addEventListener('input', function() {
                document.getElementById('preview_name').textContent = this.value || '<?php echo e(__('Package Name')); ?>';
            });

            document.getElementById('download_speed_mbps').addEventListener('input', function() {
                document.getElementById('preview_download').textContent = this.value + ' <?php echo e(__('Mbps Download')); ?>';
            });

            document.getElementById('upload_speed_mbps').addEventListener('input', function() {
                document.getElementById('preview_upload').textContent = this.value + ' <?php echo e(__('Mbps Upload')); ?>';
            });

            document.getElementById('price').addEventListener('input', function() {
                const price = parseInt(this.value) || 0;
                document.getElementById('preview_price').textContent = 'Rp ' + price.toLocaleString('id-ID');
            });

            document.querySelectorAll('input[name="quota_type"]').forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'unlimited') {
                        document.getElementById('preview_quota').textContent = '<?php echo e(__('Unlimited Quota')); ?>';
                    } else {
                        const gb = document.getElementById('quota_gb').value || 0;
                        document.getElementById('preview_quota').textContent = gb +
                            ' <?php echo e(__('GB Monthly Quota')); ?>';
                    }
                });
            });

            document.getElementById('quota_gb').addEventListener('input', function() {
                if (document.querySelector('input[name="quota_type"]:checked').value === 'limited') {
                    document.getElementById('preview_quota').textContent = this.value +
                    ' <?php echo e(__('GB Monthly Quota')); ?>';
                }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\packages\create.blade.php ENDPATH**/ ?>