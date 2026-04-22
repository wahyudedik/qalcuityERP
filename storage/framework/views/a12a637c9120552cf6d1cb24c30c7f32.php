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
     <?php $__env->slot('header', null, []); ?> Buat Resep Elektronik - <?php echo e($visit->patient->full_name ?? 'Pasien'); ?> <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto">
        
        <div class="bg-gradient-to-r from-purple-500 to-pink-600 rounded-2xl p-6 mb-6 text-white">
            <div class="flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-sm flex items-center justify-center">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                        </path>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold"><?php echo e($visit->patient->full_name ?? '-'); ?></h2>
                    <p class="text-sm text-white/80">RM: <?php echo e($visit->patient->medical_record_number ?? '-'); ?> |
                        <?php echo e($visit->visit_date ? \Carbon\Carbon::parse($visit->visit_date)->format('d M Y') : '-'); ?></p>
                </div>
            </div>
        </div>

        <form action="<?php echo e(route('healthcare.emr.prescriptions.store', $visit)); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div
                    class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Daftar Obat</h3>
                    <button type="button" onclick="addMedication()"
                        class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">
                        + Tambah Obat
                    </button>
                </div>
                <div class="p-6">
                    <div id="medication-list" class="space-y-6">
                        
                        <div
                            class="medication-item p-4 bg-gray-50 dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10">
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-sm font-semibold text-gray-900 dark:text-white">Obat #1</h4>
                                <button type="button" onclick="removeMedication(this)"
                                    class="text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Nama Obat <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="medications[0][medication_name]" required
                                        placeholder="Contoh: Paracetamol 500mg"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Dosis <span class="text-red-500">*</span>
                                    </label>
                                    <input type="text" name="medications[0][dosage]" required
                                        placeholder="Contoh: 3x1 tablet"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Frekuensi <span class="text-red-500">*</span>
                                    </label>
                                    <select name="medications[0][frequency]" required
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Pilih Frekuensi</option>
                                        <option value="1x sehari">1x sehari</option>
                                        <option value="2x sehari">2x sehari</option>
                                        <option value="3x sehari">3x sehari</option>
                                        <option value="4x sehari">4x sehari</option>
                                        <option value="Bila perlu">Bila perlu (PRN)</option>
                                        <option value="Sekali pemberian">Sekali pemberian</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Waktu Pemberian
                                    </label>
                                    <select name="medications[0][timing]"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <option value="">Pilih Waktu</option>
                                        <option value="Sebelum makan">Sebelum makan</option>
                                        <option value="Setelah makan">Setelah makan</option>
                                        <option value="Saat makan">Saat makan</option>
                                        <option value="Pagi hari">Pagi hari</option>
                                        <option value="Malam hari">Malam hari</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Durasi <span class="text-red-500">*</span>
                                    </label>
                                    <div class="flex gap-2">
                                        <input type="number" name="medications[0][duration_value]" required
                                            min="1" placeholder="Jumlah"
                                            class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                        <select name="medications[0][duration_unit]"
                                            class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                            <option value="hari">Hari</option>
                                            <option value="minggu">Minggu</option>
                                            <option value="bulan">Bulan</option>
                                        </select>
                                    </div>
                                </div>

                                <div>
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Jumlah Total <span class="text-red-500">*</span>
                                    </label>
                                    <input type="number" name="medications[0][quantity]" required min="1"
                                        placeholder="Contoh: 30"
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                </div>

                                <div class="md:col-span-2">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                        Instruksi Khusus
                                    </label>
                                    <textarea name="medications[0][instructions]" rows="2" placeholder="Instruksi tambahan untuk pasien..."
                                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            
            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Catatan Resep</h3>
                </div>
                <div class="p-6">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Catatan Dokter
                            </label>
                            <textarea name="prescription_notes" rows="3" placeholder="Catatan tambahan untuk apoteker atau pasien..."
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-2">
                                Status Resep
                            </label>
                            <select name="status"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="pending">Pending</option>
                                <option value="active">Aktif</option>
                                <option value="completed">Selesai</option>
                            </select>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="send_to_pharmacy" value="1" checked
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Kirim resep ke farmasi</span>
                            </label>
                        </div>

                        <div>
                            <label class="flex items-center gap-2">
                                <input type="checkbox" name="print_prescription" value="1"
                                    class="w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500">
                                <span class="text-sm text-gray-700 dark:text-slate-300">Cetak resep</span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            
            <div class="flex justify-end gap-3">
                <a href="<?php echo e(route('healthcare.emr.show', $visit)); ?>"
                    class="px-6 py-2.5 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">Batal</a>
                <button type="submit"
                    class="px-6 py-2.5 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">
                    Simpan Resep
                </button>
            </div>
        </form>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            let medicationCount = 1;

            function addMedication() {
                const template = document.querySelector('.medication-item').cloneNode(true);
                medicationCount++;

                // Update all field names and labels
                template.querySelectorAll('input, select, textarea').forEach(field => {
                    const name = field.getAttribute('name');
                    if (name) {
                        field.setAttribute('name', name.replace(/\[\d+\]/, `[${medicationCount - 1}]`));
                        if (field.tagName === 'INPUT' || field.tagName === 'TEXTAREA') {
                            field.value = '';
                        } else if (field.tagName === 'SELECT') {
                            field.selectedIndex = 0;
                        }
                    }
                });

                // Update label
                const label = template.querySelector('h4');
                if (label) {
                    label.textContent = `Obat #${medicationCount}`;
                }

                // Show remove button for new items
                const removeBtn = template.querySelector('button[onclick="removeMedication(this)"]');
                if (removeBtn) {
                    removeBtn.style.display = 'block';
                }

                document.getElementById('medication-list').appendChild(template);
            }

            function removeMedication(button) {
                const items = document.querySelectorAll('.medication-item');
                if (items.length > 1) {
                    button.closest('.medication-item').remove();
                } else {
                    alert('Minimal harus ada 1 obat');
                }
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\healthcare\emr\prescribe.blade.php ENDPATH**/ ?>