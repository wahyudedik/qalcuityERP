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
     <?php $__env->slot('header', null, []); ?> Surat Peringatan & Disiplin <?php $__env->endSlot(); ?>

    
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-gray-200 dark:border-white/10">
            <p class="text-xs text-gray-500 dark:text-slate-400">SP Aktif</p>
            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1"><?php echo e($summary['active']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-yellow-500/20">
            <p class="text-xs text-gray-500 dark:text-slate-400">SP I Aktif</p>
            <p class="text-2xl font-bold text-yellow-500 mt-1"><?php echo e($summary['sp1']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-orange-500/20">
            <p class="text-xs text-gray-500 dark:text-slate-400">SP II Aktif</p>
            <p class="text-2xl font-bold text-orange-500 mt-1"><?php echo e($summary['sp2']); ?></p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl p-4 border border-red-500/20">
            <p class="text-xs text-gray-500 dark:text-slate-400">SP III Aktif</p>
            <p class="text-2xl font-bold text-red-500 mt-1"><?php echo e($summary['sp3']); ?></p>
        </div>
    </div>

    <div class="flex flex-col lg:flex-row gap-5">

        
        <div class="lg:w-72 shrink-0 space-y-4">

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="font-semibold text-gray-900 dark:text-white text-sm">Terbitkan SP</h3>
                    <button onclick="openAiDraft()"
                        class="text-xs px-2.5 py-1 bg-purple-600/80 hover:bg-purple-600 text-white rounded-lg flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z" />
                        </svg>
                        AI Draft
                    </button>
                </div>
                <form method="POST" action="<?php echo e(route('hrm.disciplinary.store')); ?>" id="form-sp" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Karyawan
                            *</label>
                        <select name="employee_id" id="sp-employee" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih karyawan...</option>
                            <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Level
                                *</label>
                            <select name="level" id="sp-level" required
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="sp1">SP I</option>
                                <option value="sp2">SP II</option>
                                <option value="sp3">SP III</option>
                                <option value="memo">Memo</option>
                                <option value="termination">PHK</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tgl Terbit
                                *</label>
                            <input type="date" name="issued_date" required value="<?php echo e(today()->format('Y-m-d')); ?>"
                                class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Berlaku
                            Hingga</label>
                        <input type="date" name="valid_until"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jenis
                            Pelanggaran *</label>
                        <input type="text" name="violation_type" id="sp-vtype" required
                            placeholder="cth: Pelanggaran Kehadiran"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Uraian
                            Pelanggaran *</label>
                        <textarea name="violation_description" id="sp-vdesc" required rows="3"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tindakan
                            Perbaikan *</label>
                        <textarea name="corrective_action" id="sp-corrective" required rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Konsekuensi</label>
                        <textarea name="consequences" id="sp-consequences" rows="2"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none"></textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Saksi</label>
                        <select name="witnessed_by"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Tidak ada</option>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $u): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($u->id); ?>"><?php echo e($u->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <button type="submit"
                        class="w-full py-2 text-sm bg-red-600 text-white rounded-xl hover:bg-red-700">
                        Terbitkan Surat Peringatan
                    </button>
                </form>
            </div>

            
            <?php if($atRisk->count()): ?>
                <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-red-500/20 p-4">
                    <p class="text-xs font-semibold text-red-400 mb-3 uppercase tracking-wide">Karyawan Berisiko</p>
                    <div class="space-y-2">
                        <?php $__currentLoopData = $atRisk; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="flex items-center gap-2">
                                <span
                                    class="px-1.5 py-0.5 rounded text-xs font-bold <?php echo e($sp->levelColor()); ?>"><?php echo e($sp->levelLabel()); ?></span>
                                <span
                                    class="text-xs text-gray-700 dark:text-slate-300 truncate"><?php echo e($sp->employee->name ?? '-'); ?></span>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        
        <div class="flex-1 min-w-0">
            
            <form method="GET" class="flex flex-wrap gap-2 mb-4">
                <select name="level"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?php if($level === 'all'): echo 'selected'; endif; ?>>Semua Level</option>
                    <?php $__currentLoopData = ['sp1' => 'SP I', 'sp2' => 'SP II', 'sp3' => 'SP III', 'memo' => 'Memo', 'termination' => 'PHK']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v); ?>" <?php if($level === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <select name="status"
                    class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all" <?php if($status === 'all'): echo 'selected'; endif; ?>>Semua Status</option>
                    <?php $__currentLoopData = ['draft' => 'Draft', 'issued' => 'Diterbitkan', 'acknowledged' => 'Dikonfirmasi', 'expired' => 'Expired']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v => $l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($v); ?>" <?php if($status === $v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <button type="submit"
                    class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
            </form>

            <div
                class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Karyawan</th>
                                <th class="px-4 py-3 text-center">Level</th>
                                <th class="px-4 py-3 text-left hidden sm:table-cell">Pelanggaran</th>
                                <th class="px-4 py-3 text-center hidden md:table-cell">Tgl Terbit</th>
                                <th class="px-4 py-3 text-center">Status</th>
                                <th class="px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            <?php $__empty_1 = true; $__currentLoopData = $letters; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $sp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                                    <td class="px-4 py-3">
                                        <p class="font-medium text-gray-900 dark:text-white">
                                            <?php echo e($sp->employee->name ?? '-'); ?></p>
                                        <p class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($sp->letter_number); ?>

                                        </p>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span
                                            class="px-2 py-0.5 rounded-full text-xs font-semibold <?php echo e($sp->levelColor()); ?>"><?php echo e($sp->levelLabel()); ?></span>
                                        <?php if($sp->source === 'ai_anomaly'): ?>
                                            <p class="text-xs text-purple-400 mt-0.5">AI</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 hidden sm:table-cell">
                                        <p class="text-xs font-medium text-gray-700 dark:text-slate-300">
                                            <?php echo e($sp->violation_type); ?></p>
                                        <p class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-[200px]">
                                            <?php echo e($sp->violation_description); ?></p>
                                    </td>
                                    <td
                                        class="px-4 py-3 hidden md:table-cell text-center text-xs text-gray-500 dark:text-slate-400">
                                        <?php echo e($sp->issued_date->format('d M Y')); ?>

                                        <?php if($sp->valid_until): ?>
                                            <p
                                                class="text-xs <?php echo e($sp->valid_until->isPast() ? 'text-red-400' : 'text-gray-400 dark:text-slate-500'); ?>">
                                                s/d <?php echo e($sp->valid_until->format('d M Y')); ?>

                                            </p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <?php $sc = ['draft'=>'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-slate-400','issued'=>'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-400','acknowledged'=>'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400','expired'=>'bg-gray-100 text-gray-400 dark:bg-white/5 dark:text-slate-500'][$sp->status] ?? ''; ?>
                                        <span class="px-2 py-0.5 rounded-full text-xs <?php echo e($sc); ?>">
                                            <?php echo e(['draft' => 'Draft', 'issued' => 'Diterbitkan', 'acknowledged' => 'Dikonfirmasi', 'expired' => 'Expired'][$sp->status] ?? $sp->status); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <div class="flex items-center justify-center gap-1">
                                            <a href="<?php echo e(route('hrm.disciplinary.show', $sp)); ?>"
                                                class="px-2.5 py-1 text-xs bg-blue-600/80 text-white rounded-lg hover:bg-blue-600">Detail</a>
                                            <?php if($sp->status === 'issued'): ?>
                                                <form method="POST"
                                                    action="<?php echo e(route('hrm.disciplinary.acknowledge', $sp)); ?>">
                                                    <?php echo csrf_field(); ?> <?php echo method_field('PATCH'); ?>
                                                    <button type="submit"
                                                        class="px-2.5 py-1 text-xs bg-green-600/80 text-white rounded-lg hover:bg-green-600">Konfirmasi</button>
                                                </form>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                                <tr>
                                    <td colspan="6"
                                        class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Tidak ada
                                        surat peringatan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <?php if($letters->hasPages()): ?>
                    <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10"><?php echo e($letters->links()); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    
    <div id="modal-ai-draft"
        class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm">
        <div
            class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">AI Draft SP dari Anomali Absensi</p>
                </div>
                <button onclick="document.getElementById('modal-ai-draft').classList.add('hidden')"
                    class="text-gray-400 hover:text-white">✕</button>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pilih
                        Karyawan</label>
                    <select id="ai-employee-select"
                        class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih karyawan...</option>
                        <?php $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($emp->id); ?>"><?php echo e($emp->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div id="ai-draft-result" class="hidden space-y-3">
                    <div
                        class="p-3 rounded-xl bg-purple-500/10 border border-purple-500/20 text-xs text-purple-300 space-y-1">
                        <p class="font-semibold text-purple-200">Anomali Terdeteksi:</p>
                        <div id="ai-anomaly-list"></div>
                    </div>
                    <div class="p-3 rounded-xl bg-amber-500/10 border border-amber-500/20 text-xs text-amber-300">
                        <span class="font-semibold">Rekomendasi Level: </span>
                        <span id="ai-level-label" class="font-bold"></span>
                    </div>
                    <button onclick="applyAiDraft()"
                        class="w-full py-2 text-sm bg-purple-600 text-white rounded-xl hover:bg-purple-700">
                        Terapkan ke Form SP
                    </button>
                </div>
                <div id="ai-draft-loading" class="hidden text-center py-4">
                    <div
                        class="animate-spin w-6 h-6 border-2 border-purple-500 border-t-transparent rounded-full mx-auto">
                    </div>
                    <p class="text-xs text-slate-400 mt-2">Menganalisis anomali absensi...</p>
                </div>
                <div id="ai-draft-empty" class="hidden text-center py-4 text-sm text-gray-400 dark:text-slate-500">
                    Tidak ada anomali terdeteksi untuk karyawan ini.
                </div>
                <button onclick="loadAiDraft()" id="btn-ai-analyze"
                    class="w-full py-2 text-sm border border-purple-500/30 text-purple-400 rounded-xl hover:bg-purple-500/10">
                    Analisis Anomali Absensi
                </button>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
        <script>
            const ANOMALY_URL = '<?php echo e(route('hrm.ai.attendance-anomalies')); ?>';
            const AI_DRAFT_URL = '<?php echo e(route('hrm.disciplinary.ai-draft')); ?>';
            const CSRF = document.querySelector('meta[name="csrf-token"]').content;

            let aiDraftData = null;

            function openAiDraft() {
                document.getElementById('modal-ai-draft').classList.remove('hidden');
                document.getElementById('ai-draft-result').classList.add('hidden');
                document.getElementById('ai-draft-empty').classList.add('hidden');
                document.getElementById('ai-draft-loading').classList.add('hidden');
            }

            async function loadAiDraft() {
                const empId = document.getElementById('ai-employee-select').value;
                if (!empId) {
                    alert('Pilih karyawan terlebih dahulu.');
                    return;
                }

                document.getElementById('ai-draft-result').classList.add('hidden');
                document.getElementById('ai-draft-empty').classList.add('hidden');
                document.getElementById('ai-draft-loading').classList.remove('hidden');
                document.getElementById('btn-ai-analyze').disabled = true;

                try {
                    // Step 1: get anomalies for this employee
                    const res = await fetch(`${ANOMALY_URL}?months=3&employee_id=${empId}`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    const data = await res.json();

                    const empAnomalies = (data.anomalies ?? []).filter(a => String(a.employee_id) === String(empId));

                    if (!empAnomalies.length) {
                        document.getElementById('ai-draft-empty').classList.remove('hidden');
                        return;
                    }

                    // Step 2: get AI draft
                    const draftRes = await fetch(AI_DRAFT_URL, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': CSRF,
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({
                            employee_id: empId,
                            anomalies: empAnomalies[0].anomalies
                        }),
                    });
                    aiDraftData = await draftRes.json();

                    // Render anomaly list
                    const list = empAnomalies[0].anomalies.map(a => `<p>• ${esc(a.message)}</p>`).join('');
                    document.getElementById('ai-anomaly-list').innerHTML = list;
                    document.getElementById('ai-level-label').textContent = aiDraftData.suggested_level_label;
                    document.getElementById('ai-draft-result').classList.remove('hidden');

                } catch (e) {
                    alert('Gagal menganalisis anomali.');
                } finally {
                    document.getElementById('ai-draft-loading').classList.add('hidden');
                    document.getElementById('btn-ai-analyze').disabled = false;
                }
            }

            function applyAiDraft() {
                if (!aiDraftData) return;
                const empId = document.getElementById('ai-employee-select').value;

                document.getElementById('sp-employee').value = empId;
                document.getElementById('sp-level').value = aiDraftData.level;
                document.getElementById('sp-vtype').value = aiDraftData.violation_type;
                document.getElementById('sp-vdesc').value = aiDraftData.violation_description;
                document.getElementById('sp-corrective').value = aiDraftData.corrective_action;
                document.getElementById('sp-consequences').value = aiDraftData.consequences;

                document.getElementById('modal-ai-draft').classList.add('hidden');
            }

            function esc(s) {
                return String(s ?? '').replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
            }

            document.getElementById('modal-ai-draft').addEventListener('click', function(e) {
                if (e.target === this) this.classList.add('hidden');
            });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/hrm/disciplinary.blade.php ENDPATH**/ ?>