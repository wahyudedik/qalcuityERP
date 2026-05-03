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
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            <?php echo e(__('Konsultasi Telemedicine Baru')); ?></h2>
     <?php $__env->endSlot(); ?>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div
                class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border border-gray-200">
                <form method="POST" action="<?php echo e(route('healthcare.telemedicine.book')); ?>">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-6">
                        <div>
                            <label for="patient_id"
                                class="block text-sm font-medium text-gray-700">Pasien *</label>
                            <select name="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Pasien</option>
                                <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->id); ?>"
                                        <?php echo e(old('patient_id') == $patient->id ? 'selected' : ''); ?>>
                                        <?php echo e($patient->full_name ?? $patient->name); ?> -
                                        <?php echo e($patient->medical_record_number ?? ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['patient_id'];
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
                            <label for="doctor_id"
                                class="block text-sm font-medium text-gray-700">Dokter *</label>
                            <select name="doctor_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Dokter</option>
                                <?php $__currentLoopData = $doctors ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($doctor->id); ?>"
                                        <?php echo e(old('doctor_id') == $doctor->id ? 'selected' : ''); ?>>
                                        <?php echo e($doctor->name); ?> - <?php echo e($doctor->specialization ?? ''); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                            <?php $__errorArgs = ['doctor_id'];
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
                            <label for="consultation_type"
                                class="block text-sm font-medium text-gray-700">Tipe Konsultasi
                                *</label>
                            <select name="consultation_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="video" <?php echo e(old('consultation_type') === 'video' ? 'selected' : ''); ?>>
                                    Video Call</option>
                                <option value="voice" <?php echo e(old('consultation_type') === 'voice' ? 'selected' : ''); ?>>
                                    Voice Call</option>
                                <option value="chat" <?php echo e(old('consultation_type') === 'chat' ? 'selected' : ''); ?>>Chat
                                </option>
                            </select>
                            <?php $__errorArgs = ['consultation_type'];
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
                            <label for="reason"
                                class="block text-sm font-medium text-gray-700">Keluhan Utama
                                *</label>
                            <textarea name="reason" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Jelaskan keluhan utama..."><?php echo e(old('reason')); ?></textarea>
                            <?php $__errorArgs = ['reason'];
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="consultation_date"
                                    class="block text-sm font-medium text-gray-700">Tanggal
                                    Konsultasi *</label>
                                <input type="date" name="consultation_date" required
                                    value="<?php echo e(old('consultation_date', now()->addDay()->format('Y-m-d'))); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php $__errorArgs = ['consultation_date'];
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
                                <label for="consultation_time"
                                    class="block text-sm font-medium text-gray-700">Waktu Konsultasi
                                    *</label>
                                <input type="time" name="consultation_time" required
                                    value="<?php echo e(old('consultation_time', '09:00')); ?>"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <?php $__errorArgs = ['consultation_time'];
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
                        </div>

                        <div>
                            <label for="notes"
                                class="block text-sm font-medium text-gray-700">Catatan
                                Tambahan</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Informasi tambahan..."><?php echo e(old('notes')); ?></textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex flex-col sm:flex-row justify-end gap-3">
                        <a href="<?php echo e(route('healthcare.telemedicine.index')); ?>"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 text-center">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Jadwalkan Konsultasi</button>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\telemedicine\create.blade.php ENDPATH**/ ?>