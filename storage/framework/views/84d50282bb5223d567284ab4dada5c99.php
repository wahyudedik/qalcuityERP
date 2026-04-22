

<?php $__env->startSection('title', 'Insurance - ' . $patient->name); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('healthcare.patients.index')); ?>">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"><?php echo e($patient->name); ?></a></li>
                    <li class="breadcrumb-item active">Insurance</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="fas fa-shield-alt text-primary"></i> Insurance Coverage
            </h1>
        </div>
        <div>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addInsuranceModal">
                <i class="fas fa-plus"></i> Add Insurance
            </button>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row">
        <?php $__empty_1 = true; $__currentLoopData = $insurances; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $insurance): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <div>
                            <span class="badge bg-<?php echo e($insurance->is_active ? 'success' : 'danger'); ?> me-2">
                                <?php echo e($insurance->is_active ? 'Active' : 'Inactive'); ?>

                            </span>
                            <strong><?php echo e($insurance->insurance_provider ?? 'N/A'); ?></strong>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Policy Number</small></div>
                            <div class="col-6"><strong><?php echo e($insurance->policy_number ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Group Number</small></div>
                            <div class="col-6"><strong><?php echo e($insurance->group_number ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Plan Type</small></div>
                            <div class="col-6"><strong><?php echo e($insurance->plan_type ?? 'N/A'); ?></strong></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Valid Period</small></div>
                            <div class="col-6">
                                <strong>
                                    <?php if($insurance->valid_from && $insurance->valid_until): ?>
                                        <?php echo e($insurance->valid_from->format('d/m/Y')); ?> -
                                        <?php echo e($insurance->valid_until->format('d/m/Y')); ?>

                                    <?php else: ?>
                                        N/A
                                    <?php endif; ?>
                                </strong>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-6"><small class="text-muted">Coverage</small></div>
                            <div class="col-6"><strong>Rp
                                    <?php echo e(number_format($insurance->coverage_limit ?? 0, 0, ',', '.')); ?></strong></div>
                        </div>
                        <div class="row">
                            <div class="col-6"><small class="text-muted">Copay</small></div>
                            <div class="col-6"><strong><?php echo e($insurance->copay_percentage ?? 0); ?>%</strong></div>
                        </div>
                    </div>
                    <div class="card-footer bg-light">
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-<?php echo e($insurance->usage_percentage > 80 ? 'danger' : ($insurance->usage_percentage > 50 ? 'warning' : 'success')); ?>"
                                style="width: <?php echo e($insurance->usage_percentage ?? 0); ?>%"></div>
                        </div>
                        <small class="text-muted">Used: <?php echo e($insurance->usage_percentage ?? 0); ?>%</small>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-12">
                <div class="card">
                    <div class="card-body text-center py-5">
                        <i class="fas fa-shield-alt fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No insurance policies added yet</p>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Insurance Modal -->
    <div class="modal fade" id="addInsuranceModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="<?php echo e(route('healthcare.patients.insurance.store', $patient)); ?>" method="POST">
                    <?php echo csrf_field(); ?>
                    <div class="modal-header">
                        <h5 class="modal-title">Add Insurance Policy</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Insurance Provider</label>
                            <input type="text" name="insurance_provider" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Policy Number</label>
                                <input type="text" name="policy_number" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Group Number</label>
                                <input type="text" name="group_number" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid From</label>
                                <input type="date" name="valid_from" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Valid Until</label>
                                <input type="date" name="valid_until" class="form-control" required>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Coverage Limit</label>
                                <input type="number" name="coverage_limit" class="form-control" step="0.01">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Copay %</label>
                                <input type="number" name="copay_percentage" class="form-control" min="0"
                                    max="100">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Insurance</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\patients\insurance.blade.php ENDPATH**/ ?>