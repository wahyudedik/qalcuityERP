

<?php $__env->startSection('title', 'Surgery Teams'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-users text-primary"></i> Surgery Teams
            </h1>
            <p class="text-muted mb-0">Manage surgical team assignments</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addTeamModal">
                <i class="fas fa-plus"></i> Create Team
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $teams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $team): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <strong><?php echo e($team->team_name ?? 'Surgery Team'); ?></strong>
                        <span class="badge bg-<?php echo e($team->is_active ? 'success' : 'secondary'); ?>">
                            <?php echo e($team->is_active ? 'Active' : 'Inactive'); ?>

                        </span>
                    </div>
                    <div class="card-body">
                        <h6 class="text-primary">Team Members</h6>
                        <div class="mb-3">
                            <?php $__empty_2 = true; $__currentLoopData = $team->members ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $member): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                <div class="d-flex justify-content-between mb-2 p-2 bg-light rounded">
                                    <div>
                                        <strong><?php echo e($member['role'] ?? '-'); ?></strong>
                                        <br><small><?php echo e($member['name'] ?? '-'); ?></small>
                                    </div>
                                    <span class="badge bg-<?php echo e($member['available'] ? 'success' : 'danger'); ?>">
                                        <?php echo e($member['available'] ? 'Available' : 'Busy'); ?>

                                    </span>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                <p class="text-muted text-center">No members assigned</p>
                            <?php endif; ?>
                        </div>
                        <div class="row text-center">
                            <div class="col-4">
                                <h5 class="text-success"><?php echo e($team->surgeries_completed ?? 0); ?></h5>
                                <small class="text-muted">Completed</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-warning"><?php echo e($team->surgeries_scheduled ?? 0); ?></h5>
                                <small class="text-muted">Scheduled</small>
                            </div>
                            <div class="col-4">
                                <h5 class="text-info"><?php echo e($team->success_rate ?? 0); ?>%</h5>
                                <small class="text-muted">Success Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-users fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No surgery teams created yet</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\surgery\teams.blade.php ENDPATH**/ ?>