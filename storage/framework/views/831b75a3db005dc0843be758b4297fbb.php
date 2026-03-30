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
     <?php $__env->slot('header', null, []); ?> AI Forecasting Dashboard <?php $__env->endSlot(); ?>

    
    <div class="flex items-center gap-3 mb-6">
        <form method="GET" class="flex items-center gap-2">
            <label class="text-sm text-gray-500 dark:text-slate-400">Proyeksi:</label>
            <select name="months" onchange="this.form.submit()" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <?php $__currentLoopData = [3=>'3 Bulan',6=>'6 Bulan',12=>'12 Bulan']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $v=>$l): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <option value="<?php echo e($v); ?>" <?php if($months==$v): echo 'selected'; endif; ?>><?php echo e($l); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </form>
        <p class="text-xs text-gray-400 dark:text-slate-500">Data historis 6 bulan terakhir + proyeksi <?php echo e($months); ?> bulan ke depan</p>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">📈 Proyeksi Revenue</h3>
        <canvas id="revenueChart" height="100"></canvas>
    </div>

    
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6 mb-6">
        <h3 class="font-semibold text-gray-900 dark:text-white mb-4">💰 Proyeksi Cash Flow</h3>
        <canvas id="cashFlowChart" height="100"></canvas>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">🏦 Piutang & Collection</h3>
            <div class="grid grid-cols-2 gap-4 mb-4">
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Total Piutang</p><p class="text-lg font-bold text-gray-900 dark:text-white">Rp <?php echo e(number_format($receivables['aging']->total ?? 0, 0, ',', '.')); ?></p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Collection Rate (3bln)</p><p class="text-lg font-bold <?php echo e(($receivables['collection_rate'] ?? 0) >= 80 ? 'text-green-500' : 'text-amber-500'); ?>"><?php echo e($receivables['collection_rate']); ?>%</p></div>
                <div><p class="text-xs text-gray-500 dark:text-slate-400">Est. Tertagih</p><p class="text-lg font-bold text-blue-500">Rp <?php echo e(number_format($receivables['estimated_collection'], 0, ',', '.')); ?></p></div>
            </div>
            <canvas id="agingChart" height="120"></canvas>
        </div>

        
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">📦 Demand Forecast (Top 10)</h3>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="text-xs text-gray-500 dark:text-slate-400">
                        <tr><th class="text-left py-2">Produk</th><th class="text-right py-2">Avg/bln</th><th class="text-right py-2">Stok</th><th class="text-right py-2">Bulan</th><th class="text-center py-2">Tren</th></tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        <?php $__currentLoopData = $demand; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <tr>
                            <td class="py-2 text-gray-900 dark:text-white"><?php echo e(Str::limit($d['product_name'], 20)); ?></td>
                            <td class="py-2 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($d['monthly_avg'])); ?></td>
                            <td class="py-2 text-right text-gray-700 dark:text-slate-300"><?php echo e(number_format($d['current_stock'])); ?></td>
                            <td class="py-2 text-right font-medium <?php echo e(($d['months_of_stock'] ?? 0) < 1 ? 'text-red-500' : (($d['months_of_stock'] ?? 0) < 2 ? 'text-amber-500' : 'text-green-500')); ?>">
                                <?php echo e($d['months_of_stock'] !== null ? $d['months_of_stock'] . ' bln' : '-'); ?>

                            </td>
                            <td class="py-2 text-center">
                                <?php if($d['trend'] > 5): ?> <span class="text-green-500">↑<?php echo e($d['trend']); ?>%</span>
                                <?php elseif($d['trend'] < -5): ?> <span class="text-red-500">↓<?php echo e(abs($d['trend'])); ?>%</span>
                                <?php else: ?> <span class="text-gray-400">→</span> <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <?php $__env->startPush('scripts'); ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.06)' : 'rgba(0,0,0,0.06)';
    const textColor = isDark ? '#94a3b8' : '#6b7280';

    // Revenue Chart
    (() => {
        const hist = <?php echo json_encode($revenue['historical'], 15, 512) ?>;
        const proj = <?php echo json_encode($revenue['projected'], 15, 512) ?>;
        const all = [...hist, ...proj];
        new Chart(document.getElementById('revenueChart'), {
            type: 'bar',
            data: {
                labels: all.map(d => d.label),
                datasets: [{
                    label: 'Aktual',
                    data: all.map(d => d.type === 'actual' ? d.amount : null),
                    backgroundColor: 'rgba(59,130,246,0.7)',
                    borderRadius: 6,
                }, {
                    label: 'Proyeksi',
                    data: all.map(d => d.type === 'forecast' ? d.amount : null),
                    backgroundColor: 'rgba(59,130,246,0.25)',
                    borderColor: 'rgba(59,130,246,0.5)',
                    borderWidth: 2,
                    borderDash: [5,5],
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: textColor } } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor, callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' }, grid: { color: gridColor } }
                }
            }
        });
    })();

    // Cash Flow Chart
    (() => {
        const data = <?php echo json_encode($cashFlow, 15, 512) ?>;
        new Chart(document.getElementById('cashFlowChart'), {
            type: 'line',
            data: {
                labels: data.map(d => d.label),
                datasets: [{
                    label: 'Inflow',
                    data: data.map(d => d.inflow),
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34,197,94,0.1)',
                    fill: true, tension: 0.3,
                    borderDash: data.map(d => d.type === 'forecast' ? 5 : 0),
                }, {
                    label: 'Outflow',
                    data: data.map(d => d.outflow),
                    borderColor: '#ef4444',
                    backgroundColor: 'rgba(239,68,68,0.1)',
                    fill: true, tension: 0.3,
                }, {
                    label: 'Net',
                    data: data.map(d => d.net),
                    borderColor: '#3b82f6',
                    borderWidth: 2, tension: 0.3,
                    pointRadius: 3,
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { labels: { color: textColor } } },
                scales: {
                    x: { ticks: { color: textColor }, grid: { color: gridColor } },
                    y: { ticks: { color: textColor, callback: v => 'Rp ' + (v/1000000).toFixed(0) + 'jt' }, grid: { color: gridColor } }
                }
            }
        });
    })();

    // Aging Chart
    (() => {
        const aging = <?php echo json_encode($receivables['aging'], 15, 512) ?>;
        new Chart(document.getElementById('agingChart'), {
            type: 'doughnut',
            data: {
                labels: ['Current', '1-30 hari', '31-60 hari', '61-90 hari', '90+ hari'],
                datasets: [{
                    data: [aging.current_amount, aging.days_1_30, aging.days_31_60, aging.days_61_90, aging.days_90_plus],
                    backgroundColor: ['#22c55e', '#3b82f6', '#f59e0b', '#f97316', '#ef4444'],
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { position: 'bottom', labels: { color: textColor, boxWidth: 12 } } }
            }
        });
    })();
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
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\forecast\index.blade.php ENDPATH**/ ?>