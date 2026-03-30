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
     <?php $__env->slot('header', null, []); ?> Mix Design — Mutu Beton <?php $__env->endSlot(); ?>

    <?php if(session('success')): ?>
    <div class="mb-4 px-4 py-3 bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/20 rounded-xl text-sm text-green-700 dark:text-green-400"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
    <div class="mb-4 px-4 py-3 bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 rounded-xl text-sm text-red-700 dark:text-red-400"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    
    <div class="flex items-center justify-between mb-5">
        <p class="text-sm text-gray-500 dark:text-slate-400">Komposisi material per 1 m³ beton untuk setiap mutu.</p>
        <div class="flex items-center gap-2">
            <form method="POST" action="<?php echo e(route('manufacturing.mix-design.seed')); ?>">
                <?php echo csrf_field(); ?>
                <button type="submit" class="px-3 py-1.5 text-xs border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">🏗️ Load Standar SNI</button>
            </form>
            <button onclick="document.getElementById('addModal').classList.remove('hidden')" class="px-3 py-1.5 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition">+ Tambah Mix Design</button>
        </div>
    </div>

    
    <?php if($designs->isEmpty()): ?>
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-12 text-center">
        <p class="text-3xl mb-3">🏗️</p>
        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada mix design. Klik "Load Standar SNI" untuk memuat mutu beton standar Indonesia (K-175 s/d K-500).</p>
    </div>
    <?php else: ?>
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__currentLoopData = $designs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-white dark:bg-[#1e293b] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
            
            <div class="px-5 py-4 border-b border-gray-100 dark:border-white/10 flex items-center justify-between">
                <div>
                    <span class="text-lg font-bold text-gray-900 dark:text-white"><?php echo e($d->grade); ?></span>
                    <?php if($d->is_standard): ?>
                    <span class="ml-2 text-[10px] px-1.5 py-0.5 rounded bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400">SNI</span>
                    <?php endif; ?>
                </div>
                <span class="text-xs text-gray-400 dark:text-slate-500"><?php echo e($d->target_strength); ?> <?php echo e($d->strength_unit === 'K' ? 'kg/cm²' : 'MPa'); ?></span>
            </div>

            
            <div class="px-5 py-3 space-y-2">
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500">Komposisi per 1 m³</p>
                <div class="grid grid-cols-2 gap-x-4 gap-y-1.5 text-xs">
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Semen</span><span class="font-mono text-gray-700 dark:text-slate-300"><?php echo e(number_format($d->cement_kg, 0)); ?> kg</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Air</span><span class="font-mono text-gray-700 dark:text-slate-300"><?php echo e(number_format($d->water_liter, 0)); ?> L</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Pasir</span><span class="font-mono text-gray-700 dark:text-slate-300"><?php echo e(number_format($d->fine_agg_kg, 0)); ?> kg</span></div>
                    <div class="flex justify-between"><span class="text-gray-500 dark:text-slate-400">Kerikil</span><span class="font-mono text-gray-700 dark:text-slate-300"><?php echo e(number_format($d->coarse_agg_kg, 0)); ?> kg</span></div>
                    <?php if($d->admixture_liter > 0): ?>
                    <div class="flex justify-between col-span-2"><span class="text-gray-500 dark:text-slate-400">Admixture</span><span class="font-mono text-gray-700 dark:text-slate-300"><?php echo e($d->admixture_liter); ?> L</span></div>
                    <?php endif; ?>
                </div>
                <div class="flex items-center gap-3 text-[10px] text-gray-400 dark:text-slate-500 pt-1 border-t border-gray-100 dark:border-white/5">
                    <span>w/c: <?php echo e($d->water_cement_ratio); ?></span>
                    <span>Slump: <?php echo e($d->slump_min); ?>–<?php echo e($d->slump_max); ?> cm</span>
                    <span><?php echo e($d->cement_type); ?></span>
                </div>
            </div>

            
            <div class="px-5 py-3 border-t border-gray-100 dark:border-white/10 flex items-center gap-2">
                <button onclick="openCalcModal(<?php echo e($d->id); ?>, '<?php echo e($d->grade); ?>')" class="text-xs text-blue-500 hover:text-blue-600 transition">🧮 Hitung</button>
                <?php if(!$d->bom_id): ?>
                <form method="POST" action="<?php echo e(route('manufacturing.mix-design.generate-bom', $d)); ?>" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="text-xs text-emerald-500 hover:text-emerald-600 transition">📦 Generate BOM</button>
                </form>
                <?php else: ?>
                <span class="text-xs text-gray-400">✅ BOM terhubung</span>
                <?php endif; ?>
                <form method="POST" action="<?php echo e(route('manufacturing.mix-design.destroy', $d)); ?>" onsubmit="return confirm('Hapus mix design ini?')" class="inline ml-auto">
                    <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                    <button type="submit" class="text-xs text-red-400 hover:text-red-500 transition">Hapus</button>
                </form>
            </div>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <div class="mt-4"><?php echo e($designs->links()); ?></div>
    <?php endif; ?>

    
    <div id="addModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tambah Mix Design</h3>
                <button onclick="document.getElementById('addModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="<?php echo e(route('manufacturing.mix-design.store')); ?>" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Mutu / Grade *</label>
                        <input type="text" name="grade" required placeholder="K-300" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Nama *</label>
                        <input type="text" name="name" required placeholder="Beton Mutu K-300" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kuat Tekan *</label>
                        <input type="number" name="target_strength" required step="0.01" placeholder="300" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Satuan</label>
                        <select name="strength_unit" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="K">K (kg/cm²)</option>
                            <option value="fc">fc' (MPa)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">w/c Ratio</label>
                        <input type="number" name="water_cement_ratio" step="0.01" value="0.50" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>
                <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 dark:text-slate-500 pt-2">Komposisi per 1 m³</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Semen (kg) *</label>
                        <input type="number" name="cement_kg" required step="0.01" placeholder="413" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Air (liter) *</label>
                        <input type="number" name="water_liter" required step="0.01" placeholder="215" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Pasir / Agregat Halus (kg) *</label>
                        <input type="number" name="fine_agg_kg" required step="0.01" placeholder="681" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Kerikil / Agregat Kasar (kg) *</label>
                        <input type="number" name="coarse_agg_kg" required step="0.01" placeholder="1021" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Admixture (liter)</label>
                        <input type="number" name="admixture_liter" step="0.001" value="0" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Tipe Semen</label>
                        <select name="cement_type" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                            <option value="PCC">PCC</option>
                            <option value="OPC">OPC</option>
                            <option value="PPC">PPC</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Slump Min (cm)</label>
                        <input type="number" name="slump_min" step="0.1" value="8" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Slump Max (cm)</label>
                        <input type="number" name="slump_max" step="0.1" value="12" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                    <textarea name="notes" rows="2" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white"></textarea>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="document.getElementById('addModal').classList.add('hidden')" class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300">Batal</button>
                    <button type="submit" class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    
    <div id="calcModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-sm p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-base font-semibold text-gray-900 dark:text-white">🧮 Hitung Kebutuhan</h3>
                <button onclick="document.getElementById('calcModal').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <p class="text-sm text-blue-600 dark:text-blue-400 font-semibold mb-3" id="calc-grade"></p>
            <div class="mb-4">
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Volume (m³)</label>
                <input type="number" id="calc-volume" step="0.1" value="1" min="0.1" class="w-full px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
            </div>
            <button onclick="doCalculate()" class="w-full px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium mb-4">Hitung</button>
            <div id="calc-result" class="hidden space-y-2 text-sm"></div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    let calcDesignId = null;
    function openCalcModal(id, grade) {
        calcDesignId = id;
        document.getElementById('calc-grade').textContent = grade;
        document.getElementById('calc-result').classList.add('hidden');
        document.getElementById('calcModal').classList.remove('hidden');
    }
    async function doCalculate() {
        const vol = document.getElementById('calc-volume').value;
        const res = await fetch(`/manufacturing/mix-design/${calcDesignId}/calculate?volume=${vol}`);
        const data = await res.json();
        const n = data.needs;
        const fmt = v => new Intl.NumberFormat('id-ID').format(Math.round(v));
        document.getElementById('calc-result').innerHTML = `
            <p class="font-semibold text-gray-900 dark:text-white">Kebutuhan untuk ${n.volume_m3} m³ ${data.grade}:</p>
            <div class="grid grid-cols-2 gap-1 text-xs">
                <span class="text-gray-500">Semen</span><span class="font-mono">${fmt(n.cement_kg)} kg (${n.cement_sak} sak)</span>
                <span class="text-gray-500">Air</span><span class="font-mono">${fmt(n.water_liter)} liter</span>
                <span class="text-gray-500">Pasir</span><span class="font-mono">${fmt(n.fine_agg_kg)} kg (${n.fine_agg_m3} m³)</span>
                <span class="text-gray-500">Kerikil</span><span class="font-mono">${fmt(n.coarse_agg_kg)} kg (${n.coarse_agg_m3} m³)</span>
                ${n.admixture_liter > 0 ? `<span class="text-gray-500">Admixture</span><span class="font-mono">${n.admixture_liter} liter</span>` : ''}
            </div>
            ${data.total_cost > 0 ? `<p class="pt-2 border-t border-gray-100 dark:border-white/10 font-semibold text-emerald-600">Estimasi biaya: Rp ${fmt(data.total_cost)}</p>` : ''}
        `;
        document.getElementById('calc-result').classList.remove('hidden');
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\manufacturing\mix-design.blade.php ENDPATH**/ ?>