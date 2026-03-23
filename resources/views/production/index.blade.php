<x-app-layout>
    <x-slot name="header">Produksi & Work Order</x-slot>

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-6">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Pending</p>
            <p class="text-2xl font-bold text-amber-500">{{ $stats['pending'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Sedang Dikerjakan</p>
            <p class="text-2xl font-bold text-blue-500">{{ $stats['in_progress'] }}</p>
        </div>
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-4">
            <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Selesai</p>
            <p class="text-2xl font-bold text-green-500">{{ $stats['completed'] }}</p>
        </div>
    </div>

    {{-- Toolbar --}}
    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-2 flex-1">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor WO / produk..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
            <select name="status" class="px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-[#1e293b] text-gray-900 dark:text-white">
                <option value="">Semua Status</option>
                <option value="pending" @selected(request('status')==='pending')>Pending</option>
                <option value="in_progress" @selected(request('status')==='in_progress')>Sedang Dikerjakan</option>
                <option value="completed" @selected(request('status')==='completed')>Selesai</option>
                <option value="cancelled" @selected(request('status')==='cancelled')>Dibatalkan</option>
            </select>
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
        <div class="flex gap-2">
            <a href="{{ route('production.recipes') }}" class="px-3 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                Resep/BOM
            </a>
            <button onclick="document.getElementById('modal-create-wo').classList.remove('hidden')"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Buat WO</button>
        </div>
    </div>

    {{-- Table --}}
    <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor WO</th>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right hidden sm:table-cell">Target</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Output</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                    @forelse($workOrders as $wo)
                    <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs font-medium text-gray-900 dark:text-white">
                            <a href="{{ route('production.show', $wo) }}" class="hover:text-blue-500">{{ $wo->number }}</a>
                        </td>
                        <td class="px-4 py-3 text-gray-700 dark:text-slate-300">
                            {{ $wo->product->name ?? '-' }}
                            @if($wo->recipe) <span class="text-xs text-gray-400 dark:text-slate-500">({{ $wo->recipe->name }})</span> @endif
                        </td>
                        <td class="px-4 py-3 text-right hidden sm:table-cell text-gray-900 dark:text-white">
                            {{ number_format($wo->target_quantity, 0, ',', '.') }} {{ $wo->unit }}
                        </td>
                        <td class="px-4 py-3 text-right hidden md:table-cell">
                            @php $good = $wo->totalGoodQty(); $reject = $wo->totalRejectQty(); @endphp
                            <span class="text-green-500">{{ $good }}</span>
                            @if($reject > 0) <span class="text-red-400 text-xs">/ {{ $reject }} reject</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            @php
                                $colors = ['pending'=>'amber','in_progress'=>'blue','completed'=>'green','cancelled'=>'red'];
                                $labels = ['pending'=>'Pending','in_progress'=>'Dikerjakan','completed'=>'Selesai','cancelled'=>'Batal'];
                                $c = $colors[$wo->status] ?? 'gray';
                            @endphp
                            <span class="px-2 py-0.5 rounded-full text-xs bg-{{ $c }}-100 text-{{ $c }}-700 dark:bg-{{ $c }}-500/20 dark:text-{{ $c }}-400">
                                {{ $labels[$wo->status] ?? $wo->status }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex items-center justify-center gap-1">
                                <a href="{{ route('production.show', $wo) }}" class="text-xs px-2 py-1 border border-gray-200 dark:border-white/10 rounded-lg text-gray-600 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-white/5">
                                    Detail
                                </a>
                                @if($wo->status === 'pending')
                                <form method="POST" action="{{ route('production.status', $wo) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <input type="hidden" name="status" value="in_progress">
                                    <button type="submit" class="text-xs px-2 py-1 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Mulai</button>
                                </form>
                                @elseif($wo->status === 'in_progress')
                                <button onclick="openOutputModal('{{ $wo->id }}','{{ $wo->number }}')"
                                    class="text-xs px-2 py-1 bg-green-600 text-white rounded-lg hover:bg-green-700">Output</button>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">Belum ada work order.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($workOrders->hasPages())
        <div class="px-4 py-3 border-t border-gray-100 dark:border-white/5">{{ $workOrders->links() }}</div>
        @endif
    </div>

    {{-- Modal Buat WO --}}
    <div id="modal-create-wo" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-lg shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Buat Work Order</h3>
                <button onclick="document.getElementById('modal-create-wo').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form method="POST" action="{{ route('production.store') }}" class="p-6 space-y-4">
                @csrf
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Produk *</label>
                        <select name="product_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Pilih Produk --</option>
                            @foreach($products as $p)<option value="{{ $p->id }}">{{ $p->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Resep/BOM</label>
                        <select name="recipe_id" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- Tanpa Resep --</option>
                            @foreach($recipes as $r)<option value="{{ $r->id }}">{{ $r->name }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Target Produksi *</label>
                        <input type="number" name="target_quantity" required min="0.001" step="0.001" placeholder="100"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya Tenaga Kerja</label>
                        <input type="number" name="labor_cost" min="0" step="1000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Biaya Overhead</label>
                        <input type="number" name="overhead_cost" min="0" step="1000" placeholder="0"
                            class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-create-wo').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat WO</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Catat Output --}}
    <div id="modal-output" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl w-full max-w-md shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h3 class="font-semibold text-gray-900 dark:text-white">Catat Output Produksi</h3>
                <button onclick="document.getElementById('modal-output').classList.add('hidden')" class="text-gray-400 hover:text-gray-600 dark:hover:text-white">✕</button>
            </div>
            <form id="form-output" method="POST" class="p-6 space-y-4">
                @csrf
                <p class="text-sm text-gray-600 dark:text-slate-400">WO: <span id="output-wo" class="font-mono font-semibold text-gray-900 dark:text-white"></span></p>
                <div class="grid grid-cols-2 gap-3">
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
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 dark:text-slate-400 mb-1">Alasan Reject</label>
                    <input type="text" name="reject_reason" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-[#0f172a] text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="auto_complete" value="1" class="rounded">
                    <span class="text-sm text-gray-700 dark:text-slate-300">Selesaikan WO & tambah stok otomatis</span>
                </label>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('modal-output').classList.add('hidden')" class="px-4 py-2 text-sm border border-gray-200 dark:border-white/10 rounded-xl text-gray-600 dark:text-slate-300">Batal</button>
                    <button type="submit" class="px-4 py-2 text-sm bg-green-600 text-white rounded-xl hover:bg-green-700">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
    <script>
    function openOutputModal(id, number) {
        document.getElementById('output-wo').textContent = number;
        document.getElementById('form-output').action = '/production/' + id + '/output';
        document.getElementById('modal-output').classList.remove('hidden');
    }
    </script>
    @endpush
</x-app-layout>
