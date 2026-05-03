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
     <?php $__env->slot('header', null, []); ?> Pelatihan & Sertifikasi <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Program Aktif</p>
            <p class="text-2xl font-bold text-gray-900 mt-1"><?php echo e($summary['total_programs']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sesi Tahun Ini</p>
            <p class="text-2xl font-bold text-blue-500 mt-1"><?php echo e($summary['sessions_this_year']); ?></p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sertifikat Akan Expired</p>
            <p class="text-2xl font-bold text-amber-500 mt-1"><?php echo e($summary['certs_expiring']); ?></p>
            <p class="text-xs text-gray-400">dalam 90 hari</p>
        </div>
        <div class="bg-white rounded-2xl p-4 border border-gray-200">
            <p class="text-xs text-gray-500">Sertifikat Expired</p>
            <p class="text-2xl font-bold text-red-500 mt-1"><?php echo e($summary['certs_expired']); ?></p>
        </div>
    </div>

    
    <div class="flex gap-1 mb-5 bg-gray-100 rounded-xl p-1 w-fit">
        <?php $__currentLoopData = ['sessions'=>'Sesi Pelatihan','certifications'=>'Sertifikasi','programs'=>'Program','matrix'=>'Skill Matrix']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t=>$label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(request()->fullUrlWithQuery(['tab'=>$t])); ?>"
            class="px-4 py-2 text-sm rounded-lg font-medium transition
                <?php echo e($tab===$t ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700'); ?>">
            <?php echo e($label); ?>

            <?php if($t==='certifications' && $summary['certs_expiring'] > 0): ?>
            <span class="ml-1 text-xs bg-amber-500/20 text-amber-400 px-1.5 py-0.5 rounded-full"><?php echo e($summary['certs_expiring']); ?></span>
            <?php endif; ?>
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <?php if($tab === 'sessions'): ?>
    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Jadwalkan Sesi</h3>
                <form method="POST" action="<?php echo e(route('hrm.training.sessions.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Program *</label>
                        <select name="training_program_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih program...</option>
                            <?php $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>"><?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Mulai *</label>
                            <input type="date" name="start_date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Selesai *</label>
                            <input type="date" name="end_date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi</label>
                        <input type="text" name="location" placeholder="Ruang meeting / online..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Trainer / Fasilitator</label>
                        <input type="text" name="trainer" placeholder="Nama trainer..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Maks. Peserta (0 = unlimited)</label>
                        <input type="number" name="max_participants" value="0" min="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Jadwalkan</button>
                </form>
            </div>
        </div>

        
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Program</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Trainer</th>
                                <th class="px-4 py-3 text-center">Peserta</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900"><?php echo e($s->program->name ?? '-'); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($s->program->category ?? ''); ?> <?php echo e($s->location ? '· '.$s->location : ''); ?></p>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-600 text-xs whitespace-nowrap">
                                    <?php echo e($s->start_date->format('d M Y')); ?>

                                    <?php if(!$s->start_date->eq($s->end_date)): ?>
                                    <br>s/d <?php echo e($s->end_date->format('d M Y')); ?>

                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs"><?php echo e($s->trainer ?? '—'); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-sm font-medium text-gray-900"><?php echo e($s->participants_count); ?></span>
                                    <?php if($s->max_participants > 0): ?>
                                    <span class="text-xs text-gray-400">/<?php echo e($s->max_participants); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <?php $sc = ['scheduled'=>'bg-blue-100 text-blue-700','ongoing'=>'bg-amber-100 text-amber-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-gray-100 text-gray-500'][$s->status] ?? ''; ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($sc); ?>"><?php echo e(ucfirst($s->status)); ?></span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <a href="<?php echo e(route('hrm.training.sessions.detail', $s)); ?>"
                                            class="px-2.5 py-1 text-xs bg-blue-600/80 text-white rounded-lg hover:bg-blue-600">Peserta</a>
                                        <form method="POST" action="<?php echo e(route('hrm.training.sessions.destroy', $s)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" onclick="return confirm('Hapus sesi ini?')"
                                                class="px-2.5 py-1 text-xs border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/10">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada sesi pelatihan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($sessions->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100"><?php echo e($sessions->links()); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'certifications'): ?>
    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Sertifikat</h3>
                <form method="POST" action="<?php echo e(route('hrm.training.certifications.store')); ?>" enctype="multipart/form-data" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Karyawan *</label>
                        <select name="employee_id" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Sertifikat *</label>
                        <input type="text" name="name" required placeholder="cth: ISO 9001 Lead Auditor"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Lembaga Penerbit</label>
                        <input type="text" name="issuer" placeholder="cth: BSN, TÜV, dll"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. Sertifikat</label>
                        <input type="text" name="certificate_number" placeholder="Nomor sertifikat..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tgl Terbit *</label>
                            <input type="date" name="issued_date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tgl Expired</label>
                            <input type="date" name="expiry_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Upload Scan (PDF/Gambar)</label>
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png"
                            class="w-full text-xs text-gray-600 file:mr-2 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:bg-blue-600 file:text-white hover:file:bg-blue-700">
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </form>
            </div>
        </div>

        
        <div class="flex-1 min-w-0 space-y-3">
            
            <div class="flex gap-2 flex-wrap">
                <?php $__currentLoopData = ['all'=>'Semua','expiring'=>'Akan Expired (90hr)','expired'=>'Sudah Expired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f=>$fl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <a href="<?php echo e(request()->fullUrlWithQuery(['tab'=>'certifications','cert_filter'=>$f])); ?>"
                    class="px-3 py-1.5 text-xs rounded-lg border transition
                        <?php echo e(($request->cert_filter ?? 'all') === $f
                            ? 'bg-blue-600 text-white border-blue-600'
                            : 'border-gray-200 text-gray-600 hover:bg-gray-50'); ?>">
                    <?php echo e($fl); ?>

                </a>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-left">Sertifikat</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Penerbit</th>
                                <th class="px-4 py-3 text-center hidden md:table-cell">Terbit</th>
                                <th class="px-4 py-3 text-center">Expired</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $certifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <?php $days = $cert->daysUntilExpiry(); ?>
                            <tr class="hover:bg-gray-50 <?php echo e($days !== null && $days <= 30 ? 'bg-red-50/30' : ''); ?>">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 text-xs"><?php echo e($cert->employee->name ?? '-'); ?></p>
                                    <p class="text-xs text-gray-400"><?php echo e($cert->employee->department ?? $cert->employee->position ?? ''); ?></p>
                                </td>
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900 text-xs"><?php echo e($cert->name); ?></p>
                                    <?php if($cert->certificate_number): ?>
                                    <p class="text-xs text-gray-400"><?php echo e($cert->certificate_number); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell text-gray-500 text-xs"><?php echo e($cert->issuer ?? '—'); ?></td>
                                <td class="px-4 py-3 hidden md:table-cell text-center text-xs text-gray-500"><?php echo e($cert->issued_date->format('d M Y')); ?></td>
                                <td class="px-4 py-3 text-center">
                                    <?php if($cert->expiry_date): ?>
                                    <div>
                                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($cert->expiryBadgeClass()); ?>">
                                            <?php echo e($cert->expiry_date->format('d M Y')); ?>

                                        </span>
                                        <?php if($days !== null && $days >= 0): ?>
                                        <p class="text-xs text-gray-400 mt-0.5"><?php echo e($days); ?>h lagi</p>
                                        <?php elseif($days !== null && $days < 0): ?>
                                        <p class="text-xs text-red-400 mt-0.5">Expired <?php echo e(abs($days)); ?>h lalu</p>
                                        <?php endif; ?>
                                    </div>
                                    <?php else: ?>
                                    <span class="text-xs text-gray-400">Tidak expired</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    <div class="flex items-center justify-center gap-1">
                                        <?php if($cert->file_path): ?>
                                        <a href="<?php echo e(Storage::url($cert->file_path)); ?>" target="_blank"
                                            class="px-2.5 py-1 text-xs bg-gray-100 text-gray-600 rounded-lg hover:bg-gray-200">📄</a>
                                        <?php endif; ?>
                                        <form method="POST" action="<?php echo e(route('hrm.training.certifications.destroy', $cert)); ?>">
                                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                            <button type="submit" onclick="return confirm('Hapus sertifikat ini?')"
                                                class="px-2.5 py-1 text-xs border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/10">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Tidak ada sertifikat ditemukan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($certifications->hasPages()): ?>
                <div class="px-4 py-3 border-t border-gray-100"><?php echo e($certifications->links()); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'programs'): ?>
    <div class="flex flex-col lg:flex-row gap-5">
        <div class="lg:w-72 shrink-0">
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <h3 class="font-semibold text-gray-900 mb-4 text-sm">Tambah Program</h3>
                <form method="POST" action="<?php echo e(route('hrm.training.programs.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Nama Program *</label>
                        <input type="text" name="name" required placeholder="cth: K3 Dasar"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="cth: ISO, K3, Soft Skill..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Provider / Vendor</label>
                        <input type="text" name="provider" placeholder="Nama lembaga..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Durasi (jam) *</label>
                            <input type="number" name="duration_hours" required value="8" min="1"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Biaya (Rp)</label>
                            <input type="number" name="cost" value="0" min="0"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Deskripsi</label>
                        <textarea name="description" rows="2" placeholder="Tujuan dan materi pelatihan..."
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambah Program</button>
                </form>
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Program</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Kategori</th>
                                <th class="px-4 py-3 text-left hidden md:table-cell">Provider</th>
                                <th class="px-4 py-3 text-center hidden sm:table-cell">Durasi</th>
                                <th class="px-4 py-3 text-right hidden md:table-cell">Biaya</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <?php $__empty_1 = true; $__currentLoopData = $programs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-3">
                                    <p class="font-medium text-gray-900"><?php echo e($p->name); ?></p>
                                    <?php if($p->description): ?>
                                    <p class="text-xs text-gray-400 truncate max-w-[200px]"><?php echo e($p->description); ?></p>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden sm:table-cell">
                                    <?php if($p->category): ?>
                                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-100 text-blue-700"><?php echo e($p->category); ?></span>
                                    <?php else: ?>
                                    <span class="text-gray-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 hidden md:table-cell text-gray-500 text-xs"><?php echo e($p->provider ?? '—'); ?></td>
                                <td class="px-4 py-3 hidden sm:table-cell text-center text-gray-600 text-xs"><?php echo e($p->duration_hours); ?>j</td>
                                <td class="px-4 py-3 hidden md:table-cell text-right text-gray-600 text-xs">
                                    <?php echo e($p->cost > 0 ? 'Rp '.number_format($p->cost,0,',','.') : 'Gratis'); ?>

                                </td>
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="<?php echo e(route('hrm.training.programs.destroy', $p)); ?>">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button type="submit" onclick="return confirm('Nonaktifkan program ini?')"
                                            class="px-2.5 py-1 text-xs border border-red-500/30 text-red-400 rounded-lg hover:bg-red-500/10">Nonaktifkan</button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada program pelatihan.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    
    <?php if($tab === 'matrix'): ?>
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <?php if(empty($matrix['categories'])): ?>
        <div class="p-12 text-center text-gray-400">
            <p class="text-sm">Belum ada data skill matrix.</p>
            <p class="text-xs mt-1">Data akan muncul setelah peserta pelatihan ditandai "Lulus" (passed).</p>
        </div>
        <?php else: ?>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left sticky left-0 bg-gray-50 z-10">Departemen</th>
                        <?php $__currentLoopData = $matrix['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 text-center whitespace-nowrap"><?php echo e($cat); ?></th>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <th class="px-4 py-3 text-center">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $matrix['data']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $dept => $cats): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $rowTotal = array_sum($cats); ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900 sticky left-0 bg-white"><?php echo e($dept); ?></td>
                        <?php $__currentLoopData = $matrix['categories']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php $count = $cats[$cat] ?? 0; ?>
                        <td class="px-4 py-3 text-center">
                            <?php if($count > 0): ?>
                            <span class="inline-flex items-center justify-center w-8 h-8 rounded-full text-xs font-bold
                                <?php echo e($count >= 5 ? 'bg-green-100 text-green-700' :
                                   ($count >= 2 ? 'bg-blue-100 text-blue-700' :
                                   'bg-amber-100 text-amber-700')); ?>">
                                <?php echo e($count); ?>

                            </span>
                            <?php else: ?>
                            <span class="text-gray-200">—</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <td class="px-4 py-3 text-center font-semibold text-gray-900"><?php echo e($rowTotal); ?></td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-100 flex items-center gap-4 text-xs text-gray-400">
            <span>Angka = jumlah karyawan yang lulus pelatihan kategori tersebut</span>
            <div class="flex items-center gap-3 ml-auto">
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-amber-200 inline-block"></span> 1</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-blue-200 inline-block"></span> 2–4</span>
                <span class="flex items-center gap-1"><span class="w-3 h-3 rounded-full bg-green-200 inline-block"></span> 5+</span>
            </div>
        </div>
        <?php endif; ?>
    </div>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\training.blade.php ENDPATH**/ ?>