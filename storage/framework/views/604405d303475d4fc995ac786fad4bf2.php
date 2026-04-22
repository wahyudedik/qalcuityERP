

<?php $__env->startSection('title', 'Average Length of Stay (ALOS)'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clock text-primary"></i> Average Length of Stay (ALOS)
            </h1>
            <p class="text-muted mb-0">Patient hospitalization duration metrics</p>
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
                            <h2 class="text-primary"><?php echo e($stats['alos'] ?? 0); ?> days</h2>
                            <small class="text-muted">Overall ALOS</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['total_discharges'] ?? 0); ?></h2>
                            <small class="text-muted">Total Discharges</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['total_days'] ?? 0); ?></h2>
                            <small class="text-muted">Total Patient Days</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning"><?php echo e($stats['target_alos'] ?? 0); ?> days</h2>
                            <small class="text-muted">Target ALOS</small>
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
                    <h5 class="mb-0">ALOS by Department</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>ALOS</th>
                                    <th>Discharges</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $deptALOS ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($dept['name']); ?></strong></td>
                                        <td><?php echo e($dept['alos']); ?> days</td>
                                        <td><?php echo e($dept['discharges']); ?></td>
                                        <td>
                                            <?php if($dept['alos'] <= ($stats['target_alos'] ?? 5)): ?>
                                                <span class="badge bg-success">On Target</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Above Target</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">ALOS Trend (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>ALOS</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $alosTrend ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($period['period']); ?></td>
                                        <td><strong><?php echo e($period['alos']); ?> days</strong></td>
                                        <td>
                                            <?php if($period['trend'] > 0): ?>
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i>
                                                    +<?php echo e($period['trend']); ?>%</span>
                                            <?php elseif($period['trend'] < 0): ?>
                                                <span class="text-success"><i class="fas fa-arrow-down"></i>
                                                    <?php echo e($period['trend']); ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-minus"></i> 0%</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No trend data</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\alos.blade.php ENDPATH**/ ?>