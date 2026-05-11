<x-app-layout>
    <x-slot name="header">Periode Akuntansi</x-slot>

    <div class="space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="flex justify-end">
            <button onclick="document.getElementById('modal-add-period').classList.remove('hidden')"
                class="bg-indigo-600 hover:bg-indigo-700 text-white text-sm px-4 py-2 rounded-lg">
                + Buat Periode Baru
            </button>
        </div>

        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Nama Periode</th>
                        <th class="px-4 py-3 text-left">Mulai</th>
                        <th class="px-4 py-3 text-left">Selesai</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-left">Ditutup Oleh</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($periods as $period)
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-3 font-medium text-white">{{ $period->name }}</td>
                        <td class="px-4 py-3">{{ $period->start_date->format('d M Y') }}</td>
                        <td class="px-4 py-3">{{ $period->end_date->format('d M Y') }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="px-2 py-0.5 rounded text-xs
                                {{ $period->status === 'open' ? 'bg-green-500/20 text-green-400' : '' }}
                                {{ $period->status === 'closed' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                {{ $period->status === 'locked' ? 'bg-red-500/20 text-red-400' : '' }}">
                                {{ ucfirst($period->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">
                            {{ $period->closedBy?->name ?? '-' }}
                            @if($period->closed_at) <span class="text-xs">({{ $period->closed_at->format('d/m/Y') }})</span> @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <div class="flex justify-center gap-2">
                                @if($period->status === 'open')
                                <form method="POST" action="{{ route('accounting.periods.close', $period) }}" data-confirm="Tutup periode ini?">
                                    @csrf @method('PATCH')
                                    <button class="text-yellow-400 hover:text-yellow-300 text-xs">Tutup</button>
                                </form>
                                @endif
                                @if($period->status === 'closed')
                                <form method="POST" action="{{ route('accounting.periods.lock', $period) }}" data-confirm="Kunci periode ini? Tidak bisa dibuka kembali.">
                                    @csrf @method('PATCH')
                                    <button class="text-red-400 hover:text-red-300 text-xs">🔒 Kunci</button>
                                </form>
                                @endif
                                @if($period->status === 'locked')
                                <span class="text-gray-600 text-xs">🔒 Terkunci</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="px-4 py-8 text-center text-gray-500">Belum ada periode akuntansi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal Tambah Periode --}}
    <div id="modal-add-period" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-md p-6">
            <h3 class="text-white font-semibold mb-4">Buat Periode Akuntansi</h3>
            <form method="POST" action="{{ route('accounting.periods.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Nama Periode *</label>
                    <input type="text" name="name" required placeholder="Maret 2026"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Mulai *</label>
                        <input type="date" name="start_date" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                    <div>
                        <label class="text-xs text-gray-400 mb-1 block">Tanggal Selesai *</label>
                        <input type="date" name="end_date" required
                            class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                    </div>
                </div>
                <div class="flex gap-3 pt-2">
                    <button type="submit" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg text-sm">Buat</button>
                    <button type="button" onclick="document.getElementById('modal-add-period').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
