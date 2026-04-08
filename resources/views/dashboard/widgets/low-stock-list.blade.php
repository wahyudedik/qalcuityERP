<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full">
    <div class="flex items-center justify-between mb-4">
        <p class="text-sm font-semibold text-gray-900 dark:text-white">Stok Menipis</p>
        <span
            class="text-xs bg-red-500/20 text-red-400 font-medium px-2 py-0.5 rounded-full">{{ $data['low_stock_count'] ?? 0 }}
            item</span>
    </div>
    @php
        $lowStockItems = $data['low_stock_items'] ?? [];

        // Convert incomplete Collection objects to array to avoid unserialize errors
        if (is_object($lowStockItems)) {
            try {
                // Try to convert to array if it's a Collection
        if (method_exists($lowStockItems, 'toArray')) {
                    $lowStockItems = $lowStockItems->toArray();
                } else {
                    $lowStockItems = (array) $lowStockItems;
                }
            } catch (\Error $e) {
                // If object is incomplete, default to empty array
                $lowStockItems = [];
            }
        }

        // Now safely check if empty
        $isEmpty =
            is_null($lowStockItems) ||
            (is_array($lowStockItems) && empty($lowStockItems)) ||
            (is_countable($lowStockItems) && count($lowStockItems) === 0);
    @endphp
    @if ($isEmpty)
        <div class="flex flex-col items-center py-6 text-gray-400 dark:text-slate-500">
            <svg class="w-10 h-10 mb-2 text-green-500/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-sm">Semua stok aman</p>
        </div>
    @else
        <div class="space-y-0">
            @foreach ($lowStockItems as $item)
                <div
                    class="flex items-center justify-between py-2.5 border-b border-gray-100 dark:border-white/5 last:border-0">
                    <div>
                        <p class="text-sm font-medium text-gray-900 dark:text-white">
                            {{ $item->product->name ?? 'Unknown Product' }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">
                            {{ $item->warehouse->name ?? 'Unknown Warehouse' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-bold text-red-400">{{ $item->quantity ?? 0 }}
                            {{ $item->product->unit ?? '' }}</p>
                        <p class="text-xs text-gray-400 dark:text-slate-500">min: {{ $item->product->stock_min ?? 0 }}
                        </p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
