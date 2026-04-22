

<?php $__env->startSection('title', 'Sterilization Tracking'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-virus text-primary"></i> Sterilization Tracking
            </h1>
            <p class="text-muted mb-0">Track equipment sterilization cycles</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCycleModal">
                <i class="fas fa-plus"></i> Log Cycle
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
                            <h3 class="text-success"><?php echo e($cycles->where('status', 'completed')->count()); ?></h3>
                            <small class="text-muted">Completed Today</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning"><?php echo e($cycles->where('status', 'in_progress')->count()); ?></h3>
                            <small class="text-muted">In Progress</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-info"><?php echo e($equipment->where('status', 'sterile')->count()); ?></h3>
                            <small class="text-muted">Sterile Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger"><?php echo e($equipment->where('status', 'contaminated')->count()); ?></h3>
                            <small class="text-muted">Contaminated</small>
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
                                    <th>Cycle #</th>
                                    <th>Date/Time</th>
                                    <th>Equipment</th>
                                    <th>Sterilizer</th>
                                    <th>Method</th>
                                    <th>Duration</th>
                                    <th>Temperature</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $cycles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cycle): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($cycle->cycle_number); ?></code></td>
                                        <td><?php echo e($cycle->started_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td><strong><?php echo e($cycle->equipment_count ?? 0); ?> items</strong></td>
                                        <td><?php echo e($cycle->sterilizer_name ?? '-'); ?></td>
                                        <td><?php echo e(ucfirst($cycle->method ?? '-')); ?></td>
                                        <td><?php echo e($cycle->duration_minutes ?? '-'); ?> min</td>
                                        <td><?php echo e($cycle->temperature ?? '-'); ?>°C</td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'completed' => 'success',
                                                    'in_progress' => 'warning',
                                                    'failed' => 'danger',
                                                    'scheduled' => 'info',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$cycle->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $cycle->status))); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No sterilization cycles found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($cycles->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\sterilization.blade.php ENDPATH**/ ?>