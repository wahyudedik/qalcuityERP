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
        <?php echo e(__('Edit Paket Internet')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white"><?php echo e(__('Edit Paket Internet')); ?></h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400"><?php echo e(__('Perbarui informasi paket internet')); ?></p>
                    </div>
                    <a href="<?php echo e(route('telecom.packages.index')); ?>"
                        class="inline-flex items-center px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                        <i class="fas fa-arrow-left mr-2"></i>
                        <?php echo e(__('Kembali ke Daftar')); ?>

                    </a>
                </div>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-100 dark:bg-red-900/30 border border-red-400 dark:border-red-800 text-red-700 dark:text-red-400 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Form Card -->
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <form action="<?php echo e(route('telecom.packages.update', $package)); ?>" method="POST" class="p-6 space-y-6">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>

                    <!-- Package Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Nama Paket')); ?> <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="name" id="name" required value="<?php echo e(old('name', $package->name)); ?>"
                            placeholder="<?php echo e(__('contoh: Premium 50Mbps')); ?>"
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
                        <div>
                            <label for="download_speed_mbps" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Kecepatan Download (Mbps)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="download_speed_mbps" id="download_speed_mbps" required
                                    min="1" max="10000" value="<?php echo e(old('download_speed_mbps', $package->download_speed_mbps)); ?>"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-16 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['download_speed_mbps'];
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

                        <div>
                            <label for="upload_speed_mbps" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Kecepatan Upload (Mbps)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <input type="number" name="upload_speed_mbps" id="upload_speed_mbps" required
                                    min="1" max="10000" value="<?php echo e(old('upload_speed_mbps', $package->upload_speed_mbps)); ?>"
                                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white pr-16 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['upload_speed_mbps'];
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

                    <!-- Quota -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="quota_bytes" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Kuota (bytes, kosongkan untuk unlimited)')); ?>

                            </label>
                            <input type="number" name="quota_bytes" id="quota_bytes" min="0"
                                value="<?php echo e(old('quota_bytes', $package->quota_bytes)); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400"><?php echo e(__('Contoh: 107374182400 = 100 GB')); ?></p>
                        </div>

                        <div>
                            <label for="quota_period" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Periode Kuota')); ?>

                            </label>
                            <select name="quota_period" id="quota_period"
                                class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                <option value=""><?php echo e(__('Tidak ada')); ?></option>
                                <option value="hourly" <?php echo e(old('quota_period', $package->quota_period) === 'hourly' ? 'selected' : ''); ?>><?php echo e(__('Per Jam')); ?></option>
                                <option value="daily" <?php echo e(old('quota_period', $package->quota_period) === 'daily' ? 'selected' : ''); ?>><?php echo e(__('Per Hari')); ?></option>
                                <option value="weekly" <?php echo e(old('quota_period', $package->quota_period) === 'weekly' ? 'selected' : ''); ?>><?php echo e(__('Per Minggu')); ?></option>
                                <option value="monthly" <?php echo e(old('quota_period', $package->quota_period) === 'monthly' ? 'selected' : ''); ?>><?php echo e(__('Per Bulan')); ?></option>
                            </select>
                        </div>
                    </div>

                    <!-- Pricing -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="price" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Harga Bulanan (IDR)')); ?> <span class="text-red-500">*</span>
                            </label>
                            <div class="relative mt-1 rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="price" id="price" required min="0" step="1000"
                                    value="<?php echo e(old('price', $package->price)); ?>"
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

                        <div>
                            <label for="billing_cycle" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                                <?php echo e(__('Siklus Penagihan')); ?> <span class="text-red-500">*</span>
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
                                <option value="monthly" <?php echo e(old('billing_cycle', $package->billing_cycle) === 'monthly' ? 'selected' : ''); ?>><?php echo e(__('Bulanan')); ?></option>
                                <option value="quarterly" <?php echo e(old('billing_cycle', $package->billing_cycle) === 'quarterly' ? 'selected' : ''); ?>><?php echo e(__('Triwulan (3 bulan)')); ?></option>
                                <option value="yearly" <?php echo e(old('billing_cycle', $package->billing_cycle) === 'yearly' ? 'selected' : ''); ?>><?php echo e(__('Tahunan')); ?></option>
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

                    <!-- Setup Fee -->
                    <div>
                        <label for="setup_fee" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Biaya Pemasangan (IDR)')); ?>

                        </label>
                        <div class="relative mt-1 rounded-md shadow-sm max-w-xs">
                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                <span class="text-gray-500 dark:text-gray-400 sm:text-sm">Rp</span>
                            </div>
                            <input type="number" name="setup_fee" id="setup_fee" min="0" step="1000"
                                value="<?php echo e(old('setup_fee', $package->installation_fee ?? 0)); ?>"
                                class="block w-full pl-12 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <label for="description" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            <?php echo e(__('Deskripsi')); ?>

                        </label>
                        <textarea name="description" id="description" rows="3"
                            placeholder="<?php echo e(__('Deskripsikan fitur dan keunggulan paket...')); ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo e(old('description', $package->description)); ?></textarea>
                    </div>

                    <!-- Active Status -->
                    <div class="flex items-start">
                        <div class="flex items-center h-5">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                <?php echo e(old('is_active', $package->is_active) ? 'checked' : ''); ?>

                                class="focus:ring-indigo-500 h-4 w-4 text-indigo-600 border-gray-300 dark:border-gray-600 rounded">
                        </div>
                        <div class="ml-3 text-sm">
                            <label for="is_active" class="font-medium text-gray-700 dark:text-gray-300"><?php echo e(__('Aktifkan Paket')); ?></label>
                            <p class="text-gray-500 dark:text-gray-400"><?php echo e(__('Jadikan paket ini tersedia untuk subscription baru')); ?></p>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200 dark:border-gray-700">
                        <a href="<?php echo e(route('telecom.packages.index')); ?>"
                            class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm text-sm font-medium text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600">
                            <?php echo e(__('Batal')); ?>

                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 dark:bg-indigo-700 dark:hover:bg-indigo-600">
                            <i class="fas fa-save mr-2"></i>
                            <?php echo e(__('Simpan Perubahan')); ?>

                        </button>
                    </div>
                </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\packages\edit.blade.php ENDPATH**/ ?>