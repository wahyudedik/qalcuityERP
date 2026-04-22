
<?php $__env->startSection('title', 'KPI Dashboard'); ?>

<?php $__env->startPush('head'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">KPI Dashboard</h1>
            <p class="text-sm text-gray-500 dark:text-slate-400 mt-1">Target vs Aktual per periode</p>
        </div>
        <div class="flex items-center gap-3">
            <form method="GET" action="<?php echo e(route('kpi.index')); ?>">
                <input type="month" name="period" value="<?php echo e($period); ?>"
                       onchange="this.form.submit()"
                       class="px-3 py-2 rounded-lg text-sm bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </form>
            <button onclick="document.getElementById('addKpiModal').classList.remove('hidden')"
                    class="flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Target
            </button>
        </div>
    </div>

    <?php if(session('success')): ?>
    <div class="p-3 rounded-lg bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-sm text-emerald-700 dark:text-emerald-300">
        <?php echo e(session('success')); ?>

    </div>
    <?php endif; ?>

    
    <?php if($targets->isNotEmpty()): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
        <?php $__currentLoopData = $targets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php
            $pct    = $kpi->achievementPercent();
            $color  = $kpi->statusColor();
            $colors = [
                'emerald' => ['ring' => 'ring-emerald-500', 'bar' => 'bg-emerald-500', 'text' => 'text-emerald-600 dark:text-emerald-400', 'badge' => 'bg-emerald-100 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300'],
                'blue'    => ['ring' => 'ring-blue-500',    'bar' => 'bg-blue-500',    'text' => 'text-blue-600 dark:text-blue-400',       'badge' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300'],
                'amber'   => ['ring' => 'ring-amber-500',   'bar' => 'bg-amber-500',   'text' => 'text-amber-600 dark:text-amber-400',     'badge' => 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-300'],
                'red'     => ['ring' => 'ring-red-500',     'bar' => 'bg-red-500',     'text' => 'text-red-600 dark:text-red-400',         'badge' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300'],
            ][$color];
        ?>
        <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 p-5 cursor-pointer hover:shadow-md transition-shadow"
             onclick="loadDrilldown('<?php echo e($kpi->metric); ?>', '<?php echo e($kpi->label); ?>')">
            <div class="flex items-start justify-between mb-3">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wide"><?php echo e($kpi->label); ?></p>
                <div class="flex items-center gap-1">
                    <span class="text-xs font-bold px-2 py-0.5 rounded-full <?php echo e($colors['badge']); ?>"><?php echo e($pct); ?>%</span>
                    <form method="POST" action="<?php echo e(route('kpi.destroy', $kpi)); ?>" onsubmit="return confirm('Hapus target ini?')">
                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                        <button type="submit" class="text-gray-300 dark:text-slate-600 hover:text-red-400 transition ml-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
            </div>
            <p class="text-2xl font-bold text-gray-900 dark:text-white">
                <?php if($kpi->unit === 'currency'): ?> Rp <?php echo e(number_format($kpi->actual, 0, ',', '.')); ?>

                <?php elseif($kpi->unit === 'percent'): ?> <?php echo e($kpi->actual); ?>%
                <?php else: ?> <?php echo e(number_format($kpi->actual, 0, ',', '.')); ?>

                <?php endif; ?>
            </p>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1">
                Target:
                <?php if($kpi->unit === 'currency'): ?> Rp <?php echo e(number_format($kpi->target, 0, ',', '.')); ?>

                <?php elseif($kpi->unit === 'percent'): ?> <?php echo e($kpi->target); ?>%
                <?php else: ?> <?php echo e(number_format($kpi->target, 0, ',', '.')); ?>

                <?php endif; ?>
            </p>
            <div class="mt-3 h-1.5 bg-gray-100 dark:bg-white/10 rounded-full overflow-hidden">
                <div class="<?php echo e($colors['bar']); ?> h-full rounded-full transition-all duration-500"
                     style="width: <?php echo e(min($pct, 100)); ?>%"></div>
            </div>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-1.5">Klik untuk drill-down →</p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php else: ?>
    <div class="bg-white dark:bg-white/5 rounded-xl border border-dashed border-gray-300 dark:border-white/20 p-10 text-center">
        <svg class="w-10 h-10 mx-auto text-gray-300 dark:text-slate-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
        <p class="text-sm text-gray-500 dark:text-slate-400">Belum ada target KPI untuk periode ini.</p>
        <button onclick="document.getElementById('addKpiModal').classList.remove('hidden')"
                class="mt-3 text-sm text-blue-500 hover:underline">Tambah target pertama →</button>
    </div>
    <?php endif; ?>

    
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-white/10">
            <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300">Semua Metrik — <?php echo e(\Carbon\Carbon::createFromFormat('Y-m', $period)->translatedFormat('F Y')); ?></h2>
        </div>
        <div class="grid grid-cols-2 sm:grid-cols-4 divide-x divide-y divide-gray-100 dark:divide-white/5">
            <?php $__currentLoopData = $kpis; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $kpi): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <div class="p-4 cursor-pointer hover:bg-gray-50 dark:hover:bg-white/5 transition-colors"
                 onclick="loadDrilldown('<?php echo e($key); ?>', '<?php echo e($kpi['label']); ?>')">
                <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo e($kpi['label']); ?></p>
                <p class="text-lg font-bold text-gray-900 dark:text-white mt-1">
                    <?php if($kpi['unit'] === 'currency'): ?> Rp <?php echo e(number_format($kpi['actual'], 0, ',', '.')); ?>

                    <?php elseif($kpi['unit'] === 'percent'): ?> <?php echo e($kpi['actual']); ?>%
                    <?php else: ?> <?php echo e(number_format($kpi['actual'], 0, ',', '.')); ?>

                    <?php endif; ?>
                </p>
                <p class="text-xs text-blue-400 mt-0.5">drill-down →</p>
            </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <div class="bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 p-5">
        <h2 class="text-sm font-semibold text-gray-700 dark:text-slate-300 mb-4">Tren 6 Bulan Terakhir</h2>
        <div class="relative h-64">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    
    <div id="drilldownPanel" class="hidden bg-white dark:bg-white/5 rounded-xl border border-gray-200 dark:border-white/10 p-5">
        <div class="flex items-center justify-between mb-4">
            <h2 id="drilldownTitle" class="text-sm font-semibold text-gray-700 dark:text-slate-300">Detail</h2>
            <button onclick="document.getElementById('drilldownPanel').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div class="relative h-64">
            <canvas id="drilldownChart"></canvas>
        </div>
    </div>

</div>


<div id="addKpiModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm p-4">
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 w-full max-w-md p-6">
        <div class="flex items-center justify-between mb-5">
            <h3 class="text-base font-semibold text-gray-900 dark:text-white">Tambah Target KPI</h3>
            <button onclick="document.getElementById('addKpiModal').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600 dark:hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="<?php echo e(route('kpi.store')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Metrik</label>
                <select name="metric" required
                        class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <?php $__currentLoopData = $metrics; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($key); ?>"><?php echo e($meta['label']); ?> (<?php echo e($meta['unit']); ?>)</option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Periode</label>
                <input type="month" name="period" value="<?php echo e($period); ?>" required
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target</label>
                <input type="number" name="target" min="0" step="any" required placeholder="0"
                       class="w-full px-3 py-2 rounded-lg text-sm bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Warna</label>
                <input type="color" name="color" value="#3b82f6"
                       class="h-9 w-full rounded-lg border border-gray-200 dark:border-white/10 cursor-pointer">
            </div>
            <div class="flex gap-3 pt-2">
                <button type="button" onclick="document.getElementById('addKpiModal').classList.add('hidden')"
                        class="flex-1 px-4 py-2 rounded-lg text-sm border border-gray-200 dark:border-white/10 text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5 transition">
                    Batal
                </button>
                <button type="submit"
                        class="flex-1 px-4 py-2 rounded-lg text-sm bg-blue-600 hover:bg-blue-700 text-white font-medium transition">
                    Simpan Target
                </button>
            </div>
        </form>
    </div>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const isDark    = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)';
    const textColor = isDark ? '#94a3b8' : '#6b7280';
    const period    = '<?php echo e($period); ?>';

    // ── Trend chart ──────────────────────────────────────────────
    const trend = <?php echo json_encode($trend, 15, 512) ?>;
    new Chart(document.getElementById('trendChart'), {
        type: 'line',
        data: {
            labels: trend.map(t => t.month),
            datasets: [
                { label: 'Pendapatan', data: trend.map(t => t.revenue), borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,0.08)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Laba',       data: trend.map(t => t.profit),  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.08)', fill: true, tension: 0.4, pointRadius: 3 },
                { label: 'Pengeluaran',data: trend.map(t => t.expense), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.08)',  fill: true, tension: 0.4, pointRadius: 3 },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { labels: { color: textColor, font: { size: 11 }, boxWidth: 10, usePointStyle: true } } },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, font: { size: 10 }, callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt' }, grid: { color: gridColor } },
            },
        },
    });

    // ── Drill-down ───────────────────────────────────────────────
    let drillChart = null;

    window.loadDrilldown = function (metric, label) {
        const panel = document.getElementById('drilldownPanel');
        document.getElementById('drilldownTitle').textContent = 'Detail: ' + label;
        panel.classList.remove('hidden');
        panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        fetch('<?php echo e(url("kpi/drilldown")); ?>/' + metric + `?period=${period}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(r => r.json())
        .then(d => {
            if (drillChart) drillChart.destroy();
            const ctx = document.getElementById('drilldownChart').getContext('2d');

            const colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#06b6d4','#ec4899','#84cc16'];

            drillChart = new Chart(ctx, {
                type: d.type,
                data: {
                    labels: d.labels,
                    datasets: [{
                        label: d.label,
                        data: d.data,
                        backgroundColor: d.type === 'doughnut'
                            ? colors.slice(0, d.data.length)
                            : 'rgba(59,130,246,0.2)',
                        borderColor: d.type === 'doughnut' ? colors.slice(0, d.data.length) : '#3b82f6',
                        borderWidth: 2,
                        borderRadius: d.type === 'bar' ? 6 : 0,
                        tension: 0.4,
                        fill: d.type === 'line',
                        pointRadius: d.type === 'line' ? 3 : 0,
                    }],
                },
                options: {
                    responsive: true, maintainAspectRatio: false,
                    plugins: {
                        legend: { display: d.type === 'doughnut', labels: { color: textColor, font: { size: 11 }, boxWidth: 10 } },
                        tooltip: { callbacks: { label: ctx => ctx.dataset.label + ': Rp ' + Number(ctx.parsed.y ?? ctx.parsed).toLocaleString('id-ID') } },
                    },
                    scales: d.type !== 'doughnut' ? {
                        x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                        y: { ticks: { color: textColor, font: { size: 10 }, callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt' }, grid: { color: gridColor } },
                    } : {},
                },
            });
        });
    };
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views\dashboard\kpi.blade.php ENDPATH**/ ?>