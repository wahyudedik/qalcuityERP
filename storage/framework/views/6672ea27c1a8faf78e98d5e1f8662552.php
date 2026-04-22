

<?php $__env->startSection('title', 'Stock Opname'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-check text-primary"></i> Stock Opname
            </h1>
            <p class="text-muted mb-0">Physical inventory count and reconciliation</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newOpnameModal">
                <i class="fas fa-plus"></i> New Stock Opname
            </button>
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
                                    <th>Opname #</th>
                                    <th>Date</th>
                                    <th>Category</th>
                                    <th>Items Counted</th>
                                    <th>Discrepancies</th>
                                    <th>Status</th>
                                    <th>Conducted By</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $opnames; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opname): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($opname->opname_number); ?></code></td>
                                        <td><?php echo e($opname->opname_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td><?php echo e(ucfirst($opname->category ?? 'All')); ?></td>
                                        <td><?php echo e($opname->items_counted ?? 0); ?></td>
                                        <td>
                                            <?php if($opname->discrepancies > 0): ?>
                                                <span class="text-danger fw-bold"><?php echo e($opname->discrepancies); ?></span>
                                            <?php else: ?>
                                                <span class="text-success">0</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reconciled' => 'info',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$opname->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $opname->status))); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($opname->conducted_by?->name ?? '-'); ?></td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo e(route('healthcare.pharmacy.stock-opname.show', $opname)); ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if($opname->status != 'reconciled'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i> Reconcile
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No stock opname records found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($opnames->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- New Stock Opname Modal -->
    <div class="modal fade" id="newOpnameModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.pharmacy.stock-opname.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">New Stock Opname</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Category</label>
                            <select name="category" class="form-select">
                                <option value="all">All Categories</option>
                                <option value="medications">Medications Only</option>
                                <option value="supplies">Medical Supplies</option>
                                <option value="equipment">Equipment</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Opname Date</label>
                            <input type="date" name="opname_date" class="form-control"
                                value="<?php echo e(today()->format('Y-m-d')); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Additional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Opname</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pharmacy\stock-opname.blade.php ENDPATH**/ ?>