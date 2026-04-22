<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Barcode Labels - Thermal</title>
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
        }

        .barcode-img {
            max-width: 100%;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .sku {
            font-size: 7pt;
            font-family: 'Courier New', monospace;
            margin-top: 2px;
            color: #333;
        }

        .product-name {
            font-size: 8pt;
            font-weight: bold;
            margin-bottom: 2px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
    </style>
</head>

<body>
    <?php $__currentLoopData = $barcodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php if($item): ?>
            <div class="label">
                <div class="product-name"><?php echo e($item['product']->name); ?></div>

                <img src="data:image/png;base64,<?php echo e($item['image']); ?>" class="barcode-img" alt="<?php echo e($item['value']); ?>">

                <div class="sku"><?php echo e($item['value']); ?></div>
            </div>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\products\labels\thermal.blade.php ENDPATH**/ ?>