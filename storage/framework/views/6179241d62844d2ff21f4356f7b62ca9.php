

<?php $__env->startSection('title', 'Audit Trail'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-alt text-primary"></i> Audit Trail
            </h1>
            <p class="text-muted mb-0">System activity and compliance logs</p>
        </div>
        <div>
            <button class="btn btn-success" onclick="window.print()">
                <i class="fas fa-print"></i> Export
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" action="<?php echo e(route('compliance.audit-trail.index')); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Date Range</label>
                            <select name="period" class="form-select">
                                <option value="today" <?php echo e(request('period') == 'today' ? 'selected' : ''); ?>>Today</option>
                                <option value="week" <?php echo e(request('period') == 'week' ? 'selected' : ''); ?>>This Week
                                </option>
                                <option value="month" <?php echo e(request('period') == 'month' ? 'selected' : ''); ?>>This Month
                                </option>
                                <option value="custom" <?php echo e(request('period') == 'custom' ? 'selected' : ''); ?>>Custom</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Action Type</label>
                            <select name="action_type" class="form-select">
                                <option value="">All Actions</option>
                                <option value="create" <?php echo e(request('action_type') == 'create' ? 'selected' : ''); ?>>Create
                                </option>
                                <option value="update" <?php echo e(request('action_type') == 'update' ? 'selected' : ''); ?>>Update
                                </option>
                                <option value="delete" <?php echo e(request('action_type') == 'delete' ? 'selected' : ''); ?>>Delete
                                </option>
                                <option value="login" <?php echo e(request('action_type') == 'login' ? 'selected' : ''); ?>>Login
                                </option>
                                <option value="logout" <?php echo e(request('action_type') == 'logout' ? 'selected' : ''); ?>>Logout
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">User</label>
                            <select name="user_id" class="form-select">
                                <option value="">All Users</option>
                                <?php $__currentLoopData = $users ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($user->id); ?>"
                                        <?php echo e(request('user_id') == $user->id ? 'selected' : ''); ?>>
                                        <?php echo e($user->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Module</label>
                            <select name="module" class="form-select">
                                <option value="">All Modules</option>
                                <option value="patients" <?php echo e(request('module') == 'patients' ? 'selected' : ''); ?>>Patients
                                </option>
                                <option value="emr" <?php echo e(request('module') == 'emr' ? 'selected' : ''); ?>>EMR</option>
                                <option value="pharmacy" <?php echo e(request('module') == 'pharmacy' ? 'selected' : ''); ?>>Pharmacy
                                </option>
                                <option value="billing" <?php echo e(request('module') == 'billing' ? 'selected' : ''); ?>>Billing
                                </option>
                                <option value="inventory" <?php echo e(request('module') == 'inventory' ? 'selected' : ''); ?>>
                                    Inventory</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Search</label>
                            <input type="text" name="search" class="form-control" placeholder="Search description..."
                                value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-filter"></i> Apply Filters
                            </button>
                            <a href="<?php echo e(route('compliance.audit-trail.index')); ?>" class="btn btn-secondary">
                                <i class="fas fa-redo"></i> Reset
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Audit Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Timestamp</th>
                                    <th>User</th>
                                    <th>Action</th>
                                    <th>Module</th>
                                    <th>Description</th>
                                    <th>IP Address</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $auditLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <small><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></small>
                                            <br><small class="text-muted"><?php echo e($log->created_at->diffForHumans()); ?></small>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user fa-xs"></i>
                                                </div>
                                                <div>
                                                    <strong><?php echo e($log->user->name ?? 'System'); ?></strong>
                                                    <br><small class="text-muted"><?php echo e($log->user->email ?? '-'); ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <?php
                                                $colors = [
                                                    'create' => 'success',
                                                    'update' => 'warning',
                                                    'delete' => 'danger',
                                                    'login' => 'info',
                                                    'logout' => 'secondary',
                                                ];
                                                $color = $colors[$log->action_type] ?? 'primary';
                                            ?>
                                            <span class="badge bg-<?php echo e($color); ?>">
                                                <?php echo e(ucfirst($log->action_type)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <span
                                                class="badge bg-light text-dark"><?php echo e(ucfirst($log->module ?? 'General')); ?></span>
                                        </td>
                                        <td>
                                            <small><?php echo e(Str::limit($log->description, 100)); ?></small>
                                        </td>
                                        <td>
                                            <small class="text-muted"><?php echo e($log->ip_address ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                data-bs-target="#detailModal<?php echo e($log->id); ?>">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Detail Modal -->
                                    <div class="modal fade" id="detailModal<?php echo e($log->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Audit Log Details</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Timestamp:</strong>
                                                            <p><?php echo e($log->created_at->format('d/m/Y H:i:s')); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>User:</strong>
                                                            <p><?php echo e($log->user->name ?? 'System'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Action Type:</strong>
                                                            <p><span
                                                                    class="badge bg-<?php echo e($color); ?>"><?php echo e(ucfirst($log->action_type)); ?></span>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Module:</strong>
                                                            <p><?php echo e(ucfirst($log->module ?? 'General')); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Description:</strong>
                                                        <p><?php echo e($log->description); ?></p>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>IP Address:</strong>
                                                            <p><?php echo e($log->ip_address ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>User Agent:</strong>
                                                            <p><small><?php echo e(Str::limit($log->user_agent ?? '-', 100)); ?></small>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <?php if($log->old_values || $log->new_values): ?>
                                                        <div class="row">
                                                            <?php if($log->old_values): ?>
                                                                <div class="col-md-6">
                                                                    <strong>Old Values:</strong>
                                                                    <pre class="bg-light p-2 rounded"><code><?php echo e(json_encode($log->old_values, JSON_PRETTY_PRINT)); ?></code></pre>
                                                                </div>
                                                            <?php endif; ?>
                                                            <?php if($log->new_values): ?>
                                                                <div class="col-md-6">
                                                                    <strong>New Values:</strong>
                                                                    <pre class="bg-light p-2 rounded"><code><?php echo e(json_encode($log->new_values, JSON_PRETTY_PRINT)); ?></code></pre>
                                                                </div>
                                                            <?php endif; ?>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No audit logs found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($auditLogs) && $auditLogs->hasPages()): ?>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <p class="text-muted mb-0">
                                Showing <?php echo e($auditLogs->firstItem()); ?> to <?php echo e($auditLogs->lastItem()); ?> of
                                <?php echo e($auditLogs->total()); ?> entries
                            </p>
                            <?php echo e($auditLogs->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Compliance Notice:</strong> Audit logs are retained for <?php echo e($retentionPeriod ?? '7 years'); ?> as per
                regulatory requirements.
                Logs are immutable and cannot be modified or deleted.
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\compliance\audit-trail.blade.php ENDPATH**/ ?>