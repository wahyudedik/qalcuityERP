<x-app-layout>
    <x-slot name="header">
        Sales Order — {{ $salesOrder->number }}
    </x-slot>

    <div class="max-w-4xl mx-auto space-y-5">

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        {{-- Header Card --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-6">
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Nomor SO</p>
                    <p class="text-xl font-bold font-mono text-gray-900 dark:text-white">{{ $salesOrder->number }}</p>
                    @php
                        $statusColors = [
                            'pending'    => 'bg-yellow-500/20 text-yellow-400',
                            'confirmed'  => 'bg-blue-500/20 text-blue-400',
                            'processing' => 'bg-purple-500/20 text-purple-400',
                            'shipped'    => 'bg-indigo-500/20 text-indigo-400',
                            'completed'  => 'bg-green-500/20 text-green-400',
                            'cancelled'  => 'bg-red-500/20 text-red-400',
                        ];
                    @endphp
                    <span class="mt-2 inline-block px-3 py-1 rounded-full text-xs {{ $statusColors[$salesOrder->status] ?? '' }}">
                        {{ ucfirst($salesOrder->status) }}
                    </span>
                </div>
                <div class="flex flex-wrap gap-2">
                    @if(!in_array($salesOrder->status, ['completed', 'cancelled']))
                        <form method="POST" action="{{ route('sales.status', $salesOrder) }}" class="flex gap-2">
                            @csrf @method('PATCH')
                            <select name="status"
                                class="bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-white/10 rounded-xl px-3 py-2 text-sm text-gray-900 dark:text-white focus:outline-none">
                                @foreach(['pending','confirmed','processing','shipped','completed','cancelled'] as $s)
                                    @if($s !== $salesOrder->status)
                                        <option value="{{ $s }}">{{ ucfirst($s) }}</option>
                                    @endif
                                @endforeach
                            </select>
                            <button type="submit" class="px-3 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-xl text-sm transition">Update</button>
                        </form>
                    @endif
                    @if(!$salesOrder->invoice()->where('status', '!=', 'cancelled')->exists())
                        <form method="POST" action="{{ route('sales.invoice', $salesOrder) }}">
                            @csrf
                            <button type="submit" class="px-3 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm transition">
                                Buat Invoice
                            </button>
                        </form>
                    @else
                        @php $inv = $salesOrder->invoice()->where('status', '!=', 'cancelled')->first(); @endphp
                        <a href="{{ route('invoices.show', $inv) }}"
                            class="px-3 py-2 bg-gray-100 dark:bg-white/10 text-gray-700 dark:text-white rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">
                            Lihat Invoice
                        </a>
                    @endif
                    <a href="{{ route('sales.index') }}"
                        class="px-3 py-2 bg-gray-100 dark:bg-white/10 text-gray-600 dark:text-slate-300 rounded-xl text-sm hover:bg-gray-200 dark:hover:bg-white/20 transition">
                        ← Kembali
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mt-6 pt-6 border-t border-gray-100 dark:border-white/10">
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Customer</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $salesOrder->customer->name ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Tanggal</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $salesOrder->date->format('d/m/Y') }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pengiriman</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">{{ $salesOrder->delivery_date?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500 dark:text-slate-400">Pembayaran</p>
                    <p class="text-sm font-medium text-gray-900 dark:text-white mt-0.5">
                        {{ $salesOrder->payment_type === 'credit' ? 'Kredit' : 'Tunai' }}
                        @if($salesOrder->due_date) — {{ $salesOrder->due_date->format('d/m/Y') }} @endif
                    </p>
                </div>
                @if($salesOrder->quotation)
                    <div>
                        <p class="text-xs text-gray-500 dark:text-slate-400">Dari Quotation</p>
                        <p class="text-sm font-medium text-blue-400 mt-0.5">{{ $salesOrder->quotation->number }}</p>
                    </div>
                @endif
                @if($salesOrder->shipping_address)
                    <div class="col-span-2">
                        <p class="text-xs text-gray-500 dark:text-slate-400">Alamat Pengiriman</p>
                        <p class="text-sm text-gray-700 dark:text-slate-300 mt-0.5">{{ $salesOrder->shipping_address }}</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Items --}}
        <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 dark:border-white/10">
                <h2 class="font-semibold text-gray-900 dark:text-white">Item Produk</h2>
            </div>
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 dark:border-white/5 text-xs text-gray-500 dark:text-slate-400">
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Qty</th>
                        <th class="px-4 py-3 text-right">Harga</th>
                        <th class="px-4 py-3 text-right">Diskon</th>
                        <th class="px-4 py-3 text-right">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-white/5">
                    @foreach($salesOrder->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-700 dark:text-slate-300">{{ $item->product->name ?? '-' }}</td>
                            <td class="px-4 py-3 text-right">{{ $item->quantity }} {{ $item->product->unit ?? '' }}</td>
                            <td class="px-4 py-3 text-right">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right text-red-400">{{ $item->discount > 0 ? '-Rp ' . number_format($item->discount, 0, ',', '.') : '-' }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-t border-gray-100 dark:border-white/10 text-sm">
                    <tr>
                        <td colspan="4" class="px-4 py-2 text-right text-gray-500 dark:text-slate-400">Subtotal</td>
                        <td class="px-4 py-2 text-right font-medium text-gray-900 dark:text-white">Rp {{ number_format($salesOrder->subtotal, 0, ',', '.') }}</td>
                    </tr>
                    @if($salesOrder->discount > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-red-400">Diskon</td>
                            <td class="px-4 py-2 text-right text-red-400">-Rp {{ number_format($salesOrder->discount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    @if($salesOrder->tax_amount > 0)
                        <tr>
                            <td colspan="4" class="px-4 py-2 text-right text-gray-500 dark:text-slate-400">Pajak</td>
                            <td class="px-4 py-2 text-right text-gray-700 dark:text-slate-300">Rp {{ number_format($salesOrder->tax_amount, 0, ',', '.') }}</td>
                        </tr>
                    @endif
                    <tr class="font-bold">
                        <td colspan="4" class="px-4 py-3 text-right text-gray-900 dark:text-white">Total</td>
                        <td class="px-4 py-3 text-right text-blue-400 text-base">Rp {{ number_format($salesOrder->total, 0, ',', '.') }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        @if($salesOrder->notes)
            <div class="bg-white dark:bg-[#1e293b] rounded-2xl border border-gray-200 dark:border-white/10 p-5">
                <p class="text-xs text-gray-500 dark:text-slate-400 mb-1">Catatan</p>
                <p class="text-sm text-gray-700 dark:text-slate-300">{{ $salesOrder->notes }}</p>
            </div>
        @endif

    </div>
</x-app-layout>
