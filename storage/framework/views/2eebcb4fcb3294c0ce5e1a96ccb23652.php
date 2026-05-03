<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">

    <!-- Preconnect to CDN for faster loads -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <!-- DNS prefetch for external resources -->
    <link rel="dns-prefetch" href="//unpkg.com">
    <link rel="dns-prefetch" href="//cdn.jsdelivr.net">

    <!-- Manifest for PWA -->
    <link rel="manifest" href="/manifest.json">

    <!-- Theme color for mobile browsers -->
    <meta name="theme-color" content="#3B82F6">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">

    <title><?php echo $__env->yieldContent('title', config('app.name', 'Qalcuity ERP')); ?></title>

    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css']); ?>

    <!-- Critical CSS inline for faster FCP -->
    <style>
        /* Critical above-the-fold styles */
        body {
            font-family: system-ui, -apple-system, sans-serif;
        }

        .loader {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }
    </style>

    <?php echo $__env->yieldPushContent('styles'); ?>
</head>

<body class="font-sans antialiased bg-gray-50 text-gray-900">
    <!-- Loading indicator -->
    <div id="page-loader" class="loader fixed inset-0 bg-white z-50 transition-opacity duration-300">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
    </div>

    <!-- Main content -->
    <div id="app" class="min-h-screen">
        <?php echo $__env->yieldContent('content'); ?>
    </div>

    <!-- Lazy load non-critical JavaScript -->
    <script type="module">
        // Hide loader when page is ready
        window.addEventListener('load', () => {
            const loader = document.getElementById('page-loader');
            if (loader) {
                setTimeout(() => {
                    loader.classList.add('opacity-0');
                    setTimeout(() => loader.remove(), 300);
                }, 100);
            }
        });

        // Service Worker registration with update detection
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', {
                        scope: '/'
                    })
                    .then(registration => {
                        console.log('[SW] Registered:', registration.scope);

                        // Check for updates
                        registration.addEventListener('updatefound', () => {
                            const newWorker = registration.installing;
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker
                                    .controller) {
                                    // New version available
                                    console.log('[SW] Update available');
                                    // Optionally show refresh prompt
                                }
                            });
                        });
                    })
                    .catch(error => {
                        console.log('[SW] Registration failed:', error);
                    });
            });
        }
    </script>

    <!-- Defer non-critical scripts -->
    <?php echo app('Illuminate\Foundation\Vite')(['resources/js/app.js']); ?>

    <!-- Toast Notification Manager -->
    <script src="<?php echo e(asset('js/toast.js')); ?>" defer></script>

    <!-- Page-specific scripts (lazy loaded) -->
    <?php echo $__env->yieldPushContent('scripts'); ?>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\layouts\optimized.blade.php ENDPATH**/ ?>