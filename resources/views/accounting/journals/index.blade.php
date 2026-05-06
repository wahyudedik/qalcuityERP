<x-app-layout>
    <x-slot name="header">Jurnal Umum</x-slot>

    <div class="space-y-5">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-50 border border-red-200 text-red-700 rounded-xl px-4 py-3 text-sm">{{ session('error') }}
            </div>
        @endif

        {{-- Filter & Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nomor / deskripsi..."
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 placeholder-gray-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 w-52">
                <select name="status"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status') == 'draft')>Draft</option>
                    <option value="posted" @selected(request('status') == 'posted')>Posted</option>
                    <option value="reversed" @selected(request('status') == 'reversed')>Reversed</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="bg-white border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-800 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500">
                <button
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('journals.recurring') }}"
                    class="bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm px-4 py-2 rounded-lg">🔄 Jurnal
                    Berulang</a>
                <a href="{{ route('journals.create') }}"
                    class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">+ Buat Jurnal</a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white border border-gray-200 rounded-xl overflow-hidden shadow-sm">
            <table class="w-full text-sm text-gray-700">
                <thead class="bg-gray-50 text-gray-500 text-xs uppercase border-b border-gray-200">
                    <tr>
                        <th class="px-4 py-3 text-left">Nomor</th>
                        <th class="px-4 py-3 text-left">Tanggal</th>
                        <th class="px-4 py-3 text-left">Deskripsi</th>
                        <th class="px-4 py-3 text-left">Periode</th>
                        <th class="px-4 py-3 text-right">Total Debit</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($journals as $j)
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-mono text-xs text-indigo-600">{{ $j->number }}</td>
                            <td class="px-4 py-3 text-gray-800">{{ $j->date->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-gray-700">{{ Str::limit($j->description, 50) }}</td>
                            <td class="px-4 py-3 text-gray-400 text-xs">{{ $j->period?->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right text-gray-800">Rp
                                {{ number_format($j->lines->sum('debit'), 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded text-xs
                                {{ $j->status === 'draft' ? 'bg-yellow-100 text-yellow-700' : '' }}
                                {{ $j->status === 'posted' ? 'bg-green-100 text-green-700' : '' }}
                                {{ $j->status === 'reversed' ? 'bg-gray-100 text-gray-600' : '' }}">
                                    {{ ucfirst($j->status) }}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('journals.show', $j) }}"
                                        class="text-indigo-600 hover:text-indigo-800 text-xs">Detail</a>
                                    @if ($j->status === 'draft')
                                        <form method="POST" action="{{ route('journals.post', $j) }}"
                                            onsubmit="return confirm('Post jurnal ini?')">
                                            @csrf @method('PATCH')
                                            <button class="text-green-600 hover:text-green-800 text-xs">Post</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-8 text-center text-gray-400">Belum ada jurnal.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $journals->links() }}
    </div>
</x-app-layout>
