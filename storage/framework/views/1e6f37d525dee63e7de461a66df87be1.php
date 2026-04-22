

<?php $__env->startSection('title', 'Aging Report'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clock text-primary"></i> Accounts Receivable Aging
            </h1>
            <p class="text-muted mb-0">Outstanding invoices by age category</p>
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
                        <div class="col-md-2">
                            <h4 class="text-primary">Rp <?php echo e(number_format($stats['total_outstanding'] ?? 0, 0, ',', '.')); ?>

                            </h4>
                            <small class="text-muted">Total Outstanding</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-success">Rp <?php echo e(number_format($stats['current'] ?? 0, 0, ',', '.')); ?></h4>
                            <small class="text-muted">Current</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-info">Rp <?php echo e(number_format($stats['days_30'] ?? 0, 0, ',', '.')); ?></h4>
                            <small class="text-muted">1-30 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-warning">Rp <?php echo e(number_format($stats['days_60'] ?? 0, 0, ',', '.')); ?></h4>
                            <small class="text-muted">31-60 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-danger">Rp <?php echo e(number_format($stats['days_90'] ?? 0, 0, ',', '.')); ?></h4>
                            <small class="text-muted">61-90 Days</small>
                        </div>
                        <div class="col-md-2">
                            <h4 class="text-dark">Rp <?php echo e(number_format($stats['over_90'] ?? 0, 0, ',', '.')); ?></h4>
                            <small class="text-muted">90+ Days</small>
                        </div>
                    </div>
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
                                    <th>Invoice #</th>
                                    <th>Patient</th>
                                    <th>Invoice Date</th>
                                    <th>Due Date</th>
                                    <th>Amount</th>
                                    <th>Days Overdue</th>
                                    <th>Category</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $invoices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $invoice): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($invoice->invoice_number); ?></code></td>
                                        <td><?php echo e($invoice->patient?->name ?? '-'); ?></td>
                                        <td><?php echo e($invoice->invoice_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td><?php echo e($invoice->due_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td><strong>Rp
                                                <?php echo e(number_format($invoice->outstanding_amount ?? 0, 0, ',', '.')); ?></strong>
                                        </td>
                                        <td>
                                            <?php
                                                $daysOverdue = $invoice->due_date?->diffInDays(now()) ?? 0;
                                            ?>
                                            <?php if($daysOverdue > 0): ?>
                                                <span class="text-danger fw-bold"><?php echo e($daysOverdue); ?> days</span>
                                            <?php else: ?>
                                                <span class="text-success">Not due</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($daysOverdue <= 0): ?>
                                                <span class="badge bg-success">Current</span>
                                            <?php elseif($daysOverdue <= 30): ?>
                                                <span class="badge bg-info">1-30 Days</span>
                                            <?php elseif($daysOverdue <= 60): ?>
                                                <span class="badge bg-warning">31-60 Days</span>
                                            <?php elseif($daysOverdue <= 90): ?>
                                                <span class="badge bg-danger">61-90 Days</span>
                                            <?php else: ?>
                                                <span class="badge bg-dark">90+ Days</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-bell"></i> Remind
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No outstanding invoices</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($invoices->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\billing\aging-report.blade.php ENDPATH**/ ?>