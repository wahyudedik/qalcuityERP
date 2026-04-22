

<?php $__env->startSection('title', 'Vital Signs Monitoring'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-heartbeat text-primary"></i> Vital Signs Monitoring
            </h1>
            <p class="text-muted mb-0">Track patient vital signs over time</p>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addVitalsModal">
                <i class="fas fa-plus"></i> Record Vitals
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
                            <select name="patient_id" class="form-select">
                                <option value="">All Patients</option>
                                <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->id); ?>"
                                        <?php echo e(request('patient_id') == $patient->id ? 'selected' : ''); ?>>
                                        <?php echo e($patient->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <input type="date" name="date" class="form-control"
                                value="<?php echo e(request('date', today()->format('Y-m-d'))); ?>">
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
                                    <th>Time</th>
                                    <th>Patient</th>
                                    <th>Temperature (°C)</th>
                                    <th>Heart Rate (bpm)</th>
                                    <th>Blood Pressure</th>
                                    <th>Resp Rate</th>
                                    <th>SpO2 (%)</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $vitals; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vital): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><?php echo e($vital->recorded_at?->format('H:i') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $vital->patient)); ?>">
                                                <?php echo e($vital->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td>
                                            <?php if($vital->temperature): ?>
                                                <span class="<?php echo e($vital->temperature > 37.5 ? 'text-danger fw-bold' : ''); ?>">
                                                    <?php echo e($vital->temperature); ?>°C
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($vital->heart_rate): ?>
                                                <span
                                                    class="<?php echo e($vital->heart_rate > 100 || $vital->heart_rate < 60 ? 'text-danger fw-bold' : ''); ?>">
                                                    <?php echo e($vital->heart_rate); ?>

                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if($vital->blood_pressure): ?>
                                                <span
                                                    class="<?php echo e(explode('/', $vital->blood_pressure)[0] > 140 ? 'text-danger fw-bold' : ''); ?>">
                                                    <?php echo e($vital->blood_pressure); ?>

                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo e($vital->respiratory_rate ?? '-'); ?></td>
                                        <td>
                                            <?php if($vital->spo2): ?>
                                                <span
                                                    class="<?php echo e($vital->spo2 < 95 ? 'text-danger fw-bold' : 'text-success'); ?>">
                                                    <?php echo e($vital->spo2); ?>%
                                                </span>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td><small><?php echo e(Str::limit($vital->notes, 30)); ?></small></td>
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
                                        <td colspan="9" class="text-center py-4 text-muted">No vital signs recorded</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($vitals->links()); ?>

                </div>
            </div>
        </div>
    </div>

    <!-- Add Vitals Modal -->
    <div class="modal fade" id="addVitalsModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.inpatient.vitals.store')); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Record Vital Signs</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Patient</label>
                            <select name="patient_id" class="form-select" required>
                                <option value="">Select patient</option>
                                <?php $__currentLoopData = $patients ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $patient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($patient->id); ?>"><?php echo e($patient->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Temperature (°C)</label>
                                <input type="number" name="temperature" class="form-control" step="0.1" min="30"
                                    max="45" placeholder="36.5">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Heart Rate (bpm)</label>
                                <input type="number" name="heart_rate" class="form-control" min="30" max="250"
                                    placeholder="72">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Blood Pressure</label>
                                <input type="text" name="blood_pressure" class="form-control" placeholder="120/80">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Respiratory Rate</label>
                                <input type="number" name="respiratory_rate" class="form-control" min="5"
                                    max="60" placeholder="16">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">SpO2 (%)</label>
                                <input type="number" name="spo2" class="form-control" min="50" max="100"
                                    placeholder="98">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Recorded At</label>
                                <input type="time" name="recorded_at" class="form-control"
                                    value="<?php echo e(now()->format('H:i')); ?>" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notes</label>
                            <textarea name="notes" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Vitals</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\inpatient\vitals.blade.php ENDPATH**/ ?>