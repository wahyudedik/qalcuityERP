@extends('layouts.app')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('delivery-orders.index') }}" class="text-slate-400 hover:text-slate-600">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-slate-800">Buat Surat Jalan</h1>
    </div>

    @if($errors->any())
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-700">
        <ul class="list-disc list-inside space-y-1">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
    </div>
    @endif

    <form method="POST" action="{{ route('delivery-orders.store') }}">
        @csrf
        <div class="bg-white rounded-xl border border-slate-200 p-6 space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Sales Order <span class="text-red-500">*</span></label>
                    <select name="sales_order_id" id="soSelect" required onchange="loadSoItems()"
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        <option value="">-- Pilih Sales Order --</option>
                        @foreach($salesOrders ?? [] as $so)
                        <option value="{{ $so->id }}" {{ old('sales_order_id', $selectedSo?->id) == $so->id ? 'selected' : '' }}>
                            {{ $so->number }} — {{ $so->customer?->name ?? '' }} ({{ $so->date->format('d/m/Y') }})
                        </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Gudang <span class="text-red-500">*</span></label>
                    <select name="warehouse_id" required
                            class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                        @foreach($warehouses ?? [] as $w)
                        <option value="{{ $w->id }}" {{ old('warehouse_id') == $w->id ? 'selected' : '' }}>{{ $w->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Tanggal Pengiriman <span class="text-red-500">*</span></label>
                    <input type="date" name="delivery_date" value="{{ old('delivery_date', today()->toDateString()) }}" required
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Kurir / Ekspedisi</label>
                    <input type="text" name="courier" value="{{ old('courier') }}" placeholder="JNE, J&T, Gojek, dll"
                           class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none">
                </div>
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Pengiriman</label>
                    <textarea name="shipping_address" rows="2"
                              class="w-full px-3 py-2 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none resize-none"
                              placeholder="Alamat tujuan pengiriman">{{ old('shipping_address', $selectedSo?->shipping_address) }}</textarea>
                </div>
            </div>

            {{-- Items --}}
            <div>
                <h3 class="text-sm font-semibold text-slate-700 mb-3">Item yang Dikirim</h3>
                <div id="itemsContainer">
                    @if($selectedSo)
                        @php
                            $deliveredQty = [];
                            foreach ($selectedSo->deliveryOrders->whereNotIn('status', ['cancelled']) as $existingDo) {
                                foreach ($existingDo->items as $doi) {
                                    $deliveredQty[$doi->sales_order_item_id] = ($deliveredQty[$doi->sales_order_item_id] ?? 0) + $doi->quantity_delivered;
                                }
                            }
                        @endphp
                        @foreach($selectedSo->items as $i => $item)
                            @php $remaining = $item->quantity - ($deliveredQty[$item->id] ?? 0); @endphp
                            @if($remaining > 0)
                            <div class="grid grid-cols-12 gap-2 items-center mb-2 p-3 bg-slate-50 rounded-lg">
                                <input type="hidden" name="items[{{ $i }}][sales_order_item_id]" value="{{ $item->id }}">
                                <input type="hidden" name="items[{{ $i }}][product_id]" value="{{ $item->product_id }}">
                                <div class="col-span-6 text-sm text-slate-800 font-medium">{{ $item->product?->name }}</div>
                                <div class="col-span-2 text-xs text-slate-500 text-center">
                                    Dipesan: {{ $item->quantity }}<br>
                                    Sisa: {{ $remaining }}
                                </div>
                                <div class="col-span-4">
                                    <input type="number" name="items[{{ $i }}][quantity_delivered]"
                                           min="0.001" max="{{ $remaining }}" step="0.001"
                                           value="{{ $remaining }}" required
                                           class="w-full px-2 py-1.5 text-sm border border-slate-300 rounded-lg bg-white text-slate-800 focus:ring-2 focus:ring-blue-500 outline-none"
                                           placeholder="Qty kirim">
                                </div>
                            </div>
                            @endif
                        @endforeach
                    @else
                        <p class="text-sm text-slate-400 text-center py-6">Pilih Sales Order untuk melihat item</p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end gap-3 pt-2">
                <a href="{{ route('delivery-orders.index') }}"
                   class="px-4 py-2 text-sm text-slate-600 hover:text-slate-800 border border-slate-300 rounded-lg">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium rounded-lg">
                    Buat Surat Jalan
                </button>
            </div>
        </div>
    </form>
</div>

<script>
function loadSoItems() {
    const soId = document.getElementById('soSelect').value;
    if (!soId) return;
    window.location.href = `{{ route('delivery-orders.create') }}?sales_order_id=${soId}`;
}
</script>
@endsection
