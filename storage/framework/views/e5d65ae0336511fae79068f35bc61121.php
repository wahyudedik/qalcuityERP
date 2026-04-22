

<?php $__env->startSection('title', 'Medication Dispensing'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-pills text-primary"></i> Medication Dispensing
            </h1>
            <p class="text-muted mb-0">Process and track medication dispensing</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($pending->count()); ?></h3>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($in_progress->count()); ?></h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($completed->count()); ?></h3>
                    <small class="text-muted">Dispensed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body text-center">
                    <h3 class="text-danger"><?php echo e($expired->count()); ?></h3>
                    <small class="text-muted">Expired</small>
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
                                    <th>Rx #</th>
                                    <th>Patient</th>
                                    <th>Medication</th>
                                    <th>Prescriber</th>
                                    <th>Prescribed</th>
                                    <th>Status</th>
                                    <th>Pharmacist</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rx): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($rx->prescription_number); ?></code></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $rx->patient)); ?>">
                                                <?php echo e($rx->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td>
                                            <strong><?php echo e($rx->medication_name ?? '-'); ?></strong>
                                            <br><small class="text-muted"><?php echo e($rx->dosage ?? ''); ?> -
                                                <?php echo e($rx->frequency ?? ''); ?></small>
                                        </td>
                                        <td><?php echo e($rx->prescriber?->name ?? '-'); ?></td>
                                        <td><?php echo e($rx->prescribed_at?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'in_progress' => 'info',
                                                    'dispensed' => 'success',
                                                    'expired' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$rx->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $rx->status))); ?>

                                            </span>
                                        </td>
                                        <td><?php echo e($rx->dispensed_by?->name ?? '-'); ?></td>
                                        <td>
                                            <?php if($rx->status == 'pending' || $rx->status == 'in_progress'): ?>
                                                <button class="btn btn-sm btn-success"
                                                    onclick="dispenseMedication(<?php echo e($rx->id); ?>)">
                                                    <i class="fas fa-check"></i> Dispense
                                                </button>
                                            <?php else: ?>
                                                <button class="btn btn-sm btn-outline-primary"
                                                    onclick="viewDetails(<?php echo e($rx->id); ?>)">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No prescriptions to display
                                        </td>
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

    <?php $__env->startPush('scripts'); ?>
        <script>
            function dispenseMedication(rxId) {
                if (confirm('Confirm medication dispensing?')) {
                    // Implement AJAX dispensing
                    window.location.reload();
                }
            }

            function viewDetails(rxId) {
                // Implement view details modal
                window.location.href = '/healthcare/pharmacy/dispensing/' + rxId;
            }
        </script>
    <?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\pharmacy\dispensing.blade.php ENDPATH**/ ?>