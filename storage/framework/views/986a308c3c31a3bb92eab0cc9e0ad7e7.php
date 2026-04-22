

<?php $__env->startSection('title', 'Inventory Requests'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list text-primary"></i> Inventory Requests
            </h1>
            <p class="text-muted mb-0">Medical supply requisition and approval</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRequestModal">
                <i class="fas fa-plus"></i> New Request
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($requests->where('status', 'pending')->count()); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($requests->where('status', 'approved')->count()); ?></h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($requests->where('status', 'fulfilled')->count()); ?></h3>
                    <small class="text-muted">Fulfilled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($requests->where('status', 'rejected')->count()); ?></h3>
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
                                    <th>Request #</th>
                                    <th>Date</th>
                                    <th>Requested By</th>
                                    <th>Department</th>
                                    <th>Items</th>
                                    <th>Priority</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $request): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($request->request_number); ?></code></td>
                                        <td><?php echo e($request->created_at->format('d/m/Y')); ?></td>
                                        <td><?php echo e($request->requested_by?->name ?? '-'); ?></td>
                                        <td><?php echo e($request->department ?? '-'); ?></td>
                                        <td><strong><?php echo e(count($request->items ?? [])); ?> items</strong></td>
                                        <td>
                                            <?php if($request->priority == 'urgent'): ?>
                                                <span class="badge bg-danger">Urgent</span>
                                            <?php elseif($request->priority == 'high'): ?>
                                                <span class="badge bg-warning">High</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Normal</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'approved' => 'info',
                                                    'fulfilled' => 'success',
                                                    'rejected' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$request->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($request->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($request->status == 'pending'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i>
                                                    </button>
                                                    <button class="btn btn-outline-danger btn-sm">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No requests found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($requests->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inventory\requests.blade.php ENDPATH**/ ?>