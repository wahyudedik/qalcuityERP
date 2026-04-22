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
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                <?php echo e(__('Bed Details')); ?> - <?php echo e($bed->bed_number); ?>

            </h2>
            <div class="flex space-x-3">
                <a href="<?php echo e(route('healthcare.beds.edit', $bed)); ?>"
                    class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-yellow-700">
                    <i class="fas fa-edit mr-2"></i>Edit
                </a>
                <a href="<?php echo e(route('healthcare.beds.index')); ?>"
                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                    <i class="fas fa-arrow-left mr-2"></i>Back to List
                </a>
            </div>
        </div>
     <?php $__env->endSlot(); ?>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Bed Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="text-center mb-6">
                            <i
                                class="fas fa-bed text-6xl
                                <?php if($bed->status === 'available'): ?> text-green-600
                                <?php elseif($bed->status === 'occupied'): ?> text-red-600
                                <?php elseif($bed->status === 'maintenance'): ?> text-yellow-600
                                <?php else: ?> text-blue-600 <?php endif; ?>"></i>
                            <h3 class="text-2xl font-bold mt-4"><?php echo e($bed->bed_number); ?></h3>
                            <span
                                class="inline-block mt-2 px-3 py-1 text-sm font-semibold rounded-full
                                <?php if($bed->status === 'available'): ?> bg-green-100 text-green-800
                                <?php elseif($bed->status === 'occupied'): ?> bg-red-100 text-red-800
                                <?php elseif($bed->status === 'maintenance'): ?> bg-yellow-100 text-yellow-800
                                <?php elseif($bed->status === 'reserved'): ?> bg-blue-100 text-blue-800
                                <?php else: ?> bg-gray-100 text-gray-800 <?php endif; ?>">
                                <?php echo e(ucfirst($bed->status)); ?>

                            </span>
                        </div>

                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Bed Type</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e(ucfirst($bed->bed_type)); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Ward</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    <?php echo e($bed->ward ? $bed->ward->name : 'Not assigned'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Room Number</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->room_number ?: '-'); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Floor</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->floor ?: '-'); ?></dd>
                            </div>
                        </dl>
                    </div>
                </div>

                <!-- Pricing & Amenities -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Pricing & Amenities</h3>
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rate Per Day</dt>
                                <dd class="mt-1 text-2xl font-bold text-blue-600">Rp
                                    <?php echo e(number_format($bed->rate_per_day, 0, ',', '.')); ?></dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Amenities</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->amenities ?: 'No amenities listed'); ?>

                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Status</dt>
                                <dd class="mt-1">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        <?php if($bed->is_active): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php echo e($bed->is_active ? 'Active' : 'Inactive'); ?>

                                    </span>
                                </dd>
                            </div>
                        </dl>
                    </div>
                </div>
            </div>

            <?php if($bed->status === 'occupied' && $bed->patientVisit): ?>
                <!-- Current Patient Information -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">
                            <i class="fas fa-user-injured mr-2 text-red-600"></i>Current Patient
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Patient Name</p>
                                <p class="mt-1 text-lg font-semibold text-gray-900">
                                    <?php echo e($bed->patientVisit->patient->name ?? 'N/A'); ?></p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Admission Date</p>
                                <p class="mt-1 text-lg text-gray-900">
                                    <?php echo e($bed->patientVisit->admission_date ? $bed->patientVisit->admission_date->format('d/m/Y H:i') : 'N/A'); ?>

                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Doctor</p>
                                <p class="mt-1 text-lg text-gray-900"><?php echo e($bed->patientVisit->doctor->name ?? 'N/A'); ?>

                                </p>
                            </div>
                        </div>
                        <div class="mt-4">
                            <form action="<?php echo e(route('healthcare.beds.release', $bed)); ?>" method="POST" class="inline">
                                <?php echo csrf_field(); ?>
                                <button type="submit"
                                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700"
                                    onclick="return confirm('Are you sure you want to release this bed?')">
                                    <i class="fas fa-sign-out-alt mr-2"></i>Release Bed
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Actions -->
            <?php if($bed->status === 'available'): ?>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                        <div class="flex space-x-4">
                            <button onclick="document.getElementById('assignModal').classList.remove('hidden')"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-user-plus mr-2"></i>Assign Patient
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Assign Patient Modal -->
                <div id="assignModal"
                    class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
                    <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
                        <div class="mt-3">
                            <h3 class="text-lg font-medium text-gray-900 mb-4">Assign Patient to Bed</h3>
                            <form action="<?php echo e(route('healthcare.beds.assign-patient', $bed)); ?>" method="POST">
                                <?php echo csrf_field(); ?>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Patient Visit</label>
                                    <select name="patient_visit_id" required
                                        class="w-full border-gray-300 rounded-md shadow-sm">
                                        <option value="">Select Visit</option>
                                        <!-- You would load this dynamically -->
                                    </select>
                                </div>
                                <div class="flex justify-end space-x-3">
                                    <button type="button"
                                        onclick="document.getElementById('assignModal').classList.add('hidden')"
                                        class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                        Cancel
                                    </button>
                                    <button type="submit"
                                        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                        Assign
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Metadata -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mt-6">
                <div class="p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Additional Information</h3>
                    <dl class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Created At</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->created_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Last Updated</dt>
                            <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->updated_at->format('d/m/Y H:i')); ?></dd>
                        </div>
                        <?php if($bed->occupied_at): ?>
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Occupied</dt>
                                <dd class="mt-1 text-sm text-gray-900"><?php echo e($bed->occupied_at->format('d/m/Y H:i')); ?>

                                </dd>
                            </div>
                        <?php endif; ?>
                    </dl>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\beds\show.blade.php ENDPATH**/ ?>