<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Halaman Tidak Ditemukan | <?php echo e(config('app.name')); ?></title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
</head>

<body
    class="bg-gradient-to-br from-blue-50 to-indigo-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-2xl w-full">
        <!-- Error Icon -->
        <div class="text-center mb-8">
            <div
                class="inline-flex items-center justify-center w-32 h-32 bg-blue-100 rounded-full mb-6">
                <svg class="w-20 h-20 text-blue-600" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>

            <h1 class="text-6xl font-bold text-blue-600 mb-4">404</h1>
            <h2 class="text-3xl font-semibold text-gray-900 mb-4">
                Halaman Tidak Ditemukan
            </h2>
            <p class="text-lg text-gray-600 mb-6">
                Halaman yang Anda cari tidak ada atau telah dipindahkan.
            </p>
        </div>

        <!-- Quick Navigation -->
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="font-semibold text-gray-900 mb-4">Navigasi Cepat</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                <a href="<?php echo e(url('/')); ?>"
                    class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                    </svg>
                    <span class="text-gray-700">Beranda</span>
                </a>

                <?php if(auth()->guard()->check()): ?>
                    <a href="<?php echo e(route('dashboard') ?? '/dashboard'); ?>"
                        class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                        </svg>
                        <span class="text-gray-700">Dashboard</span>
                    </a>
                <?php else: ?>
                    <a href="<?php echo e(route('login') ?? '/login'); ?>"
                        class="flex items-center gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-lg transition-colors group">
                        <svg class="w-5 h-5 text-gray-400 group-hover:text-blue-500" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                        </svg>
                        <span class="text-gray-700">Login</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-col sm:flex-row gap-4 justify-center">
            <a href="<?php echo e(url('/')); ?>"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors min-h-[44px] min-w-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                </svg>
                Kembali ke Beranda
            </a>

            <button onclick="history.back()"
                class="inline-flex items-center justify-center gap-2 px-6 py-3 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded-lg transition-colors min-h-[44px] min-w-[44px]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                </svg>
                Kembali
            </button>
        </div>
    </div>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\errors\404.blade.php ENDPATH**/ ?>