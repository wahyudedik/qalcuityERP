<div class="bg-white rounded-2xl border border-gray-200 p-5 h-full">
    <div class="flex items-start justify-between mb-4">
        <p class="text-xs font-medium text-gray-500 leading-tight">Order Marketplace</p>
        <div class="w-9 h-9 rounded-xl bg-orange-500/20 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
            </svg>
        </div>
    </div>
    <p class="text-2xl font-bold text-gray-900">{{ number_format($data['this_month_orders'] ?? 0) }}</p>
    <p class="text-xs text-gray-400 mt-1">
        {{ ($data['growth_percent'] ?? 0) >= 0 ? '▲' : '▼' }} {{ abs($data['growth_percent'] ?? 0) }}% vs bulan lalu
    </p>
    @if (($data['pending_orders'] ?? 0) > 0)
        <p class="text-xs text-orange-400 mt-2 font-medium">
            {{ number_format($data['pending_orders']) }} order menunggu proses
        </p>
    @endif
</div>
