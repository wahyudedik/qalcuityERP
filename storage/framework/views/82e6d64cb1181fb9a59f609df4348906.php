

<?php $__env->startSection('title', 'Bed Occupancy Report'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-bed text-primary"></i> Bed Occupancy Report
            </h1>
            <p class="text-muted mb-0">Hospital bed utilization and occupancy statistics</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Print Report
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
                            <h2 class="text-primary"><?php echo e($stats['total_beds'] ?? 0); ?></h2>
                            <small class="text-muted">Total Beds</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['occupied_beds'] ?? 0); ?></h2>
                            <small class="text-muted">Occupied</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['available_beds'] ?? 0); ?></h2>
                            <small class="text-muted">Available</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-<?php echo e(($stats['occupancy_rate'] ?? 0) > 85 ? 'danger' : 'warning'); ?>">
                                <?php echo e($stats['occupancy_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Occupancy Rate</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-bar"></i> Occupancy by Ward
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Ward</th>
                                    <th>Total</th>
                                    <th>Occupied</th>
                                    <th>Available</th>
                                    <th>Rate</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $wardStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ward): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($ward['name']); ?></td>
                                        <td><?php echo e($ward['total']); ?></td>
                                        <td><?php echo e($ward['occupied']); ?></td>
                                        <td><?php echo e($ward['available']); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-<?php echo e($ward['rate'] > 85 ? 'danger' : ($ward['rate'] > 60 ? 'warning' : 'success')); ?>"
                                                    style="width: <?php echo e($ward['rate']); ?>%">
                                                    <?php echo e($ward['rate']); ?>%
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No ward data available</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-clock"></i> Average Length of Stay
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Department</th>
                                    <th>Avg LOS (days)</th>
                                    <th>Admissions</th>
                                    <th>Discharges</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $departmentStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($dept['name']); ?></td>
                                        <td><strong><?php echo e($dept['avg_los'] ?? 0); ?></strong></td>
                                        <td><?php echo e($dept['admissions'] ?? 0); ?></td>
                                        <td><?php echo e($dept['discharges'] ?? 0); ?></td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No department data</td>
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-bed"></i> Current Bed Status
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Bed Number</th>
                                    <th>Ward</th>
                                    <th>Status</th>
                                    <th>Patient</th>
                                    <th>Admission Date</th>
                                    <th>Length of Stay</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $beds ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $bed): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($bed->bed_number); ?></strong></td>
                                        <td><?php echo e($bed->ward?->name ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'occupied' => 'success',
                                                    'available' => 'info',
                                                    'maintenance' => 'warning',
                                                    'reserved' => 'secondary',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$bed->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($bed->status)); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($bed->currentPatient?->name ?? '-'); ?></td>
                                        <td><?php echo e($bed->currentPatient?->admission?->admission_date?->format('d/m/Y') ?? '-'); ?>

                                        </td>
                                        <td>
                                            <?php if($bed->currentPatient?->admission): ?>
                                                <?php echo e($bed->currentPatient->admission->admission_date->diffInDays(now())); ?>

                                                days
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No bed data available</td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inpatient\occupancy-report.blade.php ENDPATH**/ ?>