<x-app-layout>
    <x-slot name="header">Detail Pesanan — {{ $order->number ?? '#' . $order->id }}</x-slot>

    <div class="flex items-center gap-2 text-sm text-gray-500 mb-4">
        <a href="{{ route('customer-portal.orders.index') }}"
            class="hover:text-blue-600">Pesanan</a>
        <span>/</span>
        <span class="text-gray-900">{{ $order->number ?? '#' . $order->id }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Order Info --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Informasi Pesanan</h3>
                <div class="grid grid-cols-2 gap-4 text-sm">
                    <div>
                        <p class="text-xs text-gray-500">No. Pesanan</p>
                        <p class="font-medium text-gray-900">{{ $order->number ?? '#' . $order->id }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Tanggal</p>
                        <p class="font-medium text-gray-900">{{ $order->created_at?->format('d/m/Y') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Status</p>
                        <p class="font-medium text-gray-900">{{ ucfirst($order->status) }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500">Total</p>
                        <p class="font-medium text-gray-900">Rp
                            {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            {{-- Items --}}
            <div
                class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
                <div class="px-4 py-3 border-b border-gray-100">
                    <h3 class="text-sm font-semibold text-gray-900">Item Pesanan</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                            <tr>
                                <th class="px-4 py-3 text-left">Produk</th>
                                <th class="px-4 py-3 text-right">Qty</th>
                                <th class="px-4 py-3 text-right hidden sm:table-cell">Harga</th>
                                <th class="px-4 py-3 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            @foreach ($order->items ?? [] as $item)
                                <tr>
                                    <td class="px-4 py-3 text-gray-900">
                                        {{ $item->product?->name ?? ($item->description ?? '-') }}</td>
                                    <td class="px-4 py-3 text-right text-gray-700">
                                        {{ $item->quantity }}</td>
                                    <td
                                        class="px-4 py-3 text-right hidden sm:table-cell text-gray-700">
                                        Rp {{ number_format($item->price ?? 0, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right font-medium text-gray-900">Rp
                                        {{ number_format(($item->quantity ?? 0) * ($item->price ?? 0), 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Tracking --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-gray-200 p-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Tracking Pesanan</h3>
                <div class="space-y-4">
                    @foreach ($tracking as $step)
                        <div class="flex items-start gap-3">
                            <div
                                class="w-6 h-6 rounded-full flex items-center justify-center {{ $step['completed'] ? 'bg-green-100' : 'bg-gray-100' }}">
                                @if ($step['completed'])
                                    <svg class="w-3.5 h-3.5 text-green-600" fill="none"
                                        stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M5 13l4 4L19 7" />
                                    </svg>
                                @else
                                    <div class="w-2 h-2 rounded-full bg-gray-300"></div>
                                @endif
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium {{ $step['completed'] ? 'text-gray-900' : 'text-gray-400' }}">
                                    {{ $step['status'] }}</p>
                                @if ($step['date'])
                                    <p class="text-xs text-gray-500">
                                        {{ $step['date']->format('d/m/Y H:i') }}</p>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
