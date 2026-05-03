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
     <?php $__env->slot('header', null, []); ?> Buat Janji Temu Baru <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Janji Temu', 'url' => route('healthcare.appointments.index')],
        ['label' => 'Buat Janji Temu'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Janji Temu', 'url' => route('healthcare.appointments.index')],
        ['label' => 'Buat Janji Temu'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <form action="<?php echo e(route('healthcare.appointments.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Pasien</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Pasien <span class="text-red-500">*</span>
                            </label>
                            <select name="patient_id" id="patient-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['patient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- Pilih Pasien --</option>
                                <?php if(isset($patients)): ?>
                                    <?php $__currentLoopData = $patients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($patient->id); ?>" <?php if(old('patient_id', request('patient_id')) == $patient->id): echo 'selected'; endif; ?>>
                                            <?php echo e($patient->full_name); ?> - <?php echo e($patient->medical_record_number); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <?php $__errorArgs = ['patient_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            <p class="mt-1 text-xs text-gray-500">
                                <a href="<?php echo e(route('healthcare.patients.create')); ?>"
                                    class="text-blue-600 hover:underline">+ Tambah pasien baru</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Dokter & Jadwal</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pilih Dokter <span class="text-red-500">*</span>
                            </label>
                            <select name="doctor_id" id="doctor-select" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['doctor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- Pilih Dokter --</option>
                                <?php if(isset($doctors)): ?>
                                    <?php $__currentLoopData = $doctors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $doctor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($doctor->id); ?>" <?php if(old('doctor_id') == $doctor->id): echo 'selected'; endif; ?>>
                                            <?php echo e($doctor->name); ?> - <?php echo e($doctor->specialization); ?>

                                        </option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php endif; ?>
                            </select>
                            <?php $__errorArgs = ['doctor_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tanggal Janji Temu <span class="text-red-500">*</span>
                            </label>
                            <input type="date" name="appointment_date" id="appointment-date"
                                value="<?php echo e(old('appointment_date', request('date'))); ?>" min="<?php echo e(date('Y-m-d')); ?>"
                                required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['appointment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                            <?php $__errorArgs = ['appointment_date'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Mulai <span class="text-red-500">*</span>
                                </label>
                                <input type="time" name="start_time" value="<?php echo e(old('start_time')); ?>" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <?php $__errorArgs = ['start_time'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                    <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                                <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Waktu Selesai
                                </label>
                                <input type="time" name="end_time" value="<?php echo e(old('end_time')); ?>"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tipe Layanan <span class="text-red-500">*</span>
                            </label>
                            <select name="service_type" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 <?php $__errorArgs = ['service_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> border-red-500 <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>">
                                <option value="">-- Pilih Layanan --</option>
                                <option value="Konsultasi Umum" <?php if(old('service_type') === 'Konsultasi Umum'): echo 'selected'; endif; ?>>Konsultasi Umum</option>
                                <option value="Pemeriksaan Rutin" <?php if(old('service_type') === 'Pemeriksaan Rutin'): echo 'selected'; endif; ?>>Pemeriksaan Rutin
                                </option>
                                <option value="Konsultasi Spesialis" <?php if(old('service_type') === 'Konsultasi Spesialis'): echo 'selected'; endif; ?>>Konsultasi Spesialis
                                </option>
                                <option value="Tindakan Medis" <?php if(old('service_type') === 'Tindakan Medis'): echo 'selected'; endif; ?>>Tindakan Medis</option>
                                <option value="Follow-up" <?php if(old('service_type') === 'Follow-up'): echo 'selected'; endif; ?>>Follow-up</option>
                                <option value="Emergency" <?php if(old('service_type') === 'Emergency'): echo 'selected'; endif; ?>>Emergency</option>
                            </select>
                            <?php $__errorArgs = ['service_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                                <p class="mt-1 text-xs text-red-500"><?php echo e($message); ?></p>
                            <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Informasi Tambahan</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Prioritas
                            </label>
                            <select name="priority"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="normal" <?php if(old('priority', 'normal') === 'normal'): echo 'selected'; endif; ?>>Normal</option>
                                <option value="urgent" <?php if(old('priority') === 'urgent'): echo 'selected'; endif; ?>>Urgent</option>
                                <option value="high" <?php if(old('priority') === 'high'): echo 'selected'; endif; ?>>High</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Keluhan / Catatan
                            </label>
                            <textarea name="notes" rows="4" placeholder="Deskripsikan keluhan atau catatan tambahan..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('notes')); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Metode Pembayaran
                            </label>
                            <select name="payment_method"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Metode --</option>
                                <option value="self_pay" <?php if(old('payment_method') === 'self_pay'): echo 'selected'; endif; ?>>Bayar Sendiri</option>
                                <option value="bpjs" <?php if(old('payment_method') === 'bpjs'): echo 'selected'; endif; ?>>BPJS</option>
                                <option value="insurance" <?php if(old('payment_method') === 'insurance'): echo 'selected'; endif; ?>>Asuransi Swasta</option>
                                <option value="corporate" <?php if(old('payment_method') === 'corporate'): echo 'selected'; endif; ?>>Korporat</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="send_notification" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Kirim notifikasi ke
                                    pasien</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('healthcare.appointments.index')); ?>"
                    class="px-6 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Buat Janji Temu
                </button>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            // Auto-fill end time when start time changes
            document.querySelector('input[name="start_time"]')?.addEventListener('change', function() {
                const startTime = this.value;
                if (startTime && !document.querySelector('input[name="end_time"]').value) {
                    const [hours, minutes] = startTime.split(':');
                    const endDate = new Date();
                    endDate.setHours(parseInt(hours) + 1);
                    endDate.setMinutes(minutes);
                    const endTime = endDate.toTimeString().slice(0, 5);
                    document.querySelector('input[name="end_time"]').value = endTime;
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\appointments\book.blade.php ENDPATH**/ ?>