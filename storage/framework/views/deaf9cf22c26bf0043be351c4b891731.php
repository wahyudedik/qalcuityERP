
<?php $__env->startSection('title', 'Proyeksi Arus Kas'); ?>

<?php $__env->startPush('head'); ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<?php $__env->stopPush(); ?>

<?php $__env->startSection('content'); ?>
<div class="p-6 space-y-6">

    
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Proyeksi Arus Kas</h1>
            <p class="text-sm text-gray-500 mt-1">Prediksi berdasarkan AR jatuh tempo & AP jatuh tempo</p>
        </div>
        <div class="flex gap-2">
            <?php $__currentLoopData = [30, 60, 90]; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $d): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <a href="<?php echo e(route('reports.cash-flow-projection', ['days' => $d])); ?>"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-colors
                      <?php echo e($days == $d ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'); ?>">
                <?php echo e($d); ?> Hari
            </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    
    <?php if(count($data['alerts']) > 0): ?>
    <div class="space-y-2">
        <?php $__currentLoopData = $data['alerts']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $alert): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="flex items-start gap-3 p-4 rounded-xl border
                    <?php echo e($alert['type'] === 'deficit' ? 'bg-red-50 border-red-200' : 'bg-amber-50 border-amber-200'); ?>">
            <svg class="w-5 h-5 shrink-0 mt-0.5 <?php echo e($alert['type'] === 'deficit' ? 'text-red-500' : 'text-amber-500'); ?>" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm font-medium <?php echo e($alert['type'] === 'deficit' ? 'text-red-700' : 'text-amber-700'); ?>">
                <?php echo e($alert['message']); ?>

            </p>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Saldo Awal</p>
            <p class="text-xl font-bold text-gray-900 mt-1">Rp <?php echo e(number_format($data['opening_balance'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Masuk (AR)</p>
            <p class="text-xl font-bold text-emerald-600 mt-1">Rp <?php echo e(number_format($data['totals']['inflow'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Total Keluar (AP)</p>
            <p class="text-xl font-bold text-red-600 mt-1">Rp <?php echo e(number_format($data['totals']['outflow'], 0, ',', '.')); ?></p>
        </div>
        <div class="bg-white rounded-xl border border-gray-200 p-4">
            <p class="text-xs text-gray-500">Net <?php echo e($days); ?> Hari</p>
            <?php $net = $data['totals']['net']; ?>
            <p class="text-xl font-bold mt-1 <?php echo e($net >= 0 ? 'text-emerald-600' : 'text-red-600'); ?>">
                Rp <?php echo e(number_format($net, 0, ',', '.')); ?>

            </p>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 p-5">
        <h2 class="text-sm font-semibold text-gray-700 mb-4">Proyeksi Saldo Kas (<?php echo e($days); ?> Hari)</h2>
        <div class="relative h-72">
            <canvas id="projectionChart"></canvas>
        </div>
    </div>

    
    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200">
            <h2 class="text-sm font-semibold text-gray-700">Rincian Per Minggu</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Periode</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">AR Masuk</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">AP Keluar</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Net</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 uppercase tracking-wider">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php $__currentLoopData = $data['weeks']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $week): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-4 py-3 text-gray-700 font-medium"><?php echo e($week['label']); ?></td>
                        <td class="px-4 py-3 text-right text-emerald-600">
                            <?php echo e($week['inflow'] > 0 ? 'Rp ' . number_format($week['inflow'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right text-red-600">
                            <?php echo e($week['outflow'] > 0 ? 'Rp ' . number_format($week['outflow'], 0, ',', '.') : '-'); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-medium <?php echo e($week['net'] >= 0 ? 'text-emerald-600' : 'text-red-600'); ?>">
                            <?php echo e($week['net'] >= 0 ? '+' : ''); ?>Rp <?php echo e(number_format($week['net'], 0, ',', '.')); ?>

                        </td>
                        <td class="px-4 py-3 text-right font-semibold <?php echo e($week['balance'] >= 0 ? 'text-gray-900' : 'text-red-600'); ?>">
                            Rp <?php echo e(number_format($week['balance'], 0, ',', '.')); ?>

                        </td>
                    </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </tbody>
            </table>
        </div>
    </div>

    <p class="text-xs text-gray-400 text-right">Dibuat: <?php echo e($data['generated_at']); ?></p>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
(function () {
    const isDark = document.documentElement.classList.contains('dark');
    const gridColor = isDark ? 'rgba(255,255,255,0.07)' : 'rgba(0,0,0,0.07)';
    const textColor = isDark ? '#94a3b8' : '#6b7280';

    const daily = <?php echo json_encode($data['daily'], 15, 512) ?>;
    const labels   = Object.keys(daily);
    const balances = labels.map(d => daily[d].balance);
    const inflows  = labels.map(d => daily[d].inflow);
    const outflows = labels.map(d => daily[d].outflow);

    // Thin out labels for readability
    const step = Math.ceil(labels.length / 15);
    const displayLabels = labels.map((l, i) => i % step === 0 ? l : '');

    new Chart(document.getElementById('projectionChart'), {
        type: 'line',
        data: {
            labels: displayLabels,
            datasets: [
                {
                    label: 'Saldo Kas',
                    data: balances,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59,130,246,0.08)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    borderWidth: 2,
                    yAxisID: 'y',
                },
                {
                    label: 'AR Masuk',
                    data: inflows,
                    borderColor: '#10b981',
                    backgroundColor: 'transparent',
                    tension: 0,
                    pointRadius: 0,
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    yAxisID: 'y',
                },
                {
                    label: 'AP Keluar',
                    data: outflows,
                    borderColor: '#ef4444',
                    backgroundColor: 'transparent',
                    tension: 0,
                    pointRadius: 0,
                    borderWidth: 1.5,
                    borderDash: [4, 4],
                    yAxisID: 'y',
                },
            ],
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { labels: { color: textColor, boxWidth: 12, font: { size: 11 } } },
                tooltip: {
                    callbacks: {
                        label: ctx => ctx.dataset.label + ': Rp ' + ctx.parsed.y.toLocaleString('id-ID'),
                    },
                },
            },
            scales: {
                x: { ticks: { color: textColor, font: { size: 10 } }, grid: { color: gridColor } },
                y: {
                    ticks: {
                        color: textColor,
                        font: { size: 10 },
                        callback: v => 'Rp ' + (v / 1_000_000).toFixed(1) + 'jt',
                    },
                    grid: { color: gridColor },
                },
            },
        },
    });
})();
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/reports/cash-flow-projection.blade.php ENDPATH**/ ?>