<!DOCTYPE html>
<html lang="id" class="h-full">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title><?php echo e($title ?? config('app.name', 'Qalcuity ERP')); ?></title>
    <link rel="icon" type="image/png" href="/favicon.png">
    <link rel="shortcut icon" href="/favicon.png">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700,800&display=swap" rel="stylesheet" />

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body class="h-full font-[Inter,sans-serif] antialiased bg-white">

    <div class="min-h-full flex">

        
        <div
            class="hidden lg:flex lg:w-1/2 xl:w-3/5 flex-col justify-between p-12 relative overflow-hidden bg-[#0a0f1e]">
            
            <div class="absolute inset-0 bg-gradient-to-br from-[#0a0f1e] via-[#0f1f3d] to-[#0a0f1e]"></div>
            <div
                class="absolute top-0 right-0 w-[500px] h-[500px] bg-blue-600/15 rounded-full blur-[120px] -translate-y-1/3 translate-x-1/3">
            </div>
            <div
                class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-indigo-600/15 rounded-full blur-[100px] translate-y-1/3 -translate-x-1/3">
            </div>
            <div
                class="absolute top-1/2 left-1/2 w-[300px] h-[300px] bg-violet-600/10 rounded-full blur-[80px] -translate-x-1/2 -translate-y-1/2">
            </div>

            
            <div class="relative z-10">
                <div class="flex items-center gap-3">
                    <img src="/logo.png" alt="Qalcuity ERP" class="h-9 w-auto object-contain"
                        style="filter: brightness(0) invert(1);" loading="lazy">
                </div>
            </div>

            
            <div class="relative z-10 space-y-8">
                <div>
                    <h1 class="text-5xl font-extrabold text-white leading-tight tracking-tight">
                        ERP Cerdas<br>
                        <span
                            class="text-transparent bg-clip-text bg-gradient-to-r from-blue-400 via-indigo-400 to-violet-400">Berbasis
                            AI</span>
                    </h1>
                    <p class="mt-5 text-slate-300 text-lg leading-relaxed max-w-md">
                        Kelola bisnis Anda dengan bantuan AI. Inventory, penjualan, keuangan, dan SDM dalam satu
                        platform.
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-3 max-w-sm">
                    <?php $__currentLoopData = ['Inventory Real-time', 'AI Chat ERP', 'Laporan Otomatis', 'Multi-tenant SaaS']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="flex items-center gap-2.5 text-sm text-slate-200">
                            <div
                                class="w-5 h-5 rounded-full bg-blue-500/25 border border-blue-500/40 flex items-center justify-center shrink-0">
                                <svg class="w-3 h-3 text-blue-400" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <?php echo e($feat); ?>

                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="relative z-10 text-xs text-slate-600">
                © <?php echo e(date('Y')); ?> Qalcuity ERP. All rights reserved.
            </div>
        </div>

        
        <div class="flex-1 flex flex-col justify-center px-6 py-12 lg:px-12 bg-white">
            <div class="mx-auto w-full max-w-sm">
                
                <div class="lg:hidden flex items-center gap-2 mb-8">
                    <img src="/logo.png" alt="Qalcuity ERP" class="h-8 w-auto object-contain brightness-0"
                        loading="lazy">
                </div>

                <?php echo e($slot); ?>

            </div>
        </div>
    </div>

</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\layouts\guest.blade.php ENDPATH**/ ?>