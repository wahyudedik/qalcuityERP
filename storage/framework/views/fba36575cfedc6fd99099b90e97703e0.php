

<?php $__env->startSection('title', 'Insurance Claims'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> Insurance Claims
            </h1>
            <p class="text-muted mb-0">Manage and track insurance claim submissions</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($claims->where('status', 'pending')->count()); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($claims->where('status', 'submitted')->count()); ?></h3>
                    <small class="text-muted">Submitted</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($claims->where('status', 'approved')->count()); ?></h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($claims->where('status', 'rejected')->count()); ?></h3>
                    <small class="text-muted">Rejected</small>
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
                                    <th>Claim #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Insurance</th>
                                    <th>Amount</th>
                                    <th>Approved</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($claim->claim_number); ?></code></td>
                                        <td><?php echo e($claim->claim_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $claim->patient)); ?>">
                                                <?php echo e($claim->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($claim->insurance_provider ?? '-'); ?></td>
                                        <td><strong>Rp <?php echo e(number_format($claim->claim_amount ?? 0, 0, ',', '.')); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($claim->approved_amount): ?>
                                                <strong class="text-success">Rp
                                                    <?php echo e(number_format($claim->approved_amount, 0, ',', '.')); ?></strong>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'submitted' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'partial' => 'secondary',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$claim->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($claim->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($claim->status == 'pending'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-paper-plane"></i> Submit
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No insurance claims found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($claims->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\billing\insurance-claims.blade.php ENDPATH**/ ?>