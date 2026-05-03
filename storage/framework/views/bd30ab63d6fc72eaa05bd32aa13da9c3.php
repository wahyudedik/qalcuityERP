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
     <?php $__env->slot('header', null, []); ?> Hotel Settings <?php $__env->endSlot(); ?>

    <div x-data="hotelSettings()" class="max-w-3xl mx-auto space-y-6">
        <form method="POST" action="<?php echo e(route('hotel.settings.update')); ?>" enctype="multipart/form-data" class="space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">General
                    Settings</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Hotel Name
                            *</label>
                        <input type="text" name="hotel_name" required
                            value="<?php echo e(old('hotel_name', $settings->hotel_name ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Check-in Time
                            *</label>
                        <input type="time" name="check_in_time" required
                            value="<?php echo e(old('check_in_time', $settings->check_in_time ?? '14:00')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Check-out Time
                            *</label>
                        <input type="time" name="check_out_time" required
                            value="<?php echo e(old('check_out_time', $settings->check_out_time ?? '12:00')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Timezone
                            *</label>
                        <select name="timezone" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = $timezones; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tz => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($tz); ?>" <?php if(old('timezone', $settings->timezone ?? 'Asia/Jakarta') === $tz): echo 'selected'; endif; ?>><?php echo e($label); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Currency
                            *</label>
                        <select name="currency" required
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php $__currentLoopData = $currencies; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $code => $name): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($code); ?>" <?php if(old('currency', $settings->currency ?? 'IDR') === $code): echo 'selected'; endif; ?>><?php echo e($name); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Tax Rate (%)
                            *</label>
                        <input type="number" name="tax_rate" required min="0" max="100" step="0.01"
                            value="<?php echo e(old('tax_rate', $settings->tax_rate ?? 10)); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>

                    
                    <div class="flex items-center gap-3">
                        <input type="checkbox" name="tax_included" id="tax_included" value="1"
                            <?php echo e(old('tax_included', $settings->tax_included ?? false) ? 'checked' : ''); ?>

                            class="rounded text-blue-600">
                        <label for="tax_included" class="text-sm text-gray-700">
                            Tax included in room rates
                        </label>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Contact
                    Information</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Phone</label>
                        <input type="text" name="phone" value="<?php echo e(old('phone', $settings->phone ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Email</label>
                        <input type="email" name="email" value="<?php echo e(old('email', $settings->email ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Website</label>
                        <input type="url" name="website" placeholder="https://"
                            value="<?php echo e(old('website', $settings->website ?? '')); ?>"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 mb-2">Address</label>
                        <textarea name="address" rows="2"
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('address', $settings->address ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Deposit
                    Settings</h3>

                <div class="space-y-4">
                    
                    <div class="flex items-center justify-between py-2">
                        <div>
                            <p class="font-medium text-gray-900">Deposit Required</p>
                            <p class="text-xs text-gray-500">Require deposit at check-in</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" name="deposit_required" id="deposit_required" value="1"
                                x-model="depositRequired"
                                <?php echo e(old('deposit_required', $settings->deposit_required ?? false) ? 'checked' : ''); ?>

                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>

                    
                    <div x-show="depositRequired" class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Default
                                    Deposit Amount</label>
                                <div class="relative">
                                    <span
                                        class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-500 text-sm">Rp</span>
                                    <input type="number" name="default_deposit_amount" min="0" step="10000"
                                        value="<?php echo e(old('default_deposit_amount', $settings->default_deposit_amount ?? 0)); ?>"
                                        class="w-full pl-10 pr-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-2">Deposit
                                    Type</label>
                                <select name="deposit_type"
                                    class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="fixed" <?php if(old('deposit_type', $settings->deposit_type ?? 'fixed') === 'fixed'): echo 'selected'; endif; ?>>Fixed Amount</option>
                                    <option value="percentage" <?php if(old('deposit_type', $settings->deposit_type ?? 'fixed') === 'percentage'): echo 'selected'; endif; ?>>Percentage of Total
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">
                    Operations</h3>

                <div class="space-y-4">
                    
                    <div class="flex items-start justify-between gap-4 py-2">
                        <div>
                            <p class="font-medium text-gray-900">Allow Overbooking</p>
                            <p class="text-xs text-gray-500">Allow reservations beyond available
                                capacity</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" name="overbooking_allowed" value="1"
                                <?php echo e(old('overbooking_allowed', $settings->overbooking_allowed ?? false) ? 'checked' : ''); ?>

                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>

                    
                    <div class="flex items-start justify-between gap-4 py-2">
                        <div>
                            <p class="font-medium text-gray-900">Auto-assign Room</p>
                            <p class="text-xs text-gray-500">Automatically assign available room at
                                check-in</p>
                        </div>
                        <label class="relative inline-flex items-center cursor-pointer mt-0.5">
                            <input type="checkbox" name="auto_assign_room" value="1"
                                <?php echo e(old('auto_assign_room', $settings->auto_assign_room ?? false) ? 'checked' : ''); ?>

                                class="sr-only peer">
                            <div
                                class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-blue-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-medium text-gray-500 uppercase tracking-wide mb-4">Policies
                </h3>

                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Cancellation
                            Policy</label>
                        <textarea name="cancellation_policy" rows="3"
                            placeholder="e.g., Free cancellation up to 24 hours before check-in..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('cancellation_policy', $settings->cancellation_policy ?? '')); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-2">Terms &
                            Conditions</label>
                        <textarea name="terms_conditions" rows="4" placeholder="Hotel terms and conditions..."
                            class="w-full px-3 py-2.5 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('terms_conditions', $settings->terms_conditions ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('hotel.dashboard')); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Cancel</a>
                <button type="submit"
                    class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Save Settings
                </button>
            </div>
        </form>
    </div>

    
    <script>
        window.hotelSettings = function() {
            return {
                depositRequired: <?php echo e(old('deposit_required', $settings->deposit_required ?? false) ? 'true' : 'false'); ?>,
            }
        };
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hotel\settings\edit.blade.php ENDPATH**/ ?>