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
     <?php $__env->slot('header', null, []); ?> Absensi Karyawan <?php $__env->endSlot(); ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-600">Tanggal:</label>
            <input type="date" name="date" value="<?php echo e($date->format('Y-m-d')); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tampilkan</button>
        </form>
        <div class="flex gap-2 ml-auto">
            <button id="btn-anomaly-check" onclick="loadAnomalies()"
                class="px-4 py-2 text-sm bg-amber-600/80 hover:bg-amber-600 text-white rounded-xl flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Deteksi Anomali AI
            </button>
            <a href="<?php echo e(route('hrm.index')); ?>" class="px-4 py-2 text-sm border border-gray-200 rounded-xl text-gray-600 hover:bg-gray-50">← Karyawan</a>
        </div>
    </div>

    
    <div id="ai-anomaly-panel" class="hidden mb-6 bg-white border border-amber-500/30 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-amber-500/20 bg-amber-500/10">
            <div class="flex items-center gap-2 text-amber-300 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                Deteksi Anomali Absensi AI — 3 Bulan Terakhir
            </div>
            <button onclick="document.getElementById('ai-anomaly-panel').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg leading-none">✕</button>
        </div>
        <div id="ai-anomaly-content" class="p-4">
            <div class="animate-pulse text-slate-500 text-sm">Menganalisis pola absensi...</div>
        </div>
    </div>

    
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        <?php $__currentLoopData = ['present'=>['Hadir','green'],'late'=>['Terlambat','yellow'],'leave'=>['Izin','blue'],'sick'=>['Sakit','orange'],'absent'=>['Absen','red']]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key=>[$label,$color]): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white rounded-2xl p-3 border border-gray-200 text-center">
            <p class="text-xs text-gray-500"><?php echo e($label); ?></p>
            <p class="text-xl font-bold text-<?php echo e($color); ?>-600 $color }}-400 mt-1"><?php echo e($summary[$key] ?? 0); ?></p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>

    
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Absensi <?php echo e($date->format('d F Y')); ?></h3>
        </div>
        <form method="POST" action="<?php echo e(route('hrm.attendance.store')); ?>">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="date" value="<?php echo e($date->format('Y-m-d')); ?>">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Karyawan</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Jabatan</th>
                            <th class="px-4 py-3 text-left hidden lg:table-cell">Shift</th>
                            <th class="px-4 py-3 text-center">Status</th>
                            <th class="px-4 py-3 text-left hidden md:table-cell">Catatan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3">
                                <input type="hidden" name="records[<?php echo e($i); ?>][employee_id]" value="<?php echo e($emp->id); ?>">
                                <p class="font-medium text-gray-900"><?php echo e($emp->name); ?></p>
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500"><?php echo e($emp->position ?? '-'); ?></td>
                            <td class="px-4 py-3 hidden lg:table-cell">
                                <span class="shift-badge-<?php echo e($emp->id); ?> text-xs text-gray-400">—</span>
                            </td>
                            <td class="px-4 py-3">
                                <select name="records[<?php echo e($i); ?>][status]" class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <?php $__currentLoopData = ['present'=>'Hadir','late'=>'Terlambat','leave'=>'Izin','sick'=>'Sakit','absent'=>'Absen']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $val=>$lbl): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($val); ?>" <?php if(($attendances[$emp->id] ?? 'present') === $val): echo 'selected'; endif; ?>><?php echo e($lbl); ?></option>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </select>
                            </td>
                            <td class="px-4 py-3 hidden md:table-cell">
                                <input type="text" name="records[<?php echo e($i); ?>][notes]" placeholder="Keterangan..."
                                    class="w-full px-2 py-1.5 text-sm rounded-lg border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="4" class="px-4 py-10 text-center text-gray-400">Belum ada karyawan aktif.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <?php if($employees->count() > 0): ?>
            <div class="px-5 py-4 border-t border-gray-100 flex justify-end">
                <button type="submit" class="px-6 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Absensi</button>
            </div>
            <?php endif; ?>
        </form>
    </div>

    <script>
    const anomalyUrl = '<?php echo e(route("hrm.ai.attendance-anomalies")); ?>';
    const riskColor  = { high: 'text-red-400', medium: 'text-amber-400', low: 'text-yellow-300' };
    const riskBg     = { high: 'bg-red-500/10 border-red-500/20', medium: 'bg-amber-500/10 border-amber-500/20', low: 'bg-yellow-500/10 border-yellow-500/20' };

    async function loadAnomalies() {
        const panel   = document.getElementById('ai-anomaly-panel');
        const content = document.getElementById('ai-anomaly-content');
        panel.classList.remove('hidden');
        content.innerHTML = '<div class="animate-pulse text-slate-500 text-sm">Menganalisis pola absensi...</div>';

        const btn = document.getElementById('btn-anomaly-check');
        btn.disabled = true;

        try {
            const res  = await fetch(anomalyUrl + '?months=3', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();

            if (!data.anomalies?.length) {
                content.innerHTML = '<p class="text-green-400 text-sm">✓ Tidak ada anomali absensi terdeteksi dalam 3 bulan terakhir.</p>';
                return;
            }

            let html = `<p class="text-xs text-slate-400 mb-3">${data.total} karyawan dengan pola absensi tidak wajar ditemukan.</p>`;
            html += '<div class="space-y-3">';

            for (const emp of data.anomalies) {
                const bg = riskBg[emp.risk] ?? riskBg.low;
                const col = riskColor[emp.risk] ?? riskColor.low;
                html += `<div class="p-3 rounded-xl border ${bg}">
                    <div class="flex items-center justify-between mb-1.5">
                        <span class="font-medium text-white text-sm">${esc(emp.employee_name)}</span>
                        <div class="flex items-center gap-2">
                            ${emp.position ? `<span class="text-xs text-slate-400">${esc(emp.position)}</span>` : ''}
                            <span class="text-xs px-2 py-0.5 rounded-full ${bg} ${col} border">Risiko ${emp.risk.toUpperCase()}</span>
                        </div>
                    </div>
                    <ul class="space-y-0.5">`;
                for (const a of emp.anomalies) {
                    const c = riskColor[a.severity] ?? 'text-slate-400';
                    html += `<li class="text-xs ${c}">• ${esc(a.message)}</li>`;
                }
                html += `</ul></div>`;
            }

            html += '</div>';
            content.innerHTML = html;
        } catch (e) {
            content.innerHTML = '<p class="text-red-400 text-sm">Gagal memuat analisis AI.</p>';
        } finally {
            btn.disabled = false;
        }
    }

    function esc(s) {
        return String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

    // Load shift schedule for the current date
    (async function loadShifts() {
        try {
            const date = '<?php echo e($date->format("Y-m-d")); ?>';
            const res  = await fetch('<?php echo e(route("hrm.shifts.today")); ?>' + `?date=${date}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            for (const [empId, shift] of Object.entries(data.schedules ?? {})) {
                const badge = document.querySelector(`.shift-badge-${empId}`);
                if (badge) {
                    badge.innerHTML = `<span class="inline-flex items-center gap-1">
                        <span class="w-2 h-2 rounded-full inline-block" style="background:${esc(shift.shift_color)}"></span>
                        ${esc(shift.shift_name)} <span class="text-slate-500">${esc(shift.shift_time)}</span>
                    </span>`;
                }
            }
        } catch (e) { /* shift data optional */ }
    })();
    </script>
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\hrm\attendance.blade.php ENDPATH**/ ?>