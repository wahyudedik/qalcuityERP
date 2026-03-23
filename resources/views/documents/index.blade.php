<x-app-layout>
    <x-slot name="header">Manajemen Dokumen</x-slot>

    <div class="space-y-6">

        {{-- Upload Form --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Unggah Dokumen Baru</h2>
            <form method="POST" action="{{ route('documents.store') }}" enctype="multipart/form-data"
                  class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Judul Dokumen *</label>
                    <input type="text" name="title" required placeholder="Nama dokumen..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Kategori</label>
                    <input type="text" name="category" placeholder="Kontrak, Invoice, SOP..."
                        list="category-list"
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                    <datalist id="category-list">
                        @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                        @endforeach
                        <option value="Kontrak">
                        <option value="Invoice">
                        <option value="SOP">
                        <option value="Laporan">
                        <option value="Lainnya">
                    </datalist>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">Deskripsi</label>
                    <input type="text" name="description" placeholder="Keterangan singkat..."
                        class="w-full bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 dark:text-slate-400 mb-1">File * (maks 20MB)</label>
                    <input type="file" name="file" required
                        class="w-full text-sm text-gray-500 dark:text-slate-400 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-500 cursor-pointer">
                </div>
                <div class="sm:col-span-2 lg:col-span-4 flex justify-end">
                    <button type="submit"
                        class="px-5 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">
                        Unggah Dokumen
                    </button>
                </div>
            </form>
        </div>

        {{-- Filter --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul atau deskripsi..."
                class="flex-1 min-w-48 bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
            <select name="category"
                class="bg-white dark:bg-[#1e293b] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $cat }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm font-medium rounded-xl transition">Filter</button>
            @if(request()->hasAny(['search','category']))
            <a href="{{ route('documents.index') }}" class="px-4 py-2 border border-gray-200 dark:border-white/10 text-gray-600 dark:text-slate-300 text-sm font-medium rounded-xl hover:bg-gray-100 dark:hover:bg-white/5 transition">Reset</a>
            @endif
        </form>

        {{-- Document List --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            @if($documents->isEmpty())
                <div class="px-6 py-16 text-center text-gray-400 dark:text-slate-500 text-sm">
                    <svg class="w-12 h-12 mx-auto mb-3 opacity-30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Belum ada dokumen.
                </div>
            @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Dokumen</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Kategori</th>
                            <th class="px-6 py-3 text-left hidden md:table-cell">Ukuran</th>
                            <th class="px-6 py-3 text-left hidden lg:table-cell">Diunggah oleh</th>
                            <th class="px-6 py-3 text-left hidden sm:table-cell">Tanggal</th>
                            <th class="px-6 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @foreach($documents as $doc)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center shrink-0">
                                        @php
                                            $icon = match(true) {
                                                str_contains($doc->file_type, 'pdf') => '📄',
                                                str_contains($doc->file_type, 'image') => '🖼️',
                                                str_contains($doc->file_type, 'spreadsheet') || str_contains($doc->file_name, '.xls') => '📊',
                                                str_contains($doc->file_type, 'word') || str_contains($doc->file_name, '.doc') => '📝',
                                                default => '📎',
                                            };
                                        @endphp
                                        <span class="text-sm">{{ $icon }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium text-gray-900 dark:text-white">{{ $doc->title }}</p>
                                        @if($doc->description)
                                        <p class="text-xs text-gray-400 dark:text-slate-500 truncate max-w-xs">{{ $doc->description }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-3 hidden sm:table-cell">
                                <span class="px-2 py-0.5 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-full text-xs font-medium">
                                    {{ $doc->category ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden md:table-cell">{{ $doc->file_size_human }}</td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 hidden lg:table-cell">{{ $doc->uploader?->name ?? '-' }}</td>
                            <td class="px-6 py-3 text-gray-400 dark:text-slate-500 hidden sm:table-cell">{{ $doc->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('documents.download', $doc) }}"
                                        class="px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium rounded-lg transition">
                                        Unduh
                                    </a>
                                    @if(auth()->user()->hasRole(['admin','manager']) || $doc->uploaded_by === auth()->id())
                                    <form method="POST" action="{{ route('documents.destroy', $doc) }}"
                                          onsubmit="return confirm('Hapus dokumen ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit"
                                            class="px-3 py-1.5 bg-red-500/20 hover:bg-red-500/30 text-red-400 text-xs font-medium rounded-lg transition">
                                            Hapus
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-100 dark:border-white/5">
                {{ $documents->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
