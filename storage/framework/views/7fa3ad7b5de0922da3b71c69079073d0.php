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
     <?php $__env->slot('header', null, []); ?> Penilaian Triage - <?php echo e($emergencyCase->patient->full_name ?? 'Pasien'); ?> <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto">
        
        <div class="bg-gradient-to-r from-red-500 to-orange-600 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                        </path>
                    </svg>
                </div>
                <div class="flex-1">
                    <h2 class="text-lg font-bold"><?php echo e($emergencyCase->patient->full_name ?? '-'); ?></h2>
                    <p class="text-sm text-white/80"><?php echo e($emergencyCase->chief_complaint ?? 'Tidak ada keluhan'); ?></p>
                    <p class="text-xs text-white/70 mt-1">Tiba:
                        <?php echo e($emergencyCase->arrival_time ? \Carbon\Carbon::parse($emergencyCase->arrival_time)->format('d M Y H:i') : '-'); ?>

                    </p>
                </div>
            </div>
        </div>

        <form action="<?php echo e(route('healthcare.er.triage.assess.store', $emergencyCase)); ?>" method="POST"
            class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Tanda Vital</h3>
                </div>
                <div class="p-6">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Tekanan Darah (mmHg)
                            </label>
                            <input type="text" name="vitals[blood_pressure]" placeholder="120/80"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Denyut Jantung (bpm)
                            </label>
                            <input type="number" name="vitals[heart_rate]" placeholder="80"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Suhu (°C)
                            </label>
                            <input type="number" name="vitals[temperature]" step="0.1" placeholder="36.5"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Pernapasan (x/menit)
                            </label>
                            <input type="number" name="vitals[respiratory_rate]" placeholder="20"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                SpO2 (%)
                            </label>
                            <input type="number" name="vitals[spo2]" placeholder="98"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                GCS (Glasgow Coma Scale)
                            </label>
                            <input type="number" name="vitals[gcs]" min="3" max="15" placeholder="15"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Nyeri (0-10)
                            </label>
                            <input type="number" name="vitals[pain_score]" min="0" max="10"
                                placeholder="0"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Berat Badan (kg)
                            </label>
                            <input type="number" name="vitals[weight]" step="0.1" placeholder="70"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Keluhan & Penilaian</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Keluhan Utama <span class="text-red-500">*</span>
                            </label>
                            <textarea name="chief_complaint" rows="3" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"><?php echo e(old('chief_complaint', $emergencyCase->chief_complaint)); ?></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Riwayat Penyakit Sekarang
                            </label>
                            <textarea name="history_of_present_illness" rows="3" placeholder="Deskripsikan onset, durasi, severity..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Alergi
                            </label>
                            <input type="text" name="allergies" placeholder="Obat, makanan, dll"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border-2 border-red-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-red-200 bg-red-50">
                    <h3 class="text-lg font-bold text-red-600">Level Triage (Emergency Severity Index)
                    </h3>
                </div>
                <div class="p-6">
                    <div class="space-y-3">
                        <label
                            class="flex items-start gap-3 p-4 border-2 border-red-200 rounded-xl cursor-pointer hover:bg-red-50 transition-colors">
                            <input type="radio" name="triage_level" value="red" required
                                class="mt-1 w-4 h-4 text-red-600 border-gray-300 focus:ring-red-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded">ESI-1</span>
                                    <span class="font-bold text-gray-900">Resusitasi - Immediate</span>
                                </div>
                                <p class="text-sm text-gray-600">Ancaman jiwa, butuh penanganan
                                    segera (henti jantung, syok, trauma mayor)</p>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 border-2 border-amber-200 rounded-xl cursor-pointer hover:bg-amber-50 transition-colors">
                            <input type="radio" name="triage_level" value="yellow"
                                class="mt-1 w-4 h-4 text-amber-600 border-gray-300 focus:ring-amber-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded">ESI-2</span>
                                    <span class="font-bold text-gray-900">Emergent - < 15 menit</span>
                                </div>
                                <p class="text-sm text-gray-600">Situasi berpotensi mengancam jiwa
                                    (nyeri hebat, gangguan pernapasan, stroke)</p>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 border-2 border-green-200 rounded-xl cursor-pointer hover:bg-green-50 transition-colors">
                            <input type="radio" name="triage_level" value="green"
                                class="mt-1 w-4 h-4 text-green-600 border-gray-300 focus:ring-green-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-green-500 text-white rounded">ESI-3</span>
                                    <span class="font-bold text-gray-900">Urgent - < 60 menit</span>
                                </div>
                                <p class="text-sm text-gray-600">Stabil tapi butuh multiple
                                    resources (lab, imaging, IV therapy)</p>
                            </div>
                        </label>

                        <label
                            class="flex items-start gap-3 p-4 border-2 border-gray-200 rounded-xl cursor-pointer hover:bg-gray-50 transition-colors">
                            <input type="radio" name="triage_level" value="black"
                                class="mt-1 w-4 h-4 text-gray-600 border-gray-300 focus:ring-gray-500">
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span
                                        class="px-2 py-1 text-xs font-bold bg-gray-500 text-white rounded">ESI-4/5</span>
                                    <span class="font-bold text-gray-900">Non-Urgent</span>
                                </div>
                                <p class="text-sm text-gray-600">Kondisi stabil, butuh 1 resource
                                    atau tidak ada resource (resep ringan, kontrol)</p>
                            </div>
                        </label>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 bg-gray-50">
                    <h3 class="text-lg font-semibold text-gray-900">Tindak Lanjut</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Disposisi <span class="text-red-500">*</span>
                            </label>
                            <select name="disposition" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">-- Pilih Tindak Lanjut --</option>
                                <option value="er_treatment">Perawatan di IGD</option>
                                <option value="admission">Rawat Inap</option>
                                <option value="icu">ICU</option>
                                <option value="surgery">Tindakan Bedah</option>
                                <option value="discharge">Pulang</option>
                                <option value="transfer">Transfer ke RS Lain</option>
                                <option value="observation">Observasi</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Catatan Triage
                            </label>
                            <textarea name="triage_notes" rows="3" placeholder="Catatan tambahan untuk tim IGD..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('healthcare.er.triage')); ?>"
                    class="px-6 py-2.5 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700 font-medium">
                    Simpan Penilaian Triage
                </button>
            </div>
        </form>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\triage\assess.blade.php ENDPATH**/ ?>