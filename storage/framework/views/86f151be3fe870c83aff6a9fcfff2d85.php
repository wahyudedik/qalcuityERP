

<?php $__env->startSection('title', 'Compliance Reports'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-contract text-primary"></i> Compliance Reports
            </h1>
            <p class="text-muted mb-0">Regulatory compliance and audit reports</p>
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
                    <form method="GET" action="<?php echo e(route('compliance.reports.index')); ?>" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Report Type</label>
                            <select name="type" class="form-select">
                                <option value="">All Reports</option>
                                <option value="hipaa" <?php echo e(request('type') == 'hipaa' ? 'selected' : ''); ?>>HIPAA Compliance
                                </option>
                                <option value="data-protection"
                                    <?php echo e(request('type') == 'data-protection' ? 'selected' : ''); ?>>Data Protection</option>
                                <option value="access-control" <?php echo e(request('type') == 'access-control' ? 'selected' : ''); ?>>
                                    Access Control</option>
                                <option value="security" <?php echo e(request('type') == 'security' ? 'selected' : ''); ?>>Security
                                    Audit</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date From</label>
                            <input type="date" name="date_from" class="form-control" value="<?php echo e(request('date_from')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Date To</label>
                            <input type="date" name="date_to" class="form-control" value="<?php echo e(request('date_to')); ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">&nbsp;</label>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-filter"></i> Generate Report
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['compliance_score'] ?? 0); ?>%</h3>
                    <small class="text-muted">Overall Compliance Score</small>
                    <div class="progress mt-2" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?php echo e($stats['compliance_score'] ?? 0); ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['audits_passed'] ?? 0); ?></h3>
                    <small class="text-muted">Audits Passed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['pending_reviews'] ?? 0); ?></h3>
                    <small class="text-muted">Pending Reviews</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($stats['violations'] ?? 0); ?></h3>
                    <small class="text-muted">Violations</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-6 mb-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Compliance by Category</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Category</th>
                                    <th>Score</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $categories ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($category['name'] ?? '-'); ?></td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-<?php echo e($category['score'] >= 90 ? 'success' : ($category['score'] >= 70 ? 'warning' : 'danger')); ?>"
                                                        style="width: <?php echo e($category['score'] ?? 0); ?>%"></div>
                                                </div>
                                                <strong><?php echo e($category['score'] ?? 0); ?>%</strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($category['score'] >= 90): ?>
                                                <span class="badge bg-success">Compliant</span>
                                            <?php elseif($category['score'] >= 70): ?>
                                                <span class="badge bg-warning">Partial</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Non-Compliant</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="3" class="text-center text-muted">No data available</td>
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
                    <h5 class="mb-0">Recent Violations</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Severity</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $violations ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $violation): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <small><?php echo e($violation['date'] ?? '-'); ?></small>
                                        </td>
                                        <td><?php echo e($violation['type'] ?? '-'); ?></td>
                                        <td>
                                            <?php if($violation['severity'] == 'critical'): ?>
                                                <span class="badge bg-danger">Critical</span>
                                            <?php elseif($violation['severity'] == 'high'): ?>
                                                <span class="badge bg-warning">High</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Medium</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($violation['status'] == 'resolved'): ?>
                                                <span class="badge bg-success">Resolved</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Open</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="4" class="text-center text-muted">No violations found</td>
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
                    <h5 class="mb-0">Compliance Timeline</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Report Date</th>
                                    <th>Report Type</th>
                                    <th>Compliance Score</th>
                                    <th>Auditor</th>
                                    <th>Findings</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $reports ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($report['date'] ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <span class="badge bg-primary"><?php echo e($report['type'] ?? '-'); ?></span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                                    <div class="progress-bar bg-<?php echo e($report['score'] >= 90 ? 'success' : ($report['score'] >= 70 ? 'warning' : 'danger')); ?>"
                                                        style="width: <?php echo e($report['score'] ?? 0); ?>%"></div>
                                                </div>
                                                <strong><?php echo e($report['score'] ?? 0); ?>%</strong>
                                            </div>
                                        </td>
                                        <td><?php echo e($report['auditor'] ?? '-'); ?></td>
                                        <td><?php echo e($report['findings'] ?? 0); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-info" title="View Report">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                            <button class="btn btn-sm btn-success" title="Download"
                                                onclick="window.print()">
                                                <i class="fas fa-download"></i> Download
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-file-contract fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No compliance reports generated</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Regulatory Requirements:</strong> Compliance reports must be reviewed quarterly and retained for a
                minimum of 7 years.
                All violations must be addressed within 30 days of discovery.
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\compliance\reports.blade.php ENDPATH**/ ?>