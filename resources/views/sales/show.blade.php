<x-app-layout>
    <x-slot name="header">
        Sales Order — {{ $salesOrder->number }}
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-5">

        @if (session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">
                {{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">
                {{ session('error') }}</div>
        @endif

        {{-- Header Card --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 mb-1">Nomor SO</p>
                    <p class="text-xl font-bold font-mono text-gray-900">{{ $salesOrder->number }}</p>
                    @php
                        $statusColors = [
                            'pending' => 'bg-yellow-500/20 text-yellow-400',
                            'confirmed' => 'bg-blue-500/20 text-blue-400',
                            'processing' => 'bg-purple-500/20 text-purple-400',
                            'shipped' => 'bg-indigo-500/20 text-indigo-400',
                            'completed' => 'bg-green-500/20 text-green-400',
                            'cancelled' => 'bg-red-500/20 text-red-400',
                        ];
                    @endphp
                    <span
                        class="mt-2 inline-block px-3 py-1 rounded-full text-xs {{ $statusColors[$salesOrder->status] ?? '' }}">
                        {{ ucfirst($salesOrder->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if (!in_array($salesOrder->status, ['completed', 'cancelled']))
                        <form method="POST" action="{{ route('sales.status', $salesOrder) }}" class="flex gap-2">
                            @csrf @method('PATCH')
                            <select name="status"
                                class="bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm text-gray-900 focus:outline-none">
                                @php
                                    // BUG-SALES-001 FIX: Only show valid transitions
                                    $validTransitions = [
                                        'pending' => ['confirmed', 'cancelled'],
                                        'confirmed' => ['processing', 'cancelled'],
                                        'processing' => ['shipped', 'cancelled'],
                                        'shipped' => ['completed', 'cancelled'],
                                        'completed' => [],
                                        'cancelled' => [],
                                    ];
                                    $allowedStatuses = $validTransitions[$salesOrder->status] ?? [];
                                @endphp
                                @foreach (['pending', 'confirmed', 'processing', 'shipped', 'completed', 'cancelled'] as $s)
                                    @if (in_array($s, $allowedStatuses))
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit"
                                class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm transition">Update</button>
                        </form>
                    @endif
                    @if (!$salesOrder->invoices()->where('status', '!=', 'cancelled')->exists())
                        <form method="POST" action="{{ route('sales.invoice', $salesOrder) }}">
                            @csrf
                            <button type="submit"
                                class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm transition">
                                Buat Invoice
                            </button>
                        </form>
                    @else
                        @php $inv = $salesOrder->invoices()->where('status', '!=', 'cancelled')->first(); @endphp
                        <a href="{{ route('invoices.show', $inv) }}"
                            class="px-3 py-2 bg-gray-100 text-gray-700 rounded-xl text-sm hover:bg-gray-200 transition">
                            Lihat Invoice
                        </a>
                    @endif
                    <a href="{{ route('sales.index') }}"
                        class="px-3 py-2 bg-gray-100 text-gray-600 rounded-xl text-sm hover:bg-gray-200 transition">
                        ← Kembali
                    </a>
                    <a href="{{ route('sign.pad', ['SalesOrder', $salesOrder->id]) }}"
                        class="px-3 py-2 bg-indigo-100 text-indigo-700 rounded-xl text-sm hover:bg-indigo-200 transition flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                        </svg>
                        TTD
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100">
                <div>
                    <p class="text-xs text-gray-500">Customer</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $salesOrder->customer->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Tanggal</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $salesOrder->date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pengiriman</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $salesOrder->delivery_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Pembayaran</p>
                    <p class="text-sm font-medium text-gray-900 mt-0.5">
                        {{ $salesOrder->payment_type === 'credit' ? 'Kredit' : 'Tunai' }}
                        @if ($salesOrder->due_date)
                            — {{ $salesOrder->due_date->format('d/m/Y') }}
                        @endif
                    </p>
                </div>
                @if ($salesOrder->quotation)
                    <div>
                        <p class="text-xs text-gray-500">Dari Quotation</p>
                        <p class="text-sm font-medium text-blue-400 mt-0.5">{{ $salesOrder->quotation->number }}</p>
                    </div>
                @endif
                @if ($salesOrder->currency_code && $salesOrder->currency_code !== 'IDR')
                    <div>
                        <p class="text-xs text-gray-500">Mata Uang</p>
                        <p class="text-sm font-medium text-gray-900 mt-0.5">
                            {{ $salesOrder->currency_code }}
                            <span class="text-xs text-gray-400">(Kurs: Rp
                                {{ number_format($salesOrder->currency_rate, 0, ',', '.') }})</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Ekuivalen IDR</p>
                        <p class="text-sm font-medium text-green-600 mt-0.5">Rp
                            {{ number_format($salesOrder->total * $salesOrder->currency_rate, 0, ',', '.') }}</p>
                    </div>
                @endif
                @if ($salesOrder->shipping_address)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500">Alamat Pengiriman</p>
                        <p class="text-sm text-gray-700 mt-0.5">{{ $salesOrder->shipping_address }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100">
                <h2 class="font-semibold text-gray-900">Item Produk</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 text-xs text-gray-500">
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($salesOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-700">{{ $item->product->name ?? '-' }}
                            </td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }} {{ $item->product->unit ?? '' }}
                            </td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-400">
                                {{ $item->discount > 0 ? '-Rp ' . number_format($item->discount, 0, ',', '.') : '-' }}
                            </td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-100 text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500">Subtotal</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900">Rp
                            {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if ($salesOrder->discount > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-red-400">Diskon</td>
                            <td class="px-4 py-2 text-right text-red-400">-Rp
                                {{ number_format($salesOrder->discount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if ($salesOrder->tax_amount > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-gray-500">Pajak</td>
                            <td class="px-4 py-2 text-right text-gray-700">Rp
                                {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="font-bold">
                        <td colspan="4" class="px-4 py-3 text-right text-gray-900">Total</td>
                        <td class="px-4 py-3 text-right text-blue-400 text-base">Rp
                            {{ number_format($salesOrder->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if ($salesOrder->notes)
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs text-gray-500 mb-1">Catatan</p>
                <p class="text-sm text-gray-700">{{ $salesOrder->notes }}</p>
            </div>
        @endif

        {{-- Digital Signatures --}}
        @php
            $signatures = \App\Models\DigitalSignature::where('model_type', 'App\\Models\\SalesOrder')
                ->where('model_id', $salesOrder->id)
                ->with('user')
                ->latest('signed_at')
                ->get();
        @endphp
        @if ($signatures->isNotEmpty())
            <div class="bg-white rounded-2xl border border-gray-200 p-5">
                <p class="text-xs font-semibold text-gray-500 uppercase mb-3">Tanda Tangan Digital
                </p>
                <div class="flex flex-wrap gap-4">
                    @foreach ($signatures as $sig)
                        <div
                            class="flex items-center gap-3 bg-gray-50 rounded-xl p-3 border border-gray-200">
                            <img src="{{ $sig->signature_data }}" alt="TTD"
                                class="h-10 border border-gray-200 rounded-lg bg-white">
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ $sig->user?->name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $sig->signed_at?->format('d M Y H:i') }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

    </div>
</x-app-layout>
