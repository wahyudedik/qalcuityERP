

<?php $__env->startSection('title', 'Mortality Rate'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-line text-primary"></i> Mortality Rate Analysis
            </h1>
            <p class="text-muted mb-0">Patient mortality metrics and trends</p>
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
                            <h2 class="text-danger"><?php echo e($stats['mortality_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Overall Mortality Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-primary"><?php echo e($stats['total_deaths'] ?? 0); ?></h2>
                            <small class="text-muted">Total Deaths</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['total_discharges'] ?? 0); ?></h2>
                            <small class="text-muted">Total Discharges</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['benchmark_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">National Benchmark</small>
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
                    <h5 class="mb-0">Mortality by Department</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Deaths</th>
                                    <th>Discharges</th>
                                    <th>Rate</th>
                                    <th>vs Benchmark</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $deptMortality ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($dept['name']); ?></strong></td>
                                        <td><?php echo e($dept['deaths']); ?></td>
                                        <td><?php echo e($dept['discharges']); ?></td>
                                        <td>
                                            <span
                                                class="badge bg-<?php echo e($dept['rate'] > ($stats['benchmark_rate'] ?? 3) ? 'danger' : 'success'); ?>">
                                                <?php echo e($dept['rate']); ?>%
                                            </span>
                                        </td>
                                        <td>
                                            <?php
                                                $diff = $dept['rate'] - ($stats['benchmark_rate'] ?? 3);
                                            ?>
                                            <?php if($diff > 0): ?>
                                                <span class="text-danger">+<?php echo e(number_format($diff, 2)); ?>%</span>
                                            <?php else: ?>
                                                <span class="text-success"><?php echo e(number_format($diff, 2)); ?>%</span>
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
                    <h5 class="mb-0">Monthly Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Month</th>
                                    <th>Deaths</th>
                                    <th>Rate</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $monthlyTrend ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $month): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($month['month']); ?></td>
                                        <td><?php echo e($month['deaths']); ?></td>
                                        <td><strong><?php echo e($month['rate']); ?>%</strong></td>
                                        <td>
                                            <?php if($month['trend'] > 0): ?>
                                                <span class="text-danger"><i class="fas fa-arrow-up"></i></span>
                                            <?php elseif($month['trend'] < 0): ?>
                                                <span class="text-success"><i class="fas fa-arrow-down"></i></span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-minus"></i></span>
                                            <?php endif; ?>
                                        </td>
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
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\mortality.blade.php ENDPATH**/ ?>