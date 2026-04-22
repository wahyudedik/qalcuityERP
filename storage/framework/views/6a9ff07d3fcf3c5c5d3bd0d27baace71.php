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
     <?php $__env->slot('header', null, []); ?> Detail Pasien - <?php echo e($patient->full_name); ?> <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien', 'url' => route('healthcare.patients.index')],
        ['label' => 'Detail Pasien'],
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\Illuminate\View\AnonymousComponent::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Pasien', 'url' => route('healthcare.patients.index')],
        ['label' => 'Detail Pasien'],
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="py-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-100 dark:bg-blue-900/30 flex items-center justify-center">
                        <svg class="w-8 h-8 text-blue-600 dark:text-blue-400" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white"><?php echo e($patient->full_name); ?></h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">RM: <?php echo e($patient->medical_record_number); ?> |
                            NIK: <?php echo e($patient->nik ?? '-'); ?></p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('healthcare.patients.edit', $patient)); ?>"
                        class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Edit</a>
                    <a href="<?php echo e(route('healthcare.appointments.book', ['patient_id' => $patient->id])); ?>"
                        class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Janji Temu</a>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Lahir</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                        <?php echo e($patient->birth_date ? $patient->birth_date->format('d M Y') : '-'); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Gender</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1">
                        <?php echo e($patient->gender === 'male' ? 'Laki-laki' : ($patient->gender === 'female' ? 'Perempuan' : '-')); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Telepon</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-1"><?php echo e($patient->phone_primary ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Status</p>
                    <p class="mt-1">
                        <?php if($patient->status === 'active'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Aktif</span>
                        <?php elseif($patient->status === 'inactive'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-300">Nonaktif</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 mb-6">
            <div class="border-b border-gray-200 dark:border-white/10">
                <nav class="flex gap-6 px-6" aria-label="Tabs">
                    <button onclick="switchTab('visits')" id="tab-visits"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600 dark:text-blue-400">
                        Kunjungan
                    </button>
                    <button onclick="switchTab('appointments')" id="tab-appointments"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Janji Temu
                    </button>
                    <button onclick="switchTab('emr')" id="tab-emr"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Rekam Medis
                    </button>
                    <button onclick="switchTab('billing')" id="tab-billing"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700 dark:text-slate-400 dark:hover:text-slate-300">
                        Tagihan
                    </button>
                </nav>
            </div>

            
            <div id="content-visits" class="tab-content p-6">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Kunjungan</h3>
                <?php if($patient->visits && $patient->visits->count() > 0): ?>
                    <div class="space-y-3">
                        <?php $__currentLoopData = $patient->visits->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-green-100 dark:bg-green-900/30 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                                            <?php echo e($visit->visit_type_label ?? $visit->visit_type ?? 'Kunjungan'); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400">
                                            <?php echo e($visit->doctor ? $visit->doctor->name : '-'); ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-900 dark:text-white">
                                        <?php echo e($visit->visit_date ? $visit->visit_date->format('d M Y') : '-'); ?>

                                    </p>
                                    <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($visit->visit_status_label ?? $visit->visit_status ?? '-'); ?>

                                    </p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada riwayat kunjungan</p>
                <?php endif; ?>
            </div>

            
            <div id="content-appointments" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Janji Temu</h3>
                    <a href="<?php echo e(route('healthcare.appointments.book', ['patient_id' => $patient->id])); ?>"
                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat Janji</a>
                </div>
                <?php if($patient->appointments && $patient->appointments->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Tanggal</th>
                                    <th class="px-4 py-3 text-left">Dokter</th>
                                    <th class="px-4 py-3 text-left">Layanan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                                <?php $__currentLoopData = $patient->appointments->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                        <td class="px-4 py-3">
                                            <?php echo e($appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y H:i') : '-'); ?>

                                        </td>
                                        <td class="px-4 py-3">
                                            <?php echo e($appointment->doctor ? $appointment->doctor->name : '-'); ?>

                                        </td>
                                        <td class="px-4 py-3"><?php echo e($appointment->service_type ?? '-'); ?></td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if($appointment->status === 'scheduled'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-400">Terjadwal</span>
                                            <?php elseif($appointment->status === 'completed'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400">Selesai</span>
                                            <?php elseif($appointment->status === 'cancelled'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400">Dibatalkan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 dark:text-slate-400 py-8">Belum ada janji temu</p>
                <?php endif; ?>
            </div>

            
            <div id="content-emr" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Rekam Medis Elektronik</h3>
                    <?php if($patient->visits && $patient->visits->count() > 0): ?>
                        <a href="<?php echo e(route('healthcare.emr.show', $patient->visits->first())); ?>"
                            class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Lihat
                            EMR</a>
                    <?php endif; ?>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Golongan Darah</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e($patient->blood_type ?? '-'); ?>

                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Alergi</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e(is_array($patient->known_allergies) ? implode(', ', $patient->known_allergies) : ($patient->known_allergies ?? '-')); ?>

                        </p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Asuransi</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e($patient->insurance_provider ?? '-'); ?></p>
                    </div>
                    <div class="p-4 bg-gray-50 dark:bg-white/5 rounded-xl">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">No. Polis</p>
                        <p class="text-lg font-semibold text-gray-900 dark:text-white">
                            <?php echo e($patient->insurance_policy_number ?? '-'); ?></p>
                    </div>
                </div>
            </div>

            
            <div id="content-billing" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Riwayat Tagihan</h3>
                <p class="text-center text-gray-500 dark:text-slate-400 py-8">Fitur tagihan akan ditampilkan di sini
                </p>
            </div>
        </div>

        <?php $__env->startPush('scripts'); ?>
            <script>
                function switchTab(tabName) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                    // Remove active state from all tabs
                    document.querySelectorAll('.tab-button').forEach(el => {
                        el.classList.remove('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                        el.classList.add('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                    });

                    // Show selected tab content
                    document.getElementById('content-' + tabName).classList.remove('hidden');
                    // Activate selected tab
                    const activeTab = document.getElementById('tab-' + tabName);
                    activeTab.classList.remove('border-transparent', 'text-gray-500', 'dark:text-slate-400');
                    activeTab.classList.add('border-blue-500', 'text-blue-600', 'dark:text-blue-400');
                }
            </script>
        <?php $__env->stopPush(); ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patients\show.blade.php ENDPATH**/ ?>