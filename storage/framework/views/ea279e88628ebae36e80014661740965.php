

<?php $__env->startSection('title', 'Medical Certificates'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-certificate text-primary"></i> My Medical Certificates
            </h1>
            <p class="text-muted mb-0">Download your medical certificates and documents</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#requestCertificateModal">
                <i class="fas fa-plus"></i> Request Certificate
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="text-primary"><?php echo e($stats['total_certificates'] ?? 0); ?></h3>
                    <small class="text-muted">Total Certificates</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['approved'] ?? 0); ?></h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['pending'] ?? 0); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Certificate List</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Certificate #</th>
                                    <th>Type</th>
                                    <th>Issue Date</th>
                                    <th>Valid Until</th>
                                    <th>Issued By</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $certificates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($cert->certificate_number ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($cert->type == 'sick_leave'): ?>
                                                <span class="badge bg-danger">Sick Leave</span>
                                            <?php elseif($cert->type == 'fitness'): ?>
                                                <span class="badge bg-success">Fitness Certificate</span>
                                            <?php elseif($cert->type == 'medical_report'): ?>
                                                <span class="badge bg-primary">Medical Report</span>
                                            <?php elseif($cert->type == 'vaccination'): ?>
                                                <span class="badge bg-info">Vaccination Certificate</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($cert->type ?? '-')); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <small><?php echo e($cert->issue_date->format('d/m/Y') ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <?php if($cert->valid_until): ?>
                                                <small><?php echo e($cert->valid_until->format('d/m/Y')); ?></small>
                                                <?php if($cert->valid_until->isPast()): ?>
                                                    <br><span class="badge bg-danger">Expired</span>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <small class="text-muted">No Expiry</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center me-2"
                                                    style="width: 30px; height: 30px;">
                                                    <i class="fas fa-user-md fa-xs"></i>
                                                </div>
                                                <strong><?php echo e($cert->doctor_name ?? '-'); ?></strong>
                                            </div>
                                        </td>
                                        <td>
                                            <?php if($cert->status == 'approved'): ?>
                                                <span class="badge bg-success">Approved</span>
                                            <?php elseif($cert->status == 'pending'): ?>
                                                <span class="badge bg-warning">Pending</span>
                                            <?php elseif($cert->status == 'rejected'): ?>
                                                <span class="badge bg-danger">Rejected</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($cert->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($cert->status == 'approved'): ?>
                                                <div class="btn-group">
                                                    <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                        data-bs-target="#viewCertModal<?php echo e($cert->id); ?>">
                                                        <i class="fas fa-eye"></i> View
                                                    </button>
                                                    <button class="btn btn-sm btn-success" title="Download"
                                                        onclick="window.print()">
                                                        <i class="fas fa-download"></i> Download
                                                    </button>
                                                </div>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                    <!-- View Certificate Modal -->
                                    <div class="modal fade" id="viewCertModal<?php echo e($cert->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Medical Certificate</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="text-center mb-4">
                                                        <h4>
                                                            <i class="fas fa-hospital text-primary"></i>
                                                            Qalcuity Medical Center
                                                        </h4>
                                                        <p class="text-muted">Medical Certificate</p>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Certificate Number:</strong>
                                                            <p><?php echo e($cert->certificate_number ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Type:</strong>
                                                            <p><?php echo e(ucfirst(str_replace('_', ' ', $cert->type ?? '-'))); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Patient Name:</strong>
                                                            <p><?php echo e($cert->patient_name ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Issue Date:</strong>
                                                            <p><?php echo e($cert->issue_date->format('d/m/Y') ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Diagnosis/Condition:</strong>
                                                        <p><?php echo e($cert->diagnosis ?? 'N/A'); ?></p>
                                                    </div>
                                                    <div class="mb-3">
                                                        <strong>Medical Notes:</strong>
                                                        <p class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                                                            <?php echo e($cert->notes ?? 'N/A'); ?></p>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Issued By:</strong>
                                                            <p><?php echo e($cert->doctor_name ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Doctor's Signature:</strong>
                                                            <p class="text-muted">[Digital Signature]</p>
                                                        </div>
                                                    </div>
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        This certificate is digitally signed and verified.
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print Certificate
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5">
                                            <i class="fas fa-certificate fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No medical certificates found</p>
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

    <!-- Request Certificate Modal -->
    <div class="modal fade" id="requestCertificateModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Request Medical Certificate</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('portal.certificates.request')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Certificate Type <span class="text-danger">*</span></label>
                            <select name="type" class="form-select" required>
                                <option value="">Select Type</option>
                                <option value="sick_leave">Sick Leave Certificate</option>
                                <option value="fitness">Fitness Certificate</option>
                                <option value="medical_report">Medical Report</option>
                                <option value="vaccination">Vaccination Certificate</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Purpose <span class="text-danger">*</span></label>
                            <input type="text" name="purpose" class="form-control" required
                                placeholder="e.g., Work requirement, Insurance claim">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Additional Notes</label>
                            <textarea name="notes" class="form-control" rows="3" placeholder="Any specific requirements..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\portal\certificates.blade.php ENDPATH**/ ?>