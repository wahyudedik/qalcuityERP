

<?php $__env->startSection('title', 'Radiology Reports'); ?>

<?php $__env->startSection('header'); ?>
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h1 class="h3 mb-0">
                <i class="fas fa-file-medical text-primary"></i> Radiology Reports
            </h1>
            <p class="text-muted mb-0">Radiologist interpretation reports</p>
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
                                    <th>Report #</th>
                                    <th>Exam Date</th>
                                    <th>Patient</th>
                                    <th>Exam Type</th>
                                    <th>Radiologist</th>
                                    <th>Findings</th>
                                    <th>Impression</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $__empty_1 = true; $__currentLoopData = $reports; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $report): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                    <tr>
                                        <td><code><?php echo e($report->report_number); ?></code></td>
                                        <td><?php echo e($report->exam_date?->format('d/m/Y') ?? '-'); ?></td>
                                        <td>
                                            <a href="<?php echo e(route('healthcare.patients.show', $report->patient)); ?>">
                                                <?php echo e($report->patient->name ?? '-'); ?>

                                            </a>
                                        </td>
                                        <td><?php echo e($report->exam_type ?? '-'); ?></td>
                                        <td><?php echo e($report->radiologist?->name ?? '-'); ?></td>
                                        <td><small><?php echo e(Str::limit($report->findings, 40) ?? '-'); ?></small></td>
                                        <td><small><?php echo e(Str::limit($report->impression, 40) ?? '-'); ?></small></td>
                                        <td>
                                            <?php
                                                $statusColors = [
                                                    'draft' => 'secondary',
                                                    'preliminary' => 'warning',
                                                    'final' => 'success',
                                                    'amended' => 'info',
                                                ];
                                            ?>
                                            <span class="badge bg-<?php echo e($statusColors[$report->status] ?? 'secondary'); ?>">
                                                <?php echo e(ucfirst($report->status)); ?>

                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button class="btn btn-outline-primary btn-sm" data-bs-toggle="modal"
                                                    data-bs-target="#viewReportModal<?php echo e($report->id); ?>">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <?php if($report->status == 'draft' || $report->status == 'preliminary'): ?>
                                                    <button class="btn btn-outline-success btn-sm">
                                                        <i class="fas fa-check"></i> Finalize
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>

                                    <!-- View Report Modal -->
                                    <div class="modal fade" id="viewReportModal<?php echo e($report->id); ?>" tabindex="-1">
                                        <div class="modal-dialog modal-xl">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Radiology Report - <?php echo e($report->report_number); ?>

                                                    </h5>
                                                    <button type="button" class="btn-close"
                                                        data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row mb-3">
                                                        <div class="col-md-6">
                                                            <strong>Patient:</strong> <?php echo e($report->patient?->name ?? '-'); ?>

                                                            <br><strong>Exam Type:</strong> <?php echo e($report->exam_type ?? '-'); ?>

                                                            <br><strong>Exam Date:</strong>
                                                            <?php echo e($report->exam_date?->format('d/m/Y H:i') ?? '-'); ?>

                                                        </div>
                                                        <div class="col-md-6">
                                                            <strong>Radiologist:</strong>
                                                            <?php echo e($report->radiologist?->name ?? '-'); ?>

                                                            <br><strong>Report Date:</strong>
                                                            <?php echo e($report->created_at->format('d/m/Y H:i')); ?>

                                                            <br><strong>Status:</strong>
                                                            <span
                                                                class="badge bg-<?php echo e($statusColors[$report->status] ?? 'secondary'); ?>">
                                                                <?php echo e(ucfirst($report->status)); ?>

                                                            </span>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Clinical History</h6>
                                                        <p class="bg-light p-3 rounded">
                                                            <?php echo e($report->clinical_history ?? 'N/A'); ?></p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Technique</h6>
                                                        <p class="bg-light p-3 rounded"><?php echo e($report->technique ?? 'N/A'); ?>

                                                        </p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Findings</h6>
                                                        <p class="bg-light p-3 rounded" style="white-space: pre-wrap;">
                                                            <?php echo e($report->findings ?? 'N/A'); ?></p>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Impression</h6>
                                                        <div class="alert alert-info">
                                                            <strong><?php echo e($report->impression ?? 'N/A'); ?></strong>
                                                        </div>
                                                    </div>

                                                    <div class="mb-3">
                                                        <h6 class="text-primary">Recommendations</h6>
                                                        <p class="bg-light p-3 rounded">
                                                            <?php echo e($report->recommendations ?? 'N/A'); ?></p>
                                                    </div>

                                                    <?php if($report->images && count($report->images) > 0): ?>
                                                        <div class="mb-3">
                                                            <h6 class="text-primary">Images (<?php echo e(count($report->images)); ?>)
                                                            </h6>
                                                            <div class="row">
                                                                <?php $__currentLoopData = $report->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $image): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                                    <div class="col-md-3 mb-2">
                                                                        <img src="<?php echo e($image['url'] ?? '#'); ?>"
                                                                            class="img-fluid rounded border"
                                                                            alt="Radiology image">
                                                                    </div>
                                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                                            </div>
                                                        </div>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary"
                                                        data-bs-dismiss="modal">Close</button>
                                                    <button class="btn btn-success" onclick="window.print()">
                                                        <i class="fas fa-print"></i> Print
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">No radiology reports found
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php echo e($reports->links()); ?>

                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\radiology\reports.blade.php ENDPATH**/ ?>