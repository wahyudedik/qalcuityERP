

<?php $__env->startSection('title', 'Edit Device — ' . $device->name); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="mb-4">
        <a href="<?php echo e(route('iot.devices.show', $device)); ?>" class="text-muted text-decoration-none">
            <i class="fas fa-arrow-left me-1"></i> Kembali
        </a>
        <h4 class="mt-2 mb-0">Edit Device: <?php echo e($device->name); ?></h4>
    </div>

    <div class="row">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form action="<?php echo e(route('iot.devices.update', $device)); ?>" method="POST">
                        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Device <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> is-invalid <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                value="<?php echo e(old('name', $device->name)); ?>">
                            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><div class="invalid-feedback"><?php echo e($message); ?></div><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Tipe Device</label>
                                <select name="device_type" class="form-select">
                                    <?php $__currentLoopData = $deviceTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val); ?>" <?php echo e($device->device_type == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Target Module</label>
                                <select name="target_module" class="form-select">
                                    <?php $__currentLoopData = $targetModules; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($val); ?>" <?php echo e($device->target_module == $val ? 'selected' : ''); ?>><?php echo e($label); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Lokasi Fisik</label>
                            <input type="text" name="location" class="form-control"
                                value="<?php echo e(old('location', $device->location)); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Tipe Sensor</label>
                            <div class="row g-2">
                                <?php $__currentLoopData = $sensorTypes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="sensor_types[]"
                                            value="<?php echo e($val); ?>" id="sensor_<?php echo e($val); ?>"
                                            <?php echo e(in_array($val, old('sensor_types', $device->sensor_types ?? [])) ? 'checked' : ''); ?>>
                                        <label class="form-check-label" for="sensor_<?php echo e($val); ?>"><?php echo e($label); ?></label>
                                    </div>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Versi Firmware</label>
                            <input type="text" name="firmware_version" class="form-control"
                                value="<?php echo e(old('firmware_version', $device->firmware_version)); ?>">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Catatan</label>
                            <textarea name="notes" class="form-control" rows="2"><?php echo e(old('notes', $device->notes)); ?></textarea>
                        </div>

                        <div class="form-check form-switch mb-4">
                            <input class="form-check-input" type="checkbox" name="is_active" id="is_active"
                                <?php echo e($device->is_active ? 'checked' : ''); ?>>
                            <label class="form-check-label" for="is_active">Device Aktif</label>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            <a href="<?php echo e(route('iot.devices.show', $device)); ?>" class="btn btn-outline-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-semibold">Info Device</div>
                <div class="card-body">
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <td class="text-muted">Device ID</td>
                            <td><code><?php echo e($device->device_id); ?></code></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Dibuat</td>
                            <td><?php echo e($device->created_at->format('d M Y')); ?></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Terakhir Online</td>
                            <td><?php echo e($device->last_seen_at ? $device->last_seen_at->diffForHumans() : '-'); ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\iot\devices\edit.blade.php ENDPATH**/ ?>