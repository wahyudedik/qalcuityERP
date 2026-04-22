<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Batch Record - <?php echo e($batch->batch_number); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 11px;
            line-height: 1.4;
            margin: 0;
            padding: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            margin: 0;
            color: #2563eb;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0;
            color: #666;
        }

        .section {
            margin-bottom: 20px;
            page-break-inside: avoid;
        }

        .section-title {
            background: #2563eb;
            color: white;
            padding: 8px 12px;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 10px;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-bottom: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-label {
            display: table-cell;
            width: 30%;
            font-weight: bold;
            padding: 5px;
            background: #f3f4f6;
        }

        .info-value {
            display: table-cell;
            width: 70%;
            padding: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th {
            background: #2563eb;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }

        td {
            padding: 6px 8px;
            border-bottom: 1px solid #e5e7eb;
        }

        tr:nth-child(even) {
            background: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 10px;
            font-weight: bold;
        }

        .status-draft {
            background: #e5e7eb;
            color: #374151;
        }

        .status-in_progress {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-qc_pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-released {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .yield-box {
            background: #f0fdf4;
            border: 2px solid #22c55e;
            padding: 15px;
            text-align: center;
            margin: 15px 0;
        }

        .yield-value {
            font-size: 32px;
            font-weight: bold;
            color: #16a34a;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 2px solid #e5e7eb;
            text-align: center;
            font-size: 9px;
            color: #6b7280;
        }

        .signature-box {
            display: inline-block;
            width: 45%;
            margin: 20px 2%;
            text-align: center;
        }

        .signature-line {
            border-top: 2px solid #000;
            margin-top: 60px;
            padding-top: 5px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>BATCH PRODUCTION RECORD</h1>
        <p><?php echo e(config('brand.name', 'QalcuityERP')); ?></p>
        <p>Generated: <?php echo e($generated_at); ?> by <?php echo e($generated_by); ?></p>
    </div>

    <!-- Batch Information -->
    <div class="section">
        <div class="section-title">BATCH INFORMATION</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Batch Number:</div>
                <div class="info-value"><strong><?php echo e($batch->batch_number); ?></strong></div>
            </div>
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="status-badge status-<?php echo e($batch->status); ?>">
                        <?php echo e(strtoupper(str_replace('_', ' ', $batch->status))); ?>

                    </span>
                </div>
            </div>
            <div class="info-row">
                <div class="info-label">Formula:</div>
                <div class="info-value"><?php echo e($batch->formula->formula_name); ?> (<?php echo e($batch->formula->formula_code); ?>)</div>
            </div>
            <div class="info-row">
                <div class="info-label">Product Type:</div>
                <div class="info-value"><?php echo e(ucfirst($batch->formula->product_type)); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Production Date:</div>
                <div class="info-value"><?php echo e($batch->production_date->format('d M Y')); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Expiry Date:</div>
                <div class="info-value"><?php echo e($batch->expiry_date ? $batch->expiry_date->format('d M Y') : 'N/A'); ?></div>
            </div>
        </div>
    </div>

    <!-- Production Quantities -->
    <div class="section">
        <div class="section-title">PRODUCTION QUANTITIES</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Planned Quantity:</div>
                <div class="info-value"><?php echo e(number_format($batch->planned_quantity, 2)); ?>

                    <?php echo e($batch->formula->batch_unit); ?></div>
            </div>
            <div class="info-row">
                <div class="info-label">Actual Quantity:</div>
                <div class="info-value">
                    <?php echo e($batch->actual_quantity ? number_format($batch->actual_quantity, 2) : 'Not recorded'); ?>

                    <?php echo e($batch->formula->batch_unit); ?></div>
            </div>
        </div>

        <div class="yield-box">
            <div style="font-size: 14px; color: #6b7280; margin-bottom: 5px;">YIELD PERCENTAGE</div>
            <div class="yield-value">
                <?php echo e($batch->yield_percentage ? number_format($batch->yield_percentage, 1) : '0'); ?>%
            </div>
            <?php if($yieldAnalysis['yield_status']): ?>
                <div style="margin-top: 5px; font-size: 12px; color: #6b7280;">
                    Status: <?php echo e(strtoupper(str_replace('_', ' ', $yieldAnalysis['yield_status']))); ?>

                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Formula Ingredients -->
    <div class="section">
        <div class="section-title">FORMULA INGREDIENTS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 30%;">INCI Name</th>
                    <th style="width: 20%;">Common Name</th>
                    <th style="width: 15%;">Quantity</th>
                    <th style="width: 15%;">%</th>
                    <th style="width: 15%;">Function</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $batch->formula->ingredients; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $ingredient): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e($ingredient->sort_order); ?></td>
                        <td><?php echo e($ingredient->inci_name); ?></td>
                        <td><?php echo e($ingredient->common_name ?? '-'); ?></td>
                        <td><?php echo e(number_format($ingredient->quantity, 2)); ?> <?php echo e($ingredient->unit); ?></td>
                        <td><?php echo e($ingredient->percentage ? number_format($ingredient->percentage, 2) . '%' : '-'); ?></td>
                        <td><?php echo e(ucfirst($ingredient->function ?? '-')); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Quality Checks -->
    <div class="section">
        <div class="section-title">QUALITY CONTROL CHECKS</div>
        <table>
            <thead>
                <tr>
                    <th style="width: 15%;">Checkpoint</th>
                    <th style="width: 25%;">Parameter</th>
                    <th style="width: 15%;">Target</th>
                    <th style="width: 15%;">Actual</th>
                    <th style="width: 15%;">Limits</th>
                    <th style="width: 15%;">Result</th>
                </tr>
            </thead>
            <tbody>
                <?php $__currentLoopData = $batch->qualityChecks; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $check): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr>
                        <td><?php echo e(ucfirst(str_replace('_', ' ', $check->check_point))); ?></td>
                        <td><?php echo e($check->parameter); ?></td>
                        <td><?php echo e($check->target_value ?? '-'); ?></td>
                        <td><?php echo e($check->actual_value ?? '-'); ?></td>
                        <td>
                            <?php if($check->lower_limit && $check->upper_limit): ?>
                                <?php echo e($check->lower_limit); ?> - <?php echo e($check->upper_limit); ?>

                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>
                        <td>
                            <strong
                                style="color: <?php echo e($check->result == 'pass' ? '#16a34a' : ($check->result == 'fail' ? '#dc2626' : '#ca8a04')); ?>">
                                <?php echo e(strtoupper($check->result)); ?>

                            </strong>
                        </td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tbody>
        </table>
    </div>

    <!-- Rework Logs -->
    <?php if($batch->reworkLogs->count() > 0): ?>
        <div class="section">
            <div class="section-title">REWORK LOGS</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 15%;">Rework Code</th>
                        <th style="width: 25%;">Reason</th>
                        <th style="width: 25%;">Action</th>
                        <th style="width: 10%;">Before</th>
                        <th style="width: 10%;">After</th>
                        <th style="width: 15%;">Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $batch->reworkLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $rework): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($rework->rework_code); ?></td>
                            <td><?php echo e($rework->reason); ?></td>
                            <td><?php echo e($rework->rework_action); ?></td>
                            <td><?php echo e(number_format($rework->quantity_before, 2)); ?></td>
                            <td><?php echo e($rework->quantity_after ? number_format($rework->quantity_after, 2) : '-'); ?></td>
                            <td><?php echo e(ucfirst(str_replace('_', ' ', $rework->status))); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>

    <!-- Production Notes -->
    <?php if($batch->production_notes || $batch->qc_notes): ?>
        <div class="section">
            <div class="section-title">NOTES</div>
            <?php if($batch->production_notes): ?>
                <div style="margin-bottom: 10px;">
                    <strong>Production Notes:</strong><br>
                    <?php echo e($batch->production_notes); ?>

                </div>
            <?php endif; ?>
            <?php if($batch->qc_notes): ?>
                <div>
                    <strong>QC Notes:</strong><br>
                    <?php echo e($batch->qc_notes); ?>

                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Signatures -->
    <div class="section">
        <div class="section-title">APPROVAL SIGNATURES</div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>Produced By</strong><br>
                <?php echo e($batch->producer->name ?? '________________'); ?><br>
                <small><?php echo e($batch->production_date->format('d M Y')); ?></small>
            </div>
        </div>
        <div class="signature-box">
            <div class="signature-line">
                <strong>QC Inspector</strong><br>
                <?php echo e($batch->qcInspector->name ?? '________________'); ?><br>
                <small><?php echo e($batch->qc_completed_at ? $batch->qc_completed_at->format('d M Y') : ''); ?></small>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is an official batch production record generated by <?php echo e(config('brand.name', 'QalcuityERP')); ?></p>
        <p>Document ID: <?php echo e($batch->batch_number); ?> | Generated: <?php echo e($generated_at); ?></p>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\cosmetic\batches\pdf\batch-record.blade.php ENDPATH**/ ?>