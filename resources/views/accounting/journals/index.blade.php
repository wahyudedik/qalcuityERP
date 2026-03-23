<x-app-layout>
    <x-slot name="header">Jurnal Umum</x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Filter & Actions --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            <form method="GET" class="flex flex-wrap gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor / deskripsi..."
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-500 focus:outline-none focus:border-indigo-500 w-52">
                <select name="status" class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    <option value="">Semua Status</option>
                    <option value="draft" @selected(request('status')=='draft')>Draft</option>
                    <option value="posted" @selected(request('status')=='posted')>Posted</option>
                    <option value="reversed" @selected(request('status')=='reversed')>Reversed</option>
                </select>
                <input type="date" name="date_from" value="{{ request('date_from') }}"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                <input type="date" name="date_to" value="{{ request('date_to') }}"
                    class="bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                <button class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">Filter</button>
            </form>
            <div class="flex gap-2">
                <a href="{{ route('journals.recurring') }}" class="bg-white/10 hover:bg-white/20 text-white text-sm px-4 py-2 rounded-lg">🔄 Jurnal Berulang</a>
                <a href="{{ route('journals.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">+ Buat Jurnal</a>
            </div>
        </div>

        {{-- Table --}}
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
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
                <tbody class="divide-y divide-white/5">
                    @forelse($journals as $j)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-mono text-xs text-indigo-400">{{ $j->number }}</td>
                        <td class="px-4 py-3">{{ $j->date->format('d/m/Y') }}</td>
                        <td class="px-4 py-3">{{ Str::limit($j->description, 50) }}</td>
                        <td class="px-4 py-3 text-gray-500 text-xs">{{ $j->period?->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($j->lines->sum('debit'), 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $j->status === 'draft' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                {{ $j->status === 'posted' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $j->status === 'reversed' ? 'bg-gray-500/20 text-gray-400' : '' }}">
                                {{ ucfirst($j->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                <a href="{{ route('journals.show', $j) }}" class="text-indigo-400 hover:text-indigo-300 text-xs">Detail</a>
                                @if($j->status === 'draft')
                                <form method="POST" action="{{ route('journals.post', $j) }}" onsubmit="return confirm('Post jurnal ini?')">
                                    @csrf @method('PATCH')
                                    <button class="text-green-400 hover:text-green-300 text-xs">Post</button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="px-4 py-8 text-center text-gray-500">Belum ada jurnal.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $journals->links() }}
    </div>
</x-app-layout>
