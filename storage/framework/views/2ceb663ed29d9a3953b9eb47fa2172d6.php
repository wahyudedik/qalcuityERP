

<?php $__env->startSection('title', 'Data Anonymization'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user-secret text-primary"></i> Data Anonymization
            </h1>
            <p class="text-muted mb-0">Patient data privacy and anonymization tools</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#anonymizeModal">
                <i class="fas fa-user-secret"></i> Anonymize Data
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($stats['anonymized_records'] ?? 0); ?></h3>
                    <small class="text-muted">Anonymized Records</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($stats['pending_anonymization'] ?? 0); ?></h3>
                    <small class="text-muted">Pending Anonymization</small>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($stats['retention_days'] ?? 0); ?></h3>
                    <small class="text-muted">Days Until Auto-Anonymize</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Anonymization Rules</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rule Name</th>
                                    <th>Data Type</th>
                                    <th>Trigger</th>
                                    <th>Method</th>
                                    <th>Status</th>
                                    <th>Last Run</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $rules ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><strong><?php echo e($rule['name'] ?? '-'); ?></strong></td>
                                        <td>
                                            <span class="badge bg-info"><?php echo e($rule['data_type'] ?? '-'); ?></span>
                                        </td>
                                        <td><?php echo e($rule['trigger'] ?? '-'); ?></td>
                                        <td>
                                            <?php if($rule['method'] == 'pseudonymization'): ?>
                                                <span class="badge bg-primary">Pseudonymization</span>
                                            <?php elseif($rule['method'] == 'generalization'): ?>
                                                <span class="badge bg-success">Generalization</span>
                                            <?php elseif($rule['method'] == 'suppression'): ?>
                                                <span class="badge bg-warning">Suppression</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><?php echo e(ucfirst($rule['method'])); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($rule['is_active']): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">Inactive</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($rule['last_run']): ?>
                                                <small><?php echo e($rule['last_run']); ?></small>
                                                <br><small class="text-muted"><?php echo e($rule['last_run_diff'] ?? '-'); ?></small>
                                            <?php else: ?>
                                                <span class="text-muted">Never</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center text-muted">No anonymization rules configured
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
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Recent Anonymization Activity</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Record Type</th>
                                    <th>Records Processed</th>
                                    <th>Method</th>
                                    <th>Triggered By</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $activities ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td>
                                            <small><?php echo e($activity['date'] ?? '-'); ?></small>
                                        </td>
                                        <td><?php echo e($activity['record_type'] ?? '-'); ?></td>
                                        <td>
                                            <strong><?php echo e($activity['records_processed'] ?? 0); ?></strong> records
                                        </td>
                                        <td><?php echo e($activity['method'] ?? '-'); ?></td>
                                        <td><?php echo e($activity['triggered_by'] ?? 'System'); ?></td>
                                        <td>
                                            <?php if($activity['status'] == 'completed'): ?>
                                                <span class="badge bg-success">Completed</span>
                                            <?php elseif($activity['status'] == 'in_progress'): ?>
                                                <span class="badge bg-warning">In Progress</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger">Failed</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-4">
                                            <i class="fas fa-user-secret fa-2x text-muted mb-2"></i>
                                            <p class="text-muted">No anonymization activity</p>
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

    <!-- Anonymize Data Modal -->
    <div class="modal fade" id="anonymizeModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Anonymize Patient Data</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="<?php echo e(route('compliance.anonymization.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-body">
                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Warning:</strong> This action will permanently anonymize patient data.
                            Original data cannot be recovered after anonymization.
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Scope <span class="text-danger">*</span></label>
                            <select name="scope" class="form-select" required>
                                <option value="">Select Scope</option>
                                <option value="discharged">Discharged Patients (> 90 days)</option>
                                <option value="deceased">Deceased Patients</option>
                                <option value="consent_withdrawn">Patients Who Withdrew Consent</option>
                                <option value="custom">Custom Date Range</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Anonymization Method <span class="text-danger">*</span></label>
                            <select name="method" class="form-select" required>
                                <option value="">Select Method</option>
                                <option value="pseudonymization">Pseudonymization (Replace identifiers)</option>
                                <option value="generalization">Generalization (Broaden values)</option>
                                <option value="suppression">Suppression (Remove data)</option>
                                <option value="aggregation">Aggregation (Combine data)</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Data Fields to Anonymize</label>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="name"
                                            id="field_name" checked>
                                        <label class="form-check-label" for="field_name">Patient Name</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="phone"
                                            id="field_phone" checked>
                                        <label class="form-check-label" for="field_phone">Phone Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="email"
                                            id="field_email" checked>
                                        <label class="form-check-label" for="field_email">Email</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="address"
                                            id="field_address" checked>
                                        <label class="form-check-label" for="field_address">Address</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]"
                                            value="id_number" id="field_id_number" checked>
                                        <label class="form-check-label" for="field_id_number">ID Number</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]"
                                            value="insurance" id="field_insurance">
                                        <label class="form-check-label" for="field_insurance">Insurance Details</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="notes"
                                            id="field_notes">
                                        <label class="form-check-label" for="field_notes">Clinical Notes</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="fields[]" value="images"
                                            id="field_images">
                                        <label class="form-check-label" for="field_images">Medical Images</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Reason for Anonymization <span class="text-danger">*</span></label>
                            <textarea name="reason" class="form-control" rows="3" required
                                placeholder="Explain why this data needs to be anonymized..."></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" id="confirm_anonymize" required>
                            <label class="form-check-label" for="confirm_anonymize">
                                I confirm that I have authorization to anonymize this data
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-user-secret"></i> Anonymize Data
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Privacy Compliance:</strong> Patient data anonymization is performed in accordance with HIPAA and
                data protection regulations.
                Anonymized data cannot be used to identify individual patients and is retained for research and statistical
                purposes.
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\compliance\anonymization.blade.php ENDPATH**/ ?>