<x-app-layout>
    <x-slot name="header">Rekonsiliasi Bank</x-slot>

    <div class="space-y-6">

        {{-- Import CSV --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <h2 class="font-semibold text-gray-900 dark:text-white mb-4">Import Mutasi Rekening</h2>
            <form method="POST" action="{{ route('bank.import') }}" enctype="multipart/form-data" class="flex flex-wrap gap-3 items-end">
                @csrf
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">Rekening Bank</label>
                    <select name="bank_account_id" required class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                        <option value="">Pilih rekening</option>
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}">{{ $acc->bank_name }} — {{ $acc->account_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1.5">File CSV Mutasi</label>
                    <input type="file" name="csv_file" accept=".csv,.txt" required
                        class="bg-gray-50 dark:bg-[#0f172a] border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:border-blue-500">
                </div>
                <button type="submit" class="px-4 py-2 bg-blue-600 text-gray-900 dark:text-white rounded-xl text-sm font-medium hover:bg-blue-500 transition">
                    Import
                </button>
            </form>
            <p class="text-xs text-gray-400 dark:text-slate-500 mt-2">Format CSV: tanggal, deskripsi, tipe (debit/kredit), jumlah</p>
        </div>

        {{-- Statements Table --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200 dark:border-white/10 flex items-center justify-between">
                <h2 class="font-semibold text-gray-900 dark:text-white">Mutasi Rekening</h2>
                <div class="flex gap-2 text-xs">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full">{{ $statements->where('status','matched')->count() }} matched</span>
                    <span class="px-2 py-1 bg-amber-500/20 text-amber-400 rounded-full">{{ $statements->where('status','unmatched')->count() }} unmatched</span>
                </div>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 text-xs text-gray-500 dark:text-slate-400 uppercase">
                        <tr>
                            <th class="px-6 py-3 text-left">Tanggal</th>
                            <th class="px-6 py-3 text-left">Rekening</th>
                            <th class="px-6 py-3 text-left">Deskripsi</th>
                            <th class="px-6 py-3 text-left">Tipe</th>
                            <th class="px-6 py-3 text-right">Jumlah</th>
                            <th class="px-6 py-3 text-left">Status</th>
                            <th class="px-6 py-3 text-left">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($statements as $stmt)
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5">
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 whitespace-nowrap">{{ $stmt->transaction_date->format('d M Y') }}</td>
                            <td class="px-6 py-3 text-gray-500 dark:text-slate-400 text-xs">{{ $stmt->bankAccount?->bank_name }}</td>
                            <td class="px-6 py-3 text-gray-900 dark:text-white max-w-xs truncate">{{ $stmt->description }}</td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $stmt->type === 'credit' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $stmt->type === 'credit' ? 'Kredit' : 'Debit' }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-right font-medium {{ $stmt->type === 'credit' ? 'text-green-400' : 'text-red-400' }}">
                                Rp {{ number_format($stmt->amount, 0, ',', '.') }}
                            </td>
                            <td class="px-6 py-3">
                                <span class="px-2 py-0.5 rounded-full text-xs font-medium
                                    {{ $stmt->status === 'matched' ? 'bg-green-500/20 text-green-400' : 'bg-amber-500/20 text-amber-400' }}">
                                    {{ $stmt->status === 'matched' ? 'Matched' : 'Unmatched' }}
                                </span>
                            </td>
                            <td class="px-6 py-3">
                                @if($stmt->status === 'unmatched')
                                <form method="POST" action="{{ route('bank.match', $stmt) }}">
                                    @csrf
                                    <button type="submit" class="text-xs text-blue-400 hover:underline">Cocokkan</button>
                                </form>
                                @else
                                <span class="text-xs text-slate-600">—</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-6 py-10 text-center text-gray-400 dark:text-slate-500">Belum ada data mutasi. Import file CSV terlebih dahulu.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 dark:border-white/10">
                {{ $statements->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
