<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Daily Site Report - <?php echo e($report->report_date->format('d M Y')); ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        .header {
            text-align: center;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .header h1 {
            color: #2563eb;
            margin: 0;
            font-size: 24px;
        }

        .header p {
            color: #6b7280;
            margin: 5px 0 0 0;
        }

        .section {
            margin-bottom: 20px;
        }

        .section-title {
            background: #f3f4f6;
            padding: 8px 12px;
            font-weight: bold;
            color: #1f2937;
            border-left: 4px solid #2563eb;
        }

        .info-grid {
            display: table;
            width: 100%;
            margin-top: 10px;
        }

        .info-row {
            display: table-row;
        }

        .info-cell {
            display: table-cell;
            padding: 8px;
            border: 1px solid #e5e7eb;
        }

        .info-label {
            font-weight: bold;
            color: #6b7280;
            width: 40%;
        }

        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }

        .status-approved {
            background: #d1fae5;
            color: #065f46;
        }

        .status-submitted {
            background: #fef3c7;
            color: #92400e;
        }

        .status-draft {
            background: #f3f4f6;
            color: #374151;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f9fafb;
            padding: 8px;
            text-align: left;
            font-size: 12px;
            border: 1px solid #e5e7eb;
        }

        td {
            padding: 8px;
            border: 1px solid #e5e7eb;
            font-size: 12px;
        }

        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #9ca3af;
            border-top: 1px solid #e5e7eb;
            padding-top: 10px;
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <h1>DAILY SITE REPORT</h1>
        <p><?php echo e($report->project->name); ?> (<?php echo e($report->project->number); ?>)</p>
        <p>Report Date: <?php echo e($report->report_date->format('d F Y')); ?></p>
    </div>

    <!-- Basic Information -->
    <div class="section">
        <div class="section-title">Basic Information</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Reported By</div>
                <div class="info-cell"><?php echo e($report->reportedBy->name ?? 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Status</div>
                <div class="info-cell">
                    <span class="status-badge status-<?php echo e($report->status); ?>">
                        <?php echo e(strtoupper($report->status)); ?>

                    </span>
                </div>
            </div>
            <?php if($report->approvedBy): ?>
                <div class="info-row">
                    <div class="info-cell info-label">Approved By</div>
                    <div class="info-cell"><?php echo e($report->approvedBy->name); ?></div>
                </div>
                <div class="info-row">
                    <div class="info-cell info-label">Approved At</div>
                    <div class="info-cell"><?php echo e($report->approved_at->format('d M Y H:i')); ?></div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Weather & Conditions -->
    <div class="section">
        <div class="section-title">Weather & Conditions</div>
        <div class="info-grid">
            <div class="info-row">
                <div class="info-cell info-label">Weather</div>
                <div class="info-cell"><?php echo e(ucfirst($report->weather_condition ?? 'N/A')); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Temperature</div>
                <div class="info-cell"><?php echo e($report->temperature ? $report->temperature . '°C' : 'N/A'); ?></div>
            </div>
            <div class="info-row">
                <div class="info-cell info-label">Manpower Count</div>
                <div class="info-cell"><?php echo e($report->manpower_count); ?> workers</div>
            </div>
        </div>
    </div>

    <!-- Work Progress -->
    <div class="section">
        <div class="section-title">Work Progress</div>
        <div style="margin-top: 10px;">
            <strong>Progress:</strong> <?php echo e($report->progress_percentage); ?>%
            <div style="background: #e5e7eb; height: 20px; margin-top: 5px; border-radius: 10px; overflow: hidden;">
                <div style="background: #2563eb; height: 100%; width: <?php echo e($report->progress_percentage); ?>%;"></div>
            </div>
        </div>
        <div style="margin-top: 15px;">
            <strong>Work Performed:</strong>
            <p style="margin-top: 5px; line-height: 1.6;"><?php echo e($report->work_performed); ?></p>
        </div>
    </div>

    <!-- Equipment & Materials -->
    <?php if($report->equipment_used || $report->materials_received): ?>
        <div class="section">
            <div class="section-title">Equipment & Materials</div>
            <?php if($report->equipment_used): ?>
                <div style="margin-top: 10px;">
                    <strong>Equipment Used:</strong>
                    <p style="margin-top: 5px;"><?php echo e($report->equipment_used); ?></p>
                </div>
            <?php endif; ?>
            <?php if($report->materials_received): ?>
                <div style="margin-top: 10px;">
                    <strong>Materials Received:</strong>
                    <p style="margin-top: 5px;"><?php echo e($report->materials_received); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <!-- Issues & Safety -->
    <div class="section">
        <div class="section-title">Issues & Safety</div>
        <?php if($report->issues_encountered): ?>
            <div style="margin-top: 10px;">
                <strong>Issues Encountered:</strong>
                <p style="margin-top: 5px;"><?php echo e($report->issues_encountered); ?></p>
            </div>
        <?php endif; ?>
        <div style="margin-top: 10px;">
            <strong>Safety Incidents:</strong> <?php echo e($report->safety_incidents); ?>

        </div>
    </div>

    <!-- Labor Logs -->
    <?php if($report->laborLogs->count() > 0): ?>
        <div class="section">
            <div class="section-title">Labor Details</div>
            <table>
                <thead>
                    <tr>
                        <th>Worker Name</th>
                        <th>Type</th>
                        <th>Trade</th>
                        <th>Hours</th>
                        <th>Rate/Hr</th>
                        <th>Total Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__currentLoopData = $report->laborLogs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $log): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td><?php echo e($log->worker_name); ?></td>
                            <td><?php echo e(ucfirst($log->worker_type)); ?></td>
                            <td><?php echo e(ucfirst($log->trade ?? '-')); ?></td>
                            <td><?php echo e($log->hours_worked); ?></td>
                            <td>Rp <?php echo e(number_format($log->hourly_rate, 0, ',', '.')); ?></td>
                            <td>Rp <?php echo e(number_format($log->total_cost, 0, ',', '.')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
            <div style="margin-top: 10px; text-align: right; font-weight: bold;">
                Total Labor Cost: Rp <?php echo e(number_format($report->laborLogs->sum('total_cost'), 0, ',', '.')); ?>

            </div>
        </div>
    <?php endif; ?>

    <!-- Notes -->
    <?php if($report->notes): ?>
        <div class="section">
            <div class="section-title">Additional Notes</div>
            <p style="margin-top: 10px;"><?php echo e($report->notes); ?></p>
        </div>
    <?php endif; ?>

    <!-- Footer -->
    <div class="footer">
        <p>Generated on <?php echo e(now()->format('d F Y H:i')); ?> | QalcuityERP Construction Module</p>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\construction\pdf\daily-report.blade.php ENDPATH**/ ?>