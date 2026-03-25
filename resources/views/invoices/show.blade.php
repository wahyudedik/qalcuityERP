<x-app-layout>
    <x-slot name="header">Invoice {{ $invoice->number }}</x-slot>

    <x-slot name="topbarActions">
        <div class="flex items-center gap-2">
            {{-- Task 35: State machine actions --}}
            @if($invoice->isDraft())
            <form method="POST" action="{{ route('invoices.post', $invoice) }}">
                @csrf
                <button type="submit"
                    class="flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Posting
                </button>
            </form>
            <button onclick="document.getElementById('modal-cancel-invoice').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-red-100 dark:bg-red-500/20 text-red-700 dark:text-red-400 text-sm font-medium hover:bg-red-200 dark:hover:bg-red-500/30 transition">
                Batalkan
            </button>
            @elseif($invoice->isPosted() && $invoice->paid_amount == 0)
            <button onclick="document.getElementById('modal-void-invoice').classList.remove('hidden')"
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-orange-100 dark:bg-orange-500/20 text-orange-700 dark:text-orange-400 text-sm font-medium hover:bg-orange-200 dark:hover:bg-orange-500/30 transition">
                Void Invoice
            </button>
            @endif

            <a href="{{ route('invoices.pdf', $invoice) }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-gray-100 dark:bg-white/10 text-sm text-gray-700 dark:text-white hover:bg-gray-200 dark:hover:bg-white/20 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                Download PDF
            </a>
            <a href="{{ route('sign.pad', ['Invoice', $invoice->id]) }}"
               class="flex items-center gap-2 px-4 py-2 rounded-xl bg-indigo-100 dark:bg-indigo-500/20 text-sm text-indigo-700 dark:text-indigo-400 hover:bg-indigo-200 dark:hover:bg-indigo-500/30 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Tanda Tangani
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
                    {{-- Task 35: Posting status badge --}}
                    <span class="inline-flex px-3 py-1.5 rounded-full text-xs font-semibold {{ $invoice->postingStatusColor() }}">
                        {{ $invoice->postingStatusLabel() }}
                    </span>
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
                    @if($invoice->currency_code && $invoice->currency_code !== 'IDR')
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Mata Uang</p>
                        <p class="font-medium text-gray-900 dark:text-white">
                            {{ $invoice->currency_code }}
                            <span class="text-xs text-gray-400">(Kurs: Rp {{ number_format($invoice->currency_rate, 0, ',', '.') }})</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400 mb-0.5">Ekuivalen IDR</p>
                        <p class="font-medium text-green-600 dark:text-green-400">Rp {{ number_format($invoice->total_amount * $invoice->currency_rate, 0, ',', '.') }}</p>
                    </div>
                    @endif
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

            {{-- Digital Signatures --}}
            @php
                $invoiceSigs = \App\Models\DigitalSignature::where('model_type', 'App\\Models\\Invoice')
                    ->where('model_id', $invoice->id)
                    ->with('user')
                    ->latest('signed_at')
                    ->get();
            @endphp
            @if($invoiceSigs->isNotEmpty())
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4">
                <p class="text-xs font-semibold text-gray-500 dark:text-slate-400 uppercase mb-3">Tanda Tangan Digital</p>
                <div class="space-y-3">
                    @foreach($invoiceSigs as $sig)
                    <div class="flex items-center gap-3">
                        <img src="{{ $sig->signature_data }}" alt="TTD" class="h-10 border border-gray-200 dark:border-white/10 rounded-lg bg-white">
                        <div>
                            <p class="text-sm font-medium text-gray-900 dark:text-white">{{ $sig->user?->name }}</p>
                            <p class="text-xs text-gray-400 dark:text-slate-500">{{ $sig->signed_at?->format('d M Y H:i') }} · {{ $sig->ip_address }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            @if($invoice->status !== 'paid')
            {{-- AI: Late Payment Risk Widget --}}
            @if($invoice->customer_id)
            <div class="rounded-2xl border border-gray-200 dark:border-white/10 bg-white dark:bg-white/5 p-4" id="ai-payment-risk">
                <div class="flex items-center gap-2 mb-2">
                    <div class="w-5 h-5 rounded-md bg-indigo-500/20 flex items-center justify-center shrink-0">
                        <svg class="w-3 h-3 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
                        </svg>
                    </div>
                    <p class="text-xs font-semibold text-gray-900 dark:text-white">Prediksi Pembayaran AI</p>
                </div>
                <div id="ai-risk-content" class="text-xs text-gray-400">Memuat analisis...</div>
            </div>
            @endif
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
                        <select name="method" class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-white dark:bg-slate-800 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
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

    {{-- Task 35: Modal Cancel Invoice --}}
    <div id="modal-cancel-invoice" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Batalkan Invoice</h3>
            <form method="POST" action="{{ route('invoices.cancel', $invoice) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Alasan Pembatalan</label>
                    <textarea name="reason" rows="3" required
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-red-500"
                        placeholder="Masukkan alasan pembatalan..."></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('modal-cancel-invoice').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-medium transition">Batalkan Invoice</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Task 35: Modal Void Invoice --}}
    <div id="modal-void-invoice" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white dark:bg-slate-800 rounded-2xl p-6 w-full max-w-md shadow-2xl">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Void Invoice</h3>
            <p class="text-sm text-gray-500 dark:text-slate-400 mb-4">Invoice yang di-void akan membuat jurnal pembalik otomatis. Tindakan ini tidak bisa dibatalkan.</p>
            <form method="POST" action="{{ route('invoices.void', $invoice) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 dark:text-slate-300 mb-1">Alasan Void</label>
                    <textarea name="reason" rows="3" required
                        class="w-full rounded-xl border border-gray-200 dark:border-white/10 bg-gray-50 dark:bg-white/5 px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-orange-500"
                        placeholder="Masukkan alasan void..."></textarea>
                </div>
                <div class="flex gap-3 justify-end">
                    <button type="button" onclick="document.getElementById('modal-void-invoice').classList.add('hidden')"
                        class="px-4 py-2 rounded-xl text-sm text-gray-600 dark:text-slate-400 hover:bg-gray-100 dark:hover:bg-white/10 transition">Batal</button>
                    <button type="submit"
                        class="px-4 py-2 rounded-xl bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium transition">Void Invoice</button>
                </div>
            </form>
        </div>
    </div>

    @if($invoice->status !== 'paid' && $invoice->customer_id)
    @push('scripts')
    <script>
    (function() {
        const RISK_URL   = "{{ route('sales.ai.late-payment-risk') }}?customer_id={{ $invoice->customer_id }}";
        const container  = document.getElementById('ai-risk-content');
        if (!container) return;

        fetch(RISK_URL)
            .then(r => r.json())
            .then(data => {
                const riskColors = {
                    high:   { bg: 'bg-red-500/10 border-red-500/30',    text: 'text-red-400',    label: 'Risiko Tinggi',   bar: 'bg-red-500' },
                    medium: { bg: 'bg-yellow-500/10 border-yellow-500/30', text: 'text-yellow-400', label: 'Risiko Sedang', bar: 'bg-yellow-500' },
                    low:    { bg: 'bg-green-500/10 border-green-500/30',  text: 'text-green-400',  label: 'Risiko Rendah',  bar: 'bg-green-500' },
                };
                const c = riskColors[data.risk] || riskColors.low;

                const tips = (data.tips || []).map(t => `<li class="flex gap-1"><span>•</span><span>${t}</span></li>`).join('');

                container.innerHTML = `
                    <div class="rounded-lg border ${c.bg} p-2.5 mb-2">
                        <div class="flex items-center justify-between mb-1">
                            <span class="${c.text} font-semibold">${c.label}</span>
                            <span class="${c.text} font-bold">${data.probability}%</span>
                        </div>
                        <div class="w-full bg-white/10 rounded-full h-1.5 mb-1.5">
                            <div class="${c.bar} h-1.5 rounded-full" style="width:${data.probability}%"></div>
                        </div>
                        <p class="text-gray-400 leading-snug">${data.reason}</p>
                    </div>
                    ${tips ? `<ul class="text-gray-400 space-y-0.5 leading-snug">${tips}</ul>` : ''}
                `;

                // Update border warna widget jika high risk
                if (data.risk === 'high') {
                    document.getElementById('ai-payment-risk')?.classList.add('border-red-500/30');
                }
            })
            .catch(() => {
                container.textContent = 'Tidak dapat memuat prediksi.';
            });
    })();
    </script>
    @endpush
    @endif
</x-app-layout>
