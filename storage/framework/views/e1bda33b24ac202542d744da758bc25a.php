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
     <?php $__env->slot('header', null, []); ?> 
        <div class="flex items-center gap-3">
            <a href="<?php echo e(route('payroll.index')); ?>" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            Komponen Gaji
        </div>
     <?php $__env->endSlot(); ?>

    <div class="flex flex-col lg:flex-row gap-6">

        
        <div class="w-full lg:w-72 shrink-0 space-y-4">
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <h3 class="font-semibold text-gray-900 dark:text-white mb-4" id="form-title">Tambah Komponen</h3>
                <form method="POST" id="comp-form" action="<?php echo e(route('payroll.components.store')); ?>" class="space-y-3">
                    <?php echo csrf_field(); ?>
                    <input type="hidden" name="_method" id="form-method" value="POST">
                    <input type="hidden" name="_comp_id" id="form-comp-id" value="">

                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Komponen *</label>
                        <input type="text" name="name" id="f-name" required placeholder="e.g. Tunjangan Transport"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kode</label>
                        <input type="text" name="code" id="f-code" placeholder="T_TRANSPORT"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="grid grid-cols-2 gap-2">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe *</label>
                            <select name="type" id="f-type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="allowance">Tunjangan</option>
                                <option value="deduction">Potongan</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Perhitungan *</label>
                            <select name="calc_type" id="f-calc" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="fixed">Nominal Tetap</option>
                                <option value="percent_base">% Gaji Pokok</option>
                            </select>
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1" id="amount-label">Nilai Default *</label>
                        <input type="number" name="default_amount" id="f-amount" min="0" step="0.01" required
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <p class="text-xs text-gray-400 dark:text-slate-500 mt-1" id="amount-hint">Masukkan nominal dalam Rupiah</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="taxable" id="f-taxable" value="1" class="rounded">
                        <label for="f-taxable" class="text-sm text-gray-700 dark:text-slate-300">Kena PPh 21</label>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Keterangan</label>
                        <input type="text" name="description" id="f-desc" placeholder="Opsional"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="flex-1 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                        <button type="button" onclick="resetForm()" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                    </div>
                </form>
            </div>
        </div>

        
        <div class="flex-1 space-y-6">

            <?php if(session('success')): ?>
            <div class="bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 text-green-700 dark:text-green-400 px-4 py-3 rounded-xl text-sm">
                <?php echo e(session('success')); ?>

            </div>
            <?php endif; ?>

            
            <?php $__currentLoopData = ['allowance' => 'Tunjangan', 'deduction' => 'Potongan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $type => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php $group = $components->where('type', $type); ?>
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                    <p class="font-semibold text-gray-900 dark:text-white"><?php echo e($label); ?></p>
                    <span class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($group->count()); ?> komponen</span>
                </div>
                <?php if($group->isEmpty()): ?>
                <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada komponen <?php echo e(strtolower($label)); ?>.</div>
                <?php else: ?>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__currentLoopData = $group; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $comp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2">
                                <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($comp->name); ?></p>
                                <?php if($comp->code): ?>
                                <span class="text-xs text-gray-400 dark:text-slate-500 font-mono"><?php echo e($comp->code); ?></span>
                                <?php endif; ?>
                                <?php if(!$comp->is_active): ?>
                                <span class="text-xs bg-gray-100 dark:bg-white/10 text-gray-500 dark:text-slate-400 px-1.5 py-0.5 rounded">Nonaktif</span>
                                <?php endif; ?>
                                <?php if($comp->taxable): ?>
                                <span class="text-xs bg-amber-100 dark:bg-amber-500/20 text-amber-700 dark:text-amber-400 px-1.5 py-0.5 rounded">PPh21</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-xs text-gray-500 dark:text-slate-400 mt-0.5">
                                <?php if($comp->calc_type === 'percent_base'): ?>
                                    <?php echo e($comp->default_amount); ?>% dari gaji pokok
                                <?php else: ?>
                                    Rp <?php echo e(number_format($comp->default_amount, 0, ',', '.')); ?>

                                <?php endif; ?>
                                <?php if($comp->description): ?> · <?php echo e($comp->description); ?> <?php endif; ?>
                            </p>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <button onclick="editComp(<?php echo e($comp->id); ?>, '<?php echo e(addslashes($comp->name)); ?>', '<?php echo e($comp->code); ?>', '<?php echo e($comp->type); ?>', '<?php echo e($comp->calc_type); ?>', <?php echo e($comp->default_amount); ?>, <?php echo e($comp->taxable ? 1 : 0); ?>, '<?php echo e(addslashes($comp->description ?? '')); ?>')"
                                class="p-1.5 text-gray-400 hover:text-blue-500 dark:hover:text-blue-400 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <form method="POST" action="<?php echo e(route('payroll.components.destroy', $comp)); ?>" onsubmit="return confirm('Hapus komponen ini?')">
                                <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                <button type="submit" class="p-1.5 text-gray-400 hover:text-red-500 dark:hover:text-red-400 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php endif; ?>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
                <div class="px-5 py-3 border-b border-gray-100 dark:border-white/10">
                    <p class="font-semibold text-gray-900 dark:text-white">Komponen per Karyawan</p>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-0.5">Klik karyawan untuk mengatur komponen gaji individual</p>
                </div>
                <div class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $employees; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $emp): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="px-5 py-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-blue-100 dark:bg-blue-500/20 flex items-center justify-center shrink-0">
                            <span class="text-blue-600 dark:text-blue-400 text-xs font-bold"><?php echo e(strtoupper(substr($emp->name, 0, 1))); ?></span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-gray-900 dark:text-white"><?php echo e($emp->name); ?></p>
                            <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($emp->position ?? '-'); ?> · <?php echo e($emp->department ?? '-'); ?></p>
                        </div>
                        <button onclick="openEmpModal(<?php echo e($emp->id); ?>, '<?php echo e(addslashes($emp->name)); ?>')"
                            class="px-3 py-1.5 text-xs bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-500/20 border border-blue-200 dark:border-blue-500/30">
                            Atur Komponen
                        </button>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="px-5 py-8 text-center text-sm text-gray-400 dark:text-slate-500">Belum ada karyawan aktif.</div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    
    <div id="emp-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/50 backdrop-blur-sm" onclick="closeEmpModal()"></div>
        <div class="relative bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg shadow-2xl">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100 dark:border-white/10">
                <p class="font-semibold text-gray-900 dark:text-white" id="modal-emp-name">Komponen Gaji</p>
                <button onclick="closeEmpModal()" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5 space-y-3 max-h-[60vh] overflow-y-auto" id="modal-body">
                <div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">Memuat...</div>
            </div>
            <div class="px-5 py-4 border-t border-gray-100 dark:border-white/10 flex justify-end gap-2">
                <button onclick="closeEmpModal()" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 rounded-xl hover:bg-gray-50 dark:hover:bg-white/5">Batal</button>
                <button onclick="saveEmpComponents()" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let currentEmpId = null;
    let allComponents = [];
    let empRows = [];

    // ── Calc type hint ──────────────────────────────────────
    document.getElementById('f-calc').addEventListener('change', function() {
        const hint = document.getElementById('amount-hint');
        const label = document.getElementById('amount-label');
        if (this.value === 'percent_base') {
            hint.textContent = 'Masukkan persentase (e.g. 5 = 5%)';
            label.textContent = 'Persentase Default *';
        } else {
            hint.textContent = 'Masukkan nominal dalam Rupiah';
            label.textContent = 'Nilai Default *';
        }
    });

    // ── Edit komponen ───────────────────────────────────────
    function editComp(id, name, code, type, calc, amount, taxable, desc) {
        document.getElementById('form-title').textContent = 'Edit Komponen';
        document.getElementById('form-method').value = 'PUT';
        document.getElementById('form-comp-id').value = id;
        document.getElementById('comp-form').action = '<?php echo e(url("payroll/components")); ?>/' + id;
        document.getElementById('f-name').value = name;
        document.getElementById('f-code').value = code;
        document.getElementById('f-type').value = type;
        document.getElementById('f-calc').value = calc;
        document.getElementById('f-amount').value = amount;
        document.getElementById('f-taxable').checked = taxable == 1;
        document.getElementById('f-desc').value = desc;
        document.getElementById('f-calc').dispatchEvent(new Event('change'));
        document.getElementById('f-name').focus();
    }

    function resetForm() {
        document.getElementById('form-title').textContent = 'Tambah Komponen';
        document.getElementById('form-method').value = 'POST';
        document.getElementById('form-comp-id').value = '';
        document.getElementById('comp-form').action = '<?php echo e(route("payroll.components.store")); ?>';
        document.getElementById('comp-form').reset();
    }

    // ── Modal karyawan ──────────────────────────────────────
    async function openEmpModal(empId, empName) {
        currentEmpId = empId;
        document.getElementById('modal-emp-name').textContent = empName;
        document.getElementById('emp-modal').classList.remove('hidden');
        document.getElementById('modal-body').innerHTML = '<div class="text-center py-8 text-gray-400 dark:text-slate-500 text-sm">Memuat...</div>';

        const res = await fetch('<?php echo e(url("payroll/components/employee")); ?>/' + empId + '/json');
        const data = await res.json();
        allComponents = data.all;
        empRows = data.assigned.map(a => ({ comp_id: a.comp_id, name: a.name, type: a.type, calc_type: a.calc_type, amount: a.amount }));
        renderModal();
    }

    function closeEmpModal() {
        document.getElementById('emp-modal').classList.add('hidden');
        currentEmpId = null;
    }

    function renderModal() {
        const body = document.getElementById('modal-body');
        let html = '';

        // Existing rows
        empRows.forEach((row, i) => {
            const comp = allComponents.find(c => c.id == row.comp_id);
            const typeLabel = row.type === 'allowance' ? 'Tunjangan' : 'Potongan';
            const typeColor = row.type === 'allowance'
                ? 'bg-green-100 text-green-700 dark:bg-green-500/20 dark:text-green-400'
                : 'bg-red-100 text-red-700 dark:bg-red-500/20 dark:text-red-400';
            const amountLabel = row.calc_type === 'percent_base' ? '%' : 'Rp';
            html += `
            <div class="flex items-center gap-3 p-3 bg-gray-50 dark:bg-white/5 rounded-xl">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="text-sm font-medium text-gray-900 dark:text-white">${row.name}</span>
                        <span class="text-xs px-1.5 py-0.5 rounded ${typeColor}">${typeLabel}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500 dark:text-slate-400">${amountLabel}</span>
                        <input type="number" min="0" step="0.01" value="${row.amount}"
                            onchange="empRows[${i}].amount = parseFloat(this.value) || 0"
                            class="w-32 px-2 py-1 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-white dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <button onclick="removeEmpRow(${i})" class="p-1.5 text-gray-400 hover:text-red-500 rounded-lg hover:bg-red-50 dark:hover:bg-red-500/10">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>`;
        });

        // Add new row
        const assignedIds = empRows.map(r => r.comp_id);
        const available = allComponents.filter(c => !assignedIds.includes(c.id));
        if (available.length > 0) {
            html += `
            <div class="flex items-center gap-2 pt-2 border-t border-gray-100 dark:border-white/10">
                <select id="new-comp-select" class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">-- Pilih komponen --</option>
                    ${available.map(c => `<option value="${c.id}" data-name="${c.name}" data-type="${c.type}" data-calc="${c.calc_type}" data-default="${c.default_amount}">${c.name} (${c.type === 'allowance' ? 'Tunjangan' : 'Potongan'})</option>`).join('')}
                </select>
                <button onclick="addEmpRow()" class="px-3 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Tambah</button>
            </div>`;
        }

        if (empRows.length === 0 && available.length === 0) {
            html = '<p class="text-center text-sm text-gray-400 dark:text-slate-500 py-4">Semua komponen sudah di-assign.</p>';
        }

        body.innerHTML = html || '<p class="text-center text-sm text-gray-400 dark:text-slate-500 py-4">Belum ada komponen. Tambahkan komponen master terlebih dahulu.</p>';
    }

    function addEmpRow() {
        const sel = document.getElementById('new-comp-select');
        const opt = sel.options[sel.selectedIndex];
        if (!opt.value) return;
        empRows.push({
            comp_id: parseInt(opt.value),
            name: opt.dataset.name,
            type: opt.dataset.type,
            calc_type: opt.dataset.calc,
            amount: parseFloat(opt.dataset.default) || 0,
        });
        renderModal();
    }

    function removeEmpRow(i) {
        empRows.splice(i, 1);
        renderModal();
    }

    async function saveEmpComponents() {
        const payload = { components: empRows.map(r => ({ component_id: r.comp_id, amount: r.amount })) };
        const res = await fetch('<?php echo e(url("payroll/components/employee")); ?>/' + currentEmpId + '/save', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
            body: JSON.stringify(payload),
        });
        if (res.ok) {
            closeEmpModal();
            window.location.reload();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\payroll\components.blade.php ENDPATH**/ ?>