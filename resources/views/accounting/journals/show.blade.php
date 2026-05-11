<x-app-layout>
    <x-slot name="header">Detail Jurnal {{ $journal->number }}</x-slot>

    <div class="max-w-4xl mx-auto space-y-5">

        @if (session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                {{ session('error') }}</div>
        @endif

        {{-- Header Info --}}
        <div class="bg-white/5 border border-white/10 rounded-xl p-5">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-gray-400 text-xs mb-1">Nomor Jurnal</div>
                    <div class="text-white font-mono font-semibold">{{ $journal->number }}</div>
                </div>
                <div>
                    <div class="text-gray-400 text-xs mb-1">Tanggal</div>
                    <div class="text-white">{{ $journal->date->format('d M Y') }}</div>
                </div>
                <div>
                    <div class="text-gray-400 text-xs mb-1">Periode</div>
                    <div class="text-white">{{ $journal->period?->name ?? '-' }}</div>
                </div>
                <div>
                    <div class="text-gray-400 text-xs mb-1">Status</div>
                    <span
                        class="px-2 py-0.5 rounded text-xs
                        {{ $journal->status === 'draft' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                        {{ $journal->status === 'posted' ? 'bg-green-500/20 text-green-400' : '' }}
                        {{ $journal->status === 'reversed' ? 'bg-gray-500/20 text-gray-400' : '' }}">
                        {{ ucfirst($journal->status) }}
                    </span>
                </div>
                <div class="md:col-span-2">
                    <div class="text-gray-400 text-xs mb-1">Deskripsi</div>
                    <div class="text-white">{{ $journal->description }}</div>
                </div>
                <div>
                    <div class="text-gray-400 text-xs mb-1">Dibuat Oleh</div>
                    <div class="text-white">{{ $journal->user?->name }}</div>
                </div>
                @if ($journal->postedBy)
                    <div>
                        <div class="text-gray-400 text-xs mb-1">Diposting Oleh</div>
                        <div class="text-white">{{ $journal->postedBy?->name }}
                            ({{ $journal->posted_at?->format('d/m/Y H:i') }})</div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Lines --}}
        <div class="bg-white/5 border border-white/10 rounded-xl overflow-hidden">
            <table class="w-full text-sm text-gray-300">
                <thead class="bg-white/5 text-gray-400 text-xs uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Kode Akun</th>
                        <th class="px-4 py-3 text-left">Nama Akun</th>
                        <th class="px-4 py-3 text-left">Keterangan</th>
                        <th class="px-4 py-3 text-right">Debit</th>
                        <th class="px-4 py-3 text-right">Kredit</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach ($journal->lines as $line)
                        <tr>
                            <td class="px-4 py-3 font-mono text-xs">{{ $line->account?->code }}</td>
                            <td class="px-4 py-3">{{ $line->account?->name }}</td>
                            <td class="px-4 py-3 text-gray-500">{{ $line->description ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ $line->debit > 0 ? 'Rp ' . number_format($line->debit, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right">
                                {{ $line->credit > 0 ? 'Rp ' . number_format($line->credit, 0, ',', '.') : '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-white/5 font-semibold text-white">
                    <tr>
                        <td colspan="3" class="px-4 py-3 text-right">TOTAL</td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($journal->totalDebit(), 0, ',', '.') }}
                        </td>
                        <td class="px-4 py-3 text-right">Rp {{ number_format($journal->totalCredit(), 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Actions --}}
        <div class="flex gap-3">
            <a href="{{ route('journals.index') }}"
                class="bg-white/10 hover:bg-white/20 text-white px-4 py-2 rounded-lg text-sm">← Kembali</a>

            @if ($journal->status === 'draft')
                <form method="POST" action="{{ route('journals.post', $journal) }}" data-confirm="Post jurnal ini? Tidak bisa diubah setelah diposting.">
                    @csrf @method('PATCH')
                    <button class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm">Post
                        Jurnal</button>
                </form>
            @endif

            @if ($journal->status === 'posted')
                <button onclick="document.getElementById('modal-reverse').classList.remove('hidden')"
                    class="bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-lg text-sm">↩ Balik
                    Jurnal</button>
            @endif
        </div>
    </div>

    {{-- Modal Reverse --}}
    <div id="modal-reverse" class="hidden fixed inset-0 bg-black/60 flex items-center justify-center z-50 p-4">
        <div class="bg-gray-900 border border-white/10 rounded-2xl w-full max-w-sm p-6">
            <h3 class="text-white font-semibold mb-4">Balik Jurnal</h3>
            <form method="POST" action="{{ route('journals.reverse', $journal) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="text-xs text-gray-400 mb-1 block">Tanggal Pembalik *</label>
                    <input type="date" name="date" value="{{ date('Y-m-d') }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-3 py-2 text-sm text-white focus:outline-none focus:border-indigo-500">
                </div>
                <div class="flex gap-3">
                    <button type="submit"
                        class="flex-1 bg-orange-600 hover:bg-orange-700 text-white py-2 rounded-lg text-sm">Balik</button>
                    <button type="button" onclick="document.getElementById('modal-reverse').classList.add('hidden')"
                        class="flex-1 bg-white/10 hover:bg-white/20 text-white py-2 rounded-lg text-sm">Batal</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
