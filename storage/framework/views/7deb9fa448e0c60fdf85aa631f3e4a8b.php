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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('New Notification Rule')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.notifications.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="name" class="block text-sm font-medium text-gray-700">Rule Name *</label>
                            <input type="text" name="name" required value="<?php echo e(old('name')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Critical Lab Results Alert">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="trigger_event" class="block text-sm font-medium text-gray-700">Trigger Event
                                    *</label>
                                <select name="trigger_event" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Event</option>
                                    <option value="lab_result_critical"
                                        <?php echo e(old('trigger_event') === 'lab_result_critical' ? 'selected' : ''); ?>>Critical
                                        Lab Result</option>
                                    <option value="appointment_reminder"
                                        <?php echo e(old('trigger_event') === 'appointment_reminder' ? 'selected' : ''); ?>>
                                        Appointment Reminder</option>
                                    <option value="prescription_ready"
                                        <?php echo e(old('trigger_event') === 'prescription_ready' ? 'selected' : ''); ?>>
                                        Prescription Ready</option>
                                    <option value="admission_confirmed"
                                        <?php echo e(old('trigger_event') === 'admission_confirmed' ? 'selected' : ''); ?>>Admission
                                        Confirmed</option>
                                    <option value="discharge_summary"
                                        <?php echo e(old('trigger_event') === 'discharge_summary' ? 'selected' : ''); ?>>Discharge
                                        Summary</option>
                                    <option value="billing_overdue"
                                        <?php echo e(old('trigger_event') === 'billing_overdue' ? 'selected' : ''); ?>>Billing
                                        Overdue</option>
                                    <option value="equipment_maintenance"
                                        <?php echo e(old('trigger_event') === 'equipment_maintenance' ? 'selected' : ''); ?>>
                                        Equipment Maintenance</option>
                                    <option value="low_stock_alert"
                                        <?php echo e(old('trigger_event') === 'low_stock_alert' ? 'selected' : ''); ?>>Low Stock
                                        Alert</option>
                                </select>
                            </div>
                            <div>
                                <label for="priority" class="block text-sm font-medium text-gray-700">Priority *</label>
                                <select name="priority" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="low" <?php echo e(old('priority') === 'low' ? 'selected' : ''); ?>>Low
                                    </option>
                                    <option value="medium" <?php echo e(old('priority') === 'medium' ? 'selected' : ''); ?>>Medium
                                    </option>
                                    <option value="high" <?php echo e(old('priority') === 'high' ? 'selected' : ''); ?>>High
                                    </option>
                                    <option value="critical" <?php echo e(old('priority') === 'critical' ? 'selected' : ''); ?>>
                                        Critical</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Notification Channels *</label>
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                <label class="flex items-center">
                                    <input type="checkbox" name="channels[]" value="email"
                                        <?php echo e(in_array('email', old('channels', [])) ? 'checked' : ''); ?>

                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700"><i
                                            class="fas fa-envelope mr-1"></i>Email</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="channels[]" value="sms"
                                        <?php echo e(in_array('sms', old('channels', [])) ? 'checked' : ''); ?>

                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700"><i class="fas fa-sms mr-1"></i>SMS</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="channels[]" value="push"
                                        <?php echo e(in_array('push', old('channels', [])) ? 'checked' : ''); ?>

                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700"><i class="fas fa-bell mr-1"></i>Push</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="checkbox" name="channels[]" value="in_app"
                                        <?php echo e(in_array('in_app', old('channels', [])) ? 'checked' : ''); ?>

                                        class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                                    <span class="ml-2 text-sm text-gray-700"><i
                                            class="fas fa-comment mr-1"></i>In-App</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label for="subject_template" class="block text-sm font-medium text-gray-700">Subject
                                Template *</label>
                            <input type="text" name="subject_template" required
                                value="<?php echo e(old('subject_template')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Critical Lab Result: {patient_name}">
                            <p class="mt-1 text-xs text-gray-500">Use variables: {patient_name}, {doctor_name}, {date},
                                {time}</p>
                        </div>

                        <div>
                            <label for="message_template" class="block text-sm font-medium text-gray-700">Message
                                Template *</label>
                            <textarea name="message_template" required rows="6"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Dear {patient_name}, your lab result for {test_name} is ready..."><?php echo e(old('message_template')); ?></textarea>
                            <p class="mt-1 text-xs text-gray-500">Use variables: {patient_name}, {doctor_name},
                                {test_name}, {result}, {date}, {time}</p>
                        </div>

                        <div class="flex items-center">
                            <input type="checkbox" name="is_active" id="is_active" value="1"
                                <?php echo e(old('is_active', true) ? 'checked' : ''); ?>

                                class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded">
                            <label for="is_active" class="ml-2 block text-sm text-gray-900">Activate Rule</label>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.notifications.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Create Rule</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\notifications\create.blade.php ENDPATH**/ ?>