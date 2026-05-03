<x-app-layout>
    <x-slot name="header">|</x-slot>

    {{-- Toolbar --}}
    <div class="flex flex-wrap items-center justify-end gap-2 mb-4">
        <a href="{{ route('printing.dashboard') }}"
                    class="text-gray-500 hover:text-gray-700 transition text-sm">
                    ← Kembali
                </a>
    </div>

    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        @if ($orders->count() === 0)
            <x-empty-state icon="document" title="Belum ada pesanan web-to-print"
                message="Belum ada pesanan dari portal web-to-print." />
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                No. Order</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Customer</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Template</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Qty</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Total</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Pembayaran</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Fulfillment</th>
                            <th
                                class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">
                                Tanggal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        @foreach ($orders as $order)
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 font-medium text-indigo-600">
                                    {{ $order->order_number }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $order->customer?->name ?? ($order->customer_name ?? 'N/A') }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $order->product_template ?? '-' }}
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ number_format($order->quantity) }}
                                </td>
                                <td class="px-6 py-4 font-semibold text-gray-900">
                                    Rp {{ number_format($order->total_price ?? 0, 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $payColors = ['pending' => 'yellow', 'paid' => 'green', 'refunded' => 'red'];
                                        $payColor = $payColors[$order->payment_status] ?? 'gray';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $payColor }}-100 text-{{ $payColor }}-700 $payColor }}-500/20 $payColor }}-400">
                                        {{ ucfirst($order->payment_status ?? 'pending') }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $fulColors = [
                                            'pending' => 'gray',
                                            'in_production' => 'blue',
                                            'shipped' => 'purple',
                                            'delivered' => 'green',
                                        ];
                                        $fulColor = $fulColors[$order->fulfillment_status] ?? 'gray';
                                    @endphp
                                    <span
                                        class="px-2 py-1 text-xs rounded-full bg-{{ $fulColor }}-100 text-{{ $fulColor }}-700 $fulColor }}-500/20 $fulColor }}-400">
                                        {{ ucfirst(str_replace('_', ' ', $order->fulfillment_status ?? 'pending')) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-gray-700">
                                    {{ $order->created_at?->format('d M Y') ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="px-6 py-4 border-t border-gray-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-app-layout>
