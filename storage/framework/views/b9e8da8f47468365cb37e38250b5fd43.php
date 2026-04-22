

<?php $__env->startSection('title', 'Radiology Exams'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-x-ray text-primary"></i> Radiology Exams
            </h1>
            <p class="text-muted mb-0">Manage radiology examinations and imaging</p>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="text-info"><?php echo e($exams->where('status', 'scheduled')->count()); ?></h3>
                    <small class="text-muted">Scheduled</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="text-warning"><?php echo e($exams->where('status', 'in_progress')->count()); ?></h3>
                    <small class="text-muted">In Progress</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="text-success"><?php echo e($exams->where('status', 'completed')->count()); ?></h3>
                    <small class="text-muted">Completed</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h3 class="text-secondary"><?php echo e($exams->where('status', 'reported')->count()); ?></h3>
                    <small class="text-muted">Reported</small>
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
                                    <th>Exam #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Body Part</th>
                                    <th>Radiologist</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $exams; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $exam): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($exam->exam_number); ?></code></td>
                                        <td><?php echo e($exam->exam_date?->format('d/m/Y H:i') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $exam->patient)); ?>">
                                                <?php echo e($exam->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td>
                                            <?php
                                                $icons = [
                                                    'X-Ray' => 'fa-x-ray',
                                                    'MRI' => 'fa-magnet',
                                                    'CT Scan' => 'fa-circle-notch',
                                                    'Ultrasound' => 'fa-wave-square',
                                                    'Mammography' => 'fa-radiation',
                                                ];
                                            ?>
                                            <i
                                                class="fas <?php echo e($icons[$exam->exam_type] ?? 'fa-x-ray'); ?> me-1 text-primary"></i>
                                            <?php echo e($exam->exam_type ?? '-'); ?>

                                        </td>
                                        <td><?php echo e($exam->body_part ?? '-'); ?></td>
                                        <td><?php echo e($exam->radiologist?->name ?? '-'); ?></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'scheduled' => 'info',
                                                    'in_progress' => 'warning',
                                                    'completed' => 'success',
                                                    'reported' => 'secondary',
                                                    'cancelled' => 'danger',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$exam->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst(str_replace('_', ' ', $exam->status))); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <a href="<?php echo e(route('healthcare.radiology.exams.show', $exam)); ?>"
                                                    class="btn btn-outline-primary btn-sm">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <?php if($exam->status == 'completed'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-file-medical"></i> Report
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">No radiology exams found</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($exams->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\radiology\exams.blade.php ENDPATH**/ ?>