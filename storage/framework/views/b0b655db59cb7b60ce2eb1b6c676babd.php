

<?php $__env->startSection('title', 'Manajemen Meja'); ?>

<?php $__env->startSection('content'); ?>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Header -->
        <div class="mb-6 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <div>
                <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100">Manajemen Meja</h1>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">Kelola meja restoran dan reservasi</p>
            </div>
            <button onclick="document.getElementById('reservationModal').classList.remove('hidden')"
                class="inline-flex items-center bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-medium transition-colors min-h-[44px]">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Reservasi Baru
            </button>
        </div>

        <?php if(session('success')): ?>
            <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/20 border-l-4 border-green-500 rounded text-green-700 dark:text-green-300">
                <?php echo e(session('success')); ?>

            </div>
        <?php endif; ?>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/20 border-l-4 border-red-500 rounded">
                <ul class="list-disc list-inside text-sm text-red-700 dark:text-red-300">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($error); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Stats Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border border-gray-200 dark:border-gray-700">
                <div class="text-sm text-gray-500 dark:text-gray-400">Total Meja</div>
                <div class="text-2xl font-bold text-gray-900 dark:text-gray-100"><?php echo e($stats['total_tables']); ?></div>
            </div>
            <div class="bg-green-50 dark:bg-green-900/20 rounded-lg shadow p-4 border-l-4 border-green-500">
                <div class="text-sm text-green-600 dark:text-green-400">Tersedia</div>
                <div class="text-2xl font-bold text-green-700 dark:text-green-300"><?php echo e($stats['available']); ?></div>
            </div>
            <div class="bg-red-50 dark:bg-red-900/20 rounded-lg shadow p-4 border-l-4 border-red-500">
                <div class="text-sm text-red-600 dark:text-red-400">Terisi</div>
                <div class="text-2xl font-bold text-red-700 dark:text-red-300"><?php echo e($stats['occupied']); ?></div>
            </div>
            <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg shadow p-4 border-l-4 border-blue-500">
                <div class="text-sm text-blue-600 dark:text-blue-400">Reservasi Hari Ini</div>
                <div class="text-2xl font-bold text-blue-700 dark:text-blue-300"><?php echo e($stats['today_reservations']); ?></div>
            </div>
        </div>

        <!-- Tables Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <?php $__empty_1 = true; $__currentLoopData = $tables; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <div
                    class="bg-white dark:bg-gray-800 rounded-lg shadow p-4 border-2 transition-colors
                        <?php echo e($table->status === 'available' ? 'border-green-400 dark:border-green-600' : ($table->status === 'occupied' ? 'border-red-400 dark:border-red-600' : 'border-yellow-400 dark:border-yellow-600')); ?>">
                    <div class="flex justify-between items-start mb-2">
                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">Meja <?php echo e($table->table_number); ?></div>
                        <span
                            class="px-2 py-1 text-xs rounded-full
                                <?php echo e($table->status === 'available' ? 'bg-green-100 dark:bg-green-900/40 text-green-800 dark:text-green-300' : ($table->status === 'occupied' ? 'bg-red-100 dark:bg-red-900/40 text-red-800 dark:text-red-300' : 'bg-yellow-100 dark:bg-yellow-900/40 text-yellow-800 dark:text-yellow-300')); ?>">
                            <?php switch($table->status):
                                case ('available'): ?> Tersedia <?php break; ?>
                                <?php case ('occupied'): ?> Terisi <?php break; ?>
                                <?php case ('reserved'): ?> Dipesan <?php break; ?>
                                <?php default: ?> <?php echo e(ucfirst($table->status)); ?>

                            <?php endswitch; ?>
                        </span>
                    </div>
                    <div class="text-sm text-gray-600 dark:text-gray-400 mb-2">Kapasitas: <?php echo e($table->capacity); ?> orang</div>
                    <div class="text-xs text-gray-500 dark:text-gray-500"><?php echo e($table->location ?? 'Area Utama'); ?></div>

                    <?php if($table->reservations->isNotEmpty()): ?>
                        <div class="mt-2 pt-2 border-t border-gray-200 dark:border-gray-700">
                            <div class="text-xs font-semibold text-blue-600 dark:text-blue-400 truncate">
                                <?php echo e($table->reservations->first()->customer_name); ?> —
                                <?php echo e(\Carbon\Carbon::parse($table->reservations->first()->reservation_time)->format('H:i')); ?>

                            </div>
                        </div>
                    <?php endif; ?>

                    <a href="<?php echo e(route('fnb.tables.reservations', $table)); ?>"
                        class="mt-2 block text-center text-xs text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 transition-colors">
                        Lihat Reservasi →
                    </a>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <div class="col-span-full text-center py-12 text-gray-500 dark:text-gray-400">
                    <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18M10 3v18M14 3v18" />
                    </svg>
                    <p>Belum ada meja terdaftar</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- New Reservation Modal -->
    <div id="reservationModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white dark:bg-gray-800 rounded-lg p-6 max-w-md w-full mx-4 max-h-screen overflow-y-auto">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">Reservasi Baru</h2>
                <button type="button" onclick="document.getElementById('reservationModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('fnb.tables.reservations.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Meja</label>
                        <select name="table_id" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                            <option value="">-- Pilih Meja --</option>
                            <?php $__currentLoopData = $tables->where('status', 'available'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $table): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($table->id); ?>">Meja <?php echo e($table->table_number); ?>

                                    (<?php echo e($table->capacity); ?> orang)</option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Pelanggan</label>
                        <input type="text" name="customer_name" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Nama lengkap pelanggan">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Telepon</label>
                        <input type="tel" name="customer_phone" required
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="08xxxxxxxxxx">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Tanggal</label>
                            <input type="date" name="reservation_date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jam</label>
                            <input type="time" name="reservation_time" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Jumlah Tamu</label>
                            <input type="number" name="party_size" required min="1"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                                placeholder="1">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Durasi (menit)</label>
                            <input type="number" name="duration_minutes" required value="120" min="30"
                                max="300"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Permintaan Khusus</label>
                        <textarea name="special_requests" rows="2"
                            class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: kursi roda, ulang tahun, dll."></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button" onclick="document.getElementById('reservationModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 dark:border-gray-600 rounded-lg text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors min-h-[44px]">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors min-h-[44px]">
                        Buat Reservasi
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Close modal on backdrop click
        document.getElementById('reservationModal').addEventListener('click', function(e) {
            if (e.target === this) this.classList.add('hidden');
        });
        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                document.getElementById('reservationModal').classList.add('hidden');
            }
        });
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\fnb\tables\index.blade.php ENDPATH**/ ?>