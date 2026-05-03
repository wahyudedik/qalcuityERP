<x-app-layout>
    <x-slot name="header">Invoice — {{ $invoice->number ?? '#' . $invoice->id }}</x-slot>

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('customer-portal.invoices.index') }}"
            class="hover:text-blue-600">Invoice</a>
        <span>/</span>
        <span class="text-gray-900">{{ $invoice->number ?? '#' . $invoice->id }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Invoice Info --}}
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-sm font-semibold text-gray-900">Detail Invoice</h3>
                    <a href="{{ route('customer-portal.invoices.download', $invoice) }}"
                        class="px-3 py-1.5 text-xs bg-green-600 text-white rounded-lg hover:bg-green-700">⬇ Download
                        PDF</a>
                </div>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">No. Invoice</p>
                        <p class="font-medium text-gray-900">{{ $invoice->number ?? '#' . $invoice->id }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tanggal</p>
                        <p class="font-medium text-gray-900">
                            {{ $invoice->created_at?->format('d/m/Y') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="font-medium text-gray-900">Rp
                            {{ number_format($invoice->total_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Sisa Bayar</p>
                        <p class="font-medium text-red-600">Rp
                            {{ number_format($invoice->remaining_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            @if ($invoice->salesOrder?->items)
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Item</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead
                                class="bg-gray-50 text-xs text-gray-500 uppercase">
                                <tr>
                                    <th class="px-4 py-3 text-left">Produk</th>
                                    <th class="px-4 py-3 text-right">Qty</th>
                                    <th class="px-4 py-3 text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100">
                                @foreach ($invoice->salesOrder->items as $item)
                                    <tr>
                                        <td class="px-4 py-3 text-gray-900">
                                            {{ $item->product?->name ?? '-' }}</td>
                                        <td class="px-4 py-3 text-right text-gray-700">
                                            {{ $item->quantity }}</td>
                                        <td class="px-4 py-3 text-right text-gray-900">Rp
                                            {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            {{-- Payment History --}}
            @if ($invoice->payments && $invoice->payments->count() > 0)
                <div
                    class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                    <div class="px-4 py-3 border-b border-gray-100">
                        <h3 class="text-sm font-semibold text-gray-900">Riwayat Pembayaran</h3>
                    </div>
                    <div class="divide-y divide-gray-100">
                        @foreach ($invoice->payments as $payment)
                            <div class="flex items-center justify-between px-4 py-3">
                                <div>
                                    <p class="text-sm text-gray-900">Rp
                                        {{ number_format($payment->amount ?? 0, 0, ',', '.') }}</p>
                                    <p class="text-xs text-gray-500">
                                        {{ $payment->payment_date?->format('d/m/Y') ?? '-' }} —
                                        {{ ucfirst($payment->payment_method ?? '-') }}</p>
                                </div>
                                @php $pc = ($payment->status ?? 'pending') === 'confirmed' ? 'green' : 'amber'; @endphp
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $pc }}-100 text-{{ $pc }}-700 $pc }}-500/20 $pc }}-400">{{ ucfirst($payment->status ?? 'pending') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Payment Form --}}
        <div class="space-y-6">
            @if (!in_array($invoice->status, ['paid', 'voided', 'cancelled']) && ($invoice->remaining_amount ?? 0) > 0)
                <div class="bg-white rounded-2xl border border-gray-200 p-6">
                    <h3 class="text-sm font-semibold text-gray-900 mb-4">Bayar Invoice</h3>
                    <form method="POST" action="{{ route('customer-portal.invoices.pay', $invoice) }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Jumlah
                                    Bayar *</label>
                                <input type="number" name="amount" required min="1"
                                    max="{{ $invoice->remaining_amount }}" value="{{ $invoice->remaining_amount }}"
                                    step="1"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Metode
                                    Pembayaran *</label>
                                <select name="payment_method" required
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="bank_transfer">Transfer Bank</option>
                                    <option value="credit_card">Kartu Kredit</option>
                                    <option value="qris">QRIS</option>
                                </select>
                            </div>
                            <div>
                                <label
                                    class="block text-xs font-medium text-gray-600 mb-1">Referensi
                                    Pembayaran</label>
                                <input type="text" name="payment_reference"
                                    class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                    placeholder="No. transfer / referensi">
                            </div>
                            <button type="submit"
                                class="w-full px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700 font-medium">Bayar
                                Sekarang</button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
