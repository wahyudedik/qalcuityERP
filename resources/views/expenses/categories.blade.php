<x-app-layout>
    <x-slot name="header">Kategori Pengeluaran</x-slot>

    <div class="max-w-4xl mx-auto space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        {{-- Form Tambah --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h2 class="font-semibold text-gray-900 mb-4">Tambah Kategori</h2>
            <form method="POST" action="{{ route('expenses.categories.store') }}" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Nama Kategori</label>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="cth: Biaya Operasional"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kode</label>
                    <input type="text" name="code" value="{{ old('code') }}" required placeholder="cth: OPS"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('code') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Tipe</label>
                    <select name="type" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="operational">Operasional</option>
                        <option value="cogs">HPP (COGS)</option>
                        <option value="marketing">Marketing</option>
                        <option value="hr">SDM / HR</option>
                        <option value="admin">Administrasi</option>
                        <option value="other">Lainnya</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">
                        Kode Akun GL
                        <span class="text-gray-400 font-normal">(opsional — override akun beban di jurnal)</span>
                    </label>
                    <input type="text" name="coa_account_code" value="{{ old('coa_account_code') }}" placeholder="cth: 5206 (kosong = otomatis)"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 font-mono">
                </div>
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Deskripsi</label>
                    <input type="text" name="description" value="{{ old('description') }}" placeholder="Opsional"
                        class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="sm:col-span-2 flex justify-end">
                    <button type="submit" class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm font-medium transition">Tambah Kategori</button>
                </div>
            </form>
        </div>

        {{-- Daftar --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900">Daftar Kategori</h2>
                <a href="{{ route('expenses.index') }}" class="text-sm text-gray-400 hover:text-white transition">← Kembali</a>
            </div>
            @if($categories->isEmpty())
                <div class="px-6 py-10 text-center text-gray-400 text-sm">Belum ada kategori.</div>
            @else
                <div class="divide-y divide-gray-100">
                    @foreach($categories as $cat)
                        <div class="px-6 py-4 flex items-center gap-4">
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium text-gray-900 text-sm">{{ $cat->name }}</span>
                                    <span class="font-mono text-xs px-2 py-0.5 bg-gray-100 text-gray-500 rounded-full">{{ $cat->code }}</span>
                                    @if(!$cat->is_active)
                                        <span class="text-xs px-2 py-0.5 bg-gray-500/20 text-gray-400 rounded-full">Nonaktif</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-500 mt-0.5">
                                    {{ ucfirst($cat->type) }} &bull; {{ $cat->expense_count }} transaksi
                                    @if($cat->coa_account_code)
                                    &bull; <span class="font-mono text-blue-500">GL: {{ $cat->coa_account_code }}</span>
                                    @else
                                    &bull; <span class="text-gray-400">GL: otomatis</span>
                                    @endif
                                    @if($cat->description) &bull; {{ $cat->description }} @endif
                                </p>
                            </div>
                            <form method="POST" action="{{ route('expenses.categories.update', $cat) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="name" value="{{ $cat->name }}">
                                <input type="hidden" name="code" value="{{ $cat->code }}">
                                <input type="hidden" name="type" value="{{ $cat->type }}">
                                <input type="hidden" name="coa_account_code" value="{{ $cat->coa_account_code }}">
                                <input type="hidden" name="description" value="{{ $cat->description }}">
                                <input type="hidden" name="is_active" value="{{ $cat->is_active ? '0' : '1' }}">
                                <button type="submit"
                                    class="text-xs px-3 py-1.5 rounded-lg border {{ $cat->is_active ? 'border-yellow-500/30 text-yellow-400 hover:bg-yellow-500/10' : 'border-green-500/30 text-green-400 hover:bg-green-500/10' }} transition">
                                    {{ $cat->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                </button>
                            </form>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
