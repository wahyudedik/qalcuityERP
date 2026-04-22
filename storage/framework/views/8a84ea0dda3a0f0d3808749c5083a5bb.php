

<?php $__env->startSection('title', 'Telemedicine Prescriptions'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-prescription text-primary"></i> E-Prescriptions
            </h1>
            <p class="text-muted mb-0">Prescriptions from teleconsultations</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rx #</th>
                                    <th>Date</th>
                                    <th>Doctor</th>
                                    <th>Consultation</th>
                                    <th>Medications</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($rx->prescription_number); ?></code></td>
                                        <td><?php echo e($rx->prescribed_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td><?php echo e($rx->doctor?->name ?? '-'); ?></td>
                                        <td>
                                            <?php if($rx->consultation): ?>
                                                <a
                                                    href="<?php echo e(route('healthcare.telemedicine.consultations.show', $rx->consultation)); ?>">
                                                    #<?php echo e($rx->consultation->consultation_number); ?>

                                                </a>
                                            <?php else: ?>
                                                -
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <strong><?php echo e(count($rx->medications ?? [])); ?> medications</strong>
                                        </td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'completed' => 'secondary',
                                                    'cancelled' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$rx->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($rx->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#viewRxModal<?php echo e($rx->id); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button class="btn btn-outline-success btn-sm">
                                                    <i class="fas fa-download"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Prescription Modal -->
                                    <div class="modal fade" id="viewRxModal<?php echo e($rx->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">E-Prescription - <?php echo e($rx->prescription_number); ?>

                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Doctor:</strong> <?php echo e($rx->doctor?->name ?? '-'); ?>

                                                            <br><strong>Date:</strong>
                                                            <?php echo e($rx->prescribed_at?->format('d/m/Y H:i') ?? '-'); ?>

                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Patient:</strong> <?php echo e($rx->patient?->name ?? '-'); ?>

                                                            <br><strong>Status:</strong>
                                                            <span
                                                                class="badge bg-<?php echo e($statusColors[$rx->status] ?? 'secondary'); ?>">
                                                                <?php echo e(ucfirst($rx->status)); ?>

                                                            </span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <h6 class="text-primary">Medications</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm">
                                                            <thead>
                                                                <tr>
                                                                    <th>Medication</th>
                                                                    <th>Dosage</th>
                                                                    <th>Frequency</th>
                                                                    <th>Duration</th>
                                                                    <th>Instructions</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <?php $__empty_2 = true; $__currentLoopData = $rx->medications ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $med): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_2 = false; ?>
                                                                    <tr>
                                                                        <td><strong><?php echo e($med['name'] ?? '-'); ?></strong></td>
                                                                        <td><?php echo e($med['dosage'] ?? '-'); ?></td>
                                                                        <td><?php echo e($med['frequency'] ?? '-'); ?></td>
                                                                        <td><?php echo e($med['duration'] ?? '-'); ?></td>
                                                                        <td><?php echo e($med['instructions'] ?? '-'); ?></td>
                                                                    </tr>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_2): ?>
                                                                    <tr>
                                                                        <td colspan="5" class="text-center text-muted">No
                                                                            medications</td>
                                                                    </tr>
                                                                <?php endif; ?>
                                                            </tbody>
                                                        </table>
                                                    </div>

                                                    <?php if($rx->notes): ?>
                                                        <div class="mt-3">
                                                            <h6 class="text-primary">Doctor's Notes</h6>
                                                            <div class="alert alert-info"><?php echo e($rx->notes); ?></div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">No prescriptions found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($prescriptions->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telemedicine\prescriptions.blade.php ENDPATH**/ ?>