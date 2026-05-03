

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-900">Catatan Vaksinasi</h1>
            <p class="mt-2 text-gray-600">Kelola jadwal dan riwayat vaksinasi ternak</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Total Vaksinasi</div>
                <div class="mt-2 text-3xl font-bold text-gray-900">
                    <?php echo e(number_format($stats['total_vaccinations'])); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Akan Datang</div>
                <div class="mt-2 text-3xl font-bold text-blue-600">
                    <?php echo e(number_format($stats['upcoming_vaccinations'])); ?></div>
            </div>
            <div class="bg-white rounded-lg shadow p-6">
                <div class="text-sm font-medium text-gray-500">Selesai</div>
                <div class="mt-2 text-3xl font-bold text-green-600">
                    <?php echo e(number_format($stats['completed_vaccinations'])); ?></div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center">
                <h3 class="text-lg font-semibold text-gray-900">Jadwal Vaksinasi</h3>
                <button type="button" onclick="document.getElementById('addVaccinationModal').classList.remove('hidden')"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Tambah Vaksinasi
                </button>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tanggal Jadwal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Kelompok Ternak</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Vaksin</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Metode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                No. Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php $__empty_1 = true; $__currentLoopData = $vaccinations; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $vaccination): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr
                                class="hover:bg-gray-50 <?php if($vaccination->isOverdue()): ?> bg-red-50 <?php endif; ?>">
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($vaccination->scheduled_date?->format('d M Y') ?? '-'); ?>

                                    <?php if($vaccination->isOverdue()): ?>
                                        <span class="ml-2 text-xs text-red-600">Terlambat</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($vaccination->herd?->name ?? 'N/A'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <?php echo e($vaccination->vaccine_name); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($vaccination->dose_method ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600">
                                    <?php echo e($vaccination->batch_number ?? '-'); ?>

                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span
                                        class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        <?php if($vaccination->status === 'scheduled'): ?> bg-blue-100 text-blue-800
                                        <?php elseif($vaccination->status === 'completed'): ?> bg-green-100 text-green-800
                                        <?php else: ?> bg-red-100 text-red-800 <?php endif; ?>">
                                        <?php echo e($vaccination->status === 'scheduled' ? 'Terjadwal' : ($vaccination->status === 'completed' ? 'Selesai' : ucfirst($vaccination->status))); ?>

                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada catatan vaksinasi
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200">
                <?php echo e($vaccinations->links()); ?>

            </div>
        </div>
    </div>

    <!-- Add Vaccination Modal -->
    <div id="addVaccinationModal"
        class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div
            class="relative top-20 mx-auto p-5 border w-full max-w-lg shadow-lg rounded-lg bg-white">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-gray-900">Tambah Catatan Vaksinasi</h3>
                <button type="button" onclick="document.getElementById('addVaccinationModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12">
                        </path>
                    </svg>
                </button>
            </div>
            <form action="<?php echo e(route('livestock-enhancement.health.vaccinations.store')); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Kelompok
                            Ternak</label>
                        <select name="livestock_herd_id" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="">Pilih kelompok ternak</option>
                            <?php $__currentLoopData = $herds; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $herd): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($herd->id); ?>"><?php echo e($herd->code); ?> - <?php echo e($herd->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Vaksin</label>
                        <input type="text" name="vaccine_name" required
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Contoh: ND-IB, Gumboro, dll">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal
                                Jadwal</label>
                            <input type="date" name="scheduled_date" value="<?php echo e(date('Y-m-d')); ?>" required
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Metode
                                Pemberian</label>
                            <select name="dose_method"
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Pilih metode</option>
                                <option value="tetes mata">Tetes Mata</option>
                                <option value="air minum">Air Minum</option>
                                <option value="suntik">Suntik</option>
                                <option value="tusuk sayap">Tusuk Sayap</option>
                                <option value="spray">Spray</option>
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">No. Batch</label>
                            <input type="text" name="batch_number"
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                                placeholder="Nomor batch vaksin">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Biaya
                                (Rp)</label>
                            <input type="number" name="cost" min="0" step="1000"
                                class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status"
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500">
                            <option value="scheduled">Terjadwal</option>
                            <option value="completed">Selesai</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Catatan</label>
                        <textarea name="notes" rows="2"
                            class="w-full rounded-lg border-gray-300 focus:ring-blue-500 focus:border-blue-500"
                            placeholder="Catatan tambahan"></textarea>
                    </div>
                </div>
                <div class="mt-6 flex justify-end space-x-3">
                    <button type="button"
                        onclick="document.getElementById('addVaccinationModal').classList.add('hidden')"
                        class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition-colors">
                        Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\livestock\health\vaccinations.blade.php ENDPATH**/ ?>