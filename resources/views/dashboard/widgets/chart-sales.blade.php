<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Penjualan 7 Hari Terakhir</p>
        <a href="{{ route('reports.index') }}" class="text-xs text-blue-400 hover:underline">Lihat laporan →</a>
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
            labels: @json(array_column($data['chart'] ?? [], 'date')),
            datasets: [{ label: 'Penjualan', data: @json(array_column($data['chart'] ?? [], 'total')),
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
