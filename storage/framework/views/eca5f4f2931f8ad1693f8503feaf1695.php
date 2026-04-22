

<?php $__env->startSection('title', 'Medical History - ' . $patient->name); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="<?php echo e(route('healthcare.patients.index')); ?>">Patients</a></li>
                    <li class="breadcrumb-item"><a
                            href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"><?php echo e($patient->name); ?></a></li>
                    <li class="breadcrumb-item active">Medical History</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0">
                <i class="fas fa-history text-primary"></i> Medical History
            </h1>
            <p class="text-muted mb-0">Complete medical history for <?php echo e($patient->name); ?></p>
        </div>
        <div>
            <a href="<?php echo e(route('healthcare.patients.show', $patient)); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left"></i> Back to Patient
            </a>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="fas fa-filter"></i> Filter Timeline
                    </h5>
                    <form method="GET" class="d-flex gap-2">
                        <select name="type" class="form-select form-select-sm" style="width: 180px;">
                            <option value="">All Types</option>
                            <option value="visit" <?php echo e(request('type') == 'visit' ? 'selected' : ''); ?>>Visits</option>
                            <option value="diagnosis" <?php echo e(request('type') == 'diagnosis' ? 'selected' : ''); ?>>Diagnoses
                            </option>
                            <option value="procedure" <?php echo e(request('type') == 'procedure' ? 'selected' : ''); ?>>Procedures
                            </option>
                            <option value="lab" <?php echo e(request('type') == 'lab' ? 'selected' : ''); ?>>Lab Results</option>
                            <option value="prescription" <?php echo e(request('type') == 'prescription' ? 'selected' : ''); ?>>
                                Prescriptions</option>
                        </select>
                        <input type="date" name="date_from" class="form-control form-control-sm" style="width: 150px;"
                            value="<?php echo e(request('date_from')); ?>">
                        <input type="date" name="date_to" class="form-control form-control-sm" style="width: 150px;"
                            value="<?php echo e(request('date_to')); ?>">
                        <button type="submit" class="btn btn-sm btn-primary">
                            <i class="fas fa-search"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="fas fa-timeline"></i> Medical Timeline
                    </h5>
                </div>
                <div class="card-body">
                    <?php if($timeline->count() > 0): ?>
                        <div class="timeline">
                            <?php $__currentLoopData = $timeline; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="timeline-item mb-4">
                                    <div class="d-flex">
                                        <div class="me-3">
                                            <div class="timeline-marker bg-<?php echo e($item['type_color']); ?>">
                                                <i class="fas <?php echo e($item['type_icon']); ?> text-white"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1">
                                            <div class="card border-<?php echo e($item['type_color']); ?>">
                                                <div class="card-header bg-light py-2">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div>
                                                            <span
                                                                class="badge bg-<?php echo e($item['type_color']); ?> me-2"><?php echo e(ucfirst($item['type'])); ?></span>
                                                            <strong><?php echo e($item['title']); ?></strong>
                                                        </div>
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i> <?php echo e($item['date']); ?>

                                                        </small>
                                                    </div>
                                                </div>
                                                <div class="card-body py-2">
                                                    <p class="mb-1 small"><?php echo e($item['description']); ?></p>
                                                    <?php if($item['provider']): ?>
                                                        <small class="text-muted">
                                                            <i class="fas fa-user-md"></i> <?php echo e($item['provider']); ?>

                                                        </small>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>

                        <?php if($timeline->hasPages()): ?>
                            <div class="d-flex justify-content-center mt-4">
                                <?php echo e($timeline->links()); ?>

                            </div>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-history fa-3x text-muted mb-3"></i>
                            <p class="text-muted">No medical history records found</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <style>
        .timeline-marker {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .timeline-item::before {
            content: '';
            position: absolute;
            left: 19px;
            top: 40px;
            bottom: -16px;
            width: 2px;
            background: #dee2e6;
        }
    </style>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\patients\medical-history.blade.php ENDPATH**/ ?>