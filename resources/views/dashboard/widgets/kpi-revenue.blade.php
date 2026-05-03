<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Pendapatan Bulan Ini</p>
        <div class="w-9 h-9 rounded-xl bg-blue-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($data['income'] ?? 0, 0, ',', '.') }}</p>
    <p class="text-xs text-gray-400 mt-1">Profit: Rp {{ number_format($data['profit'] ?? 0, 0, ',', '.') }}</p>
</div>
