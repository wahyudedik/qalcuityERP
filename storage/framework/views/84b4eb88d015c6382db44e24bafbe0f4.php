

<?php $__env->startSection('title', 'Lab Orders'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-flask text-primary"></i> Laboratory Orders
            </h1>
            <p class="text-muted mb-0">Manage lab test orders and track results</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addOrderModal">
                <i class="fas fa-plus"></i> New Lab Order
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($orders->where('status', 'pending')->count()); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($orders->where('status', 'in_progress')->count()); ?></h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($orders->where('status', 'completed')->count()); ?></h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($orders->where('status', 'cancelled')->count()); ?></h3>
                    <small class="text-muted">Cancelled</small>
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
                                    <th>Order #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Test Type</th>
                                    <th>Priority</th>
                                    <th>Ordered By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $orders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $order): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($order->order_number); ?></code></td>
                                        <td><?php echo e($order->order_date?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $order->patient)); ?>">
                                                <?php echo e($order->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($order->test_type ?? '-'); ?></td>
                                        <td>
                                            <?php if($order->priority == 'stat'): ?>
                                                <span class="badge bg-danger">STAT</span>
                                            <?php elseif($order->priority == 'urgent'): ?>
                                                <span class="badge bg-warning">Urgent</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Routine</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($order->ordered_by?->name ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'pending' => 'info',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'cancelled' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$order->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $order->status))); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo e(route('healthcare.emr.lab-orders.show', $order)); ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if($order->status == 'pending'): ?>
                                                    <button class="btn btn-outline-success btn-sm"
                                                        onclick="processOrder(<?php echo e($order->id); ?>)">
                                                        <i class="fas fa-play"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No lab orders found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($orders->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- New Lab Order Modal -->
    <div class="modal fade" id="addOrderModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.emr.lab-orders.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">New Laboratory Order</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Test Type</label>
                                <input type="text" name="test_type" class="form-control"
                                    placeholder="e.g., Complete Blood Count" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Priority</label>
                                <select name="priority" class="form-select" required>
                                    <option value="routine">Routine</option>
                                    <option value="urgent">Urgent</option>
                                    <option value="stat">STAT (Immediate)</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Order Date</label>
                                <input type="datetime-local" name="order_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Clinical Notes</label>
                            <textarea name="clinical_notes" class="form-control" rows="3" placeholder="Reason for test, clinical context..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function processOrder(orderId) {
                if (confirm('Process this lab order?')) {
                    // Implement AJAX order processing
                    window.location.reload();
                }
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\emr\lab-orders.blade.php ENDPATH**/ ?>