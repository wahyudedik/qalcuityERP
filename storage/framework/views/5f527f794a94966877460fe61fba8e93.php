<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Network Maps Report</title>
    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            margin: 20px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #3B82F6;
            padding-bottom: 15px;
        }

        .header h1 {
            margin: 0;
            color: #1F2937;
            font-size: 24px;
        }

        .header p {
            margin: 5px 0 0 0;
            color: #6B7280;
            font-size: 12px;
        }

        .stats-grid {
            display: table;
            width: 100%;
            margin-bottom: 30px;
        }

        .stats-row {
            display: table-row;
        }

        .stat-box {
            display: table-cell;
            width: 16.66%;
            padding: 15px;
            text-align: center;
            background: #F3F4F6;
            border-right: 1px solid white;
        }

        .stat-box:last-child {
            border-right: none;
        }

        .stat-box .value {
            font-size: 24px;
            font-weight: bold;
            color: #3B82F6;
            margin-bottom: 5px;
        }

        .stat-box .label {
            font-size: 9px;
            color: #6B7280;
            text-transform: uppercase;
        }

        .table-container {
            margin-top: 20px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
        }

        thead {
            background: #3B82F6;
            color: white;
        }

        th {
            padding: 10px 8px;
            text-align: left;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 8px;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #E5E7EB;
        }

        tr:nth-child(even) {
            background: #F9FAFB;
        }

        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 8px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .status-online {
            background: #D1FAE5;
            color: #065F46;
        }

        .status-offline {
            background: #FEE2E2;
            color: #991B1B;
        }

        .status-maintenance {
            background: #FEF3C7;
            color: #92400E;
        }

        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #E5E7EB;
            text-align: center;
            color: #9CA3AF;
            font-size: 8px;
        }
    </style>
</head>

<body>
    <div class="header">
        <h1>📡 Network Maps Report</h1>
        <p>Generated on <?php echo e(now()->format('F d, Y H:i:s')); ?></p>
    </div>

    <!-- Statistics -->
    <div class="stats-grid">
        <div class="stats-row">
            <div class="stat-box">
                <div class="value"><?php echo e($stats['total_devices']); ?></div>
                <div class="label">Total Devices</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #10B981;"><?php echo e($stats['online_devices']); ?></div>
                <div class="label">Online</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #EF4444;"><?php echo e($stats['offline_devices']); ?></div>
                <div class="label">Offline</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #F59E0B;"><?php echo e($stats['maintenance_devices']); ?></div>
                <div class="label">Maintenance</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #8B5CF6;"><?php echo e($stats['total_subscriptions']); ?></div>
                <div class="label">Subscriptions</div>
            </div>
            <div class="stat-box">
                <div class="value" style="color: #EC4899;"><?php echo e($stats['total_hotspot_users']); ?></div>
                <div class="label">Hotspot Users</div>
            </div>
        </div>
    </div>

    <!-- Devices Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>No</th>
                    <th>Device Name</th>
                    <th>Type</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th>Coordinates</th>
                    <th>Coverage</th>
                    <th>Subscriptions</th>
                    <th>Hotspot Users</th>
                </tr>
            </thead>
            <tbody>
                <?php $__empty_1 = true; $__currentLoopData = $devices; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $index => $device): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr>
                        <td><?php echo e($index + 1); ?></td>
                        <td><strong><?php echo e($device->name); ?></strong></td>
                        <td><?php echo e(ucfirst($device->device_type)); ?></td>
                        <td>
                            <span class="status-badge status-<?php echo e($device->status); ?>">
                                <?php echo e($device->status); ?>

                            </span>
                        </td>
                        <td><?php echo e($device->location ?: '-'); ?></td>
                        <td style="font-family: monospace;"><?php echo e($device->latitude); ?>, <?php echo e($device->longitude); ?></td>
                        <td><?php echo e($device->coverage_radius ? number_format($device->coverage_radius) . 'm' : '-'); ?></td>
                        <td><?php echo e($device->subscriptions_count); ?></td>
                        <td><?php echo e($device->hotspot_users_count); ?></td>
                    </tr>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr>
                        <td colspan="9" style="text-align: center; padding: 30px; color: #9CA3AF;">
                            No devices found with location data
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="footer">
        <p>QalcuityERP - Telecom Module | Network Maps Report</p>
        <p>This report was automatically generated on <?php echo e(now()->format('Y-m-d H:i:s')); ?></p>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\maps\pdf-export.blade.php ENDPATH**/ ?>