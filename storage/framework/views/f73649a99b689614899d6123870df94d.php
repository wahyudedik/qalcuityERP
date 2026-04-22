<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voucher Print</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Arial, sans-serif;
            background: #f5f5f5;
        }

        .page {
            width: 210mm;
            min-height: 297mm;
            padding: 10mm;
            margin: 0 auto;
            background: white;
        }

        .header {
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid #333;
        }

        .header h1 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }

        .header p {
            font-size: 10px;
            color: #666;
        }

        .vouchers-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
        }

        .voucher-card {
            border: 2px dashed #333;
            padding: 15px;
            page-break-inside: avoid;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 8px;
        }

        .voucher-header {
            text-align: center;
            margin-bottom: 10px;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
        }

        .voucher-header h2 {
            font-size: 14px;
            margin-bottom: 3px;
        }

        .voucher-header p {
            font-size: 9px;
            opacity: 0.9;
        }

        .voucher-code {
            text-align: center;
            background: rgba(255, 255, 255, 0.2);
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 2px;
        }

        .voucher-details {
            font-size: 9px;
            line-height: 1.6;
        }

        .voucher-details .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 3px;
        }

        .voucher-details .label {
            opacity: 0.8;
        }

        .voucher-details .value {
            font-weight: bold;
        }

        .voucher-footer {
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid rgba(255, 255, 255, 0.3);
            text-align: center;
            font-size: 8px;
            opacity: 0.8;
        }

        @media print {
            body {
                background: white;
            }

            .page {
                padding: 5mm;
            }

            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="no-print" style="text-align: center; padding: 20px; background: #333; color: white;">
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 14px; cursor: pointer;">
            🖨️ Print Vouchers
        </button>
        <p style="margin-top: 10px; font-size: 12px;"><?php echo e(count($vouchers)); ?> vouchers ready to print</p>
    </div>

    <div class="page">
        <div class="header">
            <h1>INTERNET VOUCHER</h1>
            <p><?php echo e(config('app.name')); ?> - Generated on <?php echo e(now()->format('d M Y H:i:s')); ?></p>
        </div>

        <div class="vouchers-grid">
            <?php $__currentLoopData = $vouchers; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $voucher): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="voucher-card">
                    <div class="voucher-header">
                        <h2><?php echo e($voucher->package?->name ?? 'Paket Internet'); ?></h2>
                        <p><?php echo e($voucher->package?->download_speed_mbps ?? 0); ?>/<?php echo e($voucher->package?->upload_speed_mbps ?? 0); ?> Mbps
                        </p>
                    </div>

                    <div class="voucher-code">
                        <?php echo e($voucher->code); ?>

                    </div>

                    <div class="voucher-details">
                        <div class="detail-row">
                            <span class="label">Valid From:</span>
                            <span class="value"><?php echo e($voucher->valid_from->format('d M Y')); ?></span>
                        </div>
                        <div class="detail-row">
                            <span class="label">Valid Until:</span>
                            <span class="value"><?php echo e($voucher->valid_until->format('d M Y H:i')); ?></span>
                        </div>
                        <?php if($voucher->package?->quota_bytes): ?>
                            <div class="detail-row">
                                <span class="label">Quota:</span>
                                <span class="value"><?php echo e(round($voucher->package->quota_bytes / 1073741824, 2)); ?>

                                    GB</span>
                            </div>
                        <?php else: ?>
                            <div class="detail-row">
                                <span class="label">Quota:</span>
                                <span class="value">Unlimited</span>
                            </div>
                        <?php endif; ?>
                        <?php if($voucher->sale_price): ?>
                            <div class="detail-row">
                                <span class="label">Price:</span>
                                <span class="value">Rp <?php echo e(number_format($voucher->sale_price, 0, ',', '.')); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="detail-row">
                            <span class="label">Batch:</span>
                            <span class="value"><?php echo e($voucher->batch_number ?? '-'); ?></span>
                        </div>
                    </div>

                    <div class="voucher-footer">
                        <p>Scan QR code or enter code above to connect</p>
                        <p style="margin-top: 5px;"><?php echo e(config('app.name')); ?></p>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\telecom\vouchers\print.blade.php ENDPATH**/ ?>