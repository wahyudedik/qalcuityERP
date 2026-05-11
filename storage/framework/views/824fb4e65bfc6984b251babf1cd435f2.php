<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-semibold text-gray-900">Penjualan 7 Hari Terakhir</p>
        <a href="<?php echo e(route('reports.index')); ?>" class="text-xs text-blue-400 hover:underline">Lihat laporan →</a>
    </div>
    <div style="height:200px;position:relative">
        <canvas id="salesChart"></canvas>
    </div>
</div>
<script>
document.addEventListener('widgets-ready', function () {
    const ctx = document.getElementById('salesChart');
    if (!ctx || ctx._chartInit) return;
    ctx._chartInit = true;
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode(array_column($data['chart'] ?? [], 'date'), 512) ?>,
            datasets: [{ label: 'Penjualan', data: <?php echo json_encode(array_column($data['chart'] ?? [], 'total'), 512) ?>,
                backgroundColor: 'rgba(59,130,246,0.2)', borderColor: '#3b82f6',
                borderWidth: 2, borderRadius: 8, borderSkipped: false }]
        },
        options: { ...window._chartDefaults, scales: {
            y: { ticks: { callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt', font: window._chartTickFont, color: window._chartTickColor }, grid: { color: window._chartGridColor } },
            x: { ticks: { font: window._chartTickFont, color: window._chartTickColor }, grid: { display: false } }
        }}
    });
});
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/dashboard/widgets/chart-sales.blade.php ENDPATH**/ ?>