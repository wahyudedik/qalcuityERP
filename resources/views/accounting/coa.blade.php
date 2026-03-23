<x-app-layout>
    <x-slot name="header">Bagan Akun (Chart of Accounts)</x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Header Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari kode / nama akun..."
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 w-56">
                <select name="type" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Tipe</option>
                    <option value="asset" @selected(request('type')=='asset')>Aset</option>
                    <option value="liability" @selected(request('type')=='liability')>Kewajiban</option>
                    <option value="equity" @selected(request('type')=='equity')>Ekuitas</option>
                    <option value="revenue" @selected(request('type')=='revenue')>Pendapatan</option>
                    <option value="expense" @selected(request('type')=='expense')>Beban</option>
                </select>
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
            </form>
            <div class="flex gap-2">
                <form method="POST" action="{{ route('accounting.coa.seed') }}">
                    @csrf
                    <button class="bg-yellow-600 hover:bg-yellow-700 text-white text-sm px-4 py-2 rounded-lg">
                        ⚡ Muat COA Default Indonesia
                    </button>
                </form>
                <button onclick="document.getElementById('modal-add-coa').classList.remove('hidden')"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                    + Tambah Akun
                </button>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode</th>
                        <th class="px-4 py-3 text-left">Nama Akun</th>
                        <th class="px-4 py-3 text-left">Tipe</th>
                        <th class="px-4 py-3 text-left">Saldo Normal</th>
                        <th class="px-4 py-3 text-left">Level</th>
                        <th class="px-4 py-3 text-left">Induk</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($accounts as $acc)
                    <tr class="hover:bg-white/5 {{ $acc->is_header ? 'font-semibold text-white' : '' }}">
                        <td class="px-4 py-3 font-mono">{{ $acc->code }}</td>
                        <td class="px-4 py-3" style="padding-left: {{ ($acc->level - 1) * 20 + 16 }}px">
                            {{ $acc->is_header ? '📁 ' : '' }}{{ $acc->name }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $acc->type === 'asset' ? 'bg-blue-500/20 text-blue-400' : '' }}
                                {{ $acc->type === 'liability' ? 'bg-red-500/20 text-red-400' : '' }}
                                {{ $acc->type === 'equity' ? 'bg-purple-500/20 text-purple-400' : '' }}
                                {{ $acc->type === 'revenue' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $acc->type === 'expense' ? 'bg-orange-500/20 text-orange-400' : '' }}">
                                {{ $acc->getTypeLabel() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 capitalize">{{ $acc->normal_balance }}</td>
                        <td class="px-4 py-3">{{ $acc->level }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ $acc->parent?->code }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs {{ $acc->is_active ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                                {{ $acc->is_active ? 'Aktif' : 'Nonaktif' }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if(!$acc->is_header)
                            <form method="POST" action="{{ route('accounting.coa.destroy', $acc) }}" onsubmit="return confirm('Hapus akun ini?')">
                                @csrf @method('DELETE')
                                <button class="text-red-400 hover:text-red-300 text-xs">Hapus</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        Belum ada akun. Klik "Muat COA Default Indonesia" untuk memulai.
                    </td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Akun --}}
    <div id="modal-add-coa" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-lg p-6">
            <h3 class="text-white font-semibold mb-4">Tambah Akun Baru</h3>
            <form method="POST" action="{{ route('accounting.coa.store') }}" class="space-y-4">
                @csrf
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Kode Akun *</label>
                        <input type="text" name="code" required placeholder="1101"
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Level *</label>
                        <select name="level" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="1">1 - Header Utama</option>
                            <option value="2">2 - Sub Header</option>
                            <option value="3" selected>3 - Detail</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Nama Akun *</label>
                    <input type="text" name="name" required placeholder="Kas"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tipe *</label>
                        <select name="type" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="asset">Aset</option>
                            <option value="liability">Kewajiban</option>
                            <option value="equity">Ekuitas</option>
                            <option value="revenue">Pendapatan</option>
                            <option value="expense">Beban</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Saldo Normal *</label>
                        <select name="normal_balance" required class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                            <option value="debit">Debit</option>
                            <option value="credit">Kredit</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Akun Induk</label>
                    <select name="parent_id" class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                        <option value="">— Tidak ada —</option>
                        @foreach($headers as $h)
                        <option value="{{ $h->id }}">{{ $h->code }} - {{ $h->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_header" value="1" id="is_header" class="rounded">
                    <label for="is_header" class="text-sm text-gray-300">Ini adalah akun header (tidak bisa diposting)</label>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Simpan</button>
                    <button type="button" onclick="document.getElementById('modal-add-coa').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
