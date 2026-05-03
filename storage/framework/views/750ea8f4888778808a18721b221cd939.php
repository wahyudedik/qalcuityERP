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
     <?php $__env->slot('header', null, []); ?> Dashboard Instalasi Gawat Darurat (IGD) <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    
    <?php if(($criticalPatients ?? 0) > 0): ?>
        <div class="bg-red-500 text-white px-6 py-4 rounded-2xl mb-6 flex items-center justify-between animate-pulse">
            <div class="flex items-center gap-3">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="text-lg font-bold">PERHATIAN: <?php echo e($criticalPatients); ?> Pasien Kritis</p>
                    <p class="text-sm text-white/80">Memerlukan penanganan segera</p>
                </div>
            </div>
            <a href="<?php echo e(route('healthcare.er.triage', ['priority' => 'red'])); ?>"
                class="px-4 py-2 bg-white text-red-600 rounded-xl font-medium hover:bg-white/90">
                Lihat Detail
            </a>
        </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-7 gap-4 mb-6">
        <?php
            $totalERPatients = \App\Models\EmergencyCase::where('tenant_id', $tid)->where('status', 'active')->count();
            $redPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'red')
                ->where('status', 'active')
                ->count();
            $yellowPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'yellow')
                ->where('status', 'active')
                ->count();
            $greenPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'green')
                ->where('status', 'active')
                ->count();
            $blackPriority = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->where('triage_level', 'black')
                ->where('status', 'active')
                ->count();
            $avgStayTime =
                \App\Models\EmergencyCase::where('tenant_id', $tid)
                    ->where('status', 'discharged')
                    ->avg('stay_duration_minutes') ?? 0;
            $todayThroughput = \App\Models\EmergencyCase::where('tenant_id', $tid)
                ->whereDate('created_at', today())
                ->count();
        ?>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Total Pasien IGD</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($totalERPatients); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border-2 border-red-200">
            <p class="text-xs text-red-600 font-semibold">🔴 Resusitasi</p>
            <p class="text-2xl font-bold text-red-600 mt-1"><?php echo e($redPriority); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border-2 border-amber-200">
            <p class="text-xs text-amber-600 font-semibold">🟠 Emergent</p>
            <p class="text-2xl font-bold text-amber-600 mt-1"><?php echo e($yellowPriority); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border-2 border-green-200">
            <p class="text-xs text-green-600 font-semibold">🟢 Urgent</p>
            <p class="text-2xl font-bold text-green-600 mt-1"><?php echo e($greenPriority); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Non-Urgent</p>
            <p class="text-2xl font-bold text-gray-600 mt-1"><?php echo e($blackPriority); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Rata-rata Lama (mnt)</p>
            <p class="text-2xl font-bold text-blue-600 mt-1"><?php echo e(round($avgStayTime)); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Throughput Hari Ini</p>
            <p class="text-2xl font-bold text-purple-600 mt-1"><?php echo e($todayThroughput); ?></p>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-6">
        <div class="flex flex-wrap items-center gap-4 text-sm">
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-red-500"></div>
                <span class="text-gray-700 font-medium">Red - Resusitasi (Immediate)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-amber-500"></div>
                <span class="text-gray-700 font-medium">Yellow - Emergent (< 15 min)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-green-500"></div>
                <span class="text-gray-700 font-medium">Green - Urgent (< 60 min)</span>
            </div>
            <div class="flex items-center gap-2">
                <div class="w-4 h-4 rounded bg-gray-400"></div>
                <span class="text-gray-700 font-medium">Black - Non-Urgent</span>
            </div>
        </div>
    </div>

    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <div class="bg-white rounded-2xl border-2 border-red-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-red-600">🔴 Resusitasi - Immediate</h3>
                    <a href="<?php echo e(route('healthcare.er.triage', ['priority' => 'red'])); ?>"
                        class="text-sm text-red-600 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="p-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $criticalCases ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="p-4 bg-red-50 rounded-xl border border-red-200">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900">
                                    <?php echo e($case->patient ? $case->patient->full_name : '-'); ?></p>
                                <p class="text-sm text-gray-600">
                                    <?php echo e($case->chief_complaint ?? '-'); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded-lg">ESI-1</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Sejak
                                <?php echo e($case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-'); ?></span>
                            <a href="<?php echo e(route('healthcare.er.triage.assess', $case)); ?>"
                                class="px-3 py-1 bg-red-600 text-white rounded-lg hover:bg-red-700">Tangani</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-center text-gray-500 py-4">Tidak ada pasien kritis</p>
                <?php endif; ?>
            </div>
        </div>

        
        <div
            class="bg-white rounded-2xl border-2 border-amber-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-amber-200 bg-amber-50">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-amber-600">🟠 Emergent - < 15 menit</h3>
                            <a href="<?php echo e(route('healthcare.er.triage', ['priority' => 'yellow'])); ?>"
                                class="text-sm text-amber-600 hover:underline">Lihat Semua</a>
                </div>
            </div>
            <div class="p-4 space-y-3">
                <?php $__empty_1 = true; $__currentLoopData = $emergentCases ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div
                        class="p-4 bg-amber-50 rounded-xl border border-amber-200">
                        <div class="flex items-start justify-between mb-2">
                            <div>
                                <p class="font-bold text-gray-900">
                                    <?php echo e($case->patient ? $case->patient->full_name : '-'); ?></p>
                                <p class="text-sm text-gray-600">
                                    <?php echo e($case->chief_complaint ?? '-'); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded-lg">ESI-2</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>Sejak
                                <?php echo e($case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-'); ?></span>
                            <a href="<?php echo e(route('healthcare.er.triage.assess', $case)); ?>"
                                class="px-3 py-1 bg-amber-600 text-white rounded-lg hover:bg-amber-700">Tangani</a>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-center text-gray-500 py-4">Tidak ada pasien emergent</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div
            class="px-6 py-4 border-b border-gray-200 bg-gray-50 flex items-center justify-between">
            <h3 class="text-lg font-semibold text-gray-900">Pemasukan IGD Terbaru</h3>
            <a href="<?php echo e(route('healthcare.er.triage')); ?>"
                class="px-4 py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">+ Triage Baru</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pasien</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Keluhan Utama</th>
                        <th class="px-4 py-3 text-center hidden sm:table-cell">Triage</th>
                        <th class="px-4 py-3 text-left hidden lg:table-cell">Waktu Tiba</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Lama (mnt)</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $recentCases ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $case): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <p class="font-medium text-gray-900">
                                    <?php echo e($case->patient ? $case->patient->full_name : '-'); ?></p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($case->patient ? $case->patient->medical_record_number : '-'); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <p class="text-gray-700">
                                    <?php echo e(Str::limit($case->chief_complaint, 50)); ?></p>
                            </td>
                            <td class="px-4 py-3 text-center hidden sm:table-cell">
                                <?php if($case->triage_level === 'red'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-red-100 text-red-700">🔴
                                        Red</span>
                                <?php elseif($case->triage_level === 'yellow'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-amber-100 text-amber-700">🟠
                                        Yellow</span>
                                <?php elseif($case->triage_level === 'green'): ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-green-100 text-green-700">🟢
                                        Green</span>
                                <?php else: ?>
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-bold rounded-lg bg-gray-100 text-gray-700">⚫
                                        Black</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-left hidden lg:table-cell">
                                <p class="text-gray-900">
                                    <?php echo e($case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('d M Y') : '-'); ?>

                                </p>
                                <p class="text-xs text-gray-500">
                                    <?php echo e($case->arrival_time ? \Carbon\Carbon::parse($case->arrival_time)->format('H:i') : '-'); ?>

                                </p>
                            </td>
                            <td class="px-4 py-3 text-center hidden lg:table-cell">
                                <span
                                    class="font-bold text-gray-900"><?php echo e($case->stay_duration_minutes ?? '-'); ?></span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="<?php echo e(route('healthcare.er.triage.assess', $case)); ?>"
                                        class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                        title="Assessment">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                            </path>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                <p>Belum ada pasien IGD</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\er\dashboard.blade.php ENDPATH**/ ?>