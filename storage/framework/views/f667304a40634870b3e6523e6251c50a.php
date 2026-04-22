

<?php $__env->startSection('title', 'BPJS Claims'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-invoice-dollar text-primary"></i> BPJS Claims
            </h1>
            <p class="text-muted mb-0">Indonesian national insurance claim submission and tracking</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#submitClaimModal">
                <i class="fas fa-plus"></i> Submit Claim
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['approved'] ?? 0); ?></h3>
                    <small class="text-muted">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['pending'] ?? 0); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($stats['rejected'] ?? 0); ?></h3>
                    <small class="text-muted">Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info">
                        Rp <?php echo e(number_format(($stats['total_amount'] ?? 0) / 1000000, 1)); ?>M
                    </h3>
                    <small class="text-muted">Total Claims</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Claims History</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Claim ID</th>
                                    <th>Patient</th>
                                    <th>Submission Date</th>
                                    <th>Diagnosis</th>
                                    <th>Claim Amount</th>
                                    <th>Status</th>
                                    <th>BPJS Response</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $claims; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $claim): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <strong><?php echo e($claim->claim_id ?? '-'); ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo e($claim->patient->name ?? '-'); ?></strong>
                                                <br><small
                                                    class="text-muted"><?php echo e($claim->patient->bpjs_number ?? '-'); ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <small><?php echo e($claim->submission_date->format('d/m/Y') ?? '-'); ?></small>
                                        </td>
                                        <td>
                                            <small><?php echo e($claim->diagnosis_code ?? '-'); ?></small>
                                            <br><small
                                                class="text-muted"><?php echo e(Str::limit($claim->diagnosis_name ?? '-', 30)); ?></small>
                                        </td>
                                        <td>
                                            <strong>Rp <?php echo e(number_format($claim->claim_amount ?? 0, 0, ',', '.')); ?></strong>
                                        </td>
                                        <td>
                                            <?php if($claim->status == 'approved'): ?>
                                                <span class="badge bg-success">
                                                    <i class="fas fa-check-circle"></i> Approved
                                                </span>
                                            <?php elseif($claim->status == 'pending'): ?>
                                                <span class="badge bg-warning">
                                                    <i class="fas fa-clock"></i> Pending
                                                </span>
                                            <?php elseif($claim->status == 'rejected'): ?>
                                                <span class="badge bg-danger">
                                                    <i class="fas fa-times-circle"></i> Rejected
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($claim->status)); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($claim->approved_amount): ?>
                                                <small class="text-success">
                                                    Approved: Rp <?php echo e(number_format($claim->approved_amount, 0, ',', '.')); ?>

                                                </small>
                                            <?php elseif($claim->rejection_reason): ?>
                                                <small class="text-danger" title="<?php echo e($claim->rejection_reason); ?>">
                                                    <i class="fas fa-exclamation-triangle"></i> Rejected
                                                </small>
                                            <?php else: ?>
                                                <small class="text-muted">-</small>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group">
                                                <button class="btn btn-sm btn-info" data-bs-toggle="modal"
                                                    data-bs-target="#viewClaimModal<?php echo e($claim->id); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($claim->status == 'rejected'): ?>
                                                    <button class="btn btn-sm btn-warning" title="Resubmit">
                                                        <i class="fas fa-redo"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Claim Modal -->
                                    <div class="modal fade" id="viewClaimModal<?php echo e($claim->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">BPJS Claim Details</h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Claim ID:</strong>
                                                            <p><?php echo e($claim->claim_id ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Status:</strong>
                                                            <p>
                                                                <?php if($claim->status == 'approved'): ?>
                                                                    <span class="badge bg-success">Approved</span>
                                                                <?php elseif($claim->status == 'pending'): ?>
                                                                    <span class="badge bg-warning">Pending</span>
                                                                <?php else: ?>
                                                                    <span class="badge bg-danger">Rejected</span>
                                                                <?php endif; ?>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Patient Name:</strong>
                                                            <p><?php echo e($claim->patient->name ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>BPJS Number:</strong>
                                                            <p><?php echo e($claim->patient->bpjs_number ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Diagnosis:</strong>
                                                            <p><?php echo e($claim->diagnosis_code); ?> -
                                                                <?php echo e($claim->diagnosis_name ?? '-'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Procedure:</strong>
                                                            <p><?php echo e($claim->procedure_code ?? '-'); ?> -
                                                                <?php echo e($claim->procedure_name ?? '-'); ?></p>
                                                        </div>
                                                    </div>
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Claim Amount:</strong>
                                                            <p class="text-primary">
                                                                <strong>Rp
                                                                    <?php echo e(number_format($claim->claim_amount ?? 0, 0, ',', '.')); ?></strong>
                                                            </p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Approved Amount:</strong>
                                                            <p class="text-success">
                                                                <strong>Rp
                                                                    <?php echo e(number_format($claim->approved_amount ?? 0, 0, ',', '.')); ?></strong>
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <?php if($claim->rejection_reason): ?>
                                                        <div class="alert alert-danger">
                                                            <strong>Rejection Reason:</strong>
                                                            <p class="mb-0"><?php echo e($claim->rejection_reason); ?></p>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div class="mb-3">
                                                        <strong>Submission Details:</strong>
                                                        <pre class="bg-light p-2 rounded"><code><?php echo e(json_encode($claim->submission_data ?? [], JSON_PRETTY_PRINT)); ?></code></pre>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5">
                                            <i class="fas fa-file-invoice-dollar fa-3x text-muted mb-3"></i>
                                            <p class="text-muted">No BPJS claims found</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if(isset($claims) && $claims->hasPages()): ?>
                        <div class="mt-3">
                            <?php echo e($claims->links()); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Claim Modal -->
    <div class="modal fade" id="submitClaimModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Submit BPJS Claim</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('integration.bpjs-claims.submit')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient <span class="text-danger">*</span></label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select Patient</option>
                                <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->id); ?>">
                                        <?php echo e($patient->name); ?> - <?php echo e($patient->bpjs_number ?? 'No BPJS Number'); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Diagnosis Code (ICD-10) <span class="text-danger">*</span></label>
                            <input type="text" name="diagnosis_code" class="form-control" required
                                placeholder="e.g., J06.9">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Procedure Code (ICD-9-CM)</label>
                            <input type="text" name="procedure_code" class="form-control" placeholder="e.g., 47.01">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Claim Amount <span class="text-danger">*</span></label>
                            <input type="number" name="claim_amount" class="form-control" required placeholder="0">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Supporting Documents</label>
                            <textarea name="documents" class="form-control" rows="3" placeholder="List supporting documents..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-paper-plane"></i> Submit Claim
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\integration\bpjs-claims.blade.php ENDPATH**/ ?>