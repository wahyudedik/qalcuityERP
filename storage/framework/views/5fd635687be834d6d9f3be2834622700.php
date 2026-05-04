<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <p class="text-sm font-semibold text-gray-900 mb-4">Keuangan 6 Bulan Terakhir</p>
    <div style="height:200px;position:relative">
        <canvas id="financeChart"></canvas>
    </div>
</div>
<script>
document.addEventListener('widgets-ready', function () {
    const ctx = document.getElementById('financeChart');
    if (!ctx || ctx._chartInit) return;
    ctx._chartInit = true;
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: <?php echo json_encode(array_column($data['chart'] ?? [], 'month'), 512) ?>,
            datasets: [
                { label: 'Pemasukan',   data: <?php echo json_encode(array_column($data['chart'] ?? [], 'income'), 512) ?>,  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#10b981' },
                { label: 'Pengeluaran', data: <?php echo json_encode(array_column($data['chart'] ?? [], 'expense'), 512) ?>, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)',   tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#ef4444' },
            ]
        },
        options: { ...window._chartDefaults,
            plugins: { legend: { display: true, labels: { font: { size: 11, family: 'Inter' }, color: window._chartTickColor, boxWidth: 10, usePointStyle: true } } },
            scales: {
                y: { ticks: { callback: v => 'Rp' + (v/1e6).toFixed(1) + 'jt', font: window._chartTickFont, color: window._chartTickColor }, grid: { color: window._chartGridColor } },
                x: { ticks: { font: window._chartTickFont, color: window._chartTickColor }, grid: { display: false } }
            }
        }
    });
});
</script>
<?php /**PATH E:\PROJEKU\qalcuityERP\resources\views/dashboard/widgets/chart-finance.blade.php ENDPATH**/ ?>