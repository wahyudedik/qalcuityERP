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
     <?php $__env->slot('header', null, []); ?> Anggaran vs Realisasi <?php $__env->endSlot(); ?>

    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 mb-6">
        <form method="GET" class="flex gap-2">
            <input type="month" name="period" value="<?php echo e($period); ?>"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tampilkan</button>
        </form>
        <div class="flex gap-3 flex-wrap">
            <div class="bg-white dark:bg-[#1e293b] rounded-xl px-4 py-2 border border-gray-200 dark:border-white/10 text-sm">
                <span class="text-gray-500 dark:text-slate-400">Total Anggaran: </span>
                <span class="font-semibold text-gray-900 dark:text-white">Rp <?php echo e(number_format($totalBudget,0,',','.')); ?></span>
            </div>
            <div class="bg-white dark:bg-[#1e293b] rounded-xl px-4 py-2 border border-gray-200 dark:border-white/10 text-sm">
                <span class="text-gray-500 dark:text-slate-400">Realisasi: </span>
                <span class="font-semibold <?php echo e($totalRealized > $totalBudget ? 'text-red-500' : 'text-green-600 dark:text-green-400'); ?>">Rp <?php echo e(number_format($totalRealized,0,',','.')); ?></span>
            </div>
            <?php if($overCount > 0): ?>
            <div class="bg-red-50 dark:bg-red-500/10 rounded-xl px-4 py-2 border border-red-200 dark:border-red-500/20 text-sm text-red-600 dark:text-red-400 font-medium">
                ⚠️ <?php echo e($overCount); ?> over budget
            </div>
            <?php endif; ?>
        </div>
        <div class="ml-auto flex gap-2 shrink-0">
            <button id="btn-ai-suggest" onclick="openSuggestPanel()"
                class="px-4 py-2 text-sm bg-purple-600/80 hover:bg-purple-600 text-white rounded-xl flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                Saran Alokasi AI
            </button>
            <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'budget', 'create')): ?>
            <button onclick="document.getElementById('modal-add-budget').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Anggaran Baru</button>
            <?php endif; ?>
        </div>
    </div>

    
    <div id="ai-overrun-banner" class="hidden mb-4 p-4 bg-amber-500/10 border border-amber-500/30 rounded-2xl text-sm">
        <div class="flex items-center gap-2 text-amber-300 font-medium mb-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Prediksi AI — Risiko Overrun Bulan Ini
        </div>
        <div id="ai-overrun-list" class="space-y-1 text-xs text-amber-200/80"></div>
    </div>

    
    <div id="ai-suggest-panel" class="hidden mb-4 bg-white dark:bg-[#1e293b] border border-purple-500/30 rounded-2xl overflow-hidden">
        <div class="flex items-center justify-between px-5 py-3 border-b border-purple-500/20 bg-purple-500/10">
            <div class="flex items-center gap-2 text-purple-300 font-medium text-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.364.364A4.004 4.004 0 0112 16a4.004 4.004 0 01-2.772-1.1l-.364-.364z"/></svg>
                Saran Alokasi AI untuk Periode <span id="suggest-period-label" class="ml-1 font-bold"></span>
            </div>
            <button onclick="document.getElementById('ai-suggest-panel').classList.add('hidden')" class="text-slate-400 hover:text-white text-lg leading-none">✕</button>
        </div>
        <div id="ai-suggest-content" class="p-4 text-sm text-slate-300">
            <div class="animate-pulse text-slate-500">Memuat saran AI...</div>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Anggaran</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Departemen</th>
                        <th class="px-4 py-3 text-right">Anggaran</th>
                        <th class="px-4 py-3 text-right">Realisasi</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Sisa</th>
                        <th class="px-4 py-3 text-center">Pemakaian</th>
                        <th class="px-4 py-3 text-center hidden lg:table-cell">Prediksi AI</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    <?php $__empty_1 = true; $__currentLoopData = $budgets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $budget): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $pct = $budget->usage_percent;
                        $over = $budget->realized > $budget->amount;
                        $warn = !$over && $pct >= 80;
                        $barColor = $over ? 'bg-red-500' : ($warn ? 'bg-amber-500' : 'bg-blue-500');
                        $statusText = $over ? 'OVER' : ($warn ? 'HAMPIR' : 'AMAN');
                        $statusColor = $over ? 'text-red-500 bg-red-50 dark:bg-red-500/10' : ($warn ? 'text-amber-600 bg-amber-50 dark:bg-amber-500/10' : 'text-green-600 bg-green-50 dark:bg-green-500/10');
                    ?>
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3">
                            <p class="font-medium text-gray-900 dark:text-white"><?php echo e($budget->name); ?></p>
                            <?php if($budget->category): ?><p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($budget->category); ?></p><?php endif; ?>
                        </td>
                        <td class="px-4 py-3 hidden sm:table-cell text-gray-500 dark:text-slate-400"><?php echo e($budget->department ?? '-'); ?></td>
                        <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp <?php echo e(number_format($budget->amount,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right font-medium <?php echo e($over ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">Rp <?php echo e(number_format($budget->realized,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right hidden md:table-cell <?php echo e($over ? 'text-red-500' : 'text-gray-500 dark:text-slate-400'); ?>">
                            <?php echo e($over ? '-' : ''); ?>Rp <?php echo e(number_format(abs($budget->variance),0,',','.')); ?>

                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <div class="flex-1 bg-gray-100 dark:bg-white/10 rounded-full h-2 min-w-[60px]">
                                    <div class="<?php echo e($barColor); ?> h-2 rounded-full transition-all" style="width:<?php echo e(min(100,$pct)); ?>%"></div>
                                </div>
                                <span class="text-xs font-medium px-1.5 py-0.5 rounded <?php echo e($statusColor); ?>"><?php echo e($statusText); ?></span>
                            </div>
                        </td>
                        
                        <td class="px-4 py-3 text-center hidden lg:table-cell">
                            <div id="ai-pred-<?php echo e($budget->id); ?>" class="text-xs text-slate-500 italic animate-pulse">—</div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'budget', 'edit')): ?>
                                <button onclick="openEdit(<?php echo e($budget->id); ?>, '<?php echo e(addslashes($budget->name)); ?>', <?php echo e($budget->amount); ?>, <?php echo e($budget->realized); ?>, '<?php echo e($budget->department); ?>', '<?php echo e($budget->category); ?>')"
                                    class="p-1.5 rounded-lg text-gray-500 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <?php endif; ?>
                                <?php if(auth()->check() && app(\App\Services\PermissionService::class)->check(auth()->user(), 'budget', 'delete')): ?>
                                <form method="POST" action="<?php echo e(route('budget.destroy', $budget)); ?>" onsubmit="return confirm('Nonaktifkan anggaran ini?')">
                                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                    <button type="submit" class="p-1.5 rounded-lg text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <tr><td colspan="8" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada anggaran untuk periode <?php echo e($period); ?>.</td></tr>
                    <?php endif; ?>
                </tbody>
                <?php if($budgets->count() > 0): ?>
                <tfoot class="bg-gray-50 dark:bg-white/5 text-sm font-semibold">
                    <tr>
                        <td class="px-4 py-3 text-gray-900 dark:text-white" colspan="2">Total</td>
                        <td class="px-4 py-3 text-right text-gray-900 dark:text-white">Rp <?php echo e(number_format($totalBudget,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right <?php echo e($totalRealized > $totalBudget ? 'text-red-500' : 'text-gray-900 dark:text-white'); ?>">Rp <?php echo e(number_format($totalRealized,0,',','.')); ?></td>
                        <td class="px-4 py-3 text-right hidden md:table-cell <?php echo e($totalRealized > $totalBudget ? 'text-red-500' : 'text-gray-500 dark:text-slate-400'); ?>">
                            Rp <?php echo e(number_format(abs($totalBudget - $totalRealized),0,',','.')); ?>

                        </td>
                        <td colspan="3"></td>
                    </tr>
                </tfoot>
                <?php endif; ?>
            </table>
        </div>
    </div>

    
    <div id="modal-add-budget" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Anggaran Baru</h3>
                <button onclick="document.getElementById('modal-add-budget').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('budget.store')); ?>" class="p-6 space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama Anggaran *</label>
                    <input type="text" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" name="department" placeholder="Marketing, Ops..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" name="category" placeholder="Gaji, Material..." class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Jumlah Anggaran (Rp) *</label>
                    <input type="number" name="amount" min="0" step="100000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode *</label>
                        <input type="month" name="period" value="<?php echo e($period); ?>" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe</label>
                        <select name="period_type" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="monthly">Bulanan</option>
                            <option value="quarterly">Kuartalan</option>
                            <option value="annual">Tahunan</option>
                        </select>
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-add-budget').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="modal-edit-budget" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Edit Anggaran</h3>
                <button onclick="document.getElementById('modal-edit-budget').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-edit-budget" method="POST" class="p-6 space-y-4">
                <?php echo csrf_field(); ?> <?php echo method_field('PUT'); ?>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                    <input type="text" id="edit-name" name="name" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Departemen</label>
                        <input type="text" id="edit-dept" name="department" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kategori</label>
                        <input type="text" id="edit-cat" name="category" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Anggaran (Rp) *</label>
                        <input type="number" id="edit-amount" name="amount" min="0" step="100000" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Realisasi (Rp)</label>
                        <input type="number" id="edit-realized" name="realized" min="0" step="1000" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('modal-edit-budget').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    function openEdit(id, name, amount, realized, dept, cat) {
        document.getElementById('form-edit-budget').action = '<?php echo e(url("budget")); ?>/' + id;
        document.getElementById('edit-name').value = name;
        document.getElementById('edit-amount').value = amount;
        document.getElementById('edit-realized').value = realized;
        document.getElementById('edit-dept').value = dept;
        document.getElementById('edit-cat').value = cat;
        document.getElementById('modal-edit-budget').classList.remove('hidden');
    }

    // ── AI: Overrun Prediction ────────────────────────────────────
    const currentPeriod = '<?php echo e($period); ?>';
    const overrunUrl    = '<?php echo e(route("budget.ai.overrun")); ?>';
    const suggestUrl    = '<?php echo e(route("budget.ai.suggest")); ?>';

    const riskBadge = {
        high:   'px-2 py-0.5 rounded-full bg-red-500/20 text-red-400',
        medium: 'px-2 py-0.5 rounded-full bg-amber-500/20 text-amber-400',
        low:    'px-2 py-0.5 rounded-full bg-yellow-500/20 text-yellow-300',
        safe:   'px-2 py-0.5 rounded-full bg-green-500/20 text-green-400',
    };
    const riskLabel = { high: '🔴 Tinggi', medium: '🟡 Sedang', low: '🟢 Rendah', safe: '✓ Aman' };

    async function loadOverrunPredictions() {
        try {
            const res  = await fetch(`${overrunUrl}?period=${currentPeriod}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const preds = data.predictions ?? {};

            const bannerItems = [];

            for (const [id, pred] of Object.entries(preds)) {
                const el = document.getElementById(`ai-pred-${id}`);
                if (el) {
                    el.className = `text-xs ${riskBadge[pred.risk] ?? riskBadge.safe}`;
                    el.textContent = riskLabel[pred.risk] ?? '—';
                    el.title = pred.message;
                }
                if (pred.risk === 'high' || pred.risk === 'medium') {
                    bannerItems.push(`<div>• ${pred.message}</div>`);
                }
            }

            if (bannerItems.length > 0) {
                document.getElementById('ai-overrun-list').innerHTML = bannerItems.join('');
                document.getElementById('ai-overrun-banner').classList.remove('hidden');
            }
        } catch (e) { /* silent */ }
    }

    // ── AI: Suggest Allocation ────────────────────────────────────
    async function openSuggestPanel() {
        const panel = document.getElementById('ai-suggest-panel');
        const content = document.getElementById('ai-suggest-content');
        document.getElementById('suggest-period-label').textContent = currentPeriod;
        panel.classList.remove('hidden');
        content.innerHTML = '<div class="animate-pulse text-slate-500 text-sm">Memuat saran AI...</div>';

        try {
            const res  = await fetch(`${suggestUrl}?period=${currentPeriod}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const data = await res.json();
            const sugs = data.suggestions ?? [];

            if (!sugs.length) {
                content.innerHTML = '<p class="text-slate-500 text-sm">Tidak ada histori yang cukup untuk membuat saran alokasi.</p>';
                return;
            }

            const confColor = { high: 'text-green-400', medium: 'text-yellow-400', low: 'text-slate-400' };

            let html = '<div class="overflow-x-auto"><table class="w-full text-xs">';
            html += `<thead><tr class="text-slate-500 border-b border-white/10">
                <th class="py-2 text-left pr-4">Nama Anggaran</th>
                <th class="py-2 text-left pr-4 hidden sm:table-cell">Dept.</th>
                <th class="py-2 text-right pr-4">Realisasi Lalu</th>
                <th class="py-2 text-right pr-4">Saran Anggaran</th>
                <th class="py-2 text-left pr-4">Basis</th>
                <th class="py-2 text-center">Status</th>
            </tr></thead><tbody class="divide-y divide-white/5">`;

            for (const s of sugs) {
                const fmt = v => v != null ? 'Rp ' + Number(v).toLocaleString('id-ID') : '—';
                const existsBadge = s.already_exists
                    ? `<span class="px-1.5 py-0.5 rounded bg-blue-500/20 text-blue-400">Ada (${fmt(s.existing_amount)})</span>`
                    : `<button onclick="prefillBudget('${escHtml(s.name)}','${escHtml(s.department??'')}','${escHtml(s.category??'')}',${s.suggested_amount})"
                        class="px-1.5 py-0.5 rounded bg-purple-500/20 hover:bg-purple-500/40 text-purple-300 transition cursor-pointer">+ Buat</button>`;

                html += `<tr class="hover:bg-white/5">
                    <td class="py-2 pr-4 text-white font-medium">${escHtml(s.name)}</td>
                    <td class="py-2 pr-4 text-slate-400 hidden sm:table-cell">${escHtml(s.department ?? '—')}</td>
                    <td class="py-2 pr-4 text-right text-slate-300">${fmt(s.last_year_realized ?? s.basis_amount)}</td>
                    <td class="py-2 pr-4 text-right font-semibold text-white">${fmt(s.suggested_amount)}</td>
                    <td class="py-2 pr-4 ${confColor[s.confidence] ?? 'text-slate-400'}">${escHtml(s.basis)}</td>
                    <td class="py-2 text-center">${existsBadge}</td>
                </tr>`;
            }

            html += '</tbody></table></div>';
            content.innerHTML = html;
        } catch (e) {
            content.innerHTML = '<p class="text-red-400 text-sm">Gagal memuat saran AI.</p>';
        }
    }

    function escHtml(str) {
        return String(str ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
    }

    function prefillBudget(name, dept, cat, amount) {
        document.getElementById('modal-add-budget').classList.remove('hidden');
        document.querySelector('#modal-add-budget input[name="name"]').value = name;
        document.querySelector('#modal-add-budget input[name="department"]').value = dept;
        document.querySelector('#modal-add-budget input[name="category"]').value = cat;
        document.querySelector('#modal-add-budget input[name="amount"]').value = amount;
    }

    document.addEventListener('DOMContentLoaded', loadOverrunPredictions);
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\budget\index.blade.php ENDPATH**/ ?>