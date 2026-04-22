

<?php $__env->startSection('title', 'Operating Room Utilization'); ?>

<?php $__env->startSection('header'); ?>
<div class="d-flex justify-content-between align-items-center">
    <div>
        <h1 class="h3 mb-0">
            <i class="fas fa-chart-pie text-primary"></i> OR Utilization Report
        </h1>
        <p class="text-muted mb-0">Operating room efficiency and usage analytics</p>
    </div>
    <div>
        <button class="btn btn-success" onclick="window.print()">
            <i class="fas fa-print"></i> Print
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
                        <h3 class="text-primary"><?php echo e($stats['total_ors'] ?? 0); ?></h3>
                        <small class="text-muted">Total ORs</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-success"><?php echo e($stats['utilization_rate'] ?? 0); ?>%</h3>
                        <small class="text-muted">Utilization Rate</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-info"><?php echo e($stats['avg_turnaround'] ?? 0); ?> min</h3>
                        <small class="text-muted">Avg Turnaround</small>
                    </div>
                    <div class="col-md-3">
                        <h3 class="text-warning"><?php echo e($stats['cancellation_rate'] ?? 0); ?>%</h3>
                        <small class="text-muted">Cancellation Rate</small>
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
                <h5 class="mb-0">
                    <i class="fas fa-door-open"></i> OR Utilization by Room
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Available hrs</th>
                                <th>Used hrs</th>
                                <th>Utilization</th>
                                <th>Surgeries</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $orUtilization ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $or): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($or['name']); ?></strong></td>
                                <td><?php echo e($or['available_hours']); ?></td>
                                <td><?php echo e($or['used_hours']); ?></td>
                                <td>
                                    <div class="progress" style="height: 20px;">
                                        <div class="progress-bar bg-<?php echo e($or['utilization'] > 80 ? 'success' : ($or['utilization'] > 50 ? 'warning' : 'danger')); ?>" 
                                             style="width: <?php echo e($or['utilization']); ?>%">
                                            <?php echo e($or['utilization']); ?>%
                                        </div>
                                    </div>
                                </td>
                                <td><?php echo e($or['surgery_count']); ?></td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted">No data available</td>
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
                <h5 class="mb-0">
                    <i class="fas fa-procedures"></i> Surgeries by Type
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm">
                        <thead>
                            <tr>
                                <th>Surgery Type</th>
                                <th>Count</th>
                                <th>Avg Duration</th>
                                <th>Success Rate</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $surgeryTypes ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><?php echo e($type['name']); ?></td>
                                <td><?php echo e($type['count']); ?></td>
                                <td><?php echo e($type['avg_duration']); ?> min</td>
                                <td>
                                    <span class="badge bg-success"><?php echo e($type['success_rate']); ?>%</span>
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

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-calendar-week"></i> Weekly Schedule
                </h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead>
                            <tr>
                                <th>OR</th>
                                <th>Monday</th>
                                <th>Tuesday</th>
                                <th>Wednesday</th>
                                <th>Thursday</th>
                                <th>Friday</th>
                                <th>Saturday</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__empty_1 = true; $__currentLoopData = $weeklySchedule ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr>
                                <td><strong><?php echo e($schedule['or_name']); ?></strong></td>
                                <?php $__currentLoopData = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <td class="text-center">
                                    <?php if(isset($schedule[$day]) && $schedule[$day] > 0): ?>
                                        <span class="badge bg-primary"><?php echo e($schedule[$day); ?> surgeries</span>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted">No schedule data available</td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\surgery\utilization.blade.php ENDPATH**/ ?>