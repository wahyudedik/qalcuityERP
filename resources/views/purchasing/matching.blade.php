<x-app-layout>
    <x-slot name="title">3-Way Matching — Qalcuity ERP</x-slot>
    <x-slot name="header">3-Way Matching</x-slot>

    <div class="mb-4">
        <p class="text-sm text-gray-500">
            Verifikasi kesesuaian antara <span class="font-semibold text-blue-600">PO</span> (Purchase Order),
            <span class="font-semibold text-green-600">GR</span> (Goods Receipt), dan
            <span class="font-semibold text-amber-600">Invoice/Hutang</span>.
            Toleransi: ±2%.
        </p>
    </div>

    {{-- Search --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nomor PO atau supplier..."
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900">
            <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Cari</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($matchingData as $m)
        @php $po = $m['po']; @endphp
        <div class="bg-white rounded-2xl border {{ $m['status'] === 'matched' ? 'border-green-200' : ($m['status'] === 'partial' ? 'border-amber-200' : 'border-red-200') }} overflow-hidden">
            {{-- Header --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <div class="flex items-center gap-2">
                        <p class="font-mono text-sm font-semibold text-gray-900">{{ $po->number }}</p>
                        @php
                            $matchBadge = match($m['status']) {
                                'matched'   => 'bg-green-100 text-green-700',
                                'partial'   => 'bg-amber-100 text-amber-700',
                                default     => 'bg-red-100 text-red-700',
                            };
                            $matchLabel = match($m['status']) {
                                'matched' => '✓ 3-Way Match', 'partial' => '⚠ Partial Match', default => '✗ Belum Match',
                            };
                        @endphp
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $matchBadge }}">{{ $matchLabel }}</span>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        {{ $po->supplier?->name }} &bull; {{ $po->date->format('d M Y') }}
                    </p>
                </div>
            </div>

            {{-- 3-Way Comparison --}}
            <div class="grid grid-cols-3 divide-x divide-gray-100">
                {{-- PO --}}
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-blue-100 flex items-center justify-center text-xs font-bold text-blue-600">PO</span>
                        <span class="text-xs font-semibold text-gray-600 uppercase">Purchase Order</span>
                        <span class="ml-auto text-xs text-green-600">✓ Referensi</span>
                    </div>
                    <p class="text-lg font-bold text-gray-900">Rp {{ number_format($m['po_total'], 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 mt-1">{{ number_format($m['po_qty'], 0) }} unit dipesan</p>
                    <div class="mt-3 space-y-1">
                        @foreach($po->items as $item)
                        <div class="flex justify-between text-xs text-gray-500">
                            <span class="truncate max-w-[120px]">{{ $item->product?->name ?? '-' }}</span>
                            <span>{{ $item->quantity_ordered }} × Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- GR --}}
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-green-100 flex items-center justify-center text-xs font-bold text-green-600">GR</span>
                        <span class="text-xs font-semibold text-gray-600 uppercase">Goods Receipt</span>
                        @if($m['gr_match'])
                        <span class="ml-auto text-xs text-green-600">✓ Match</span>
                        @else
                        <span class="ml-auto text-xs text-red-500">✗ Selisih</span>
                        @endif
                    </div>
                    @if($po->goodsReceipts->count())
                    <p class="text-lg font-bold {{ $m['gr_match'] ? 'text-green-600' : 'text-red-500' }}">
                        {{ number_format($m['gr_qty'], 0) }} unit diterima
                    </p>
                    <p class="text-xs text-gray-500 mt-1">{{ $po->goodsReceipts->count() }} GR</p>
                    <div class="mt-3 space-y-1">
                        @foreach($po->goodsReceipts as $gr)
                        <div class="text-xs text-gray-500">
                            {{ $gr->number }} — {{ $gr->receipt_date->format('d M Y') }}
                        </div>
                        @endforeach
                    </div>
                    @else
                    <p class="text-sm text-gray-400 mt-2">Belum ada GR</p>
                    <a href="{{ route('purchasing.goods-receipts') }}" class="inline-block mt-2 text-xs text-blue-600 hover:underline">Catat GR →</a>
                    @endif
                </div>

                {{-- Invoice --}}
                <div class="px-5 py-4">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="w-6 h-6 rounded-full bg-amber-100 flex items-center justify-center text-xs font-bold text-amber-600">INV</span>
                        <span class="text-xs font-semibold text-gray-600 uppercase">Invoice / Hutang</span>
                        @if($m['inv_match'])
                        <span class="ml-auto text-xs text-green-600">✓ Match</span>
                        @elseif($m['inv_total'] > 0)
                        <span class="ml-auto text-xs text-red-500">✗ Selisih</span>
                        @else
                        <span class="ml-auto text-xs text-gray-400">Belum ada</span>
                        @endif
                    </div>
                    @if($m['inv_total'] > 0)
                    <p class="text-lg font-bold {{ $m['inv_match'] ? 'text-green-600' : 'text-red-500' }}">
                        Rp {{ number_format($m['inv_total'], 0, ',', '.') }}
                    </p>
                    @php $diff = $m['inv_total'] - $m['po_total']; @endphp
                    @if(!$m['inv_match'])
                    <p class="text-xs {{ $diff > 0 ? 'text-red-500' : 'text-amber-500' }} mt-1">
                        Selisih: Rp {{ number_format(abs($diff), 0, ',', '.') }} {{ $diff > 0 ? '(lebih)' : '(kurang)' }}
                    </p>
                    @endif
                    @else
                    <p class="text-sm text-gray-400 mt-2">Belum ada invoice</p>
                    <a href="{{ route('payables.index') }}" class="inline-block mt-2 text-xs text-blue-600 hover:underline">Lihat Hutang →</a>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white rounded-2xl border border-gray-200 px-4 py-12 text-center text-gray-400">
            Tidak ada PO yang perlu diverifikasi.
        </div>
        @endforelse

        @if($orders->hasPages())
        <div>{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
