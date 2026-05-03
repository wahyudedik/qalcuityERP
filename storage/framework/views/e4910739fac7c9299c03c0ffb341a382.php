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
     <?php $__env->slot('header', null, []); ?> Pelamar — <?php echo e($posting->title); ?> <?php $__env->endSlot(); ?>

    
    <?php
    $stages = ['applied'=>'Lamaran','screening'=>'Seleksi','interview'=>'Interview','offer'=>'Penawaran','hired'=>'Diterima','rejected'=>'Ditolak'];
    ?>
    <div class="flex flex-wrap gap-2 mb-5">
        <a href="<?php echo e(route('hrm.recruitment.applications', $posting)); ?>"
           class="px-3 py-1.5 text-xs rounded-xl border <?php echo e(!request('stage') ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'); ?>">
            Semua (<?php echo e($stageCounts->sum()); ?>)
        </a>
        <?php $__currentLoopData = $stages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <a href="<?php echo e(route('hrm.recruitment.applications', [$posting, 'stage' => $key])); ?>"
           class="px-3 py-1.5 text-xs rounded-xl border <?php echo e(request('stage') === $key ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50'); ?>">
            <?php echo e($label); ?> (<?php echo e($stageCounts[$key] ?? 0); ?>)
        </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    <div class="flex items-center justify-between mb-4">
        <a href="<?php echo e(route('hrm.recruitment.index')); ?>" class="text-sm text-blue-500 hover:underline">← Kembali ke Lowongan</a>
        <button onclick="document.getElementById('modal-add-app').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah Pelamar</button>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Pelamar</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Kontak</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left hidden md:table-cell">Interview</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Gaji Ditawarkan</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__empty_1 = true; $__currentLoopData = $apps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $app): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900"><?php echo e($app->applicant_name); ?></p>
                            <?php if($app->notes): ?><p class="text-xs text-gray-400 truncate max-w-[180px]"><?php echo e($app->notes); ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500">
                            <?php if($app->applicant_email): ?><p><?php echo e($app->applicant_email); ?></p><?php endif; ?>
                            <?php if($app->applicant_phone): ?><p><?php echo e($app->applicant_phone); ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($app->stageBadgeClass()); ?>"><?php echo e($app->stageLabel()); ?></span>
                        </td>
                        <td class="px-4 py-3 hidden md:table-cell text-xs text-gray-500">
                            <?php if($app->interview_date): ?>
                            <p><?php echo e($app->interview_date->format('d M Y')); ?></p>
                            <?php if($app->interview_location): ?><p><?php echo e($app->interview_location); ?></p><?php endif; ?>
                            <?php else: ?><span class="text-gray-300">—</span><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">
                            <?php echo e($app->offered_salary ? 'Rp '.number_format($app->offered_salary,0,',','.') : '—'); ?>

                        </td>
                        <td class="px-4 py-3 text-center">
                            <button onclick="openUpdateStage(<?php echo e($app->id); ?>, '<?php echo e(addslashes($app->applicant_name)); ?>', '<?php echo e($app->stage); ?>', '<?php echo e($app->interview_date?->format('Y-m-d') ?? ''); ?>', '<?php echo e(addslashes($app->interview_location ?? '')); ?>', <?php echo e($app->offered_salary ?? 'null'); ?>, '<?php echo e($app->expected_join_date?->format('Y-m-d') ?? ''); ?>', <?php echo e(json_encode($app->notes)); ?>)"
                                class="px-3 py-1.5 text-xs border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">
                                Update Status
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400">Belum ada pelamar.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($apps->hasPages()): ?>
        <div class="px-4 py-3 border-t border-gray-100"><?php echo e($apps->links()); ?></div>
        <?php endif; ?>
    </div>

    
    <div id="modal-add-app" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Tambah Pelamar</h3>
                <button onclick="document.getElementById('modal-add-app').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('hrm.recruitment.application.store', $posting)); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama Pelamar *</label>
                    <input type="text" name="applicant_name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">Email</label>
                        <input type="email" name="applicant_email" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 mb-1">No. HP</label>
                        <input type="text" name="applicant_phone" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-add-app').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-update-stage" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-lg shadow-xl max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 sticky top-0 bg-white">
                <h3 id="stage-modal-title" class="font-semibold text-gray-900 text-sm">Update Status Pelamar</h3>
                <button onclick="document.getElementById('modal-update-stage').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form id="form-update-stage" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Status / Tahap</label>
                    <select id="us-stage" name="stage" onchange="toggleStageFields(this.value)"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
                        <option value="applied">Lamaran Masuk</option>
                        <option value="screening">Seleksi</option>
                        <option value="interview">Interview</option>
                        <option value="offer">Penawaran</option>
                        <option value="hired">Diterima ✓</option>
                        <option value="rejected">Ditolak ✗</option>
                    </select>
                </div>
                <div id="fields-interview" class="space-y-3 hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Tanggal Interview</label>
                            <input type="date" id="us-interview-date" name="interview_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Lokasi / Link</label>
                            <input type="text" id="us-interview-loc" name="interview_location" placeholder="Ruang meeting / Zoom"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                </div>
                <div id="fields-offer" class="space-y-3 hidden">
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Gaji Ditawarkan</label>
                            <input type="number" id="us-salary" name="offered_salary" min="0" step="100000"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">Rencana Mulai Kerja</label>
                            <input type="date" id="us-join-date" name="expected_join_date"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="bg-green-500/10 border border-green-500/20 rounded-xl p-3 text-xs text-green-400">
                        Jika status diubah ke <strong>Diterima</strong>, karyawan baru akan otomatis dibuat dan onboarding dimulai.
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Catatan Internal</label>
                    <textarea id="us-notes" name="notes" rows="2"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-update-stage').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openUpdateStage(id, name, stage, interviewDate, interviewLoc, salary, joinDate, notes) {
        document.getElementById('stage-modal-title').textContent = 'Update Status — ' + name;
        document.getElementById('form-update-stage').action = '<?php echo e(url("hrm/recruitment/applications")); ?>/' + id + '/stage';
        document.getElementById('us-stage').value          = stage;
        document.getElementById('us-interview-date').value = interviewDate;
        document.getElementById('us-interview-loc').value  = interviewLoc;
        document.getElementById('us-salary').value         = salary ?? '';
        document.getElementById('us-join-date').value      = joinDate;
        document.getElementById('us-notes').value          = notes ?? '';
        toggleStageFields(stage);
        document.getElementById('modal-update-stage').classList.remove('hidden');
    }

    function toggleStageFields(stage) {
        document.getElementById('fields-interview').classList.toggle('hidden', stage !== 'interview');
        document.getElementById('fields-offer').classList.toggle('hidden', !['offer','hired'].includes(stage));
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\applications.blade.php ENDPATH**/ ?>