<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Queue Display - <?php echo e(config('app.name')); ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap');

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .queue-item {
            animation: slideIn 0.5s ease-out;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }

            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        .pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        .now-calling {
            animation: blink 1s ease-in-out infinite;
        }

        @keyframes blink {

            0%,
            100% {
                background-color: rgb(59, 130, 246);
            }

            50% {
                background-color: rgb(37, 99, 235);
            }
        }
    </style>
</head>

<body class="min-h-screen text-white">
    
    <div class="bg-white/10 backdrop-blur-md border-b border-white/20 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="w-16 h-16 bg-white/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                        </path>
                    </svg>
                </div>
                <div>
                    <h1 class="text-3xl font-black"><?php echo e(config('app.name')); ?></h1>
                    <p class="text-lg text-white/80">Sistem Antrian Digital</p>
                </div>
            </div>
            <div class="text-right">
                <div id="clock" class="text-4xl font-black"><?php echo e(now()->format('H:i:s')); ?></div>
                <p class="text-lg text-white/80"><?php echo e(now()->format('d F Y')); ?></p>
            </div>
        </div>
    </div>

    
    <div class="p-8">
        
        <?php if($nowCalling ?? null): ?>
            <div class="now-calling rounded-3xl p-8 mb-8 shadow-2xl">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-6">
                        <div class="w-24 h-24 bg-white rounded-2xl flex items-center justify-center">
                            <svg class="w-16 h-16 text-blue-600 pulse" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z">
                                </path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-2xl text-white/90 mb-2">Sedang Dipanggil</p>
                            <p class="text-7xl font-black"><?php echo e($nowCalling['queue_number'] ?? '-'); ?></p>
                            <p class="text-2xl text-white/90 mt-2"><?php echo e($nowCalling['patient_name'] ?? '-'); ?></p>
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="text-xl text-white/80 mb-2">Loket / Ruangan</p>
                        <p class="text-5xl font-black"><?php echo e($nowCalling['counter'] ?? '-'); ?></p>
                        <p class="text-xl text-white/80 mt-2"><?php echo e($nowCalling['department'] ?? '-'); ?></p>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-3 gap-6">
            
            <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-amber-500 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Menunggu</h2>
                        <p class="text-sm text-white/70"><?php echo e(count($waitingQueue ?? [])); ?> antrian</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $waitingQueue ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="queue-item bg-white/10 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-3xl font-black"><?php echo e($queue['queue_number'] ?? '-'); ?></p>
                                    <p class="text-sm text-white/70 mt-1"><?php echo e($queue['patient_name'] ?? '-'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-white/60">Loket</p>
                                    <p class="text-xl font-bold"><?php echo e($queue['counter'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-12 text-white/50">
                            <p class="text-lg">Tidak ada antrian</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-blue-500 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Sedang Dilayani</h2>
                        <p class="text-sm text-white/70"><?php echo e(count($inProgress ?? [])); ?> pasien</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $inProgress ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="queue-item bg-blue-500/30 rounded-xl p-4 border-2 border-blue-400">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-3xl font-black"><?php echo e($queue['queue_number'] ?? '-'); ?></p>
                                    <p class="text-sm text-white/70 mt-1"><?php echo e($queue['patient_name'] ?? '-'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-white/60">Loket</p>
                                    <p class="text-xl font-bold"><?php echo e($queue['counter'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-12 text-white/50">
                            <p class="text-lg">Tidak ada pasien</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-12 h-12 bg-green-500 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-2xl font-bold">Selesai</h2>
                        <p class="text-sm text-white/70"><?php echo e(count($completedQueue ?? [])); ?> pasien</p>
                    </div>
                </div>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    <?php $__empty_1 = true; $__currentLoopData = $completedQueue ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $queue): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="queue-item bg-green-500/20 rounded-xl p-4">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-2xl font-bold text-white/80"><?php echo e($queue['queue_number'] ?? '-'); ?>

                                    </p>
                                    <p class="text-sm text-white/60 mt-1"><?php echo e($queue['patient_name'] ?? '-'); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-white/50">Selesai</p>
                                    <p class="text-sm text-white/70"><?php echo e($queue['completed_at'] ?? '-'); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <div class="text-center py-12 text-white/50">
                            <p class="text-lg">Belum ada yang selesai</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        
        <div class="mt-8 bg-white/10 backdrop-blur-md rounded-3xl p-6 border border-white/20">
            <div class="grid grid-cols-4 gap-6 text-center">
                <div>
                    <p class="text-5xl font-black text-amber-400"><?php echo e($stats['total_waiting'] ?? 0); ?></p>
                    <p class="text-lg text-white/70 mt-2">Total Menunggu</p>
                </div>
                <div>
                    <p class="text-5xl font-black text-blue-400"><?php echo e($stats['total_in_progress'] ?? 0); ?></p>
                    <p class="text-lg text-white/70 mt-2">Sedang Dilayani</p>
                </div>
                <div>
                    <p class="text-5xl font-black text-green-400"><?php echo e($stats['total_completed'] ?? 0); ?></p>
                    <p class="text-lg text-white/70 mt-2">Selesai</p>
                </div>
                <div>
                    <p class="text-5xl font-black text-purple-400"><?php echo e($stats['avg_wait_time'] ?? 0); ?> min</p>
                    <p class="text-lg text-white/70 mt-2">Rata-rata Tunggu</p>
                </div>
            </div>
        </div>
    </div>

    
    <script>
        // Update clock every second
        function updateClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', {
                hour12: false
            });
            document.getElementById('clock').textContent = timeString;
        }
        setInterval(updateClock, 1000);

        // Auto refresh page every 10 seconds
        setTimeout(function() {
            window.location.reload();
        }, 10000);

        // Audio notification for new call
        function playNotificationSound() {
            const audio = new Audio('/sounds/queue-call.mp3');
            audio.play().catch(e => console.log('Audio playback failed:', e));
        }

        // Check for new calls (implement based on your needs)
        let lastQueueNumber = '<?php echo e($nowCalling['queue_number'] ?? ''); ?>';

        // You can implement WebSocket or polling here for real-time updates
    </script>
</body>

</html>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\queue\display.blade.php ENDPATH**/ ?>