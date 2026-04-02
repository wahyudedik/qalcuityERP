<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full">
    <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Keuangan 6 Bulan Terakhir</p>
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
            labels: @json(array_column($data['chart'] ?? [], 'month')),
            datasets: [
                { label: 'Pemasukan',   data: @json(array_column($data['chart'] ?? [], 'income')),  borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#10b981' },
                { label: 'Pengeluaran', data: @json(array_column($data['chart'] ?? [], 'expense')), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.1)',   tension: 0.4, fill: true, pointRadius: 3, pointBackgroundColor: '#ef4444' },
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
