<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #1f2937; }
        .header { background: #1d4ed8; color: white; padding: 20px 24px; margin-bottom: 20px; }
        .header h1 { font-size: 18px; font-weight: bold; }
        .header p  { font-size: 11px; opacity: 0.8; margin-top: 4px; }
        .meta { padding: 0 24px 16px; display: flex; gap: 24px; }
        .meta-item label { font-size: 10px; color: #6b7280; text-transform: uppercase; }
        .meta-item span  { font-size: 12px; font-weight: 600; color: #111827; display: block; }
        .summary { margin: 0 24px 20px; display: flex; gap: 12px; }
        .summary-card { flex: 1; background: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; }
        .summary-card .label { font-size: 10px; color: #6b7280; }
        .summary-card .value { font-size: 15px; font-weight: bold; color: #1d4ed8; margin-top: 2px; }
        table { width: 100%; border-collapse: collapse; margin: 0 24px; width: calc(100% - 48px); }
        thead tr { background: #dbeafe; }
        th { padding: 8px 10px; text-align: left; font-size: 10px; text-transform: uppercase; color: #1e40af; font-weight: 600; }
        td { padding: 7px 10px; border-bottom: 1px solid #f3f4f6; font-size: 11px; }
        tr:nth-child(even) td { background: #f9fafb; }
        .footer { margin-top: 24px; padding: 12px 24px; border-top: 1px solid #e5e7eb; text-align: center; font-size: 10px; color: #9ca3af; }
        .badge { display: inline-block; padding: 2px 8px; border-radius: 9999px; font-size: 10px; font-weight: 600; }
        .badge-green  { background: #d1fae5; color: #065f46; }
        .badge-red    { background: #fee2e2; color: #991b1b; }
        .badge-yellow { background: #fef3c7; color: #92400e; }
    </style>
</head>
<body>
    <div class="header">
        <h1><?php echo e($title); ?></h1>
        <p><?php echo e($tenant_name); ?> &nbsp;·&nbsp; Periode: <?php echo e($period); ?></p>
    </div>

    <?php if(!empty($summary)): ?>
    <div class="summary">
        <?php $__currentLoopData = $summary; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="summary-card">
            <div class="label"><?php echo e($item['label']); ?></div>
            <div class="value"><?php echo e($item['value']); ?></div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    <table>
        <thead>
            <tr>
                <?php $__currentLoopData = $headers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $h): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <th><?php echo e($h); ?></th>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
        </thead>
        <tbody>
            <?php $__empty_1 = true; $__currentLoopData = $rows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <tr>
                <?php $__currentLoopData = $row; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <td><?php echo e($cell); ?></td>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </tr>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <tr><td colspan="<?php echo e(count($headers)); ?>" style="text-align:center;color:#9ca3af;padding:20px">Tidak ada data</td></tr>
            <?php endif; ?>
        </tbody>
    </table>

    <div class="footer">
        Dicetak pada <?php echo e(now()->format('d M Y H:i')); ?> &nbsp;·&nbsp; Qalcuity ERP
    </div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\reports\pdf.blade.php ENDPATH**/ ?>