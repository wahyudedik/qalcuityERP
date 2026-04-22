

<?php $__env->startSection('title', 'Inventory Reports'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-chart-bar text-primary"></i> Inventory Reports
            </h1>
            <p class="text-muted mb-0">Medical inventory analytics and insights</p>
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
                            <h3 class="text-primary"><?php echo e($stats['total_items'] ?? 0); ?></h3>
                            <small class="text-muted">Total Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-success">Rp <?php echo e(number_format($stats['total_value'] ?? 0, 0, ',', '.')); ?></h3>
                            <small class="text-muted">Total Value</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-warning"><?php echo e($stats['low_stock'] ?? 0); ?></h3>
                            <small class="text-muted">Low Stock Items</small>
                        </div>
                        <div class="col-md-3">
                            <h3 class="text-danger"><?php echo e($stats['expiring_soon'] ?? 0); ?></h3>
                            <small class="text-muted">Expiring Soon</small>
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
                        <i class="fas fa-boxes"></i> Inventory by Category
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Items</th>
                                    <th>Value</th>
                                    <th>% of Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $categoryStats ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($cat['name']); ?></td>
                                        <td><?php echo e($cat['items']); ?></td>
                                        <td>Rp <?php echo e(number_format($cat['value'], 0, ',', '.')); ?></td>
                                        <td>
                                            <div class="progress" style="height: 20px;">
                                                <div class="progress-bar bg-primary"
                                                    style="width: <?php echo e($cat['percentage']); ?>%">
                                                    <?php echo e($cat['percentage']); ?>%
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
                    <h5 class="mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Critical Stock Alerts
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Current</th>
                                    <th>Min. Stock</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $criticalStock ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($item['name']); ?></strong></td>
                                        <td class="text-danger fw-bold"><?php echo e($item['stock']); ?></td>
                                        <td><?php echo e($item['min_stock']); ?></td>
                                        <td>
                                            <?php if($item['stock'] == 0): ?>
                                                <span class="badge bg-danger">Out of Stock</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Critical</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">All items well stocked</td>
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
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-chart-line"></i> Stock Movement (Last 7 Days)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Received</th>
                                    <th>Used</th>
                                    <th>Net Change</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $stockMovement ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $day): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($day['date']); ?></td>
                                        <td class="text-success">+<?php echo e($day['received']); ?></td>
                                        <td class="text-danger">-<?php echo e($day['used']); ?></td>
                                        <td>
                                            <?php
                                                $net = $day['received'] - $day['used'];
                                            ?>
                                            <span class="<?php echo e($net >= 0 ? 'text-success' : 'text-danger'); ?> fw-bold">
                                                <?php echo e($net >= 0 ? '+' : ''); ?><?php echo e($net); ?>

                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No movement data</td>
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
                        <i class="fas fa-calendar-times"></i> Expiration Tracking
                    </h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Batch</th>
                                    <th>Qty</th>
                                    <th>Expiry Date</th>
                                    <th>Days Left</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $expiringItems ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($item['name']); ?></td>
                                        <td><code><?php echo e($item['batch']); ?></code></td>
                                        <td><?php echo e($item['quantity']); ?></td>
                                        <td><?php echo e($item['expiry_date']); ?></td>
                                        <td>
                                            <?php
                                                $days = $item['days_left'];
                                            ?>
                                            <span
                                                class="badge bg-<?php echo e($days <= 7 ? 'danger' : ($days <= 30 ? 'warning' : 'info')); ?>">
                                                <?php echo e($days); ?> days
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">No items expiring soon</td>
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

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\reports.blade.php ENDPATH**/ ?>