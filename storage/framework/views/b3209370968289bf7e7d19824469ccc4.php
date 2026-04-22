

<?php $__env->startSection('title', 'Bed Occupancy Rate (BOR)'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bed text-primary"></i> Bed Occupancy Rate (BOR)
            </h1>
            <p class="text-muted mb-0">Hospital bed utilization metrics</p>
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
                            <h2 class="text-primary"><?php echo e($stats['current_bor'] ?? 0); ?>%</h2>
                            <small class="text-muted">Current BOR</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['avg_bor_month'] ?? 0); ?>%</h2>
                            <small class="text-muted">Monthly Average</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['total_beds'] ?? 0); ?></h2>
                            <small class="text-muted">Total Beds</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning"><?php echo e($stats['occupied_beds'] ?? 0); ?></h2>
                            <small class="text-muted">Occupied</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">BOR by Ward</h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $wardBOR ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong><?php echo e($ward['name']); ?></strong>
                                <span><?php echo e($ward['bor']); ?>%</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php echo e($ward['bor'] > 85 ? 'danger' : ($ward['bor'] > 60 ? 'warning' : 'success')); ?>"
                                    style="width: <?php echo e($ward['bor']); ?>%">
                                    <?php echo e($ward['bor']); ?>%
                                </div>
                            </div>
                            <small class="text-muted"><?php echo e($ward['occupied']); ?>/<?php echo e($ward['total']); ?> beds occupied</small>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted text-center">No ward data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">BOR Trend (Last 7 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>BOR</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $borTrend ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($day['date']); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo e($day['bor'] > 85 ? 'danger' : ($day['bor'] > 60 ? 'warning' : 'success')); ?>">
                                                <?php echo e($day['bor']); ?>%
                                            </span>
                                        </td>
                                        <td><?php echo e($day['occupied']); ?></td>
                                        <td><?php echo e($day['available']); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No trend data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>BOR Benchmark:</strong> Ideal BOR is 60-85%. Above 85% indicates overcapacity, below 60% indicates
                underutilization.
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\bor.blade.php ENDPATH**/ ?>