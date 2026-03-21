<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->number }}</x-slot>

    <x-slot name="topbarActions">
        <div class="flex items-center gap-2">
            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-white/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            @if($invoice->customer?->email)
            <form method="POST" action="{{ route('invoices.send-email', $invoice) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    Kirim ke Email
                </button>
            </form>
            @endif
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main invoice card --}}
        <div class="lg:col-span-2 space-y-5">

            {{-- Header info --}}
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 mb-6">
                    <div>
                        <p class="text-xs text-gray-400 uppercase tracking-wider mb-1">No. Invoice</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-white">{{ $invoice->number }}</p>
                    </div>
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
                            default   => 'Belum Dibayar',
                        };
                    @endphp
                    <span class="inline-flex px-3 py-1.5 rounded-full text-sm font-semibold {{ $statusColor }}">{{ $statusLabel }}</span>
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Customer</p>
                        <p class="font-semibold text-gray-900 dark:text-white">{{ $invoice->customer?->name ?? '-' }}</p>
                        @if($invoice->customer?->company)<p class="text-gray-500 dark:text-slate-400 text-xs">{{ $invoice->customer->company }}</p>@endif
                        @if($invoice->customer?->email)<p class="text-gray-500 dark:text-slate-400 text-xs">{{ $invoice->customer->email }}</p>@endif
                        @if($invoice->customer?->phone)<p class="text-gray-500 dark:text-slate-400 text-xs">{{ $invoice->customer->phone }}</p>@endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Tanggal Invoice</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $invoice->created_at->format('d M Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Jatuh Tempo</p>
                        <p class="font-medium {{ $isOverdue ? 'text-red-600 dark:text-red-400' : 'text-gray-900 dark:text-white' }}">
                            {{ $invoice->due_date?->format('d M Y') ?? '-' }}
                        </p>
                        @if($isOverdue)<p class="text-xs text-red-500">Terlambat {{ $invoice->daysOverdue() }} hari</p>@endif
                    </div>
                    @if($invoice->salesOrder)
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Sales Order</p>
                        <p class="font-medium text-gray-900 dark:text-white">{{ $invoice->salesOrder->number }}</p>
                    </div>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            @if($invoice->salesOrder && $invoice->salesOrder->items->count())
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                    <p class="font-semibold text-gray-900 dark:text-white text-sm">Item Pesanan</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Produk</th>
                                <th class="px-4 py-3 text-center text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase hidden sm:table-cell">Qty</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase hidden sm:table-cell">Harga</th>
                                <th class="px-4 py-3 text-right text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-white/5">
                            @foreach($invoice->salesOrder->items as $item)
                            <tr>
                                <td class="px-4 py-3 text-gray-900 dark:text-white">
                                    {{ $item->product?->name ?? '-' }}
                                    <span class="sm:hidden text-xs text-gray-400 block">{{ $item->quantity }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                </td>
                                <td class="px-4 py-3 text-center text-gray-600 dark:text-slate-400 hidden sm:table-cell">{{ $item->quantity }}</td>
                                <td class="px-4 py-3 text-right text-gray-600 dark:text-slate-400 hidden sm:table-cell">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="px-6 py-4 border-t border-gray-100 dark:border-white/10 space-y-1.5">
                    @if($invoice->salesOrder->discount > 0)
                    <div class="flex justify-between text-sm text-gray-500 dark:text-slate-400">
                        <span>Diskon</span><span>- Rp {{ number_format($invoice->salesOrder->discount, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    @if($invoice->salesOrder->tax > 0)
                    <div class="flex justify-between text-sm text-gray-500 dark:text-slate-400">
                        <span>Pajak</span><span>Rp {{ number_format($invoice->salesOrder->tax, 0, ',', '.') }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between text-base font-bold text-gray-900 dark:text-white pt-1 border-t border-gray-100 dark:border-white/10">
                        <span>Total</span><span>Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            @endif

            {{-- Notes --}}
            @if($invoice->notes)
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                <p class="text-xs text-gray-400 uppercase tracking-wider mb-2">Catatan</p>
                <p class="text-sm text-gray-700 dark:text-slate-300">{{ $invoice->notes }}</p>
            </div>
            @endif
        </div>

        {{-- Sidebar: payment summary + record payment + history --}}
        <div class="space-y-5">

            {{-- Payment summary --}}
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6 space-y-3">
                <p class="font-semibold text-gray-900 dark:text-white text-sm">Ringkasan Pembayaran</p>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Total Tagihan</span>
                        <span class="font-semibold text-gray-900 dark:text-white">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-500 dark:text-slate-400">Terbayar</span>
                        <span class="font-semibold text-green-600 dark:text-green-400">Rp {{ number_format($invoice->paid_amount, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between border-t border-gray-100 dark:border-white/10 pt-2">
                        <span class="font-semibold text-gray-700 dark:text-slate-300">Sisa Tagihan</span>
                        <span class="font-bold text-lg {{ $invoice->remaining_amount > 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">
                            Rp {{ number_format($invoice->remaining_amount, 0, ',', '.') }}
                        </span>
                    </div>
                </div>

                {{-- Progress bar --}}
                @php $pct = $invoice->total_amount > 0 ? min(100, ($invoice->paid_amount / $invoice->total_amount) * 100) : 0; @endphp
                <div class="w-full bg-gray-100 dark:bg-white/10 rounded-full h-2">
                    <div class="bg-green-500 h-2 rounded-full transition-all" style="width: {{ $pct }}%"></div>
                </div>
                <p class="text-xs text-gray-400 text-right">{{ number_format($pct, 0) }}% terbayar</p>
            </div>

            {{-- Record payment --}}
            @if($invoice->status !== 'paid')
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                <p class="font-semibold text-gray-900 dark:text-white text-sm mb-4">Catat Pembayaran</p>
                <form method="POST" action="{{ route('invoices.payment', $invoice) }}" class="space-y-3">
                    @csrf
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Jumlah (Rp)</label>
                        <input type="number" name="amount" min="1" max="{{ $invoice->remaining_amount }}"
                            value="{{ old('amount', $invoice->remaining_amount) }}" required
                            class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('amount')<p class="mt-1 text-xs text-red-500">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Metode</label>
                        <select name="method" class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Cash</option>
                            <option value="transfer">Transfer Bank</option>
                            <option value="qris">QRIS</option>
                            <option value="other">Lainnya</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-500 dark:text-slate-400 mb-1">Catatan</label>
                        <input type="text" name="notes" placeholder="Opsional"
                            class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <button type="submit" class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-sm font-medium transition">
                        Simpan Pembayaran
                    </button>
                </form>
            </div>
            @endif

            {{-- Payment history --}}
            @if($invoice->payments->count())
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-6">
                <p class="font-semibold text-gray-900 dark:text-white text-sm mb-3">Riwayat Pembayaran</p>
                <div class="space-y-2">
                    @foreach($invoice->payments as $pay)
                    <div class="flex items-center justify-between p-3 rounded-xl bg-green-50 dark:bg-green-900/20 text-sm">
                        <div>
                            <p class="font-medium text-green-700 dark:text-green-400">Rp {{ number_format($pay->amount, 0, ',', '.') }}</p>
                            <p class="text-xs text-gray-500 dark:text-slate-400">{{ $pay->payment_date?->format('d M Y') }} · {{ strtoupper($pay->payment_method ?? '-') }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <a href="{{ route('invoices.index') }}" class="flex items-center gap-2 text-sm text-gray-500 dark:text-slate-400 hover:text-gray-700 dark:hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke daftar invoice
            </a>
        </div>
    </div>
</x-app-layout>
