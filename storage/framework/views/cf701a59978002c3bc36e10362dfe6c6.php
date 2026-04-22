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
     <?php $__env->slot('header', null, []); ?> Buat Jurnal Baru <?php $__env->endSlot(); ?>

    <div class="max-w-4xl mx-auto space-y-5">

        <?php if($errors->any()): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <div><?php echo e($e); ?></div> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('journals.store')); ?>" id="journal-form" class="space-y-5">
            <?php echo csrf_field(); ?>

            
            <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-4">
                <h3 class="text-white font-semibold">Informasi Jurnal</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal *</label>
                        <input type="date" name="date" value="<?php echo e(old('date', date('Y-m-d'))); ?>" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div class="md:col-span-2">
                        <label class="text-xs text-gray-400 mb-1 block">Deskripsi *</label>
                        <input type="text" name="description" id="journal-description" value="<?php echo e(old('description')); ?>" required placeholder="Keterangan jurnal..."
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        
                        <div id="ai-account-suggestion" class="hidden mt-2 p-3 bg-indigo-500/10 border border-indigo-500/30 rounded-lg text-xs space-y-1">
                            <div class="flex items-center gap-1.5 text-indigo-300 font-medium mb-1">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                                Saran AI
                            </div>
                            <div id="ai-suggestion-content"></div>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Periode Akuntansi</label>
                        <select name="period_id" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="">— Pilih Periode —</option>
                            <?php $__currentLoopData = $periods; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($p->id); ?>" <?php if(old('period_id') == $p->id): echo 'selected'; endif; ?>><?php echo e($p->name); ?></option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Mata Uang</label>
                        <input type="text" name="currency_code" value="<?php echo e(old('currency_code', 'IDR')); ?>" maxlength="3"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Kurs</label>
                        <input type="number" name="currency_rate" value="<?php echo e(old('currency_rate', 1)); ?>" step="0.000001" min="0"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
            </div>

            
            <div class="bg-white/5 border border-white/10 rounded-xl p-5 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-white font-semibold">Baris Jurnal</h3>
                    <div class="text-sm text-gray-400">
                        Debit: <span id="total-debit" class="text-white font-mono">0</span> |
                        Kredit: <span id="total-credit" class="text-white font-mono">0</span> |
                        <span id="balance-status" class="text-yellow-400">Belum balance</span>
                    </div>
                </div>

                <div id="lines-container" class="space-y-2">
                    
                    <div class="line-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-4">
                            <select name="lines[0][account_id]" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                <option value="">— Pilih Akun —</option>
                                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->code); ?> - <?php echo e($acc->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="lines[0][debit]" placeholder="Debit" min="0" step="0.01"
                                class="debit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="lines[0][credit]" placeholder="Kredit" min="0" step="0.01"
                                class="credit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="col-span-2">
                            <input type="text" name="lines[0][description]" placeholder="Keterangan"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                    
                    <div class="line-row grid grid-cols-12 gap-2 items-start">
                        <div class="col-span-4">
                            <select name="lines[1][account_id]" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                                <option value="">— Pilih Akun —</option>
                                <?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($acc->id); ?>"><?php echo e($acc->code); ?> - <?php echo e($acc->name); ?></option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="lines[1][debit]" placeholder="Debit" min="0" step="0.01"
                                class="debit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="col-span-3">
                            <input type="number" name="lines[1][credit]" placeholder="Kredit" min="0" step="0.01"
                                class="credit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                        <div class="col-span-2">
                            <input type="text" name="lines[1][description]" placeholder="Keterangan"
                                class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        </div>
                    </div>
                </div>

                <button type="button" id="add-line" class="text-indigo-400 hover:text-indigo-300 text-sm">+ Tambah Baris</button>
            </div>

            <div class="flex gap-3">
                
                <div id="ai-anomaly-panel" class="hidden w-full mb-3 p-3 rounded-lg border text-xs space-y-1"></div>
            </div>
            <div class="flex gap-3">
                <button type="button" id="btn-check-anomaly"
                    class="bg-amber-600/80 hover:bg-amber-600 text-white px-4 py-2 rounded-lg text-sm flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>
                    Cek Anomali
                </button>
                <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white px-6 py-2 rounded-lg text-sm">Simpan sebagai Draft</button>
                <a href="<?php echo e(route('journals.index')); ?>" class="bg-white/10 hover:bg-white/20 text-white px-6 py-2 rounded-lg text-sm">Batal</a>
            </div>
        </form>
    </div>

    <script>
    const accountOptions = `<?php $__currentLoopData = $accounts; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $acc): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><option value="<?php echo e($acc->id); ?>"><?php echo e($acc->code); ?> - <?php echo e($acc->name); ?></option><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>`;
    let lineCount = 2;

    function updateTotals() {
        let debit = 0, credit = 0;
        document.querySelectorAll('.debit-input').forEach(i => debit += parseFloat(i.value || 0));
        document.querySelectorAll('.credit-input').forEach(i => credit += parseFloat(i.value || 0));
        document.getElementById('total-debit').textContent = debit.toLocaleString('id-ID');
        document.getElementById('total-credit').textContent = credit.toLocaleString('id-ID');
        const balanced = Math.abs(debit - credit) < 0.01 && debit > 0;
        const el = document.getElementById('balance-status');
        el.textContent = balanced ? '✓ Balance' : 'Belum balance';
        el.className = balanced ? 'text-green-400' : 'text-yellow-400';
    }

    document.addEventListener('input', e => {
        if (e.target.classList.contains('debit-input') || e.target.classList.contains('credit-input')) updateTotals();
    });

    document.getElementById('add-line').addEventListener('click', () => {
        const idx = lineCount++;
        const div = document.createElement('div');
        div.className = 'line-row grid grid-cols-12 gap-2 items-start';
        div.innerHTML = `
            <div class="col-span-4">
                <select name="lines[${idx}][account_id]" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">— Pilih Akun —</option>${accountOptions}
                </select>
            </div>
            <div class="col-span-3">
                <input type="number" name="lines[${idx}][debit]" placeholder="Debit" min="0" step="0.01"
                    class="debit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-3">
                <input type="number" name="lines[${idx}][credit]" placeholder="Kredit" min="0" step="0.01"
                    class="credit-input w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-1">
                <input type="text" name="lines[${idx}][description]" placeholder="Ket."
                    class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
            </div>
            <div class="col-span-1">
                <button type="button" onclick="this.closest('.line-row').remove(); updateTotals();"
                    class="text-red-400 hover:text-red-300 text-lg leading-none px-2 py-2">×</button>
            </div>`;
        document.getElementById('lines-container').appendChild(div);
    });

    // ── AI: Account Suggestion ────────────────────────────────────
    let suggestTimer = null;
    const descInput  = document.getElementById('journal-description');
    const aiPanel    = document.getElementById('ai-account-suggestion');
    const aiContent  = document.getElementById('ai-suggestion-content');

    descInput.addEventListener('input', () => {
        clearTimeout(suggestTimer);
        const val = descInput.value.trim();
        if (val.length < 4) { aiPanel.classList.add('hidden'); return; }
        suggestTimer = setTimeout(() => fetchAccountSuggestion(val), 600);
    });

    function fetchAccountSuggestion(description) {
        const totalDebit = Array.from(document.querySelectorAll('.debit-input'))
            .reduce((s, i) => s + parseFloat(i.value || 0), 0);
        const url = `<?php echo e(route('accounting.ai.suggest-accounts')); ?>?description=${encodeURIComponent(description)}&amount=${totalDebit}`;
        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                if (!data.suggestions?.length) { aiPanel.classList.add('hidden'); return; }
                const s = data.suggestions[0];
                const badge = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-gray-400' }[s.confidence] ?? 'text-gray-400';
                aiContent.innerHTML = `
                    <div class="flex flex-wrap gap-2 items-center">
                        <span class="text-gray-300">Debit:</span>
                        <button type="button" onclick="applyAccount('debit', ${s.debit_account_id})"
                            class="px-2 py-0.5 bg-indigo-500/20 hover:bg-indigo-500/40 text-indigo-300 rounded cursor-pointer transition">
                            ${s.debit_account_code} — ${s.debit_account_name}
                        </button>
                        <span class="text-gray-300 ml-2">Kredit:</span>
                        <button type="button" onclick="applyAccount('credit', ${s.credit_account_id})"
                            class="px-2 py-0.5 bg-purple-500/20 hover:bg-purple-500/40 text-purple-300 rounded cursor-pointer transition">
                            ${s.credit_account_code} — ${s.credit_account_name}
                        </button>
                        <span class="${badge} ml-auto">${s.basis}</span>
                    </div>`;
                aiPanel.classList.remove('hidden');
            })
            .catch(() => aiPanel.classList.add('hidden'));
    }

    function applyAccount(side, accountId) {
        // Apply to first empty select of that side (debit = first row, credit = second row)
        const rows = document.querySelectorAll('.line-row');
        for (const row of rows) {
            const sel = row.querySelector('select[name*="account_id"]');
            const debitInput  = row.querySelector('.debit-input');
            const creditInput = row.querySelector('.credit-input');
            if (!sel) continue;
            const hasDebit  = parseFloat(debitInput?.value || 0) > 0;
            const hasCredit = parseFloat(creditInput?.value || 0) > 0;
            if (side === 'debit' && hasDebit && !hasCredit) { sel.value = accountId; return; }
            if (side === 'credit' && hasCredit && !hasDebit) { sel.value = accountId; return; }
            if (!sel.value) { sel.value = accountId; return; }
        }
        // fallback: set first row
        const firstSel = document.querySelector('.line-row select[name*="account_id"]');
        if (firstSel) firstSel.value = accountId;
    }

    // ── AI: Anomaly Check ─────────────────────────────────────────
    document.getElementById('btn-check-anomaly').addEventListener('click', () => {
        const lines = [];
        document.querySelectorAll('.line-row').forEach(row => {
            const accountId = row.querySelector('select[name*="account_id"]')?.value;
            const debit     = parseFloat(row.querySelector('.debit-input')?.value || 0);
            const credit    = parseFloat(row.querySelector('.credit-input')?.value || 0);
            if (accountId) lines.push({ account_id: parseInt(accountId), debit, credit });
        });

        const totalDebit = lines.reduce((s, l) => s + l.debit, 0);
        const payload = {
            lines,
            date:         document.querySelector('input[name="date"]').value,
            description:  descInput.value,
            total_amount: totalDebit,
            _token:       document.querySelector('meta[name="csrf-token"]')?.content
                          ?? '<?php echo e(csrf_token()); ?>',
        };

        const btn = document.getElementById('btn-check-anomaly');
        btn.disabled = true;
        btn.textContent = 'Memeriksa...';

        fetch('<?php echo e(route("accounting.ai.check-journal")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload),
        })
        .then(r => r.json())
        .then(data => {
            const panel = document.getElementById('ai-anomaly-panel');
            if (data.risk === 'none') {
                panel.className = 'w-full mb-3 p-3 rounded-lg border text-xs bg-green-500/10 border-green-500/30 text-green-300';
                panel.innerHTML = '✓ Tidak ada anomali terdeteksi. Jurnal terlihat normal.';
            } else {
                const colors = { high: 'bg-red-500/10 border-red-500/30 text-red-300', medium: 'bg-amber-500/10 border-amber-500/30 text-amber-300', low: 'bg-yellow-500/10 border-yellow-500/30 text-yellow-300' };
                panel.className = `w-full mb-3 p-3 rounded-lg border text-xs space-y-1 ${colors[data.risk] ?? colors.low}`;
                let html = `<div class="font-medium mb-1">⚠ Hasil Pemeriksaan AI (risiko: ${data.risk.toUpperCase()})</div>`;
                data.errors?.forEach(e => html += `<div>🔴 ${e}</div>`);
                data.warnings?.forEach(w => html += `<div>🟡 ${w}</div>`);
                panel.innerHTML = html;
            }
            panel.classList.remove('hidden');
        })
        .catch(() => {})
        .finally(() => { btn.disabled = false; btn.innerHTML = '<svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/></svg>Cek Anomali'; });
    });
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\accounting\journals\create.blade.php ENDPATH**/ ?>