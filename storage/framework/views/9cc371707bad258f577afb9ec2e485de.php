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
        <?php echo e(__('Buat Subscription Baru')); ?>

     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6 flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e(__('Buat Subscription Baru')); ?></h1>
                    <p class="mt-1 text-sm text-gray-600"><?php echo e(__('Daftarkan pelanggan ke paket internet')); ?></p>
                </div>
                <a href="<?php echo e(route('telecom.subscriptions.index')); ?>"
                    class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                    <i class="fas fa-arrow-left mr-2"></i><?php echo e(__('Kembali')); ?>

                </a>
            </div>

            <?php if($errors->any()): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
                    <ul class="list-disc list-inside">
                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <li><?php echo e($error); ?></li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="bg-white shadow-sm sm:rounded-lg">
                <form action="<?php echo e(route('telecom.subscriptions.store')); ?>" method="POST" class="p-6 space-y-6">
                    <?php echo csrf_field(); ?>

                    <!-- Customer -->
                    <div>
                        <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo e(__('Pelanggan')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="customer_id" id="customer_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['customer_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value=""><?php echo e(__('Pilih Pelanggan...')); ?></option>
                            <?php $__currentLoopData = $customers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $customer): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($customer->id); ?>" <?php echo e(old('customer_id') == $customer->id ? 'selected' : ''); ?>>
                                    <?php echo e($customer->name); ?> <?php echo e($customer->email ? '(' . $customer->email . ')' : ''); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['customer_id'];
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

                    <!-- Package -->
                    <div>
                        <label for="package_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo e(__('Paket Internet')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="package_id" id="package_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['package_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value=""><?php echo e(__('Pilih Paket...')); ?></option>
                            <?php $__currentLoopData = $packages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $package): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($package->id); ?>" <?php echo e(old('package_id') == $package->id ? 'selected' : ''); ?>>
                                    <?php echo e($package->name); ?> — <?php echo e($package->download_speed_mbps); ?>/<?php echo e($package->upload_speed_mbps); ?> Mbps — Rp <?php echo e(number_format($package->price, 0, ',', '.')); ?>/bln
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

                    <!-- Device -->
                    <div>
                        <label for="device_id" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo e(__('Perangkat Jaringan')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="device_id" id="device_id" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['device_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value=""><?php echo e(__('Pilih Perangkat...')); ?></option>
                            <?php $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($device->id); ?>" <?php echo e(old('device_id') == $device->id ? 'selected' : ''); ?>>
                                    <?php echo e($device->name); ?> (<?php echo e($device->ip_address); ?>) — <?php echo e(ucfirst($device->status)); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                        <?php $__errorArgs = ['device_id'];
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

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="started_at" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e(__('Tanggal Mulai')); ?> <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="started_at" id="started_at" required
                                value="<?php echo e(old('started_at', now()->format('Y-m-d'))); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['started_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['started_at'];
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
                            <label for="ends_at" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e(__('Tanggal Berakhir (opsional)')); ?>

                            </label>
                            <input type="date" name="ends_at" id="ends_at"
                                value="<?php echo e(old('ends_at')); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Auth Type -->
                    <div>
                        <label for="auth_type" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo e(__('Tipe Autentikasi')); ?> <span class="text-red-500">*</span>
                        </label>
                        <select name="auth_type" id="auth_type" required
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm <?php $__errorArgs = ['auth_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-300 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <option value="username_password" <?php echo e(old('auth_type') === 'username_password' ? 'selected' : ''); ?>><?php echo e(__('Username & Password')); ?></option>
                            <option value="mac_address" <?php echo e(old('auth_type') === 'mac_address' ? 'selected' : ''); ?>><?php echo e(__('MAC Address')); ?></option>
                            <option value="voucher" <?php echo e(old('auth_type') === 'voucher' ? 'selected' : ''); ?>><?php echo e(__('Voucher')); ?></option>
                        </select>
                        <?php $__errorArgs = ['auth_type'];
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

                    <!-- Hotspot Credentials -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="hotspot_username" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e(__('Username Hotspot')); ?>

                            </label>
                            <input type="text" name="hotspot_username" id="hotspot_username"
                                value="<?php echo e(old('hotspot_username')); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>

                        <div>
                            <label for="hotspot_password" class="block text-sm font-medium text-gray-700 mb-1">
                                <?php echo e(__('Password Hotspot')); ?>

                            </label>
                            <input type="password" name="hotspot_password" id="hotspot_password"
                                class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                        </div>
                    </div>

                    <!-- Notes -->
                    <div>
                        <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">
                            <?php echo e(__('Catatan')); ?>

                        </label>
                        <textarea name="notes" id="notes" rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 bg-white text-gray-900 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"><?php echo e(old('notes')); ?></textarea>
                    </div>

                    <!-- Submit -->
                    <div class="flex items-center justify-end space-x-3 pt-6 border-t border-gray-200">
                        <a href="<?php echo e(route('telecom.subscriptions.index')); ?>"
                            class="px-4 py-2 border border-gray-300 rounded-md shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50">
                            <?php echo e(__('Batal')); ?>

                        </a>
                        <button type="submit"
                            class="px-6 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700">
                            <i class="fas fa-check mr-2"></i><?php echo e(__('Buat Subscription')); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\subscriptions\create.blade.php ENDPATH**/ ?>