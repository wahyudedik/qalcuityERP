<x-app-layout>
    <x-slot name="header">Picking List</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <div class="flex gap-2">
            @foreach (['' => 'Semua', 'pending' => 'Pending', 'in_progress' => 'Progress', 'completed' => 'Selesai'] as $v => $l)
                <a href="?status={{ $v }}"
                    class="px-3 py-1.5 text-xs rounded-xl {{ request('status') === $v ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-600' }}">{{ $l }}</a>
            @endforeach
        </div>
        <div class="flex-1"></div>
        @canmodule('wms', 'create')
        <button onclick="document.getElementById('modal-pick').classList.remove('hidden')"
            class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Picking List</button>
        @endcanmodule
    </div>

    <div class="space-y-4">
        @forelse($lists as $list)
            @php $sc = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'gray'][$list->status] ?? 'gray'; @endphp
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span
                            class="font-mono text-sm font-bold text-gray-900">{{ $list->number }}</span>
                        <span
                            class="text-xs text-gray-500 ml-2">{{ $list->warehouse->name ?? '-' }}</span>
                        @if ($list->assignee)
                            <span class="text-xs text-blue-500 ml-2">→ {{ $list->assignee->name }}</span>
                        @endif
                    </div>
                    <div class="flex items-center gap-2">
                        @if (in_array($list->status, ['pending', 'in_progress']))
                            <a href="{{ route('wms.picking.scan', $list) }}"
                                class="inline-flex items-center gap-1 px-3 py-1 text-xs bg-blue-600 hover:bg-blue-700 text-white rounded-xl transition">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 9V6a1 1 0 011-1h3M3 15v3a1 1 0 001 1h3m11-4v3a1 1 0 01-1 1h-3m4-11h-3a1 1 0 00-1 1v3M9 3H6a1 1 0 00-1 1v3m0 6v3a1 1 0 001 1h3m6-10h3a1 1 0 011 1v3" />
                                </svg>
                                Scan & Pick
                            </a>
                        @endif
                        <span
                            class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst(str_replace('_', ' ', $list->status)) }}</span>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="text-xs text-gray-500">
                            <tr>
                                <th class="text-left py-1">Produk</th>
                                <th class="text-left py-1">Bin</th>
                                <th class="text-right py-1">Diminta</th>
                                <th class="text-right py-1">Diambil</th>
                                <th class="text-center py-1">Status</th>
                                <th class="text-center py-1">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($list->items as $item)
                                @php $ic = ['pending'=>'amber','picked'=>'green','short'=>'red'][$item->status] ?? 'gray'; @endphp
                                <tr>
                                    <td class="py-1.5 text-gray-900">{{ $item->product->name ?? '-' }}
                                    </td>
                                    <td class="py-1.5 font-mono text-xs text-gray-500">
                                        {{ $item->bin->code ?? '-' }}</td>
                                    <td class="py-1.5 text-right text-gray-700">
                                        {{ number_format($item->quantity_requested, 0) }}</td>
                                    <td class="py-1.5 text-right text-gray-900">
                                        {{ number_format($item->quantity_picked, 0) }}</td>
                                    <td class="py-1.5 text-center"><span
                                            class="px-1.5 py-0.5 rounded text-[10px] bg-{{ $ic }}-100 text-{{ $ic }}-700 $ic }}-500/20 $ic }}-400">{{ ucfirst($item->status) }}</span>
                                    </td>
                                    <td class="py-1.5 text-center">
                                        @if ($item->status === 'pending')
                                            @canmodule('wms', 'edit')
                                            <form method="POST" action="{{ route('wms.picking.confirm', $item) }}"
                                                class="inline flex items-center gap-1">
                                                @csrf @method('PATCH')
                                                <input type="number" name="quantity_picked"
                                                    value="{{ $item->quantity_requested }}" min="0"
                                                    step="1"
                                                    class="w-16 px-1 py-0.5 text-xs rounded border border-gray-200 bg-gray-50 text-gray-900">
                                                <button type="submit"
                                                    class="text-xs px-2 py-0.5 bg-green-600 text-white rounded">✓</button>
                                            </form>
                                            @endcanmodule
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @empty
            <div class="text-center py-12 text-gray-400 text-sm">Belum ada picking list.</div>
        @endforelse
    </div>
    @if ($lists->hasPages())
        <div class="mt-4">{{ $lists->links() }}</div>
    @endif

    {{-- Modal Create Picking --}}
    <div id="modal-pick" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Picking List</h3>
                <button onclick="document.getElementById('modal-pick').classList.add('hidden')"
                    class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.picking.store') }}" class="p-6 space-y-3">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="{{ $cls }}">
                        @foreach ($warehouses as $w)
                            <option value="{{ $w->id }}">{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div><label class="block text-xs text-gray-600 mb-1">Assign ke</label>
                    <select name="assigned_to" class="{{ $cls }}">
                        <option value="">-- Auto --</option>
                        @foreach ($users as $u)
                            <option value="{{ $u->id }}">{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="pick-items">
                    <p class="text-xs text-gray-400">Item akan ditambahkan setelah simpan (via edit).</p>
                </div>
                <p class="text-xs text-gray-400">Minimal 1 item. Tambah via JS di bawah.</p>
                <div id="pick-lines" class="space-y-2"></div>
                <button type="button" onclick="addPickLine()"
                    class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg">+ Item</button>
                <button type="submit"
                    class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat</button>
            </form>
        </div>
    </div>
    @push('scripts')
        <script>
            let pickIdx = 0;

            function addPickLine() {
                const i = pickIdx++;
                const c = document.getElementById('pick-lines');
                const d = document.createElement('div');
                d.className = 'grid grid-cols-2 gap-2';
                const cls =
                    'w-full px-2 py-1.5 text-xs rounded-lg border border-gray-200 bg-gray-50 text-gray-900';
                d.innerHTML =
                    `<input type="number" name="items[${i}][product_id]" required placeholder="ID Produk" class="${cls}"><input type="number" name="items[${i}][quantity]" required min="0.001" step="1" placeholder="Jml" class="${cls}">`;
                c.appendChild(d);
            }
            addPickLine();
        </script>
    @endpush
</x-app-layout>
