<x-app-layout>
    <x-slot name="header">Detail Work Order — {{ $workOrder->number }}</x-slot>

    <div class="space-y-6">
        {{-- Info WO --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $workOrder->number }}</h2>
                    <p class="text-sm text-gray-500 dark:text-slate-400">{{ $workOrder->product->name }}</p>
                </div>
                <div class="flex items-center gap-2">
                    @php
                        $colors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'];
                        $labels = ['pending'=>'Pending','in_progress'=>'Sedang Dikerjakan','completed'=>'Selesai','cancelled'=>'Dibatalkan'];
                        $c = $colors[$workOrder->status] ?? 'gray';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                        {{ $labels[$workOrder->status] ?? $workOrder->status }}
                    </span>
                    @if($workOrder->status === 'pending')
                    <form method="POST" action="{{ route('production.status', $workOrder) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="in_progress">
                        <button type="submit" class="px-3 py-1 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Mulai Produksi</button>
                    </form>
                    @elseif($workOrder->status === 'in_progress')
                    <form method="POST" action="{{ route('production.status', $workOrder) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="completed">
                        <button type="submit" class="px-3 py-1 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesaikan</button>
                    </form>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Target</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ number_format($workOrder->target_quantity, 0, ',', '.') }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Output Bagus</p>
                    <p class="font-semibold text-green-500">{{ $workOrder->totalGoodQty() }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Reject</p>
                    <p class="font-semibold text-red-500">{{ $workOrder->totalRejectQty() }} {{ $workOrder->unit }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Yield Rate</p>
                    <p class="font-semibold text-gray-900 dark:text-white">{{ $workOrder->yieldRate() ?? '-' }}%</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya Material</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($workOrder->material_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Biaya Tenaga Kerja</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($workOrder->labor_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Overhead</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($workOrder->overhead_cost, 0, ',', '.') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Total Biaya</p>
                    <p class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($workOrder->total_cost, 0, ',', '.') }}</p>
                </div>
            </div>

            @if($workOrder->recipe)
            <div class="mt-4 pt-4 border-t border-gray-100 dark:border-white/10">
                <p class="text-xs font-medium text-gray-500 dark:text-slate-400 mb-2">Resep: {{ $workOrder->recipe->name }} (batch {{ $workOrder->recipe->batch_size }} {{ $workOrder->recipe->batch_unit }})</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($workOrder->recipe->ingredients as $ing)
                    <span class="px-2 py-1 text-xs rounded-lg bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-slate-300">
                        {{ $ing->product->name }}: {{ $ing->quantity_per_batch }} {{ $ing->unit }}
                    </span>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        {{-- Catat Output (jika in_progress) --}}
        @if($workOrder->status === 'in_progress')
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h3 class="font-semibold text-gray-900 dark:text-white mb-4">Catat Output Produksi</h3>
            <form method="POST" action="{{ route('production.output', $workOrder) }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty Bagus *</label>
                        <input type="number" name="good_qty" required min="0" step="0.001"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Qty Reject</label>
                        <input type="number" name="reject_qty" min="0" step="0.001" value="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Reject</label>
                        <input type="text" name="reject_reason" placeholder="Opsional"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex items-center justify-between">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="auto_complete" value="1" class="rounded">
                        <span class="text-sm text-gray-700 dark:text-slate-300">Selesaikan WO & tambah stok otomatis</span>
                    </label>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan Output</button>
                </div>
            </form>
        </div>
        @endif

        {{-- Riwayat Output --}}
        @if($workOrder->outputs->isNotEmpty())
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Riwayat Output</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-4 py-3 text-left">Tanggal</th>
                            <th class="px-4 py-3 text-right">Bagus</th>
                            <th class="px-4 py-3 text-right">Reject</th>
                            <th class="px-4 py-3 text-right">Total</th>
                            <th class="px-4 py-3 text-left hidden sm:table-cell">Alasan Reject</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($workOrder->outputs as $out)
                        <tr>
                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-slate-400">{{ $out->created_at->format('d M Y H:i') }}</td>
                            <td class="px-4 py-3 text-right text-green-500 font-medium">{{ $out->good_qty + 0 }}</td>
                            <td class="px-4 py-3 text-right text-red-400">{{ $out->reject_qty + 0 }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">{{ $out->output_qty + 0 }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-xs text-gray-500 dark:text-slate-400">{{ $out->reject_reason ?? '-' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
