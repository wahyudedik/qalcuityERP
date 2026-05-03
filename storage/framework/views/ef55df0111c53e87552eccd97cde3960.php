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
     <?php $__env->slot('header', null, []); ?> Medical Records <?php $__env->endSlot(); ?>

    <?php
        $patient = auth()->user()->patient;
        $tid = auth()->user()->tenant_id;
    ?>

    <?php if(!$patient): ?>
        <div
            class="bg-red-50 border border-red-200 rounded-2xl p-6 text-center">
            <p class="text-sm text-red-700">Patient profile not found. Please contact reception.</p>
        </div>
    <?php else: ?>
        
        <div class="bg-white rounded-2xl border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex -mb-px">
                    <button onclick="switchTab('visits')" id="tab-visits"
                        class="tab-btn active px-6 py-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                        Visit History
                    </button>
                    <button onclick="switchTab('diagnoses')" id="tab-diagnoses"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Diagnoses
                    </button>
                    <button onclick="switchTab('prescriptions')" id="tab-prescriptions"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Prescriptions
                    </button>
                    <button onclick="switchTab('lab-results')" id="tab-lab-results"
                        class="tab-btn px-6 py-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                        Lab Results
                    </button>
                </nav>
            </div>
        </div>

        
        <div id="content-visits" class="tab-content">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Department</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Keluhan</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                                $visits = \App\Models\OutpatientVisit::where('patient_id', $patient->id)
                                    ->orderBy('visit_date', 'desc')
                                    ->paginate(10);
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $visits; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $visit): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3">
                                        <p class="text-gray-900">
                                            <?php echo e($visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-'); ?>

                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('H:i') : '-'); ?>

                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                        <?php echo e($visit->doctor ? $visit->doctor->name : '-'); ?></td>
                                    <td class="px-4 py-3 hidden lg:table-cell">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">
                                            <?php echo e($visit->department ?? '-'); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-600 hidden sm:table-cell">
                                        <?php echo e(Str::limit($visit->chief_complaint ?? '-', 50)); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">
                                            <?php echo e(ucfirst($visit->status ?? 'Completed')); ?>

                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <p>Belum ada riwayat kunjungan</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($visits->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-200">
                        <?php echo e($visits->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div id="content-diagnoses" class="tab-content hidden">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Kode ICD-10</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Diagnosa</th>
                                <th class="px-4 py-3 text-left hidden lg:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-center">Tipe</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                                $diagnoses = \App\Models\Diagnosis::where('patient_id', $patient->id)
                                    ->orderBy('diagnosis_date', 'desc')
                                    ->paginate(10);
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $diagnoses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $diagnosis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-900">
                                        <?php echo e($diagnosis->diagnosis_date ? \Carbon\Carbon::parse($diagnosis->diagnosis_date)->format('d M Y') : '-'); ?>

                                    </td>
                                    <td class="px-4 py-3">
                                        <span
                                            class="font-mono text-sm font-bold text-blue-600"><?php echo e($diagnosis->icd_code ?? '-'); ?></span>
                                    </td>
                                    <td class="px-4 py-3 text-gray-900 hidden md:table-cell">
                                        <?php echo e($diagnosis->description ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-gray-600 hidden lg:table-cell">
                                        <?php echo e($diagnosis->doctor ? $diagnosis->doctor->name : '-'); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($diagnosis->diagnosis_type === 'primary'): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Primary</span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Secondary</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">
                                        <p>Belum ada diagnosa</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($diagnoses->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-200">
                        <?php echo e($diagnoses->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div id="content-prescriptions" class="tab-content hidden">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Dokter</th>
                                <th class="px-4 py-3 text-left">Obat</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Dosis</th>
                                <th class="px-4 py-3 text-center hidden lg:table-cell">Durasi</th>
                                <th class="px-4 py-3 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                                $prescriptions = \App\Models\Prescription::where('patient_id', $patient->id)
                                    ->orderBy('prescription_date', 'desc')
                                    ->paginate(10);
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $prescriptions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $prescription): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-900">
                                        <?php echo e($prescription->prescription_date ? \Carbon\Carbon::parse($prescription->prescription_date)->format('d M Y') : '-'); ?>

                                    </td>
                                    <td class="px-4 py-3 text-gray-600 hidden md:table-cell">
                                        <?php echo e($prescription->doctor ? $prescription->doctor->name : '-'); ?></td>
                                    <td class="px-4 py-3">
                                        <p class="text-gray-900">
                                            <?php echo e($prescription->medication_name ?? '-'); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($prescription->notes ?? ''); ?></p>
                                    </td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 hidden sm:table-cell">
                                        <?php echo e($prescription->dosage ?? '-'); ?></td>
                                    <td
                                        class="px-4 py-3 text-center text-gray-600 hidden lg:table-cell">
                                        <?php echo e($prescription->duration ?? '-'); ?></td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($prescription->status === 'active'): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Active</span>
                                        <?php elseif($prescription->status === 'completed'): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Completed</span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Cancelled</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">
                                        <p>Belum ada resep</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($prescriptions->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-200">
                        <?php echo e($prescriptions->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>

        
        <div id="content-lab-results" class="tab-content hidden">
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Tanggal</th>
                                <th class="px-4 py-3 text-left">Test Name</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Category</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php
                                $labResults = \App\Models\LabResult::where('patient_id', $patient->id)
                                    ->orderBy('result_date', 'desc')
                                    ->paginate(10);
                            ?>
                            <?php $__empty_1 = true; $__currentLoopData = $labResults; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $result): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-4 py-3 text-gray-900">
                                        <?php echo e($result->result_date ? \Carbon\Carbon::parse($result->result_date)->format('d M Y') : '-'); ?>

                                    </td>
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900">
                                            <?php echo e($result->test_name ?? '-'); ?></p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($result->lab_order ? $result->lab_order->order_number : '-'); ?></p>
                                    </td>
                                    <td class="px-4 py-3 hidden md:table-cell">
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-purple-100 text-purple-700">
                                            <?php echo e($result->category ?? '-'); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center hidden sm:table-cell">
                                        <?php if($result->status === 'completed'): ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Completed</span>
                                        <?php else: ?>
                                            <span
                                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php if($result->status === 'completed'): ?>
                                            <button
                                                class="p-1.5 text-blue-600 hover:bg-blue-50 rounded-lg"
                                                title="View Results">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor"
                                                    viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        stroke-width="1.5"
                                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                                    </path>
                                                </svg>
                                            </button>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="5"
                                        class="px-4 py-8 text-center text-gray-500">
                                        <p>Belum ada hasil lab</p>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($labResults->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-200">
                        <?php echo e($labResults->links()); ?>

                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function switchTab(tabName) {
                // Hide all content
                document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                document.querySelectorAll('.tab-btn').forEach(el => {
                    el.classList.remove('active', 'border-blue-600', 'text-blue-600');
                    el.classList.add('border-transparent', 'text-gray-500');
                });

                // Show selected content
                document.getElementById('content-' + tabName).classList.remove('hidden');
                const activeBtn = document.getElementById('tab-' + tabName);
                activeBtn.classList.add('active', 'border-blue-600', 'text-blue-600');
                activeBtn.classList.remove('border-transparent', 'text-gray-500');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\patient-portal\records.blade.php ENDPATH**/ ?>