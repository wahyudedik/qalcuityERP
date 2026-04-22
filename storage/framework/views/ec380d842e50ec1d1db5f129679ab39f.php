

<?php $__env->startSection('title', 'Infection Rate'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-virus text-primary"></i> Healthcare-Associated Infection Rate
            </h1>
            <p class="text-muted mb-0">Hospital-acquired infection tracking</p>
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
                            <h2 class="text-danger"><?php echo e($stats['infection_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Overall HAI Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-warning"><?php echo e($stats['total_infections'] ?? 0); ?></h2>
                            <small class="text-muted">Total Infections</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['total_admissions'] ?? 0); ?></h2>
                            <small class="text-muted">Total Admissions</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['target_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Target Rate</small>
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
                    <h5 class="mb-0">Infections by Type</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Infection Type</th>
                                    <th>Cases</th>
                                    <th>Rate</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $infectionTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($type['name']); ?></strong></td>
                                        <td><?php echo e($type['cases']); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo e($type['rate'] > ($stats['target_rate'] ?? 2) ? 'danger' : 'success'); ?>">
                                                <?php echo e($type['rate']); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-danger"
                                                    style="width: <?php echo e($type['percentage']); ?>%">
                                                    <?php echo e($type['percentage']); ?>%
                                                </div>
                                            </div>
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
                    <h5 class="mb-0">Infections by Ward</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Infections</th>
                                    <th>Admissions</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $wardInfections ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($ward['name']); ?></td>
                                        <td><?php echo e($ward['infections']); ?></td>
                                        <td><?php echo e($ward['admissions']); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo e($ward['rate'] > ($stats['target_rate'] ?? 2) ? 'danger' : 'success'); ?>">
                                                <?php echo e($ward['rate']); ?>%
                                            </span>
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
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\infection.blade.php ENDPATH**/ ?>