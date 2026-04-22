

<?php $__env->startSection('title', 'Doctor Rounds'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-user-md text-primary"></i> Doctor Rounds
            </h1>
            <p class="text-muted mb-0">Daily ward rounds and patient assessments</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addRoundModal">
                <i class="fas fa-plus"></i> Record Round
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <form method="GET" class="d-flex gap-2">
                                <input type="date" name="date" class="form-control"
                                    value="<?php echo e(request('date', today()->format('Y-m-d'))); ?>">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                            </form>
                        </div>
                        <div class="col-md-8 text-end">
                            <span class="badge bg-primary me-2"><?php echo e(count($rounds)); ?> rounds today</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $rounds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $round): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 mb-3">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <i class="fas fa-clock text-muted me-2"></i>
                            <strong><?php echo e($round->round_time?->format('H:i') ?? '-'); ?></strong>
                            <span class="ms-3">
                                <i class="fas fa-user-md text-primary me-1"></i>
                                <?php echo e($round->doctor?->name ?? '-'); ?>

                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Patient Information</h6>
                                <div class="mb-2">
                                    <strong><?php echo e($round->patient?->name ?? '-'); ?></strong>
                                    <span
                                        class="badge bg-info ms-2"><?php echo e($round->patient->medical_record_number ?? ''); ?></span>
                                </div>
                                <div class="small text-muted">
                                    <i class="fas fa-bed me-1"></i> Bed: <?php echo e($round->bed?->bed_number ?? '-'); ?> |
                                    Ward: <?php echo e($round->ward?->name ?? '-'); ?>

                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Assessment</h6>
                                <div class="mb-2">
                                    <span
                                        class="badge bg-<?php echo e($round->condition == 'stable' ? 'success' : ($round->condition == 'critical' ? 'danger' : 'warning')); ?>">
                                        <?php echo e(ucfirst($round->condition ?? 'N/A')); ?>

                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Subjective</h6>
                                <p class="small bg-light p-2 rounded"><?php echo e($round->subjective ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Objective</h6>
                                <p class="small bg-light p-2 rounded"><?php echo e($round->objective ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Assessment</h6>
                                <p class="small bg-light p-2 rounded"><?php echo e($round->assessment ?? 'N/A'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-primary mb-2">Plan</h6>
                                <p class="small bg-light p-2 rounded"><?php echo e($round->plan ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <?php if($round->follow_up_needed): ?>
                            <div class="alert alert-warning mt-2 mb-0">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Follow-up needed:</strong> <?php echo e($round->follow_up_notes ?? 'Schedule follow-up'); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-user-md fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No rounds recorded for this date</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Round Modal -->
    <div class="modal fade" id="addRoundModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.inpatient.rounds.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Record Doctor Round</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    <?php $__currentLoopData = $admittedPatients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Round Time</label>
                                <input type="time" name="round_time" class="form-control" required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Condition</label>
                                <select name="condition" class="form-select" required>
                                    <option value="stable">Stable</option>
                                    <option value="improving">Improving</option>
                                    <option value="deteriorating">Deteriorating</option>
                                    <option value="critical">Critical</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Subjective</label>
                                <textarea name="subjective" class="form-control" rows="3" placeholder="Patient complaints, symptoms..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Objective</label>
                                <textarea name="objective" class="form-control" rows="3"
                                    placeholder="Physical examination findings, vitals..."></textarea>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assessment</label>
                                <textarea name="assessment" class="form-control" rows="3" placeholder="Diagnosis, clinical assessment..."></textarea>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Plan</label>
                                <textarea name="plan" class="form-control" rows="3" placeholder="Treatment plan, medications, orders..."></textarea>
                            </div>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input" type="checkbox" name="follow_up_needed" id="followUpNeeded">
                            <label class="form-check-label" for="followUpNeeded">Follow-up needed</label>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Follow-up Notes</label>
                            <textarea name="follow_up_notes" class="form-control" rows="2" placeholder="When and what to follow up..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Round</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inpatient\rounds.blade.php ENDPATH**/ ?>