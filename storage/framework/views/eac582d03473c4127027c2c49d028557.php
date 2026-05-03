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
     <?php $__env->slot('header', null, []); ?> Kunjungan Rawat Jalan <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <?php
            $totalVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)->count();
            $todayVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->whereDate('visit_date', today())
                ->count();
            $activeVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->where('status', 'in_progress')
                ->count();
            $completedVisits = \App\Models\OutpatientVisit::where('tenant_id', $tid)
                ->where('status', 'completed')
                ->count();
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Kunjungan</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e(number_format($totalVisits)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Kunjungan Hari Ini</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e($todayVisits); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sedang Berlangsung</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($activeVisits); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Selesai</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($completedVisits); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 mb-4">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 p-4">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
                <input type="text" name="search" value="<?php echo e(request('search')); ?>"
                    placeholder="Cari pasien / No. RM..."
                    class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <input type="date" name="date_from" value="<?php echo e(request('date_from')); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <input type="date" name="date_to" value="<?php echo e(request('date_to')); ?>"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                    <option value="">Semua Status</option>
                    <option value="registered" <?php if(request('status') === 'registered'): echo 'selected'; endif; ?>>Terdaftar</option>
                    <option value="in_progress" <?php if(request('status') === 'in_progress'): echo 'selected'; endif; ?>>Berlangsung</option>
                    <option value="completed" <?php if(request('status') === 'completed'): echo 'selected'; endif; ?>>Selesai</option>
                    <option value="cancelled" <?php if(request('status') === 'cancelled'): echo 'selected'; endif; ?>>Dibatalkan</option>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
            </form>
            <div class="flex gap-2">
                <a href="<?php echo e(route('healthcare.outpatient.visits.create')); ?>"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Kunjungan</a>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                        <th class="px-4 py-3 text-left">Tanggal Kunjungan</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Keperluan</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $visits ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
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
                                            <?php echo e($visit->patient ? $visit->patient->full_name : '-'); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($visit->patient ? $visit->patient->medical_record_number : '-'); ?></p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($visit->doctor ? $visit->doctor->name : '-'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($visit->doctor ? $visit->doctor->specialization : ''); ?></p>
                            </td>
                            <td class="px-4 py-3">
                                <p class="text-gray-900">
                                    <?php echo e($visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('H:i') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <p class="text-gray-700">
                                    <?php echo e(Str::limit($visit->purpose ?? '-', 40)); ?></p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <?php if($visit->status === 'registered'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Terdaftar</span>
                                <?php elseif($visit->status === 'in_progress'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Berlangsung</span>
                                <?php elseif($visit->status === 'completed'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Selesai</span>
                                <?php elseif($visit->status === 'cancelled'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Dibatalkan</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.emr.show', $visit)); ?>"
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
                                    <?php if($visit->status === 'registered' || $visit->status === 'in_progress'): ?>
                                        <a href="<?php echo e(route('healthcare.emr.show', $visit)); ?>"
                                            class="p-1.5 text-green-600 hover:bg-green-50 rounded-lg"
                                            title="Mulai Konsultasi">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    stroke-width="1.5"
                                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                                </path>
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
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                    </path>
                                </svg>
                                <p>Belum ada kunjungan rawat jalan</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        
        <?php if(isset($visits) && $visits->hasPages()): ?>
            <div class="px-4 py-3 border-t border-gray-200">
                <?php echo e($visits->links()); ?>

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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\outpatient\visits\index.blade.php ENDPATH**/ ?>