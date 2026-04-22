

<?php $__env->startSection('title', 'Surgery Equipment'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-tools text-primary"></i> Surgery Equipment
            </h1>
            <p class="text-muted mb-0">Track and manage surgical equipment</p>
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
                            <h3 class="text-success"><?php echo e($equipment->where('status', 'available')->count()); ?></h3>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning"><?php echo e($equipment->where('status', 'in_use')->count()); ?></h3>
                            <small class="text-muted">In Use</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info"><?php echo e($equipment->where('status', 'sterilizing')->count()); ?></h3>
                            <small class="text-muted">Sterilizing</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger"><?php echo e($equipment->where('status', 'maintenance')->count()); ?></h3>
                            <small class="text-muted">Maintenance</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Equipment</th>
                                    <th>Category</th>
                                    <th>Serial #</th>
                                    <th>Location</th>
                                    <th>Status</th>
                                    <th>Last Sterilized</th>
                                    <th>Next Maintenance</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $equipment; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($item->name); ?></strong></td>
                                        <td><?php echo e(ucfirst($item->category ?? '-')); ?></td>
                                        <td><code><?php echo e($item->serial_number ?? '-'); ?></code></td>
                                        <td><?php echo e($item->location ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'available' => 'success',
                                                    'in_use' => 'warning',
                                                    'sterilizing' => 'info',
                                                    'maintenance' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$item->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $item->status))); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <?php if($item->last_sterilized): ?>
                                                <?php echo e($item->last_sterilized->format('d/m/Y')); ?>

                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($item->next_maintenance): ?>
                                                <?php if($item->next_maintenance->isPast()): ?>
                                                    <span
                                                        class="text-danger fw-bold"><?php echo e($item->next_maintenance->format('d/m/Y')); ?></span>
                                                <?php else: ?>
                                                    <?php echo e($item->next_maintenance->format('d/m/Y')); ?>

                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No surgical equipment found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($equipment->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\surgery\equipment.blade.php ENDPATH**/ ?>