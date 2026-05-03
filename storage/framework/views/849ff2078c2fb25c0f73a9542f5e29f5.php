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
     <?php $__env->slot('header', null, []); ?> <?php echo e($patient->full_name); ?> <?php $__env->endSlot(); ?>

    
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"
                    class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">
                    Back to Patient
                </a>
        <a href="<?php echo e(route('healthcare.emr.timeline', $patient)); ?>"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                    View Timeline
                </a>
    </div>

    <div class="max-w-7xl mx-auto">
        
        <?php if($allergy_alerts->count() > 0): ?>
            <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-red-800 mb-2">
                            ⚠️ Allergy Alert<?php echo e($allergy_alerts->count() > 1 ? 's' : ''); ?>

                        </h3>
                        <div class="space-y-1">
                            <?php $__currentLoopData = $allergy_alerts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $allergy): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <p class="text-sm text-red-700">
                                    <strong><?php echo e($allergy->allergen); ?></strong> - <?php echo e(ucfirst($allergy->severity)); ?>:
                                    <?php echo e($allergy->reaction); ?>

                                </p>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-blue-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900"><?php echo e($statistics['total_visits']); ?>

                        </p>
                        <p class="text-xs text-gray-500">Total Visits</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-purple-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo e($statistics['total_prescriptions']); ?></p>
                        <p class="text-xs text-gray-500">Prescriptions</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div
                        class="w-10 h-10 rounded-lg bg-amber-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-900">
                            <?php echo e($statistics['last_visit_date'] ? \Carbon\Carbon::parse($statistics['last_visit_date'])->diffForHumans() : 'N/A'); ?>

                        </p>
                        <p class="text-xs text-gray-500">Last Visit</p>
                    </div>
                </div>
            </div>

            <div class="bg-white rounded-xl border border-gray-200 p-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-gray-900">
                            <?php echo e($statistics['chronic_conditions']); ?></p>
                        <p class="text-xs text-gray-500">Chronic Conditions</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
            
            <div
                class="lg:col-span-2 bg-white rounded-xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Vital Signs Trend</h3>
                    <select id="vitalDays" onchange="loadVitalSignsChart()"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-lg bg-gray-50 text-gray-900">
                        <option value="7">Last 7 days</option>
                        <option value="30" selected>Last 30 days</option>
                        <option value="90">Last 90 days</option>
                    </select>
                </div>
                <div id="vitalSignsLoading" class="text-center py-12">
                    <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto" fill="none"
                        viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                            stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                        </path>
                    </svg>
                    <p class="text-sm text-gray-600 mt-2">Loading vital signs...</p>
                </div>
                <canvas id="vitalSignsChart" class="hidden" style="max-height: 400px;"></canvas>
            </div>

            
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Active Medications</h3>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $active_medications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $med): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div class="p-3 bg-gray-50 rounded-lg">
                            <p class="text-sm font-medium text-gray-900"><?php echo e($med->medication_name); ?>

                            </p>
                            <p class="text-xs text-gray-500 mt-1">
                                <?php echo e($med->dosage); ?> - <?php echo e($med->frequency); ?>

                            </p>
                            <p class="text-xs text-gray-400 mt-1">
                                Until
                                <?php echo e($med->valid_until ? \Carbon\Carbon::parse($med->valid_until)->format('d M Y') : 'N/A'); ?>

                            </p>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-8">No active medications</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Upcoming Appointments</h3>
                <div class="space-y-3">
                    <?php $__empty_1 = true; $__currentLoopData = $upcoming_appointments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <div
                            class="p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-lg border border-green-200">
                            <div class="flex items-start justify-between">
                                <div>
                                    <p class="text-sm font-semibold text-gray-900">
                                        <?php echo e($appointment->visit_date ? \Carbon\Carbon::parse($appointment->visit_date)->format('d M Y') : 'TBD'); ?>

                                    </p>
                                    <p class="text-xs text-gray-600 mt-1">
                                        <?php echo e($appointment->department?->name ?? 'General'); ?>

                                    </p>
                                    <p class="text-xs text-gray-500 mt-1">
                                        Dr. <?php echo e($appointment->doctor?->name ?? 'TBD'); ?>

                                    </p>
                                </div>
                                <span class="px-2 py-1 text-xs font-medium bg-green-600 text-white rounded-lg">
                                    <?php echo e(ucfirst($appointment->status)); ?>

                                </span>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <p class="text-sm text-gray-500 text-center py-8">No upcoming appointments
                        </p>
                    <?php endif; ?>
                </div>
            </div>

            
            <div class="bg-white rounded-xl border border-gray-200 p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 gap-3">
                    <a href="#" onclick="openNewVisitModal()"
                        class="p-4 bg-gradient-to-br from-blue-500 to-blue-600 text-white rounded-xl hover:from-blue-600 hover:to-blue-700 transition">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                        </svg>
                        <p class="text-sm font-medium">New Visit</p>
                    </a>
                    <a href="<?php echo e(route('healthcare.patients.show', $patient)); ?>"
                        class="p-4 bg-gradient-to-br from-purple-500 to-purple-600 text-white rounded-xl hover:from-purple-600 hover:to-purple-700 transition">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium">Resep</p>
                    </a>
                    <a href="#" onclick="openLabOrderModal()"
                        class="p-4 bg-gradient-to-br from-amber-500 to-amber-600 text-white rounded-xl hover:from-amber-600 hover:to-amber-700 transition">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                        </svg>
                        <p class="text-sm font-medium">Order Lab</p>
                    </a>
                    <a href="<?php echo e(route('healthcare.emr.export', $patient)); ?>"
                        class="p-4 bg-gradient-to-br from-gray-500 to-gray-600 text-white rounded-xl hover:from-gray-600 hover:to-gray-700 transition">
                        <svg class="w-6 h-6 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <p class="text-sm font-medium">Export EMR</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            const patientId = <?php echo e($patient->id); ?>;
            let vitalSignsChart = null;

            // Load vital signs chart
            function loadVitalSignsChart() {
                const days = document.getElementById('vitalDays').value;

                document.getElementById('vitalSignsLoading').classList.remove('hidden');
                document.getElementById('vitalSignsChart').classList.add('hidden');

                fetch(`/healthcare/emr/${patientId}/vital-signs-chart?days=${days}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            renderVitalSignsChart(data.data);
                        }
                    })
                    .catch(error => {
                        console.error('Error loading vital signs:', error);
                    })
                    .finally(() => {
                        document.getElementById('vitalSignsLoading').classList.add('hidden');
                        document.getElementById('vitalSignsChart').classList.remove('hidden');
                    });
            }

            function renderVitalSignsChart(data) {
                const ctx = document.getElementById('vitalSignsChart').getContext('2d');

                if (vitalSignsChart) {
                    vitalSignsChart.destroy();
                }

                vitalSignsChart = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: data.labels,
                        datasets: [{
                                label: 'Heart Rate',
                                data: data.heart_rate,
                                borderColor: 'rgb(239, 68, 68)',
                                backgroundColor: 'rgba(239, 68, 68, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y',
                            },
                            {
                                label: 'Temperature',
                                data: data.temperature,
                                borderColor: 'rgb(245, 158, 11)',
                                backgroundColor: 'rgba(245, 158, 11, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y1',
                            },
                            {
                                label: 'SpO2',
                                data: data.spo2,
                                borderColor: 'rgb(59, 130, 246)',
                                backgroundColor: 'rgba(59, 130, 246, 0.1)',
                                tension: 0.4,
                                yAxisID: 'y',
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false,
                        },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'top',
                            }
                        },
                        scales: {
                            y: {
                                type: 'linear',
                                display: true,
                                position: 'left',
                                title: {
                                    display: true,
                                    text: 'Heart Rate / SpO2'
                                }
                            },
                            y1: {
                                type: 'linear',
                                display: true,
                                position: 'right',
                                title: {
                                    display: true,
                                    text: 'Temperature (°C)'
                                },
                                grid: {
                                    drawOnChartArea: false,
                                },
                            }
                        }
                    }
                });
            }

            function openNewVisitModal() {
                alert('New Visit modal - to be implemented');
            }

            function openLabOrderModal() {
                alert('Lab Order modal - to be implemented');
            }

            // Load chart on page load
            document.addEventListener('DOMContentLoaded', loadVitalSignsChart);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\emr\dashboard.blade.php ENDPATH**/ ?>