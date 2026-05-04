<x-app-layout>
    <x-slot name="header">Price List: {{ $priceList->name }}</x-slot>

    @if(session('success'))
    <div class="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-xl text-sm text-green-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Info --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-200 p-6">
            <div class="flex items-start justify-between mb-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">{{ $priceList->name }}</h2>
                    @if($priceList->code)<p class="text-xs text-gray-400">{{ $priceList->code }}</p>@endif
                </div>
                <div class="flex gap-2">
                    @php
                        $typeColor = match($priceList->type) {
                            'tier'     => 'bg-blue-100 text-blue-700',
                            'contract' => 'bg-purple-100 text-purple-700',
                            'promo'    => 'bg-orange-100 text-orange-700',
                            default    => 'bg-gray-100 text-gray-500',
                        };
                    @endphp
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $typeColor }}">{{ $priceList->typeLabel() }}</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $priceList->isValid() ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                        {{ $priceList->isValid() ? 'Aktif' : 'Tidak Aktif' }}
                    </span>
                </div>
            </div>

            @if($priceList->description)
            <p class="text-sm text-gray-500 mb-4">{{ $priceList->description }}</p>
            @endif

            @if($priceList->valid_from || $priceList->valid_until)
            <p class="text-sm text-gray-500 mb-4">
                Berlaku: {{ $priceList->valid_from?->format('d M Y') ?? '∞' }} – {{ $priceList->valid_until?->format('d M Y') ?? '∞' }}
            </p>
            @endif

            {{-- Edit form --}}
            <form method="POST" action="{{ route('price-lists.update', $priceList) }}" class="border-t border-gray-100 pt-4 mt-4 grid grid-cols-2 gap-3">
                @csrf @method('PUT')
                <div class="col-span-2">
                    <label class="block text-xs font-medium text-gray-600 mb-1">Nama</label>
                    <input type="text" name="name" value="{{ $priceList->name }}" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku Dari</label>
                    <input type="date" name="valid_from" value="{{ $priceList->valid_from?->format('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1">Berlaku Sampai</label>
                    <input type="date" name="valid_until" value="{{ $priceList->valid_until?->format('Y-m-d') }}" class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
                <div class="col-span-2 flex items-center justify-between">
                    <label class="flex items-center gap-2 text-sm text-gray-700">
                        <input type="checkbox" name="is_active" value="1" {{ $priceList->is_active ? 'checked' : '' }} class="rounded">
                        Aktif
                    </label>
                    <button type="submit" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Simpan Perubahan</button>
                </div>
            </form>
        </div>

        {{-- Customers --}}
        <div class="bg-white rounded-2xl border border-gray-200 p-6">
            <h3 class="font-semibold text-gray-900 mb-4">Customer Terdaftar</h3>
            <div class="space-y-2 mb-4">
                @forelse($priceList->customers as $c)
                <div class="flex items-center justify-between p-2 rounded-lg bg-gray-50">
                    <div>
                        <p class="text-sm font-medium text-gray-900">{{ $c->name }}</p>
                        <p class="text-xs text-gray-400">Prioritas: {{ $c->pivot->priority }}</p>
                    </div>
                    <form method="POST" action="{{ route('price-lists.customers.remove', [$priceList, $c]) }}">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1 text-red-500 hover:bg-red-50 rounded">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </form>
                </div>
                @empty
                <p class="text-xs text-gray-400">Belum ada customer.</p>
                @endforelse
            </div>

            <form method="POST" action="{{ route('price-lists.customers.assign', $priceList) }}" class="space-y-2">
                @csrf
                <select name="customer_id" required class="w-full px-3 py-2 text-sm rounded-xl border border-gray-200 bg-gray-50 text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">+ Tambah Customer</option>
                    @foreach(\App\Models\Customer::where('tenant_id', auth()->user()->tenant_id)->where('is_active', true)->orderBy('name')->get() as $c)
                    <option value="{{ $c->id }}">{{ $c->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="w-full px-3 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Tambahkan</button>
            </form>
        </div>
    </div>

    {{-- Product Items --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100">
            <h3 class="font-semibold text-gray-900">Daftar Harga Produk</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">Produk</th>
                        <th class="px-4 py-3 text-right">Harga Khusus</th>
                        <th class="px-4 py-3 text-right">Diskon %</th>
                        <th class="px-4 py-3 text-right">Harga Efektif</th>
                        <th class="px-4 py-3 text-right">Min Qty</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($priceList->items->sortBy('product.name') as $item)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $item->product?->name }}</td>
                        <td class="px-4 py-3 text-right text-gray-700">Rp {{ number_format($item->price,0,',','.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $item->discount_percent > 0 ? $item->discount_percent . '%' : '-' }}</td>
                        <td class="px-4 py-3 text-right font-semibold text-green-700">Rp {{ number_format($item->effectivePrice(),0,',','.') }}</td>
                        <td class="px-4 py-3 text-right text-gray-500">{{ $item->min_qty }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400">Belum ada produk.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
