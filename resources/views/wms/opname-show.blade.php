<x-app-layout>
    <x-slot name="header">Opname — {{ $stockOpnameSession->number }}</x-slot>

    @php $s = $stockOpnameSession; @endphp
    <div class="flex items-center justify-between mb-4">
        <div>
            <p class="text-sm text-gray-500 dark:text-slate-400">{{ $s->warehouse->name ?? '-' }} ·
                {{ $s->opname_date->format('d/m/Y') }}</p>
        </div>
        @if ($s->status !== 'completed')
            @canmodule('wms', 'edit')
            <form method="POST" action="{{ route('wms.opname.complete', $s) }}"
                onsubmit="return confirm('Selesaikan opname? Stok bin akan diperbarui.')">
                @csrf @method('PATCH')
                <button type="submit"
                    class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Selesaikan
                    Opname</button>
            </form>
            @endcanmodule
        @endif
    </div>

    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-left">Bin</th>
                        <th class="px-4 py-3 text-right">Sistem</th>
                        <th class="px-4 py-3 text-right">Aktual</th>
                        <th class="px-4 py-3 text-right">Selisih</th>
                        @if ($s->status !== 'completed')
                            <th class="px-4 py-3 text-center">Input</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @foreach ($s->items as $item)
                        @php $diff = $item->difference ?? 0; @endphp
                        <tr
                            class="{{ $diff != 0 ? ($diff > 0 ? 'bg-green-50/50 dark:bg-green-500/5' : 'bg-red-50/50 dark:bg-red-500/5') : '' }}">
                            <td class="px-4 py-3 text-gray-900 dark:text-white">{{ $item->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 font-mono text-xs text-gray-500 dark:text-slate-400">
                                {{ $item->bin->code ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-700 dark:text-slate-300">
                                {{ number_format($item->system_qty, 0) }}</td>
                            <td class="px-4 py-3 text-right text-gray-900 dark:text-white">
                                {{ $item->actual_qty !== null ? number_format($item->actual_qty, 0) : '-' }}</td>
                            <td
                                class="px-4 py-3 text-right font-medium {{ $diff > 0 ? 'text-green-500' : ($diff < 0 ? 'text-red-500' : 'text-gray-400') }}">
                                {{ $diff != 0 ? ($diff > 0 ? '+' : '') . number_format($diff, 0) : '-' }}
                            </td>
                            @if ($s->status !== 'completed')
                                <td class="px-4 py-3 text-center">
                                    <form method="POST" action="{{ route('wms.opname.item.update', $item) }}"
                                        class="inline flex items-center justify-center gap-1">
                                        @csrf @method('PATCH')
                                        <input type="number" name="actual_qty"
                                            value="{{ $item->actual_qty ?? $item->system_qty }}" min="0"
                                            step="1"
                                            class="w-20 px-2 py-1 text-xs rounded-lg border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white">
                                        <button type="submit"
                                            class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg">Confirm</button>
                                    </form>
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
