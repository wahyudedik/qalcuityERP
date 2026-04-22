

<?php $__env->startSection('title', 'Medical Waste Management'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-biohazard text-primary"></i> Medical Waste Management
            </h1>
            <p class="text-muted mb-0">Track and manage medical waste disposal</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addWasteModal">
                <i class="fas fa-plus"></i> Log Waste
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e(number_format($stats['total_waste_kg'] ?? 0, 2)); ?> kg</h3>
                    <small class="text-muted">Total This Month</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['infectious_kg'] ?? 0); ?> kg</h3>
                    <small class="text-muted">Infectious Waste</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['sharps_kg'] ?? 0); ?> kg</h3>
                    <small class="text-muted">Sharps</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['disposed_kg'] ?? 0); ?> kg</h3>
                    <small class="text-muted">Properly Disposed</small>
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
                                    <th>Log #</th>
                                    <th>Date</th>
                                    <th>Waste Type</th>
                                    <th>Weight (kg)</th>
                                    <th>Source</th>
                                    <th>Disposal Method</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $wasteLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($log->log_number); ?></code></td>
                                        <td><?php echo e($log->created_at->format('d/m/Y H:i')); ?></td>
                                        <td>
                                            <?php
                                                $typeColors = [
                                                    'infectious' => 'danger',
                                                    'sharps' => 'warning',
                                                    'pharmaceutical' => 'info',
                                                    'general' => 'secondary',
                                                    'chemical' => 'dark',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($typeColors[$log->waste_type] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($log->waste_type ?? '-')); ?>

                                            </span>
                                        </td>
                                        <td><strong><?php echo e($log->weight_kg ?? 0); ?> kg</strong></td>
                                        <td><?php echo e($log->source_department ?? '-'); ?></td>
                                        <td><?php echo e(ucfirst($log->disposal_method ?? '-')); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'collected' => 'info',
                                                    'disposed' => 'success',
                                                    'incinerated' => 'dark',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$log->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($log->status)); ?>

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
                                        <td colspan="8" class="text-center py-4 text-muted">No waste logs found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($wasteLogs->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\waste-management.blade.php ENDPATH**/ ?>