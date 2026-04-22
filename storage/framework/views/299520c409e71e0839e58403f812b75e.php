

<?php $__env->startSection('title', 'Diagnoses'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-stethoscope text-primary"></i> Diagnoses
            </h1>
            <p class="text-muted mb-0">ICD-10 diagnosis codes and records</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addDiagnosisModal">
                <i class="fas fa-plus"></i> Add Diagnosis
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <form method="GET" class="row g-2">
                        <div class="col-md-4">
                            <input type="text" name="search" class="form-control"
                                placeholder="Search ICD-10 code or description..." value="<?php echo e(request('search')); ?>">
                        </div>
                        <div class="col-md-2">
                            <select name="type" class="form-select">
                                <option value="">All Types</option>
                                <option value="primary" <?php echo e(request('type') == 'primary' ? 'selected' : ''); ?>>Primary
                                </option>
                                <option value="secondary" <?php echo e(request('type') == 'secondary' ? 'selected' : ''); ?>>Secondary
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-search"></i> Filter
                            </button>
                        </div>
                    </form>
                </div>
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
                                    <th>Patient</th>
                                    <th>ICD-10 Code</th>
                                    <th>Description</th>
                                    <th>Type</th>
                                    <th>Doctor</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $diagnoses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($diag->diagnosis_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $diag->patient)); ?>">
                                                <?php echo e($diag->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td><code><?php echo e($diag->icd10_code); ?></code></td>
                                        <td><?php echo e(Str::limit($diag->description, 50)); ?></td>
                                        <td>
                                            <?php if($diag->type == 'primary'): ?>
                                                <span class="badge bg-danger">Primary</span>
                                            <?php else: ?>
                                                <span class="badge bg-info">Secondary</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($diag->doctor?->name ?? '-'); ?></td>
                                        <td>
                                            <?php if($diag->status == 'active'): ?>
                                                <span class="badge bg-success">Active</span>
                                            <?php elseif($diag->status == 'resolved'): ?>
                                                <span class="badge bg-secondary">Resolved</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Chronic</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-warning btn-sm">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            No diagnoses found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($diagnoses->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Diagnosis Modal -->
    <div class="modal fade" id="addDiagnosisModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.emr.diagnoses.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Add Diagnosis</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Patient</label>
                                <select name="patient_id" class="form-select" required>
                                    <option value="">Select patient</option>
                                    <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Diagnosis Date</label>
                                <input type="date" name="diagnosis_date" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">ICD-10 Code</label>
                                <input type="text" name="icd10_code" class="form-control" placeholder="e.g., J06.9"
                                    required>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Type</label>
                                <select name="type" class="form-select" required>
                                    <option value="primary">Primary</option>
                                    <option value="secondary">Secondary</option>
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="active">Active</option>
                                    <option value="resolved">Resolved</option>
                                    <option value="chronic">Chronic</option>
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Diagnosis</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\emr\diagnoses.blade.php ENDPATH**/ ?>