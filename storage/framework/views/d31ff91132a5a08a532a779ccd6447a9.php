<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>QR Labels - Thermal</title>
    <style>
        @page {
            size: 50mm 25mm;
            margin: 0;
        }
        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }
        .label {
            width: 48mm;
            height: 23mm;
            padding: 1mm;
            text-align: center;
            page-break-inside: avoid;
            border: 0.5pt solid #ccc;
            display: flex;
            flex-direction: row;
            align-items: center;
        }
        .qr-img {
            width: 18mm;
            height: 18mm;
            flex-shrink: 0;
        }
        .info {
            flex: 1;
            padding-left: 1mm;
            text-align: left;
            overflow: hidden;
        }
        .product-name {
            font-size: 7pt;
            font-weight: bold;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .sku {
            font-size: 6pt;
            font-family: 'Courier New', monospace;
            color: #333;
            margin-top: 1px;
        }
        .cert-number {
            font-size: 5pt;
            color: #555;
            margin-top: 1px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>
<body>
    <?php $__currentLoopData = $labels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($item): ?>
            <div class="label">
                <?php if($item['qr_image']): ?>
                    <img src="data:image/png;base64,<?php echo e($item['qr_image']); ?>" class="qr-img" alt="QR">
                <?php endif; ?>
                <div class="info">
                    <div class="product-name"><?php echo e($item['product']->name); ?></div>
                    <div class="sku"><?php echo e($item['product']->sku); ?></div>
                    <?php if($item['certificate_number']): ?>
                        <div class="cert-number"><?php echo e($item['certificate_number']); ?></div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\products\labels\qr-thermal.blade.php ENDPATH**/ ?>