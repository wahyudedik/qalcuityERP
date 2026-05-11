@extends('layouts.app')

@section('content')
@php $title = 'Cost Center / Divisi'; @endphp
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-semibold text-gray-900">Cost Center / Divisi</h2>
            <p class="text-sm text-slate-500 mt-0.5">Kelola divisi, cabang, dan proyek untuk segment reporting</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('cost-centers.report') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Laporan Segment
            </a>
            @canmodule('cost_centers', 'create')
            <button onclick="document.getElementById('modal-add').classList.remove('hidden')"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-sm font-medium bg-blue-600 hover:bg-blue-700 text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Cost Center
            </button>
            @endcanmodule
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" class="flex gap-3 flex-wrap">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama..."
            class="px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 w-56">
        <select name="type" class="px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            <option value="">Semua Tipe</option>
            <option value="department" @selected(request('type')=='department')>Departemen</option>
            <option value="branch" @selected(request('type')=='branch')>Cabang</option>
            <option value="project" @selected(request('type')=='project')>Proyek</option>
            <option value="product_line" @selected(request('type')=='product_line')>Lini Produk</option>
        </select>
        <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 text-white hover:bg-blue-700 transition">Filter</button>
        @if(request()->hasAny(['search','type']))
        <a href="{{ route('cost-centers.index') }}" class="px-4 py-2 rounded-xl text-sm bg-white border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Reset</a>
        @endif
    </form>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Kode</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Nama</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Tipe</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Induk</th>
                    <th class="text-left px-4 py-3 font-medium text-gray-500">Status</th>
                    <th class="text-right px-4 py-3 font-medium text-gray-500">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @forelse($centers as $cc)
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-4 py-3 font-mono text-blue-600 font-medium">{{ $cc->code }}</td>
                    <td class="px-4 py-3 text-gray-900">
                        {{ $cc->name }}
                        @if($cc->description)
                        <p class="text-xs text-slate-400 mt-0.5">{{ $cc->description }}</p>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php $typeColors = ['department'=>'blue','branch'=>'purple','project'=>'amber','product_line'=>'green']; $c = $typeColors[$cc->type] ?? 'gray'; @endphp
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-{{ $c  }}-100 text-{{ $c }}-700 $c }}-500/20 $c }}-300">
                            {{ $cc->typeLabel() }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-slate-500">{{ $cc->parent?->name ?? '—' }}</td>
                    <td class="px-4 py-3">
                        @if($cc->is_active)
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-green-100 text-green-700">Aktif</span>
                        @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium bg-gray-100 text-gray-500">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right">
                        @canmodule('cost_centers', 'edit')
                        <button onclick='openEdit({{ json_encode(["id"=>$cc->id,"name"=>$cc->name,"type"=>$cc->type,"parent_id"=>$cc->parent_id,"is_active"=>$cc->is_active,"description"=>$cc->description]) }})'
                            class="text-xs text-blue-600 hover:underline mr-3">Edit</button>
                        @endcanmodule
                        @canmodule('cost_centers', 'delete')
                        <form method="POST" action="{{ route('cost-centers.destroy', $cc) }}" class="inline" data-confirm="Hapus cost center {{ $cc->name }}?" data-confirm-type="danger">
                            @csrf @method('DELETE')
                            <button type="submit" class="text-xs text-red-500 hover:underline">Hapus</button>
                        </form>
                        @endcanmodule
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="px-4 py-10 text-center text-slate-400">Belum ada cost center. Tambahkan yang pertama.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

{{-- Modal Tambah --}}
<div id="modal-add" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Tambah Cost Center</h3>
            <button onclick="document.getElementById('modal-add').classList.add('hidden')" class="text-slate-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('cost-centers.store') }}" class="px-6 py-4 space-y-4">
            @csrf
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Kode *</label>
                    <input type="text" name="code" required maxlength="20" placeholder="mis. DIV-01"
                        class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tipe *</label>
                    <select name="type" required class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="department">Departemen</option>
                        <option value="branch">Cabang</option>
                        <option value="project">Proyek</option>
                        <option value="product_line">Lini Produk</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama *</label>
                <input type="text" name="name" required maxlength="100"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Induk (opsional)</label>
                <select name="parent_id" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Tidak ada —</option>
                    @foreach($parents ?? [] as $p)
                    <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
                <input type="text" name="description" maxlength="255"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            @if($errors->any())
            <div class="text-xs text-red-500">{{ $errors->first() }}</div>
            @endif
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-add').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit --}}
<div id="modal-edit" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-md border border-gray-200">
        <div class="flex items-center justify-between px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Edit Cost Center</h3>
            <button onclick="document.getElementById('modal-edit').classList.add('hidden')" class="text-slate-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form id="form-edit" method="POST" class="px-6 py-4 space-y-4">
            @csrf @method('PUT')
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama *</label>
                <input type="text" id="edit-name" name="name" required maxlength="100"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tipe *</label>
                <select id="edit-type" name="type" required class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="department">Departemen</option>
                    <option value="branch">Cabang</option>
                    <option value="project">Proyek</option>
                    <option value="product_line">Lini Produk</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Induk</label>
                <select id="edit-parent" name="parent_id" class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">— Tidak ada —</option>
                    @foreach($parents ?? [] as $p)
                    <option value="{{ $p->id }}">{{ $p->code }} — {{ $p->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Deskripsi</label>
                <input type="text" id="edit-description" name="description" maxlength="255"
                    class="w-full px-3 py-2 rounded-xl text-sm border border-gray-200 bg-white text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" id="edit-active" name="is_active" value="1" class="rounded">
                <label for="edit-active" class="text-sm text-gray-700">Aktif</label>
            </div>
            <div class="flex justify-end gap-3 pt-2">
                <button type="button" onclick="document.getElementById('modal-edit').classList.add('hidden')"
                    class="px-4 py-2 rounded-xl text-sm border border-gray-200 text-gray-600 hover:bg-gray-50 transition">Batal</button>
                <button type="submit" class="px-4 py-2 rounded-xl text-sm bg-blue-600 hover:bg-blue-700 text-white transition">Simpan</button>
            </div>
        </form>
    </div>
</div>

@if($errors->any())
<script>document.getElementById('modal-add').classList.remove('hidden');</script>
@endif

<script>
function openEdit(data) {
    document.getElementById('form-edit').action = '{{ route("cost-centers.index") }}/' + data.id;
    document.getElementById('edit-name').value = data.name;
    document.getElementById('edit-type').value = data.type;
    document.getElementById('edit-parent').value = data.parent_id || '';
    document.getElementById('edit-description').value = data.description || '';
    document.getElementById('edit-active').checked = !!data.is_active;
    document.getElementById('modal-edit').classList.remove('hidden');
}
</script>
@endsection
