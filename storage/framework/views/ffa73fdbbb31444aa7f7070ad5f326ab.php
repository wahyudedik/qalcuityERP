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
     <?php $__env->slot('title', null, []); ?> <?php echo e($ad->exists ? 'Edit' : 'Buat'); ?> Popup Iklan — Qalcuity ERP <?php $__env->endSlot(); ?>
     <?php $__env->slot('header', null, []); ?> <?php echo e($ad->exists ? 'Edit' : 'Buat'); ?> Popup Iklan <?php $__env->endSlot(); ?>
     <?php $__env->slot('pageHeader', null, []); ?> 
        <a href="<?php echo e(route('super-admin.popup-ads.index')); ?>"
            class="flex items-center gap-2 text-sm text-slate-400 hover:text-white px-3 py-2 rounded-xl hover:bg-white/10 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Kembali
        </a>
     <?php $__env->endSlot(); ?>

    <?php if($errors->any()): ?>
        <div class="mb-4 px-4 py-3 bg-red-500/20 border border-red-500/30 text-red-400 text-sm rounded-xl space-y-1">
            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <p><?php echo e($error); ?></p>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <form method="POST"
        action="<?php echo e($ad->exists ? route('super-admin.popup-ads.update', $ad) : route('super-admin.popup-ads.store')); ?>"
        enctype="multipart/form-data" x-data="popupAdForm()" class="max-w-2xl space-y-6">
        <?php echo csrf_field(); ?>
        <?php if($ad->exists): ?>
            <?php echo method_field('PUT'); ?>
        <?php endif; ?>

        
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Konten Iklan</h3>

            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Judul <span
                        class="text-red-400">*</span></label>
                <input type="text" name="title" value="<?php echo e(old('title', $ad->title)); ?>" required maxlength="200"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Contoh: Promo Ramadan 50% untuk semua tenant!">
            </div>

            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Deskripsi</label>
                <textarea name="body" rows="3" maxlength="1000"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent resize-none"
                    placeholder="Teks pendek yang muncul di bawah judul popup..."><?php echo e(old('body', $ad->body)); ?></textarea>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Gambar Banner</label>
                <div class="space-y-3">
                    <?php if($ad->image_path): ?>
                        <div class="relative inline-block">
                            <img src="<?php echo e(Storage::url($ad->image_path)); ?>" id="img-preview"
                                class="h-32 w-auto rounded-xl object-cover border border-white/10">
                        </div>
                    <?php else: ?>
                        <img id="img-preview" src=""
                            class="h-32 w-auto rounded-xl object-cover border border-white/10 hidden">
                    <?php endif; ?>
                    <input type="file" name="image" accept="image/*" id="image-input"
                        class="block w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-600 file:text-white hover:file:bg-blue-700 cursor-pointer"
                        onchange="previewImage(this)">
                    <p class="text-xs text-slate-500">Maks 2MB — JPG, PNG, GIF, WEBP. Resolusi ideal: 600×300px.</p>
                </div>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Label Tombol CTA</label>
                    <input type="text" name="button_label" value="<?php echo e(old('button_label', $ad->button_label)); ?>"
                        maxlength="100"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="Lihat Promo">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">URL Tombol CTA</label>
                    <input type="url" name="button_url" value="<?php echo e(old('button_url', $ad->button_url)); ?>"
                        maxlength="500"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                        placeholder="https://...">
                </div>
            </div>
        </div>

        
        <div class="bg-[#1e293b] border border-white/10 rounded-2xl p-6 space-y-5">
            <h3 class="text-sm font-semibold text-slate-300 uppercase tracking-wider">Target & Jadwal</h3>

            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Target Tenant <span
                        class="text-red-400">*</span></label>
                <div class="flex flex-col sm:flex-row gap-3">
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                        :class="target === 'all' ? 'border-blue-500 bg-blue-500/10' :
                            'border-white/10 bg-white/5 hover:border-white/20'">
                        <input type="radio" name="target" value="all" x-model="target"
                            class="text-blue-600 border-white/20 bg-transparent">
                        <div>
                            <p class="text-sm font-medium text-white">Semua Tenant</p>
                            <p class="text-xs text-slate-400">Tampil ke semua pengguna aktif</p>
                        </div>
                    </label>
                    <label class="flex items-center gap-3 px-4 py-3 rounded-xl border cursor-pointer transition"
                        :class="target === 'specific' ? 'border-blue-500 bg-blue-500/10' :
                            'border-white/10 bg-white/5 hover:border-white/20'">
                        <input type="radio" name="target" value="specific" x-model="target"
                            class="text-blue-600 border-white/20 bg-transparent">
                        <div>
                            <p class="text-sm font-medium text-white">Tenant Tertentu</p>
                            <p class="text-xs text-slate-400">Pilih tenant yang ditarget</p>
                        </div>
                    </label>
                </div>
            </div>

            
            <div x-show="target === 'specific'" x-transition>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Pilih Tenant</label>
                <div
                    class="max-h-52 overflow-y-auto rounded-xl border border-white/10 bg-white/5 divide-y divide-white/5">
                    <?php $__currentLoopData = $tenants; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tenant): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="flex items-center gap-3 px-4 py-2.5 hover:bg-white/5 cursor-pointer transition">
                            <input type="checkbox" name="tenant_ids[]" value="<?php echo e($tenant->id); ?>"
                                class="rounded border-white/20 text-blue-600 bg-transparent"
                                <?php echo e(in_array($tenant->id, old('tenant_ids', $ad->tenant_ids ?? [])) ? 'checked' : ''); ?>>
                            <span class="text-sm text-slate-200"><?php echo e($tenant->name); ?></span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-1.5">Frekuensi Tampil <span
                        class="text-red-400">*</span></label>
                <select name="frequency"
                    class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="once" <?php echo e(old('frequency', $ad->frequency) === 'once' ? 'selected' : ''); ?>

                        class="bg-slate-800">Sekali saja (per user)</option>
                    <option value="daily" <?php echo e(old('frequency', $ad->frequency) === 'daily' ? 'selected' : ''); ?>

                        class="bg-slate-800">Setiap hari</option>
                    <option value="always" <?php echo e(old('frequency', $ad->frequency) === 'always' ? 'selected' : ''); ?>

                        class="bg-slate-800">Selalu (setiap kunjungan)</option>
                </select>
                <p class="text-xs text-slate-500 mt-1.5">
                    <strong class="text-slate-400">Sekali saja</strong> — user yang sudah dismiss tidak akan lihat
                    lagi. |
                    <strong class="text-slate-400">Harian</strong> — muncul kembali keesokan harinya. |
                    <strong class="text-slate-400">Selalu</strong> — muncul setiap kali buka dashboard.
                </p>
            </div>

            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Mulai Tanggal</label>
                    <input type="date" name="starts_at"
                        value="<?php echo e(old('starts_at', $ad->starts_at?->format('Y-m-d'))); ?>"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan = tidak ada batas mulai</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-300 mb-1.5">Berakhir Tanggal</label>
                    <input type="date" name="ends_at"
                        value="<?php echo e(old('ends_at', $ad->ends_at?->format('Y-m-d'))); ?>"
                        class="w-full px-4 py-2.5 rounded-xl bg-white/5 border border-white/10 text-white text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-slate-500 mt-1">Kosongkan = tidak ada batas akhir</p>
                </div>
            </div>

            
            <div class="flex items-center justify-between py-3 border-t border-white/10">
                <div>
                    <p class="text-sm font-medium text-slate-200">Status Aktif</p>
                    <p class="text-xs text-slate-500">Iklan hanya tampil jika diaktifkan</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="sr-only peer"
                        <?php echo e(old('is_active', $ad->is_active ?? true) ? 'checked' : ''); ?>>
                    <div
                        class="w-11 h-6 bg-slate-600 peer-focus:ring-2 peer-focus:ring-blue-500 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-blue-600">
                    </div>
                </label>
            </div>
        </div>

        
        <div class="flex items-center gap-3">
            <button type="submit"
                class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold rounded-xl transition">
                <?php echo e($ad->exists ? 'Simpan Perubahan' : 'Buat Iklan'); ?>

            </button>
            <a href="<?php echo e(route('super-admin.popup-ads.index')); ?>"
                class="px-5 py-2.5 text-sm text-slate-400 hover:text-white border border-white/10 rounded-xl hover:bg-white/5 transition">
                Batal
            </a>
        </div>
    </form>

    <?php $__env->startPush('scripts'); ?>
        <script>
            function previewImage(input) {
                const preview = document.getElementById('img-preview');
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = e => {
                        preview.src = e.target.result;
                        preview.classList.remove('hidden');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            }

            function popupAdForm() {
                return {
                    target: '<?php echo e(old('target', $ad->target ?? 'all')); ?>',
                };
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\super-admin\popup-ads\form.blade.php ENDPATH**/ ?>