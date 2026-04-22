

<?php $__env->startSection('title', 'Lab Equipment'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-microscope text-primary"></i> Laboratory Equipment
            </h1>
            <p class="text-muted mb-0">Manage laboratory equipment and maintenance</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addEquipmentModal">
                <i class="fas fa-plus"></i> Add Equipment
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-3">
                            <h3 class="text-success"><?php echo e($equipment->where('status', 'active')->count()); ?></h3>
                            <small class="text-muted">Active</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning"><?php echo e($equipment->where('status', 'maintenance')->count()); ?></h3>
                            <small class="text-muted">Maintenance</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger"><?php echo e($equipment->where('status', 'out_of_order')->count()); ?></h3>
                            <small class="text-muted">Out of Order</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info"><?php echo e($equipment->where('auto_polling', true)->count()); ?></h3>
                            <small class="text-muted">Auto-Polling</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo e($device->name); ?></strong>
                            <span
                                class="badge bg-<?php echo e($device->status == 'active' ? 'success' : ($device->status == 'maintenance' ? 'warning' : 'danger')); ?> ms-2">
                                <?php echo e(ucfirst(str_replace('_', ' ', $device->status))); ?>

                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Manufacturer</small></div>
                            <div class="col-6"><strong><?php echo e($device->manufacturer ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Model</small></div>
                            <div class="col-6"><strong><?php echo e($device->model ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Serial Number</small></div>
                            <div class="col-6"><code><?php echo e($device->serial_number ?? 'N/A'); ?></code></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Location</small></div>
                            <div class="col-6"><strong><?php echo e($device->location ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Last Calibration</small></div>
                            <div class="col-6">
                                <strong>
                                    <?php if($device->last_calibration_date): ?>
                                        <?php echo e($device->last_calibration_date->format('d/m/Y')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Next Calibration</small></div>
                            <div class="col-6">
                                <?php if($device->next_calibration_date): ?>
                                    <?php if($device->next_calibration_date->isPast()): ?>
                                        <strong
                                            class="text-danger"><?php echo e($device->next_calibration_date->format('d/m/Y')); ?></strong>
                                    <?php else: ?>
                                        <strong><?php echo e($device->next_calibration_date->format('d/m/Y')); ?></strong>
                                    <?php endif; ?>
                                <?php else: ?>
                                    N/A
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-6"><small class="text-muted">Auto Polling</small></div>
                            <div class="col-6">
                                <?php if($device->auto_polling): ?>
                                    <span class="badge bg-success">Enabled</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Disabled</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php if($device->auto_polling): ?>
                        <div class="card-footer bg-light">
                            <div class="row text-center">
                                <div class="col-6">
                                    <small class="text-muted">Last Polled</small>
                                    <br><strong><?php echo e($device->last_polled_at?->diffForHumans() ?? 'Never'); ?></strong>
                                </div>
                                <div class="col-6">
                                    <small class="text-muted">Poll Interval</small>
                                    <br><strong><?php echo e($device->poll_interval_minutes ?? 30); ?> min</strong>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-microscope fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No laboratory equipment registered</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Equipment Modal -->
    <div class="modal fade" id="addEquipmentModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.laboratory.equipment.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Add Laboratory Equipment</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Equipment Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Manufacturer</label>
                                <input type="text" name="manufacturer" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Model</label>
                                <input type="text" name="model" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Serial Number</label>
                                <input type="text" name="serial_number" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Location</label>
                                <input type="text" name="location" class="form-control"
                                    placeholder="e.g., Lab Room 1">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select">
                                    <option value="active">Active</option>
                                    <option value="maintenance">Maintenance</option>
                                    <option value="out_of_order">Out of Order</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="auto_polling" id="autoPolling">
                            <label class="form-check-label" for="autoPolling">Enable Auto Polling</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Equipment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\laboratory\equipment.blade.php ENDPATH**/ ?>