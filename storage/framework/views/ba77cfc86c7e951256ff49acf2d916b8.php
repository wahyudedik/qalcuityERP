<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
     <?php $__env->slot('header', null, []); ?> Daftar Pasien Rawat Inap <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Rawat Inap</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($statistics['total_active'] ?? 0)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sedang Rawat</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($statistics['total_active'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Masuk Hari Ini</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($statistics['today_admissions'] ?? 0); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Pulang Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo e($statistics['today_discharges'] ?? 0); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                    placeholder="Cari pasien / No. RM..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="admitted" <?php if(request('status') === 'admitted'): echo 'selected'; endif; ?>>Sedang Rawat</option>
                    <option value="discharged" <?php if(request('status') === 'discharged'): echo 'selected'; endif; ?>>Sudah Pulang</option>
                    <option value="transferred" <?php if(request('status') === 'transferred'): echo 'selected'; endif; ?>>Dipindahkan</option>
                    <option value="against_medical_advice" <?php if(request('status') === 'against_medical_advice'): echo 'selected'; endif; ?>>Pulang Paksa</option>
                </select>
                <select name="ward"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Ruang</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('healthcare.inpatient.admissions.create')); ?>"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Rawat Inap</a>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Ruang / Bed</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter Penanggung Jawab</th>
                        <th class="px-4 py-3 text-left">Tanggal Masuk</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $admissions ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $admission): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl bg-blue-100 flex items-center justify-center shrink-0">
                                        <svg class="w-4 h-4 text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900">
                                            <?php echo e($admission->patient ? $admission->patient->full_name : '-'); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($admission->patient ? $admission->patient->medical_record_number : '-'); ?>

                                        </p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($admission->bed?->ward?->name ?? '-'); ?></p>
                                <p class="text-xs text-gray-500">Bed
                                    <?php echo e($admission->bed?->bed_number ?? '-'); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($admission->doctor ? $admission->doctor->name : '-'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($admission->doctor ? $admission->doctor->specialization : ''); ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">
                                    <?php echo e($admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('d M Y') : '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($admission->admission_date ? \Carbon\Carbon::parse($admission->admission_date)->format('H:i') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <?php if($admission->status === 'active'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Sedang
                                        Rawat</span>
                                <?php elseif($admission->status === 'discharged'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Sudah
                                        Pulang</span>
                                <?php elseif($admission->status === 'transferred'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">Dipindahkan</span>
                                <?php elseif($admission->status === 'ama'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Pulang
                                        Paksa</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700"><?php echo e(ucfirst($admission->status ?? '-')); ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.inpatient.admissions.show', $admission)); ?>"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                            </path>
                                        </svg>
                                    </a>
                                    <?php if($admission->status === 'active'): ?>
                                        <a href="<?php echo e(route('healthcare.inpatient.admissions.show', $admission)); ?>"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Proses Pulang">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                            </svg>
                                        </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <svg class="w-12 h-12 mx-auto mb-3 text-gray-300" fill="none"
                                    stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                                    </path>
                                </svg>
                                <p>Belum ada data rawat inap</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(isset($admissions) && $admissions->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($admissions->links()); ?>

            </div>
        <?php endif; ?>
    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\inpatient\admissions\index.blade.php ENDPATH**/ ?>