<div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5 h-full">
    <p class="text-sm font-semibold text-gray-900 dark:text-white mb-4">Ringkasan Cepat</p>
    <div class="space-y-3">
        @php
        $stats = [
            ['label' => 'Order Pending',         'value' => $data['sales']['pending_orders'] ?? 0,                                         'color' => 'text-yellow-400'],
            ['label' => 'PO Belum Diterima',     'value' => $data['finance']['pending_po'] ?? 0,                                           'color' => 'text-orange-400'],
            ['label' => 'Total Pelanggan',       'value' => $data['hrm']['total_customers'] ?? 0,                                          'color' => 'text-blue-400'],
            ['label' => 'Total Gudang',          'value' => $data['inventory']['total_warehouses'] ?? 0,                                   'color' => 'text-slate-300'],
            ['label' => 'Pengeluaran Bulan Ini', 'value' => 'Rp ' . number_format($data['finance']['expense'] ?? 0, 0, ',', '.'),          'color' => 'text-red-400'],
        ];
        @endphp
        @foreach($stats as $stat)
        <div class="flex items-center justify-between">
            <span class="text-sm text-gray-500 dark:text-slate-400">{{ $stat['label'] }}</span>
            <span class="text-sm font-semibold {{ $stat['color'] }}">{{ $stat['value'] }}</span>
        </div>
        @endforeach
    </div>
    <div class="mt-5 pt-4 border-t border-gray-200 dark:border-white/10">
        <a href="{{ route('chat.index') }}"
           class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white text-sm font-semibold py-2.5 rounded-xl transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
            Tanya Qalcuity AI
        </a>
    </div>
</div>
