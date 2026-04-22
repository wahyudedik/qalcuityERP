

<?php $__env->startSection('title', 'Backup Logs'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-database text-primary"></i> Backup Logs
            </h1>
            <p class="text-muted mb-0">System backup history and status</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createBackupModal">
                <i class="fas fa-plus"></i> Create Backup
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['total_backups'] ?? 0); ?></h3>
                    <small class="text-muted">Total Backups</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['completed'] ?? 0); ?></h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['in_progress'] ?? 0); ?></h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($stats['failed'] ?? 0); ?></h3>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Backup History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Duration</th>
                                    <th>Status</th>
                                    <th>Location</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $backups; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $backup): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($backup->created_at->format('d/m/Y H:i')); ?></strong>
                                            <br><small class="text-muted"><?php echo e($backup->created_at->diffForHumans()); ?></small>
                                        </td>
                                        <td>
                                            <?php if($backup->type == 'full'): ?>
                                                <span class="badge bg-primary">Full Backup</span>
                                            <?php elseif($backup->type == 'incremental'): ?>
                                                <span class="badge bg-info">Incremental</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($backup->type)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($backup->size): ?>
                                                <?php
                                                    $size = $backup->size;
                                                    $unit = 'B';
                                                    if ($size >= 1073741824) {
                                                        $size = round($size / 1073741824, 2);
                                                        $unit = 'GB';
                                                    } elseif ($size >= 1048576) {
                                                        $size = round($size / 1048576, 2);
                                                        $unit = 'MB';
                                                    } elseif ($size >= 1024) {
                                                        $size = round($size / 1024, 2);
                                                        $unit = 'KB';
                                                    }
                                                ?>
                                                <strong><?php echo e($size); ?> <?php echo e($unit); ?></strong>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($backup->duration): ?>
                                                <?php echo e($backup->duration); ?> seconds
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($backup->status == 'completed'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Completed
                                                </span>
                                            <?php elseif($backup->status == 'in_progress'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-spinner fa-spin"></i> In Progress
                                                </span>
                                            <?php elseif($backup->status == 'failed'): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Failed
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($backup->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small>
                                                <?php if($backup->location == 'local'): ?>
                                                    <i class="fas fa-hdd"></i> Local
                                                <?php elseif($backup->location == 'cloud'): ?>
                                                    <i class="fas fa-cloud"></i> Cloud
                                                <?php else: ?>
                                                    <?php echo e(ucfirst($backup->location)); ?>

                                                <?php endif; ?>
                                            </small>
                                        </td>
                                        <td>
                                            <?php if($backup->status == 'completed'): ?>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info" title="Download">
                                                        <i class="fas fa-download"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-warning" title="Restore"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#restoreModal<?php echo e($backup->id); ?>">
                                                        <i class="fas fa-undo"></i>
                                                    </button>
                                                    <button class="btn btn-sm btn-danger" title="Delete"
                                                        onclick="confirm('Are you sure?') && this.closest('tr').remove()">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>

                                                <!-- Restore Modal -->
                                                <div class="modal fade" id="restoreModal<?php echo e($backup->id); ?>"
                                                    tabindex="-1">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content">
                                                            <div class="modal-header bg-warning">
                                                                <h5 class="modal-title">
                                                                    <i class="fas fa-exclamation-triangle"></i> Confirm
                                                                    Restore
                                                                </h5>
                                                                <button type="button" class="btn-close"
                                                                    data-bs-dismiss="modal"></button>
                                                            </div>
                                                            <div class="modal-body">
                                                                <div class="alert alert-warning">
                                                                    <strong>Warning!</strong> Restoring from backup will
                                                                    replace current data.
                                                                    This action cannot be undone.
                                                                </div>
                                                                <p><strong>Backup Date:</strong>
                                                                    <?php echo e($backup->created_at->format('d/m/Y H:i')); ?></p>
                                                                <p><strong>Type:</strong> <?php echo e(ucfirst($backup->type)); ?></p>
                                                                <p><strong>Size:</strong> <?php echo e($size ?? '-'); ?>

                                                                    <?php echo e($unit ?? ''); ?></p>
                                                                <form
                                                                    action="<?php echo e(route('compliance.backup-logs.restore', $backup->id)); ?>"
                                                                    method="POST">
                                                                    <?php echo csrf_field(); ?>
                                                                    <div class="form-check mb-3">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            id="confirm<?php echo e($backup->id); ?>" required>
                                                                        <label class="form-check-label"
                                                                            for="confirm<?php echo e($backup->id); ?>">
                                                                            I understand the risks and want to proceed
                                                                        </label>
                                                                    </div>
                                                                    <button type="submit" class="btn btn-warning w-100">
                                                                        <i class="fas fa-undo"></i> Restore Now
                                                                    </button>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-database fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No backups found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($backups) && $backups->hasPages()): ?>
                        <div class="mt-3">
                            <?php echo e($backups->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Backup Modal -->
    <div class="modal fade" id="createBackupModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create New Backup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('compliance.backup-logs.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Backup Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="full">Full Backup</option>
                                <option value="incremental">Incremental Backup</option>
                                <option value="differential">Differential Backup</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Storage Location <span class="text-danger">*</span></label>
                            <select name="location" class="form-select" required>
                                <option value="">Select Location</option>
                                <option value="local">Local Storage</option>
                                <option value="cloud">Cloud Storage</option>
                                <option value="both">Both</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-database"></i> Create Backup
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\compliance\backup-logs.blade.php ENDPATH**/ ?>