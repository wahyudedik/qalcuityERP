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
     <?php $__env->slot('header', null, []); ?> Profil Dokter - <?php echo e($doctor->name); ?> <?php $__env->endSlot(); ?>

    
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Data Dokter', 'url' => route('healthcare.doctors.index')],
        ['label' => 'Profil Dokter'],
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
        ['label' => 'Data Dokter', 'url' => route('healthcare.doctors.index')],
        ['label' => 'Profil Dokter'],
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
        <div class="bg-white rounded-2xl border border-gray-200 p-6 mb-6">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <?php if($doctor->photo): ?>
                        <img src="<?php echo e($doctor->photo); ?>" alt="<?php echo e($doctor->name); ?>" loading="lazy"
                            class="w-20 h-20 rounded-2xl object-cover border-2 border-gray-200">
                    <?php else: ?>
                        <div
                            class="w-20 h-20 rounded-2xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-10 h-10 text-purple-600" fill="none"
                                stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                    <?php endif; ?>
                    <div>
                        <h2 class="text-xl font-bold text-gray-900"><?php echo e($doctor->name); ?></h2>
                        <p class="text-sm text-gray-500">
                            <?php echo e($doctor->specialization ?? 'Dokter Umum'); ?>

                        </p>
                        <p class="text-xs text-gray-400 mt-1">STR: <?php echo e($doctor->str_number ?? '-'); ?>

                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <a href="<?php echo e(route('healthcare.doctors.index')); ?>"
                        class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Kembali</a>
                    <button onclick="document.getElementById('modal-schedule').classList.remove('hidden')"
                        class="px-4 py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">+
                        Jadwal</button>
                </div>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-200">
                <div>
                    <p class="text-xs text-gray-500">Email</p>
                    <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e($doctor->email ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Telepon</p>
                    <p class="text-sm font-medium text-gray-900 mt-1"><?php echo e($doctor->phone ?? '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Status</p>
                    <p class="mt-1">
                        <?php if($doctor->status === 'active'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Aktif</span>
                        <?php elseif($doctor->status === 'inactive'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Nonaktif</span>
                        <?php elseif($doctor->status === 'on_leave'): ?>
                            <span
                                class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-amber-100 text-amber-700">Cuti</span>
                        <?php endif; ?>
                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Total Pasien</p>
                    <p class="text-sm font-medium text-gray-900 mt-1">
                        <?php
                            $patientCount = $doctor->appointments
                                ? $doctor->appointments->where('status', 'completed')->count()
                                : 0;
                        ?>
                        <?php echo e($patientCount); ?>

                    </p>
                </div>
            </div>
        </div>

        
        <div class="bg-white rounded-2xl border border-gray-200 mb-6">
            <div class="border-b border-gray-200">
                <nav class="flex gap-6 px-6" aria-label="Tabs">
                    <button onclick="switchTab('schedule')" id="tab-schedule"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-blue-500 text-blue-600">
                        Jadwal Praktik
                    </button>
                    <button onclick="switchTab('appointments')" id="tab-appointments"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700">
                        Janji Temu
                    </button>
                    <button onclick="switchTab('patients')" id="tab-patients"
                        class="tab-button py-4 px-1 border-b-2 font-medium text-sm border-transparent text-gray-500 hover:text-gray-700">
                        Riwayat Pasien
                    </button>
                </nav>
            </div>

            
            <div id="content-schedule" class="tab-content p-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Jadwal Praktik Mingguan</h3>
                <?php if($doctor->schedules && $doctor->schedules->count() > 0): ?>
                    <div class="space-y-3">
                        <?php
                            $days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
                        ?>
                        <?php $__currentLoopData = $doctor->schedules->sortBy('day_of_week'); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $schedule): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-xl">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                                        <svg class="w-5 h-5 text-blue-600" fill="none"
                                            stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                                d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z">
                                            </path>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($days[$schedule->day_of_week] ?? '-'); ?></p>
                                        <?php if($schedule->location): ?>
                                            <p class="text-xs text-gray-500">
                                                <?php echo e($schedule->location); ?>

                                            </p>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <div class="text-right">
                                        <p class="text-sm font-medium text-gray-900">
                                            <?php echo e($schedule->start_time); ?> - <?php echo e($schedule->end_time); ?>

                                        </p>
                                        <p class="text-xs text-gray-500">
                                            <?php echo e($schedule->max_patients ? 'Maks ' . $schedule->max_patients . ' pasien' : 'Tidak ada batas'); ?>

                                        </p>
                                    </div>
                                    <?php if($schedule->is_active): ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Aktif</span>
                                    <?php else: ?>
                                        <span
                                            class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-gray-100 text-gray-700">Nonaktif</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">Belum ada jadwal praktik</p>
                <?php endif; ?>
            </div>

            
            <div id="content-appointments" class="tab-content p-6 hidden">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Janji Temu</h3>
                    <a href="<?php echo e(route('healthcare.appointments.index', ['doctor_id' => $doctor->id])); ?>"
                        class="px-3 py-1.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Lihat
                        Semua</a>
                </div>
                <?php if($doctor->appointments && $doctor->appointments->count() > 0): ?>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Pasien</th>
                                    <th class="px-4 py-3 text-left hidden md:table-cell">Tanggal</th>
                                    <th class="px-4 py-3 text-left hidden lg:table-cell">Layanan</th>
                                    <th class="px-4 py-3 text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                <?php $__currentLoopData = $doctor->appointments->take(10); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $appointment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <p class="font-medium text-gray-900">
                                                <?php echo e($appointment->patient ? $appointment->patient->full_name : '-'); ?>

                                            </p>
                                        </td>
                                        <td class="px-4 py-3 hidden md:table-cell">
                                            <?php echo e($appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d M Y H:i') : '-'); ?>

                                        </td>
                                        <td class="px-4 py-3 hidden lg:table-cell">
                                            <?php echo e($appointment->service_type ?? '-'); ?>

                                        </td>
                                        <td class="px-4 py-3 text-center">
                                            <?php if($appointment->status === 'scheduled'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-blue-100 text-blue-700">Terjadwal</span>
                                            <?php elseif($appointment->status === 'completed'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-green-100 text-green-700">Selesai</span>
                                            <?php elseif($appointment->status === 'cancelled'): ?>
                                                <span
                                                    class="inline-flex items-center px-2 py-1 text-xs font-medium rounded-lg bg-red-100 text-red-700">Dibatalkan</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <p class="text-center text-gray-500 py-8">Belum ada janji temu</p>
                <?php endif; ?>
            </div>

            
            <div id="content-patients" class="tab-content p-6 hidden">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Riwayat Pasien</h3>
                <p class="text-center text-gray-500 py-8">Data riwayat pasien akan ditampilkan di
                    sini
                </p>
            </div>
        </div>

        
        <div id="modal-schedule"
            class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm z-50 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl w-full max-w-lg">
                <div class="flex items-center justify-between p-6 border-b border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Jadwal Praktik</h3>
                    <button onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                        class="p-2 hover:bg-gray-100 rounded-xl">
                        <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <form action="<?php echo e(route('healthcare.doctors.schedules.store', $doctor)); ?>" method="POST"
                    class="p-6 space-y-4">
                    <?php echo csrf_field(); ?>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Hari
                                *</label>
                            <select name="day_of_week" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Pilih Hari</option>
                                <option value="1">Senin</option>
                                <option value="2">Selasa</option>
                                <option value="3">Rabu</option>
                                <option value="4">Kamis</option>
                                <option value="5">Jumat</option>
                                <option value="6">Sabtu</option>
                                <option value="0">Minggu</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jam
                                    Mulai
                                    *</label>
                                <input type="time" name="start_time" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jam
                                    Selesai
                                    *</label>
                                <input type="time" name="end_time" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        <div>
                            <label
                                class="block text-sm font-medium text-gray-700 mb-2">Lokasi</label>
                            <input type="text" name="location" placeholder="Contoh: Ruang Praktik A"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Maksimum
                                Pasien</label>
                            <input type="number" name="max_patients" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="is_active" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700">Jadwal Aktif</span>
                            </label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 pt-4">
                        <button type="button"
                            onclick="document.getElementById('modal-schedule').classList.add('hidden')"
                            class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit"
                            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                    </div>
                </form>
            </div>
        </div>

        <?php $__env->startPush('scripts'); ?>
            <script>
                function switchTab(tabName) {
                    // Hide all tab contents
                    document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
                    // Remove active state from all tabs
                    document.querySelectorAll('.tab-button').forEach(el => {
                        el.classList.remove('border-blue-500', 'text-blue-600');
                        el.classList.add('border-transparent', 'text-gray-500');
                    });

                    // Show selected tab content
                    document.getElementById('content-' + tabName).classList.remove('hidden');
                    // Activate selected tab
                    const activeTab = document.getElementById('tab-' + tabName);
                    activeTab.classList.remove('border-transparent', 'text-gray-500');
                    activeTab.classList.add('border-blue-500', 'text-blue-600');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\doctors\show.blade.php ENDPATH**/ ?>