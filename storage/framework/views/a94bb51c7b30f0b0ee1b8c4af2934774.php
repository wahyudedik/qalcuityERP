

<?php $__env->startSection('title', 'Status Fingerprint Karyawan'); ?>

<?php $__env->startSection('content'); ?>
    <div class="container mx-auto px-4 py-6">
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 dark:text-white">Status Fingerprint Karyawan</h1>
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">Kelola registrasi fingerprint untuk semua karyawan</p>
        </div>

        <div class="bg-white dark:bg-gray-800 rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-700">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Karyawan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Jabatan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Departemen</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">
                            Status Fingerprint</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-300 uppercase">Aksi
                        </th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $employee): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700">
                            <td class="px-6 py-4">
                                <div class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($employee->name); ?></div>
                                <div class="text-sm text-gray-500 dark:text-gray-400"><?php echo e($employee->employee_id); ?></div>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?php echo e($employee->position ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900 dark:text-white"><?php echo e($employee->department ?? '-'); ?>

                            </td>
                            <td class="px-6 py-4">
                                <?php if($employee->fingerprint_registered): ?>
                                    <span class="px-2 py-1 bg-green-100 text-green-800 rounded-full text-xs font-semibold">
                                        ✓ Terdaftar
                                    </span>
                                    <div class="text-xs text-gray-500 mt-1"><?php echo e($employee->fingerprint_uid); ?></div>
                                <?php else: ?>
                                    <span
                                        class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded-full text-xs font-semibold">
                                        Belum Terdaftar
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 text-right text-sm font-medium">
                                <a href="<?php echo e(route('hrm.fingerprint.employees.register', $employee)); ?>"
                                    class="text-blue-600 hover:text-blue-900 dark:text-blue-400">
                                    <?php echo e($employee->fingerprint_registered ? 'Kelola' : 'Daftarkan'); ?>

                                </a>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500 dark:text-gray-400">
                                Tidak ada data karyawan
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if($employees->hasPages()): ?>
            <div class="mt-4">
                <?php echo e($employees->links()); ?>

            </div>
        <?php endif; ?>
    </div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\fingerprint\employees\index.blade.php ENDPATH**/ ?>