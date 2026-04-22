<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Barcode Labels - Avery A4</title>
    <style>
        @page {
            size: A4;
            margin: 10mm 7mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            font-family: Arial, sans-serif;
        }

        .sheet {
            width: 210mm;
        }

        .labels-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 0;
        }

        /* Avery L7160 / similar: 3 columns x 7 rows = 21 labels per sheet */
        /* Each label: 63.5mm wide x 38.1mm tall */
        .label {
            width: 63.5mm;
            height: 38.1mm;
            padding: 2mm 3mm;
            border: 0.5pt dashed #ccc;
            page-break-inside: avoid;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .label.empty {
            /* empty filler label - no border */
            border-color: transparent;
        }

        .product-name {
            font-size: 7pt;
            font-weight: bold;
            text-align: center;
            margin-bottom: 1mm;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            line-height: 1.2;
        }

        .barcode-img {
            max-width: 56mm;
            height: auto;
            display: block;
            margin: 0 auto;
        }

        .barcode-value {
            font-size: 6pt;
            font-family: 'Courier New', monospace;
            text-align: center;
            margin-top: 1mm;
            color: #333;
            letter-spacing: 0.5px;
        }

        .price {
            font-size: 8pt;
            font-weight: bold;
            text-align: center;
            margin-top: 1mm;
            color: #000;
        }
    </style>
</head>

<body>
    <div class="sheet">
        <div class="labels-grid">
            <?php $__currentLoopData = $barcodes; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php if($item): ?>
                    <div class="label">
                        <div class="product-name" title="<?php echo e($item['product']->name); ?>">
                            <?php echo e($item['product']->name); ?>

                        </div>
                        <img src="data:image/png;base64,<?php echo e($item['image']); ?>" class="barcode-img"
                            alt="<?php echo e($item['value']); ?>">
                        <div class="barcode-value"><?php echo e($item['value']); ?></div>
                        <?php if(!empty($item['product']->price_sell)): ?>
                            <div class="price">
                                Rp <?php echo e(number_format($item['product']->price_sell, 0, ',', '.')); ?>

                            </div>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div class="label empty"></div>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\products\labels\avery.blade.php ENDPATH**/ ?>