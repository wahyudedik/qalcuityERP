<!DOCTYPE html>
<html lang="id" class="h-full dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $__env->yieldContent('title'); ?> — Qalcuity ERP</title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet"/>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="h-full bg-[#f8f8f8] text-gray-900 flex items-center justify-center p-6">
    <div class="w-full max-w-md text-center">
        <div class="mb-6">
            <div class="w-20 h-20 mx-auto rounded-2xl <?php echo $__env->yieldContent('icon-bg', 'bg-red-500/10'); ?> flex items-center justify-center mb-4">
                <span class="text-4xl"><?php echo $__env->yieldContent('icon', '⚠️'); ?></span>
            </div>
            <h1 class="text-6xl font-bold text-gray-200 mb-2"><?php echo $__env->yieldContent('code'); ?></h1>
            <h2 class="text-xl font-semibold text-gray-900"><?php echo $__env->yieldContent('heading'); ?></h2>
            <p class="text-sm text-gray-500 mt-2 leading-relaxed"><?php echo $__env->yieldContent('message'); ?></p>
        </div>

        <?php echo $__env->yieldContent('extra'); ?>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mt-6">
            <a href="<?php echo e(url('/dashboard')); ?>"
                class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition w-full sm:w-auto">
                Ke Dashboard
            </a>
            <button onclick="history.back()"
                class="px-5 py-2.5 border border-gray-200 text-gray-600 text-sm rounded-xl hover:bg-gray-50 transition w-full sm:w-auto">
                ← Kembali
            </button>
        </div>

        <p class="text-xs text-gray-300 mt-8">Qalcuity ERP</p>
    </div>
</body>
</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\layout.blade.php ENDPATH**/ ?>