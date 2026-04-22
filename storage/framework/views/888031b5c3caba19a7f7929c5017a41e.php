

<?php $__env->startSection('title', 'Quality Control'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-check-double text-primary"></i> Quality Control
            </h1>
            <p class="text-muted mb-0">Laboratory quality control and calibration</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addQCModal">
                <i class="fas fa-plus"></i> Add QC Record
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>Quality Control:</strong> Regular QC checks ensure accurate and reliable laboratory test results.
                All equipment must be calibrated and validated according to schedule.
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
                                    <th>Date</th>
                                    <th>Equipment/Test</th>
                                    <th>QC Type</th>
                                    <th>Result</th>
                                    <th>Acceptable Range</th>
                                    <th>Status</th>
                                    <th>Performed By</th>
                                    <th>Next Due</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $qcRecords; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $qc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($qc->performed_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td><strong><?php echo e($qc->equipment_name ?? ($qc->test_name ?? '-')); ?></strong></td>
                                        <td><?php echo e(ucfirst($qc->qc_type ?? 'Routine')); ?></td>
                                        <td><code><?php echo e($qc->result_value ?? '-'); ?></code></td>
                                        <td><?php echo e($qc->acceptable_range ?? '-'); ?></td>
                                        <td>
                                            <?php if($qc->status == 'pass'): ?>
                                                <span class="badge bg-success">Pass</span>
                                            <?php elseif($qc->status == 'fail'): ?>
                                                <span class="badge bg-danger">Fail</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Warning</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($qc->performed_by?->name ?? '-'); ?></td>
                                        <td>
                                            <?php if($qc->next_due_date): ?>
                                                <?php if($qc->next_due_date->isPast()): ?>
                                                    <span
                                                        class="text-danger fw-bold"><?php echo e($qc->next_due_date->format('d/m/Y')); ?></span>
                                                <?php else: ?>
                                                    <?php echo e($qc->next_due_date->format('d/m/Y')); ?>

                                                <?php endif; ?>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No QC records found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($qcRecords->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Add QC Record Modal -->
    <div class="modal fade" id="addQCModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.laboratory.quality-control.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Add Quality Control Record</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Equipment/Test Name</label>
                            <input type="text" name="equipment_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">QC Type</label>
                            <select name="qc_type" class="form-select" required>
                                <option value="routine">Routine QC</option>
                                <option value="calibration">Calibration</option>
                                <option value="validation">Validation</option>
                                <option value="maintenance">Maintenance Check</option>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Result Value</label>
                                <input type="text" name="result_value" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Acceptable Range</label>
                                <input type="text" name="acceptable_range" class="form-control"
                                    placeholder="e.g., 95-105">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="pass">Pass</option>
                                <option value="warning">Warning</option>
                                <option value="fail">Fail</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Next Due Date</label>
                            <input type="date" name="next_due_date" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save QC Record</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\laboratory\quality-control.blade.php ENDPATH**/ ?>