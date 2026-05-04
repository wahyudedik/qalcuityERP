<x-app-layout>
    <x-slot name="header">Pesanan Saya</x-slot>

    {{-- Filters --}}
    <div class="bg-white rounded-2xl border border-gray-200 p-4 mb-4">
        <form method="GET" class="flex flex-col sm:flex-row gap-3">
            <select name="status"
                class="flex-1 px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900">
                <option value="">Semua Status</option>
                @foreach (['pending' => 'Menunggu', 'confirmed' => 'Dikonfirmasi', 'processing' => 'Diproses', 'shipped' => 'Dikirim', 'completed' => 'Selesai', 'cancelled' => 'Dibatalkan'] as $v => $l)
                    <option value="{{ $v }}" @selected(request('status') === $v)>{{ $l }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900"
                placeholder="Dari">
            <input type="date" name="date_to" value="{{ request('date_to') }}"
                class="px-3 py-2 text-sm rounded-xl border border-gray-200 bg-white text-gray-900"
                placeholder="Sampai">
            <button type="submit"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-xl hover:bg-blue-700">Filter</button>
        </form>
    </div>

    {{-- Orders Table --}}
    <div class="bg-white rounded-2xl border border-gray-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-xs text-gray-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-left">No. Pesanan</th>
                        <th class="px-4 py-3 text-left hidden sm:table-cell">Tanggal</th>
                        <th class="px-4 py-3 text-right hidden md:table-cell">Total</th>
                        <th class="px-4 py-3 text-center">Status</th>
                        <th class="px-4 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($orders as $order)
                        @php
                            $sc = match ($order->status) {
                                'completed', 'delivered' => 'green',
                                'cancelled' => 'red',
                                'shipped' => 'purple',
                                'processing' => 'blue',
                                default => 'amber',
                            };
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="px-4 py-3 font-medium text-gray-900">
                                {{ $order->number ?? '#' . $order->id }}</td>
                            <td class="px-4 py-3 hidden sm:table-cell text-gray-500">
                                {{ $order->created_at?->format('d/m/Y') }}</td>
                            <td class="px-4 py-3 text-right hidden md:table-cell text-gray-900">Rp
                                {{ number_format($order->total_amount ?? 0, 0, ',', '.') }}</td>
                            <td class="px-4 py-3 text-center">
                                <span
                                    class="px-2 py-0.5 rounded-full text-xs bg-{{ $sc  }}-100 text-{{ $sc }}-700 $sc }}-500/20 $sc }}-400">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <a href="{{ route('customer-portal.orders.show', $order) }}"
                                    class="text-blue-600 hover:underline text-xs">Detail</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-gray-400">Belum
                                ada pesanan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($orders->hasPages())
            <div class="px-4 py-3 border-t border-gray-100">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
