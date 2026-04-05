

<?php $__env->startSection('content'); ?>
<div class="max-w-5xl mx-auto px-4 py-8 space-y-8">

    
    <div>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Profil Perusahaan</h1>
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Identitas perusahaan yang tampil di semua dokumen cetak (invoice, laporan, surat).</p>
    </div>

    <?php if(session('success')): ?>
    <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-700 dark:text-green-300 rounded-xl px-4 py-3 text-sm">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    
    <form method="POST" action="<?php echo e(route('company-profile.update')); ?>" enctype="multipart/form-data"
          class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6 space-y-6">
        <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Identitas Utama</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Perusahaan <span class="text-red-500">*</span></label>
                    <input type="text" name="name" value="<?php echo e(old('name', $tenant->name)); ?>" required
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Tagline / Slogan</label>
                    <input type="text" name="tagline" value="<?php echo e(old('tagline', $tenant->tagline)); ?>" placeholder="Solusi terbaik untuk bisnis Anda"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Email</label>
                    <input type="email" name="email" value="<?php echo e(old('email', $tenant->email)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Telepon</label>
                    <input type="text" name="phone" value="<?php echo e(old('phone', $tenant->phone)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">NPWP</label>
                    <input type="text" name="npwp" value="<?php echo e(old('npwp', $tenant->npwp)); ?>" placeholder="00.000.000.0-000.000"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Website</label>
                    <input type="url" name="website" value="<?php echo e(old('website', $tenant->website)); ?>" placeholder="https://perusahaan.com"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Alamat</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Alamat Lengkap</label>
                    <textarea name="address" rows="2"
                              class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo e(old('address', $tenant->address)); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kota</label>
                    <input type="text" name="city" value="<?php echo e(old('city', $tenant->city)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Provinsi</label>
                    <input type="text" name="province" value="<?php echo e(old('province', $tenant->province)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Kode Pos</label>
                    <input type="text" name="postal_code" value="<?php echo e(old('postal_code', $tenant->postal_code)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Rekening Bank</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Bank</label>
                    <input type="text" name="bank_name" value="<?php echo e(old('bank_name', $tenant->bank_name)); ?>" placeholder="BCA, Mandiri, BNI..."
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">No. Rekening</label>
                    <input type="text" name="bank_account" value="<?php echo e(old('bank_account', $tenant->bank_account)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Atas Nama</label>
                    <input type="text" name="bank_account_name" value="<?php echo e(old('bank_account_name', $tenant->bank_account_name)); ?>"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>
        </div>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Pengaturan Dokumen</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Catatan Footer Invoice</label>
                    <textarea name="invoice_footer_notes" rows="2" placeholder="Terima kasih atas kepercayaan Anda..."
                              class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent"><?php echo e(old('invoice_footer_notes', $tenant->invoice_footer_notes)); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Syarat Pembayaran Default</label>
                    <input type="text" name="invoice_payment_terms" value="<?php echo e(old('invoice_payment_terms', $tenant->invoice_payment_terms)); ?>"
                           placeholder="Pembayaran dalam 14 hari setelah invoice diterima"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <div class="mt-4">
                        <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Prefix Nomor Dokumen</label>
                        <input type="text" name="doc_number_prefix" value="<?php echo e(old('doc_number_prefix', $tenant->doc_number_prefix)); ?>"
                               placeholder="INV, PO, QT..."
                               class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Warna Kop Surat</label>
                    <div class="flex items-center gap-3">
                        <input type="color" name="letter_head_color" value="<?php echo e(old('letter_head_color', $tenant->letter_head_color ?? '#1d4ed8')); ?>"
                               class="h-10 w-16 rounded-lg border border-gray-300 dark:border-white/10 cursor-pointer">
                        <span class="text-sm text-gray-500 dark:text-gray-400">Warna aksen pada kop surat PDF</span>
                    </div>
                </div>
            </div>
        </div>

        
        <div>
            <h2 class="text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-4">Logo, Stempel & Tanda Tangan</h2>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

                <?php $__currentLoopData = [
                    ['field' => 'logo', 'label' => 'Logo Perusahaan', 'desc' => 'Tampil di kop surat semua dokumen'],
                    ['field' => 'stamp_image', 'label' => 'Stempel Perusahaan', 'desc' => 'Tampil di footer invoice & surat'],
                    ['field' => 'director_signature', 'label' => 'TTD Direktur', 'desc' => 'Tanda tangan default untuk surat'],
                ]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="border border-gray-200 dark:border-white/10 rounded-xl p-4">
                    <p class="text-sm font-medium text-gray-700 dark:text-slate-300 mb-1"><?php echo e($img['label']); ?></p>
                    <p class="text-xs text-gray-400 mb-3"><?php echo e($img['desc']); ?></p>

                    <?php if($tenant->{$img['field']}): ?>
                    <div class="mb-3 relative">
                        <img src="<?php echo e(Storage::url($tenant->{$img['field']})); ?>" alt="<?php echo e($img['label']); ?>"
                             class="max-h-20 max-w-full object-contain rounded-lg border border-gray-200 dark:border-white/10">
                        <form method="POST" action="<?php echo e(route('company-profile.remove-image', $img['field'])); ?>" class="mt-2">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-xs text-red-500 hover:text-red-700">Hapus gambar</button>
                        </form>
                    </div>
                    <?php endif; ?>

                    <input type="file" name="<?php echo e($img['field']); ?>" accept="image/*"
                           class="block w-full text-sm text-gray-500 dark:text-gray-400
                                  file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0
                                  file:text-xs file:font-medium file:bg-blue-50 file:text-blue-700
                                  dark:file:bg-blue-900/30 dark:file:text-blue-300
                                  hover:file:bg-blue-100 cursor-pointer">
                    <p class="text-xs text-gray-400 mt-1">PNG/JPG, maks 2MB</p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            </div>
        </div>

        <div class="flex justify-end pt-2">
            <button type="submit"
                    class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                Simpan Profil Perusahaan
            </button>
        </div>
    </form>

    
    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-gray-200 dark:border-white/10 p-6">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-white">Template Dokumen</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kustomisasi template HTML untuk setiap jenis dokumen.</p>
            </div>
            <button onclick="document.getElementById('modal-add-template').classList.remove('hidden')"
                    class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl transition-colors">
                + Tambah Template
            </button>
        </div>

        <?php if($templates->isEmpty()): ?>
        <div class="text-center py-10 text-gray-400 dark:text-gray-500">
            <svg class="w-10 h-10 mx-auto mb-3 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
            <p class="text-sm">Belum ada template. Tambahkan template kustom untuk dokumen Anda.</p>
        </div>
        <?php else: ?>
        <div class="space-y-3">
            <?php $__currentLoopData = $templates; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tpl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="flex items-center justify-between p-4 bg-gray-50 dark:bg-[#0f172a]/50 rounded-xl">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($tpl->name); ?></span>
                        <?php if($tpl->is_default): ?>
                        <span class="text-xs bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 px-2 py-0.5 rounded-full">Default</span>
                        <?php endif; ?>
                    </div>
                    <span class="text-xs text-gray-500 dark:text-gray-400"><?php echo e(\App\Models\DocumentTemplate::docTypeLabel($tpl->doc_type)); ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="editTemplate(<?php echo e($tpl->id); ?>, '<?php echo e(addslashes($tpl->name)); ?>', `<?php echo e(addslashes($tpl->html_content)); ?>`, <?php echo e($tpl->is_default ? 'true' : 'false'); ?>)"
                            class="text-xs text-blue-600 dark:text-blue-400 hover:underline">Edit</button>
                    <form method="POST" action="<?php echo e(route('company-profile.templates.destroy', $tpl)); ?>" onsubmit="return confirm('Hapus template ini?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                    </form>
                </div>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
        <?php endif; ?>
    </div>
</div>


<div id="modal-add-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Tambah Template Dokumen</h3>
        <form method="POST" action="<?php echo e(route('company-profile.templates.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Template</label>
                    <input type="text" name="name" required placeholder="Template Invoice Standar"
                           class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Jenis Dokumen</label>
                    <select name="doc_type" required class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm">
                        <option value="invoice">Invoice</option>
                        <option value="po">Purchase Order</option>
                        <option value="quotation">Penawaran (Quotation)</option>
                        <option value="letter">Surat Umum</option>
                        <option value="memo">Memo Internal</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Konten HTML</label>
                <p class="text-xs text-gray-400 mb-2">Gunakan placeholder: <code class="bg-gray-100 dark:bg-[#0f172a] px-1 rounded">{{ company_name }}</code>, <code class="bg-gray-100 dark:bg-[#0f172a] px-1 rounded">{{ date }}</code>, <code class="bg-gray-100 dark:bg-[#0f172a] px-1 rounded">{{ recipient }}</code>, <code class="bg-gray-100 dark:bg-[#0f172a] px-1 rounded">{{ npwp }}</code></p>
                <textarea name="html_content" rows="10" required
                          class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm font-mono text-xs"
                          placeholder="<div>{{ company_name }}</div>..."></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="is_default_new" class="rounded">
                <label for="is_default_new" class="text-sm text-gray-700 dark:text-slate-300">Jadikan template default untuk jenis ini</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add-template').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">Simpan</button>
            </div>
        </form>
    </div>
</div>


<div id="modal-edit-template" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white dark:bg-slate-800 rounded-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto p-6">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Edit Template</h3>
        <form id="form-edit-template" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Nama Template</label>
                <input type="text" name="name" id="edit_name" required
                       class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Konten HTML</label>
                <textarea name="html_content" id="edit_html" rows="10" required
                          class="w-full rounded-xl border border-gray-300 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white px-3 py-2 text-sm font-mono text-xs"></textarea>
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="is_default" value="1" id="edit_is_default" class="rounded">
                <label for="edit_is_default" class="text-sm text-gray-700 dark:text-slate-300">Jadikan template default</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit-template').classList.add('hidden')"
                        class="px-4 py-2 text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white">Batal</button>
                <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-xl">Perbarui</button>
            </div>
        </form>
    </div>
</div>

<script>
function editTemplate(id, name, html, isDefault) {
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_html').value = html;
    document.getElementById('edit_is_default').checked = isDefault;
    document.getElementById('form-edit-template').action = '<?php echo e(url("settings/company-profile/templates")); ?>/' + id;
    document.getElementById('modal-edit-template').classList.remove('hidden');
}
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/settings/company-profile.blade.php ENDPATH**/ ?>