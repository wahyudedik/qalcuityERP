

<?php $__env->startSection('title', 'Payment Plans'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-calendar-alt text-primary"></i> Payment Plans
            </h1>
            <p class="text-muted mb-0">Patient payment installment plans</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Plan #</th>
                                    <th>Patient</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Installments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $paymentPlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($plan->plan_number); ?></code></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $plan->patient)); ?>">
                                                <?php echo e($plan->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td><strong>Rp <?php echo e(number_format($plan->total_amount ?? 0, 0, ',', '.')); ?></strong>
                                        </td>
                                        <td class="text-success">Rp
                                            <?php echo e(number_format($plan->paid_amount ?? 0, 0, ',', '.')); ?></td>
                                        <td class="text-danger">Rp <?php echo e(number_format($plan->balance ?? 0, 0, ',', '.')); ?>

                                        </td>
                                        <td>
                                            <?php echo e($plan->paid_installments ?? 0); ?>/<?php echo e($plan->total_installments ?? 0); ?>

                                            <div class="progress mt-1" style="height: 6px;">
                                                <div class="progress-bar bg-success"
                                                    style="width: <?php echo e($plan->progress_percentage ?? 0); ?>%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'completed' => 'secondary',
                                                    'defaulted' => 'danger',
                                                    'cancelled' => 'warning',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$plan->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($plan->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($plan->status == 'active'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-money-bill"></i> Payment
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No payment plans found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($paymentPlans->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\billing\payment-plans.blade.php ENDPATH**/ ?>