

<?php $__env->startSection('title', 'IoT Device Management'); ?>

<?php $__env->startSection('content'); ?>
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0">IoT Device Management</h4>
            <small class="text-muted">ESP32 · Arduino · Raspberry Pi</small>
        </div>
        <a href="<?php echo e(route('iot.devices.create')); ?>" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Tambah Device
        </a>
    </div>

    <?php if(session('success')): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo e(session('success')); ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-primary"><?php echo e($devices->total()); ?></div>
                <div class="text-muted small">Total Device</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-success"><?php echo e($devices->where('is_connected', true)->count()); ?></div>
                <div class="text-muted small">Online</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-warning"><?php echo e($devices->where('is_active', true)->where('is_connected', false)->count()); ?></div>
                <div class="text-muted small">Aktif / Offline</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-2 fw-bold text-secondary"><?php echo e($devices->where('is_active', false)->count()); ?></div>
                <div class="text-muted small">Nonaktif</div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Device</th>
                            <th>Tipe</th>
                            <th>Lokasi</th>
                            <th>Module</th>
                            <th>Status</th>
                            <th>Terakhir Online</th>
                            <th>Log</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td>
                                <div class="fw-semibold"><?php echo e($device->name); ?></div>
                                <small class="text-muted font-monospace"><?php echo e($device->device_id); ?></small>
                            </td>
                            <td>
                                <?php
                                    $icons = ['esp32'=>'🔌','arduino'=>'⚡','raspberry_pi'=>'🍓','generic'=>'📡'];
                                ?>
                                <?php echo e($icons[$device->device_type] ?? '📡'); ?>

                                <?php echo e(\App\Models\IotDevice::deviceTypes()[$device->device_type] ?? $device->device_type); ?>

                            </td>
                            <td><?php echo e($device->location ?? '-'); ?></td>
                            <td>
                                <span class="badge bg-info-subtle text-info">
                                    <?php echo e(\App\Models\IotDevice::targetModules()[$device->target_module] ?? $device->target_module); ?>

                                </span>
                            </td>
                            <td>
                                <?php if(!$device->is_active): ?>
                                    <span class="badge bg-secondary">Nonaktif</span>
                                <?php elseif($device->is_connected): ?>
                                    <span class="badge bg-success">Online</span>
                                <?php else: ?>
                                    <span class="badge bg-warning text-dark">Offline</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <small><?php echo e($device->last_seen_at ? $device->last_seen_at->diffForHumans() : 'Belum pernah'); ?></small>
                            </td>
                            <td><small class="text-muted"><?php echo e(number_format($device->telemetry_logs_count)); ?></small></td>
                            <td>
                                <a href="<?php echo e(route('iot.devices.show', $device)); ?>" class="btn btn-sm btn-outline-primary">Detail</a>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-microchip fa-2x mb-2 d-block"></i>
                                Belum ada device IoT. <a href="<?php echo e(route('iot.devices.create')); ?>">Tambah sekarang</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-3"><?php echo e($devices->links()); ?></div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\iot\devices\index.blade.php ENDPATH**/ ?>