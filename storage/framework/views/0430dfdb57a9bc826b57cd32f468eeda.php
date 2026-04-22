<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Barcode - <?php echo e($product->name); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            body {
                background: white !important;
            }

            .print-card {
                box-shadow: none !important;
                border: none !important;
            }
        }
    </style>
</head>

<body class="bg-gray-100 dark:bg-gray-900 min-h-screen flex items-center justify-center p-6">

    <div class="w-full max-w-sm">

        
        <div class="no-print flex items-center justify-between mb-4">
            <a href="<?php echo e(url()->previous()); ?>"
                class="inline-flex items-center gap-1.5 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <button onclick="window.print()"
                class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                </svg>
                Print
            </button>
        </div>

        
        <div
            class="print-card bg-white dark:bg-gray-800 rounded-2xl shadow-lg border border-gray-200 dark:border-gray-700 p-6 text-center">

            
            <h1 class="text-base font-bold text-gray-900 dark:text-white mb-1 leading-tight">
                <?php echo e($product->name); ?>

            </h1>

            
            <?php if($product->sku): ?>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-4">
                    SKU: <?php echo e($product->sku); ?>

                </p>
            <?php endif; ?>

            
            <div class="bg-white rounded-xl p-3 inline-block border border-gray-100">
                <img src="data:image/png;base64,<?php echo e(base64_encode($barcodeImage)); ?>" alt="<?php echo e($barcodeValue); ?>"
                    class="max-w-full h-auto" style="min-width: 200px;">
            </div>

            
            <p class="mt-3 text-xs font-mono tracking-widest text-gray-600 dark:text-gray-300">
                <?php echo e($barcodeValue); ?>

            </p>

            
            <?php if(!empty($product->price_sell)): ?>
                <p class="mt-3 text-xl font-bold text-gray-900 dark:text-white">
                    Rp <?php echo e(number_format($product->price_sell, 0, ',', '.')); ?>

                </p>
            <?php endif; ?>

            
            <div class="mt-3 flex items-center justify-center gap-3 text-xs text-gray-400 dark:text-gray-500">
                <?php if(!empty($product->category)): ?>
                    <span><?php echo e($product->category); ?></span>
                <?php endif; ?>
                <?php if(!empty($product->unit)): ?>
                    <span>/ <?php echo e($product->unit); ?></span>
                <?php endif; ?>
            </div>

        </div>

        
        <p class="no-print text-center text-xs text-gray-400 dark:text-gray-600 mt-4">
            Use Ctrl+P / Cmd+P to print, or click the Print button above.
        </p>

    </div>

</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\products\barcode-show.blade.php ENDPATH**/ ?>