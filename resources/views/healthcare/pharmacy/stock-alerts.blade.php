<x-app-layout>
    <x-slot name="header">{{ __('Stock Alerts') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Low Stock -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-amber-600 mb-4">Stok Menipis</h3>
                    <div class="space-y-3">
                        @forelse($lowStock ?? [] as $item)
                            <div class="flex justify-between items-center p-3 bg-amber-50 rounded-lg">
                                <div>
                                    <p class="font-medium">{{ $item->item_name }}</p>
                                    <p class="text-xs text-gray-500">Min: {{ $item->minimum_stock }}
                                        {{ $item->unit_of_measure }}</p>
                                </div>
                                <span
                                    class="px-2 py-1 text-xs font-bold bg-amber-500 text-white rounded">{{ $item->stock_quantity }}
                                    {{ $item->unit_of_measure }}</span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">Tidak ada item stok menipis.</p>
                        @endforelse
                    </div>
                </div>

                <!-- Out of Stock -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-600 mb-4">Stok Habis</h3>
                    <div class="space-y-3">
                        @forelse($outOfStock ?? [] as $item)
                            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
                                <div>
                                    <p class="font-medium">{{ $item->item_name }}</p>
                                    <p class="text-xs text-gray-500">{{ $item->generic_name ?? '-' }}</p>
                                </div>
                                <span class="px-2 py-1 text-xs font-bold bg-red-500 text-white rounded">0</span>
                            </div>
                        @empty
                            <p class="text-gray-500 text-center">Tidak ada item stok habis.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
