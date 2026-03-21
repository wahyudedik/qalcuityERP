<x-app-layout>
    <x-slot name="header">Invoice</x-slot>

    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
            @php
            $statCards = [
                ['label' => 'Total',       'value' => $stats['total'],   'color' => 'text-blue-600 dark:text-blue-400',   'bg' => 'bg-blue-50 dark:bg-blue-900/20'],
                ['label' => 'Belum Bayar', 'value' => $stats['unpaid'],  'color' => 'text-red-600 dark:text-red-400',     'bg' => 'bg-red-50 dark:bg-red-900/20'],
                ['label' => 'Sebagian',    'value' => $stats['partial'], 'color' => 'text-amber-600 dark:text-amber-400', 'bg' => 'bg-amber-50 dark:bg-amber-900/20'],
                ['label' => 'Lunas',       'value' => $stats['paid'],    'color' => 'text-green-600 dark:text-green-400', 'bg' => 'bg-green-50 dark:bg-green-900/20'],
                ['label' => 'Jatuh Tempo', 'value' => $stats['overdue'], 'color' => 'text-rose-600 dark:text-rose-400',   'bg' => 'bg-rose-50 dark:bg-rose-900/20'],
            ];
            @endphp
            @foreach($statCards as $card)
            <div class="rounded-xl border border-gray-200 dark:border-white/10 p-4 {{ $card['bg'] }}">
                <p class="text-xs text-gray-500 dark:text-slate-400">{{ $card['label'] }}</p>
                <p class="text-2xl font-bold mt-1 {{ $card['color'] }}">{{ $card['value'] }}</p>
            </div>
            @endforeach
        </div>

        {{-- Toolbar --}}
        <div class="flex flex-col sm:flex-row gap-3 items-start sm:items-center justify-between">
            <form method="GET" class="flex gap-2 flex-1 flex-wrap">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari no. invoice / customer..."
                    class="flex-1 min-w-0 rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-4 py-2 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-blue-500">
                <select name="status" class="rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Semua Status</option>
                    <option value="unpaid"  {{ request('status') === 'unpaid'  ? 'selected' : '' }}>Belum Dibayar</option>
                    <option value="partial" {{ request('status') === 'partial' ? 'selected' : '' }}>Sebagian</option>
                    <option value="paid"    {{ request('status') === 'paid'    ? 'selected' : '' }}>Lunas</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-white/20 transition">Cari</button>
            </form>
            <a href="{{ route('invoices.create') }}" class="shrink-0 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Buat Invoice
            </a>
        </div>

        {{-- Table --}}
        <div class="rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden bg-white dark:bg-white/5">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 dark:bg-white/5 border-b border-gray-200 dark:border-white/10">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">No. Invoice</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Customer</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden sm:table-cell">Jatuh Tempo</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider hidden md:table-cell">Sisa</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                        @forelse($invoices as $invoice)
                        @php
                            $isOverdue = $invoice->status !== 'paid' && $invoice->due_date < now();
                            $statusColor = match($invoice->status) {
                                'paid'    => 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400',
                                'partial' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400',
                                default   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400',
                            };
                            $statusLabel = match($invoice->status) {
                                'paid'    => 'Lunas',
                                'partial' => 'Sebagian',
                                default   => 'Belum Bayar',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50 dark:hover:bg-white/5 transition">
                            <td class="px-4 py-3">
                                <a href="{{ route('invoices.show', $invoice) }}" class="font-medium text-blue-600 dark:text-blue-400 hover:underline">{{ $invoice->number }}</a>
                            </td>
                            <td class="px-4 py-3 text-gray-900 dark:text-white">
                                {{ $invoice->customer?->name ?? '-' }}
                                @if($invoice->customer?->company)
                                <span class="block text-xs text-gray-400">{{ $invoice->customer->company }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 hidden sm:table-cell {{ $isOverdue ? 'text-red-600 dark:text-red-400 font-medium' : 'text-gray-600 dark:text-slate-400' }}">
                                {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                                @if($isOverdue)<span class="block text-xs">Terlambat {{ $invoice->daysOverdue() }} hari</span>@endif
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">
                                Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-right hidden md:table-cell {{ $invoice->remaining_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                                Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="inline-flex px-2 py-1 rounded-full text-xs font-semibold {{ $statusColor }}">{{ $statusLabel }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <div class="flex items-center justify-center gap-1">
                                    <a href="{{ route('invoices.show', $invoice) }}" title="Detail" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-slate-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    <a href="{{ route('invoices.pdf', $invoice) }}" title="Download PDF" class="p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-white/10 text-gray-500 dark:text-slate-400 transition">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center text-gray-400 dark:text-slate-500">
                                Belum ada invoice. <a href="{{ route('invoices.create') }}" class="text-blue-500 hover:underline">Buat invoice pertama</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($invoices->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 dark:border-white/10">
                {{ $invoices->links() }}
            </div>
            @endif
        </div>
    </div>
</x-app-layout>
