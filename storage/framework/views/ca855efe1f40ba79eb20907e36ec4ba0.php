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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight"><?php echo e(__('New Medical Certificate')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="<?php echo e(route('healthcare.medical-certificates.store')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700">Patient *</label>
                            <select name="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Patient</option>
                                <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->id); ?>"
                                        <?php echo e(old('patient_id') == $patient->id ? 'selected' : ''); ?>>
                                        <?php echo e($patient->name); ?> - <?php echo e($patient->medical_record_number); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label for="certificate_type" class="block text-sm font-medium text-gray-700">Certificate
                                Type *</label>
                            <select name="certificate_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="sick_leave"
                                    <?php echo e(old('certificate_type') === 'sick_leave' ? 'selected' : ''); ?>>Sick Leave
                                    Certificate</option>
                                <option value="fitness_to_work"
                                    <?php echo e(old('certificate_type') === 'fitness_to_work' ? 'selected' : ''); ?>>Fitness to
                                    Work</option>
                                <option value="medical_report"
                                    <?php echo e(old('certificate_type') === 'medical_report' ? 'selected' : ''); ?>>Medical Report
                                </option>
                                <option value="referral" <?php echo e(old('certificate_type') === 'referral' ? 'selected' : ''); ?>>
                                    Referral Letter</option>
                                <option value="vaccination"
                                    <?php echo e(old('certificate_type') === 'vaccination' ? 'selected' : ''); ?>>Vaccination
                                    Certificate</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue Date
                                    *</label>
                                <input type="date" name="issue_date" required
                                    value="<?php echo e(old('issue_date', now()->format('Y-m-d'))); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid
                                    Until</label>
                                <input type="date" name="valid_until" value="<?php echo e(old('valid_until')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <input type="text" name="diagnosis" value="<?php echo e(old('diagnosis')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Medical diagnosis...">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description/Notes
                                *</label>
                            <textarea name="description" required rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Certificate details..."><?php echo e(old('description')); ?></textarea>
                        </div>

                        <div>
                            <label for="doctor_name" class="block text-sm font-medium text-gray-700">Issuing Doctor
                                *</label>
                            <input type="text" name="doctor_name" required value="<?php echo e(old('doctor_name')); ?>"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Dr. ">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="<?php echo e(route('healthcare.medical-certificates.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Issue Certificate</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\medical-certificates\create.blade.php ENDPATH**/ ?>