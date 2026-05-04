<x-app-layout>
    <x-slot name="header">Detail Penawaran — {{ $quotation->number }}</x-slot>

    <div class="space-y-6">
        {{-- Header --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-xl font-bold text-gray-900">{{ $quotation->number }}</h2>
                    <p class="text-sm text-gray-500 mt-1">
                        Customer: <span class="font-medium text-gray-700">{{ $quotation->customer?->name }}</span>
                    </p>
                    <p class="text-sm text-gray-500">
                        Tanggal: {{ $quotation->date->format('d M Y') }} —
                        Berlaku hingga: <span class="{{ $quotation->valid_until < today() ? 'text-red-500' : '' }}">{{ $quotation->valid_until?->format('d M Y') ?? '-' }}</span>
                    </p>
                </div>
                <div class="flex flex-col items-end gap-2">
                    @php
                        $colors = ['draft'=>'gray','sent'=>'blue','accepted'=>'green','rejected'=>'red','expired'=>'orange'];
                        $labels = ['draft'=>'Draft','sent'=>'Terkirim','accepted'=>'Diterima','rejected'=>'Ditolak','expired'=>'Kadaluarsa'];
                        $c = $colors[$quotation->status] ?? 'gray';
                    @endphp
                    <span class="px-3 py-1 rounded-full text-sm font-medium bg-{{ $c  }}-100 text-{{ $c }}-700 $c }}-500/20 $c }}-400">
                        {{ $labels[$quotation->status] ?? $quotation->status }}
                    </span>

                    {{-- Status Actions --}}
                    @if($quotation->status === 'draft')
                    <form method="POST" action="{{ route('quotations.status', $quotation) }}">
                        @csrf @method('PATCH')
                        <input type="hidden" name="status" value="sent">
                        <button type="submit" class="text-sm px-3 py-1.5 bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tandai Terkirim</button>
                    </form>
                    @elseif($quotation->status === 'sent')
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('quotations.status', $quotation) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="accepted">
                            <button type="submit" class="text-sm px-3 py-1.5 bg-green-600 text-white rounded-xl hover:bg-green-700">Diterima</button>
                        </form>
                        <form method="POST" action="{{ route('quotations.status', $quotation) }}">
                            @csrf @method('PATCH')
                            <input type="hidden" name="status" value="rejected">
                            <button type="submit" class="text-sm px-3 py-1.5 bg-red-600 text-white rounded-xl hover:bg-red-700">Ditolak</button>
                        </form>
                    </div>
                    @endif

                    @if(in_array($quotation->status, ['draft','sent']) && $quotation->valid_until >= today())
                    <form method="POST" action="{{ route('quotations.convert', $quotation) }}">
                        @csrf
                        <button type="submit" class="text-sm px-3 py-1.5 bg-indigo-600 text-white rounded-xl hover:bg-indigo-700"
                            onclick="return confirm('Konversi penawaran ini ke Sales Order?')">
                            Konversi ke Sales Order
                        </button>
                    </form>
                    @endif
                </div>
            </div>

            {{-- Items --}}
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                        <tr>
                            <th class="px-4 py-2 text-left">Deskripsi</th>
                            <th class="px-4 py-2 text-right">Qty</th>
                            <th class="px-4 py-2 text-right">Harga</th>
                            <th class="px-4 py-2 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @foreach($quotation->items as $item)
                        <tr>
                            <td class="px-4 py-3 text-gray-700">
                                {{ $item->description }}
                                @if($item->product) <span class="text-xs text-gray-400">({{ $item->product?->name }})</span> @endif
                            </td>
                            <td class="px-4 py-3 text-right text-gray-900">{{ $item->quantity }}</td>
                            <td class="px-4 py-3 text-right text-gray-900">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-right font-medium text-gray-900">Rp {{ number_format($item->total, 0, ',', '.') }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot class="border-t border-gray-200">
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-500">Subtotal</td>
                            <td class="px-4 py-2 text-right text-gray-900">Rp {{ number_format($quotation->subtotal, 0, ',', '.') }}</td>
                        </tr>
                        @if($quotation->discount > 0)
                        <tr>
                            <td colspan="3" class="px-4 py-2 text-right text-sm text-gray-500">Diskon</td>
                            <td class="px-4 py-2 text-right text-red-500">- Rp {{ number_format($quotation->discount, 0, ',', '.') }}</td>
                        </tr>
                        @endif
                        <tr class="font-semibold">
                            <td colspan="3" class="px-4 py-2 text-right text-gray-900">Total</td>
                            <td class="px-4 py-2 text-right text-lg text-gray-900">Rp {{ number_format($quotation->total, 0, ',', '.') }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            @if($quotation->notes)
            <p class="mt-4 text-sm text-gray-500">Catatan: {{ $quotation->notes }}</p>
            @endif
        </div>

        {{-- Sales Orders dari penawaran ini --}}
        @if($quotation->salesOrders->isNotEmpty())
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-3">Sales Order Terkait</h3>
            <div class="space-y-2">
                @foreach($quotation->salesOrders as $so)
                <div class="flex items-center justify-between px-4 py-3 rounded-xl bg-gray-50">
                    <span class="font-mono text-sm text-gray-900">{{ $so->number }}</span>
                    <span class="text-sm text-gray-500">{{ $so->date->format('d M Y') }}</span>
                    <span class="font-medium text-gray-900">Rp {{ number_format($so->total, 0, ',', '.') }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs bg-blue-500/20 text-blue-400">{{ $so->status }}</span>
                </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>
</x-app-layout>
