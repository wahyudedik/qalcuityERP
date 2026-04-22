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
     <?php $__env->slot('header', null, []); ?> Laporan Radiologi <?php $__env->endSlot(); ?>

    <?php $tid = auth()->user()->tenant_id; ?>

    <?php if(!isset($exam)): ?>
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl p-6 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-amber-600 dark:text-amber-400 mt-0.5" fill="none" stroke="currentColor"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z">
                    </path>
                </svg>
                <div>
                    <p class="font-bold text-amber-800 dark:text-amber-200">Pilih Pemeriksaan</p>
                    <p class="text-sm text-amber-700 dark:text-amber-300 mt-1">Silakan pilih pemeriksaan radiologi dari
                        daftar untuk membuat laporan.</p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    
    <?php if(isset($exam)): ?>
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden mb-6">
            <div
                class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gradient-to-r from-blue-50 to-indigo-50 dark:from-blue-900/20 dark:to-indigo-900/20">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white">Exam:
                            <?php echo e($exam->exam_number ?? '-'); ?></h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mt-1">
                            <?php echo e($exam->patient ? $exam->patient->full_name : '-'); ?> |
                            <?php echo e($exam->patient ? $exam->patient->medical_record_number : '-'); ?></p>
                    </div>
                    <a href="<?php echo e(route('healthcare.radiology.exams')); ?>"
                        class="px-4 py-2 text-sm border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                        Kembali
                    </a>
                </div>
            </div>
            <div class="p-6 grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Jenis Exam</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e(ucfirst(str_replace('_', ' ', $exam->exam_type ?? '-'))); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Body Part</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1"><?php echo e($exam->body_part ?? '-'); ?>

                    </p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Dokter</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e($exam->doctor ? $exam->doctor->name : '-'); ?></p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal Exam</p>
                    <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                        <?php echo e($exam->exam_date ? \Carbon\Carbon::parse($exam->exam_date)->format('d M Y H:i') : '-'); ?></p>
                </div>
            </div>
        </div>

        
        <form action="<?php echo e(route('healthcare.radiology.reports.store', $exam)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Informasi Klinis</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Clinical
                            History</label>
                        <textarea name="clinical_history" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Riwayat klinis pasien..."><?php echo e(old('clinical_history', $exam->clinical_notes ?? '')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Temuan (Findings)</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Deskripsi
                            Temuan</label>
                        <textarea name="findings" rows="6"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Deskripsi lengkap temuan radiologi..."><?php echo e(old('findings')); ?></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Ukuran Lesi (jika
                            ada)</label>
                        <input type="text" name="lesion_size" value="<?php echo e(old('lesion_size')); ?>"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Contoh: 2.5 x 3.0 cm">
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Kesan & Kesimpulan</h3>
                </div>
                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Impression /
                            Diagnosis</label>
                        <textarea name="impression" rows="4"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Kesan radiologis..."><?php echo e(old('impression')); ?></textarea>
                    </div>
                    <div>
                        <label
                            class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Rekomendasi</label>
                        <textarea name="recommendations" rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"
                            placeholder="Rekomendasi follow-up..."><?php echo e(old('recommendations')); ?></textarea>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Status Laporan</h3>
                </div>
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="is_urgent" value="1"
                                <?php echo e(old('is_urgent') ? 'checked' : ''); ?>

                                class="w-4 h-4 text-red-600 border-gray-300 rounded focus:ring-red-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Temuan Kritis (Urgent)</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="checkbox" name="notify_doctor" value="1" checked
                                class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500">
                            <span class="text-sm text-gray-700 dark:text-gray-300">Notifikasi Dokter</span>
                        </label>
                    </div>
                </div>
            </div>

            
            <div class="flex items-center justify-end gap-3">
                <a href="<?php echo e(route('healthcare.radiology.exams')); ?>"
                    class="px-6 py-2.5 text-sm border border-gray-300 dark:border-white/10 text-gray-700 dark:text-gray-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">
                    Batal
                </a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Laporan
                </button>
            </div>
        </form>
    <?php endif; ?>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\radiology\reports.blade.php ENDPATH**/ ?>