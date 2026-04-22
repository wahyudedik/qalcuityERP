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
     <?php $__env->slot('header', null, []); ?> Pengiriman <?php $__env->endSlot(); ?>

    <div class="space-y-6">

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-white mb-4">Cek Ongkos Kirim</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-3">
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Kota Asal</label>
                    <input id="rate-origin" type="text" placeholder="Contoh: 501"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Kota Tujuan</label>
                    <input id="rate-dest" type="text" placeholder="Contoh: 114"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Berat (kg)</label>
                    <input id="rate-weight" type="number" step="0.1" min="0.1" value="1"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Kurir</label>
                    <select id="rate-courier" class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                        <option value="jne">JNE</option>
                        <option value="jnt">J&T</option>
                        <option value="sicepat">SiCepat</option>
                        <option value="pos">POS Indonesia</option>
                        <option value="tiki">TIKI</option>
                    </select>
                </div>
            </div>
            <button onclick="checkRate()" class="mt-4 px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                Cek Ongkir
            </button>
            <div id="rate-result" class="mt-3 hidden"></div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-white mb-4">Lacak Pengiriman</h2>
            <div class="flex flex-col sm:flex-row gap-3">
                <select id="track-courier" class="w-full sm:w-auto bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white focus:outline-none focus:border-blue-500">
                    <option value="jne">JNE</option>
                    <option value="jnt">J&T</option>
                    <option value="sicepat">SiCepat</option>
                    <option value="pos">POS Indonesia</option>
                </select>
                <input id="track-number" type="text" placeholder="Nomor resi..."
                    class="flex-1 bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-white placeholder-slate-600 focus:outline-none focus:border-blue-500">
                <button onclick="trackShipment()" class="px-4 py-2 bg-blue-600 text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                    Lacak
                </button>
            </div>
            <div id="track-result" class="mt-3 hidden"></div>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10">
                <h2 class="font-semibold text-white">Daftar Pengiriman</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Kurir</th>
                            <th class="px-6 py-3 text-left">No. Resi</th>
                            <th class="px-6 py-3 text-left">Tujuan</th>
                            <th class="px-6 py-3 text-right hidden sm:table-cell">Berat</th>
                            <th class="px-6 py-3 text-right">Ongkir</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left hidden md:table-cell">Estimasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__empty_1 = true; $__currentLoopData = $shipments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $s): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3 font-medium text-white uppercase"><?php echo e($s->courier); ?></td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 font-mono text-xs"><?php echo e($s->tracking_number ?? '—'); ?></td>
                            <td class="px-6 py-3 text-gray-700 dark:text-slate-300"><?php echo e($s->destination); ?></td>
                            <td class="px-6 py-3 text-right text-gray-500 dark:text-slate-400 hidden sm:table-cell"><?php echo e($s->weight); ?> kg</td>
                            <td class="px-6 py-3 text-right font-medium text-white">Rp <?php echo e(number_format($s->cost, 0, ',', '.')); ?></td>
                            <td class="px-6 py-3">
                                <?php $colors = ['pending'=>'amber','shipped'=>'blue','delivered'=>'green','returned'=>'red']; ?>
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium bg-<?php echo e($colors[$s->status] ?? 'gray'); ?>-500/20 text-<?php echo e($colors[$s->status] ?? 'gray'); ?>-400">
                                    <?php echo e(ucfirst($s->status)); ?>

                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-400 dark:text-slate-500 hidden md:table-cell"><?php echo e($s->estimated_delivery?->format('d M Y') ?? '—'); ?></td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada data pengiriman.</td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10"><?php echo e($shipments->links()); ?></div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script>
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    async function checkRate() {
        const res = await fetch('<?php echo e(route("shipping.rate")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                origin:      document.getElementById('rate-origin').value,
                destination: document.getElementById('rate-dest').value,
                weight:      document.getElementById('rate-weight').value,
                courier:     document.getElementById('rate-courier').value,
            }),
        });
        const data = await res.json();
        const el = document.getElementById('rate-result');
        el.classList.remove('hidden');
        if (!data.length) { el.innerHTML = '<p class="text-sm text-gray-500 dark:text-slate-400">Tidak ada data ongkir.</p>'; return; }
        el.innerHTML = '<div class="grid grid-cols-2 md:grid-cols-3 gap-2">' +
            data.map(r => `<div class="border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] rounded-xl p-3">
                <p class="font-medium text-sm text-white">${r.service}</p>
                <p class="text-xs text-gray-500 dark:text-slate-400">${r.description}</p>
                <p class="text-blue-400 font-semibold text-sm mt-1">Rp ${(r.cost[0]?.value||0).toLocaleString('id-ID')}</p>
                <p class="text-xs text-gray-400 dark:text-slate-500">ETD: ${r.cost[0]?.etd||'-'} hari</p>
            </div>`).join('') + '</div>';
    }

    async function trackShipment() {
        const res = await fetch('<?php echo e(route("shipping.track")); ?>', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({
                courier:          document.getElementById('track-courier').value,
                tracking_number:  document.getElementById('track-number').value,
            }),
        });
        const data = await res.json();
        const el = document.getElementById('track-result');
        el.classList.remove('hidden');
        el.innerHTML = `<pre class="text-xs bg-gray-50 dark:bg-[#0f172a] text-gray-700 dark:text-slate-300 rounded-xl p-3 overflow-x-auto border border-gray-200 dark:border-white/10">${JSON.stringify(data, null, 2)}</pre>`;
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

<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\shipping\index.blade.php ENDPATH**/ ?>