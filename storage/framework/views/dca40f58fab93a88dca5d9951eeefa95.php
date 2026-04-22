

<?php $__env->startSection('title', 'Care Plans'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-clipboard-list text-primary"></i> Care Plans
            </h1>
            <p class="text-muted mb-0">Patient care plans and nursing interventions</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus"></i> Create Care Plan
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $carePlans; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $plan): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <strong><?php echo e($plan->patient?->name ?? 'N/A'); ?></strong>
                            <span
                                class="badge bg-<?php echo e($plan->status == 'active' ? 'success' : ($plan->status == 'completed' ? 'secondary' : 'warning')); ?> ms-2">
                                <?php echo e(ucfirst($plan->status)); ?>

                            </span>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block">Created</small>
                                <strong><?php echo e($plan->created_at->format('d/m/Y H:i')); ?></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Target Date</small>
                                <strong><?php echo e($plan->target_date?->format('d/m/Y') ?? 'N/A'); ?></strong>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block">Assigned Nurse</small>
                                <strong><?php echo e($plan->assigned_nurse?->name ?? 'N/A'); ?></strong>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-primary">Diagnosis & Goals</h6>
                            <div class="bg-light p-3 rounded">
                                <p class="mb-2"><strong>Nursing Diagnosis:</strong> <?php echo e($plan->diagnosis ?? 'N/A'); ?></p>
                                <p class="mb-0"><strong>Goals:</strong> <?php echo e($plan->goals ?? 'N/A'); ?></p>
                            </div>
                        </div>

                        <div class="mb-3">
                            <h6 class="text-primary">Interventions</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Intervention</th>
                                            <th>Frequency</th>
                                            <th>Status</th>
                                            <th>Notes</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php $__empty_2 = true; $__currentLoopData = $plan->interventions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $intervention): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                            <tr>
                                                <td><?php echo e($intervention['description'] ?? '-'); ?></td>
                                                <td><?php echo e($intervention['frequency'] ?? '-'); ?></td>
                                                <td>
                                                    <span
                                                        class="badge bg-<?php echo e($intervention['status'] == 'completed' ? 'success' : 'warning'); ?>">
                                                        <?php echo e(ucfirst($intervention['status'] ?? 'pending')); ?>

                                                    </span>
                                                </td>
                                                <td><?php echo e($intervention['notes'] ?? '-'); ?></td>
                                            </tr>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                            <tr>
                                                <td colspan="4" class="text-center text-muted">No interventions added
                                                </td>
                                            </tr>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div>
                            <h6 class="text-primary">Evaluation</h6>
                            <p class="bg-light p-3 rounded mb-0"><?php echo e($plan->evaluation ?? 'Pending evaluation'); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No care plans created yet</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Care Plan Modal -->
    <div class="modal fade" id="addPlanModal" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.inpatient.care-plans.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Create Care Plan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    <?php $__currentLoopData = $admittedPatients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Target Date</label>
                                <input type="date" name="target_date" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Nursing Diagnosis</label>
                            <textarea name="diagnosis" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Goals</label>
                            <textarea name="goals" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Interventions (one per line)</label>
                            <textarea name="interventions" class="form-control" rows="4"
                                placeholder="Medication administration&#10;Wound care&#10;Vital signs monitoring"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Evaluation</label>
                            <textarea name="evaluation" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Create Care Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inpatient\care-plans.blade.php ENDPATH**/ ?>