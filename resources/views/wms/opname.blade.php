<x-app-layout>
    <x-slot name="header">Stock Opname</x-slot>

    <div class="flex flex-col sm:flex-row gap-2 mb-4">
        <div class="flex-1"></div>
        @canmodule('wms', 'create')
        <button onclick="document.getElementById('modal-opname').classList.remove('hidden')" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">+ Sesi Opname</button>
        @endcanmodule
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr><th class="px-4 py-3 text-left">Nomor</th><th class="px-4 py-3 text-left">Gudang</th><th class="px-4 py-3 text-center">Tanggal</th><th class="px-4 py-3 text-center">Status</th><th class="px-4 py-3 text-center">Aksi</th></tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($sessions as $s)
                    @php $sc = ['draft'=>'gray','in_progress'=>'amber','completed'=>'green'][$s->status] ?? 'gray'; @endphp
                    <tr>
                        <td class="px-4 py-3 font-mono text-xs text-gray-900">{{ $s->number }}</td>
                        <td class="px-4 py-3 text-gray-700">{{ $s->warehouse?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-center text-xs text-gray-500">{{ $s->opname_date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-center"><span class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst($s->status) }}</span></td>
                        <td class="px-4 py-3 text-center"><a href="{{ route('wms.opname.show', $s) }}" class="text-xs px-2 py-1 border border-gray-200 rounded-lg text-gray-600">Detail</a></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum ada sesi opname.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div id="modal-opname" class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
        <div class="bg-white rounded-2xl w-full max-w-sm shadow-xl">
            <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
                <h3 class="font-semibold text-gray-900">Buat Sesi Opname</h3>
                <button onclick="document.getElementById('modal-opname').classList.add('hidden')" class="text-gray-400 hover:text-gray-600">✕</button>
            </div>
            <form method="POST" action="{{ route('wms.opname.store') }}" class="p-6 space-y-3">
                @csrf
                @php $cls = 'w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900'; @endphp
                <div><label class="block text-xs text-gray-600 mb-1">Gudang *</label>
                    <select name="warehouse_id" required class="{{ $cls }}">@foreach($warehouses ?? [] as $w)<option value="{{ $w->id }}">{{ $w->name }}</option>@endforeach</select>
                </div>
                <div><label class="block text-xs text-gray-600 mb-1">Tanggal *</label><input type="date" name="opname_date" required value="{{ date('Y-m-d') }}" class="{{ $cls }}"></div>
                <button type="submit" class="w-full py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Buat & Auto-populate</button>
            </form>
        </div>
    </div>
</x-app-layout>
