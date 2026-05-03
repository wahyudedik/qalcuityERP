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
     <?php $__env->slot('header', null, []); ?> <?php echo e(__('Edit Triage Assessment')); ?> - <?php echo e($assessment->triage_code); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.triage.show', $assessment)); ?>"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Details
            </a>
    </div>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="<?php echo e(route('healthcare.triage.update', $assessment)); ?>">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>

                        <div class="space-y-6">
                            <!-- Patient Selection -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Patient Information</h3>
                                <div>
                                    <label for="patient_visit_id"
                                        class="block text-sm font-medium text-gray-700">Patient Visit</label>
                                    <select name="patient_visit_id" id="patient_visit_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Patient Visit</option>
                                        <?php $__currentLoopData = $visits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                            <option value="<?php echo e($visit->id); ?>"
                                                <?php echo e(old('patient_visit_id', $assessment->patient_visit_id) == $visit->id ? 'selected' : ''); ?>>
                                                <?php echo e($visit->patient->name); ?> - <?php echo e($visit->visit_number); ?>

                                            </option>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </select>
                                </div>
                            </div>

                            <!-- Chief Complaint -->
                            <div>
                                <label for="chief_complaint" class="block text-sm font-medium text-gray-700">Chief
                                    Complaint</label>
                                <textarea name="chief_complaint" id="chief_complaint" required rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Describe the main reason for visit..."><?php echo e(old('chief_complaint', $assessment->chief_complaint)); ?></textarea>
                            </div>

                            <!-- Priority Level -->
                            <div>
                                <label for="priority_level" class="block text-sm font-medium text-gray-700">Priority
                                    Level</label>
                                <select name="priority_level" id="priority_level" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Priority</option>
                                    <option value="critical"
                                        <?php echo e(old('priority_level', $assessment->priority_level) === 'critical' ? 'selected' : ''); ?>>
                                        T1 - RED (Critical - Immediate)</option>
                                    <option value="emergency"
                                        <?php echo e(old('priority_level', $assessment->priority_level) === 'emergency' ? 'selected' : ''); ?>>
                                        T2 - ORANGE (Emergency - < 10 min)</option>
                                    <option value="urgent"
                                        <?php echo e(old('priority_level', $assessment->priority_level) === 'urgent' ? 'selected' : ''); ?>>
                                        T3 - YELLOW (Urgent - < 60 min)</option>
                                    <option value="semi_urgent"
                                        <?php echo e(old('priority_level', $assessment->priority_level) === 'semi_urgent' ? 'selected' : ''); ?>>
                                        T4 - GREEN (Semi-Urgent - < 120 min)</option>
                                    <option value="non_urgent"
                                        <?php echo e(old('priority_level', $assessment->priority_level) === 'non_urgent' ? 'selected' : ''); ?>>
                                        T5 - BLUE (Non-Urgent - < 240 min)</option>
                                </select>
                            </div>

                            <!-- Vital Signs -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vital Signs</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="blood_pressure_systolic"
                                            class="block text-sm font-medium text-gray-700">BP Systolic</label>
                                        <input type="number" name="blood_pressure_systolic"
                                            id="blood_pressure_systolic"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('blood_pressure_systolic', $assessment->blood_pressure_systolic)); ?>"
                                            placeholder="120">
                                    </div>
                                    <div>
                                        <label for="blood_pressure_diastolic"
                                            class="block text-sm font-medium text-gray-700">BP Diastolic</label>
                                        <input type="number" name="blood_pressure_diastolic"
                                            id="blood_pressure_diastolic"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('blood_pressure_diastolic', $assessment->blood_pressure_diastolic)); ?>"
                                            placeholder="80">
                                    </div>
                                    <div>
                                        <label for="heart_rate" class="block text-sm font-medium text-gray-700">Heart
                                            Rate</label>
                                        <input type="number" name="heart_rate" id="heart_rate"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('heart_rate', $assessment->heart_rate)); ?>" placeholder="72">
                                    </div>
                                    <div>
                                        <label for="temperature"
                                            class="block text-sm font-medium text-gray-700">Temperature (°C)</label>
                                        <input type="number" name="temperature" id="temperature" step="0.1"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('temperature', $assessment->temperature)); ?>"
                                            placeholder="36.5">
                                    </div>
                                    <div>
                                        <label for="respiratory_rate"
                                            class="block text-sm font-medium text-gray-700">Respiratory Rate</label>
                                        <input type="number" name="respiratory_rate" id="respiratory_rate"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('respiratory_rate', $assessment->respiratory_rate)); ?>"
                                            placeholder="16">
                                    </div>
                                    <div>
                                        <label for="oxygen_saturation"
                                            class="block text-sm font-medium text-gray-700">O2 Saturation (%)</label>
                                        <input type="number" name="oxygen_saturation" id="oxygen_saturation"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('oxygen_saturation', $assessment->oxygen_saturation)); ?>"
                                            placeholder="98">
                                    </div>
                                    <div>
                                        <label for="pain_score" class="block text-sm font-medium text-gray-700">Pain
                                            Score (0-10)</label>
                                        <input type="number" name="pain_score" id="pain_score" min="0"
                                            max="10"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('pain_score', $assessment->pain_score)); ?>" placeholder="0">
                                    </div>
                                    <div>
                                        <label for="gcs" class="block text-sm font-medium text-gray-700">GCS
                                            Score</label>
                                        <input type="number" name="gcs" id="gcs" min="3"
                                            max="15"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="<?php echo e(old('gcs', $assessment->gcs)); ?>" placeholder="15">
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending"
                                        <?php echo e(old('status', $assessment->status) === 'pending' ? 'selected' : ''); ?>>
                                        Pending</option>
                                    <option value="in_progress"
                                        <?php echo e(old('status', $assessment->status) === 'in_progress' ? 'selected' : ''); ?>>In
                                        Progress</option>
                                    <option value="completed"
                                        <?php echo e(old('status', $assessment->status) === 'completed' ? 'selected' : ''); ?>>
                                        Completed</option>
                                </select>
                            </div>

                            <!-- Additional Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                    Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Any additional observations..."><?php echo e(old('notes', $assessment->notes)); ?></textarea>
                            </div>

                            <!-- Assessment Time -->
                            <div>
                                <label for="assessment_time"
                                    class="block text-sm font-medium text-gray-700">Assessment Time</label>
                                <input type="datetime-local" name="assessment_time" id="assessment_time"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="<?php echo e(old('assessment_time', $assessment->assessment_time ? $assessment->assessment_time->format('Y-m-d\TH:i') : '')); ?>">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="<?php echo e(route('healthcare.triage.show', $assessment)); ?>"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Update Assessment
                            </button>
                        </div>
                    </form>
                </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\triage\edit.blade.php ENDPATH**/ ?>