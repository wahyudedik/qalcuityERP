

<?php $__env->startSection('title', 'Patient Satisfaction'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-smile text-primary"></i> Patient Satisfaction Score
            </h1>
            <p class="text-muted mb-0">Patient experience and satisfaction metrics</p>
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
                            <h2 class="text-warning">
                                <?php for($i = 1; $i <= 5; $i++): ?>
                                    <i
                                        class="fas fa-star <?php echo e($i <= ($stats['avg_rating'] ?? 0) ? 'text-warning' : 'text-muted'); ?>"></i>
                                <?php endfor; ?>
                            </h2>
                            <small class="text-muted">Average Rating</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-success"><?php echo e($stats['satisfaction_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Satisfaction Rate</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-primary"><?php echo e($stats['total_surveys'] ?? 0); ?></h2>
                            <small class="text-muted">Total Surveys</small>
                        </div>
                        <div class="col-md-3">
                            <h2 class="text-info"><?php echo e($stats['response_rate'] ?? 0); ?>%</h2>
                            <small class="text-muted">Response Rate</small>
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
                    <h5 class="mb-0">Satisfaction by Category</h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $categoryScores ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <strong><?php echo e($category['name']); ?></strong>
                                <span><?php echo e($category['score']); ?>/5.0</span>
                            </div>
                            <div class="progress" style="height: 25px;">
                                <div class="progress-bar bg-<?php echo e($category['score'] >= 4 ? 'success' : ($category['score'] >= 3 ? 'warning' : 'danger')); ?>"
                                    style="width: <?php echo e(($category['score'] / 5) * 100); ?>%">
                                    <?php echo e($category['score']); ?>/5.0
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted text-center">No category data available</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Satisfaction Trend</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th>Rating</th>
                                    <th>Surveys</th>
                                    <th>Trend</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $satisfactionTrend ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $period): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($period['period']); ?></td>
                                        <td>
                                            <strong><?php echo e($period['rating']); ?>/5.0</strong>
                                        </td>
                                        <td><?php echo e($period['surveys']); ?></td>
                                        <td>
                                            <?php if($period['trend'] > 0): ?>
                                                <span class="text-success"><i class="fas fa-arrow-up"></i>
                                                    +<?php echo e($period['trend']); ?>%</span>
                                            <?php elseif($period['trend'] < 0): ?>
                                                <span class="text-danger"><i class="fas fa-arrow-down"></i>
                                                    <?php echo e($period['trend']); ?>%</span>
                                            <?php else: ?>
                                                <span class="text-muted"><i class="fas fa-minus"></i> 0%</span>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Feedback</h5>
                </div>
                <div class="card-body">
                    <?php $__empty_1 = true; $__currentLoopData = $recentFeedback ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feedback): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="mb-3 p-3 bg-light rounded">
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <strong><?php echo e($feedback['patient_name'] ?? 'Anonymous'); ?></strong>
                                    <br><small class="text-muted"><?php echo e($feedback['created_at'] ?? '-'); ?></small>
                                </div>
                                <div>
                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                        <i
                                            class="fas fa-star <?php echo e($i <= ($feedback['rating'] ?? 0) ? 'text-warning' : 'text-muted'); ?>"></i>
                                    <?php endfor; ?>
                                </div>
                            </div>
                            <p class="mb-0"><?php echo e($feedback['comment'] ?? 'No comment'); ?></p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-muted text-center">No recent feedback</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\analytics\satisfaction.blade.php ENDPATH**/ ?>