<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Barcode - <?php echo e($asset->asset_code); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
</head>

<body class="bg-gray-50 dark:bg-[#0f172a] min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl shadow-xl border border-gray-200 dark:border-white/10 p-6">
            
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($asset->name); ?></h1>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                        <?php echo e(ucfirst($asset->category)); ?> &bull; <?php echo e($asset->asset_code); ?>

                    </p>
                </div>
                <?php $sc = ['active'=>'green','maintenance'=>'amber','disposed'=>'red','retired'=>'gray'][$asset->status] ?? 'gray'; ?>
                <span
                    class="px-2 py-0.5 rounded-full text-xs bg-<?php echo e($sc); ?>-100 text-<?php echo e($sc); ?>-700 dark:bg-<?php echo e($sc); ?>-500/20 dark:text-<?php echo e($sc); ?>-400">
                    <?php echo e(ucfirst($asset->status)); ?>

                </span>
            </div>

            
            <div class="bg-white dark:bg-white/5 rounded-xl p-6 text-center mb-5">
                <img src="data:image/png;base64,<?php echo e(base64_encode($barcodeImage)); ?>" alt="<?php echo e($asset->asset_code); ?>"
                    class="mx-auto max-w-full h-auto" style="max-height: 120px;">
                <p class="mt-3 text-sm font-mono tracking-widest text-gray-700 dark:text-slate-300">
                    <?php echo e($asset->asset_code); ?>

                </p>
            </div>

            
            <div class="space-y-2 text-sm mb-6">
                <?php if($asset->serial_number): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Serial Number</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($asset->serial_number); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($asset->brand): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Brand</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($asset->brand); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($asset->model): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Model</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($asset->model); ?></span>
                    </div>
                <?php endif; ?>
                <?php if($asset->location): ?>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Location</span>
                        <span class="font-medium text-gray-900 dark:text-white"><?php echo e($asset->location); ?></span>
                    </div>
                <?php endif; ?>
                <div class="flex justify-between pt-2 border-t border-gray-100 dark:border-white/10 mt-3">
                    <span class="text-gray-500 dark:text-slate-400">Purchase Date</span>
                    <span class="font-medium text-gray-900 dark:text-white">
                        <?php echo e($asset->purchase_date?->format('d M Y') ?? '-'); ?>

                    </span>
                </div>
            </div>

            
            <div class="flex gap-2">
                <button onclick="window.print()"
                    class="flex-1 px-4 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                    <svg class="w-4 h-4 inline mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z" />
                    </svg>
                    Print Label
                </button>
                <a href="<?php echo e(route('assets.index')); ?>"
                    class="px-4 py-2.5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 text-sm font-semibold rounded-xl hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    Back
                </a>
            </div>
        </div>

        
        <style media="print">
            @page {
                margin: 15mm;
                size: A4;
            }

            body {
                background: white !important;
            }

            .shadow-xl {
                box-shadow: none !important;
            }

            button,
            a[href] {
                display: none !important;
            }
        </style>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\assets\barcode-show.blade.php ENDPATH**/ ?>