<x-app-layout>
    <x-slot name="header">{{ __('Expiring Soon') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Item</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Batch</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Days Left</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($items ?? [] as $item)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $item->item_name }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item->batch_number ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ $item->stock_quantity }} {{ $item->unit_of_measure }}
                                </td>
                                <td class="px-6 py-4 text-sm text-red-600">
                                    {{ $item->expiry_date?->format('d M Y') ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">
                                    {{ $item->expiry_date ? now()->diffInDays($item->expiry_date) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500">Tidak ada item yang akan
                                    kadaluarsa.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
