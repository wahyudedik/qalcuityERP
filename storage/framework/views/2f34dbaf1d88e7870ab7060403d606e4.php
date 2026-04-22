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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('New Message')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.patient-messages.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="recipient_id" class="block text-sm font-medium text-gray-700">Recipient *</label>
                            <select name="recipient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Recipient</option>
                                <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->user_id); ?>"
                                        <?php echo e(old('recipient_id') == $patient->user_id ? 'selected' : ''); ?>>
                                        <?php echo e($patient->full_name); ?> - <?php echo e($patient->medical_record_number); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="category" class="block text-sm font-medium text-gray-700">Category *</label>
                                <select name="category" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Category</option>
                                    <option value="general" <?php echo e(old('category') === 'general' ? 'selected' : ''); ?>>
                                        General</option>
                                    <option value="prescription"
                                        <?php echo e(old('category') === 'prescription' ? 'selected' : ''); ?>>Prescription</option>
                                    <option value="test_results"
                                        <?php echo e(old('category') === 'test_results' ? 'selected' : ''); ?>>Test Results</option>
                                    <option value="appointment"
                                        <?php echo e(old('category') === 'appointment' ? 'selected' : ''); ?>>Appointment</option>
                                    <option value="billing" <?php echo e(old('category') === 'billing' ? 'selected' : ''); ?>>
                                        Billing</option>
                                    <option value="symptoms" <?php echo e(old('category') === 'symptoms' ? 'selected' : ''); ?>>
                                        Symptoms</option>
                                    <option value="follow_up" <?php echo e(old('category') === 'follow_up' ? 'selected' : ''); ?>>
                                        Follow-up</option>
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
                                    <option value="urgent" <?php echo e(old('priority') === 'urgent' ? 'selected' : ''); ?>>Urgent
                                    </option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label for="subject" class="block text-sm font-medium text-gray-700">Subject *</label>
                            <input type="text" name="subject" required value="<?php echo e(old('subject')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Message subject">
                        </div>

                        <div>
                            <label for="message" class="block text-sm font-medium text-gray-700">Message *</label>
                            <textarea name="message" required rows="10"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Type your message here..."><?php echo e(old('message')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.patient-messages.inbox')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-paper-plane mr-2"></i>Send Message</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-messages\create.blade.php ENDPATH**/ ?>