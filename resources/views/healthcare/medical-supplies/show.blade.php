<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Supply Details') }} -
                {{ $supply->item_code }}</h2>
            <a href="{{ route('healthcare.medical-supplies.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-box mr-2 text-blue-600"></i>Supply Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Item Code</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $supply->item_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-lg text-gray-900">{{ $supply->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Category</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-purple-100 text-purple-800">{{ ucfirst(str_replace('_', ' ', $supply->category)) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $supply->status === 'in_stock' ? 'bg-green-100 text-green-800' : ($supply->status === 'low_stock' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst(str_replace('_', ' ', $supply->status)) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-warehouse mr-2 text-green-600"></i>Stock Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Current Quantity</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">{{ $supply->quantity }}
                                {{ $supply->unit }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Minimum Stock Level</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $supply->min_stock_level }} {{ $supply->unit }}
                            </dd>
                        </div>
                        @if ($supply->storage_location)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Storage Location</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $supply->storage_location }}</dd>
                            </div>
                        @endif
                        @if ($supply->expiry_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Expiry Date</dt>
                                <dd
                                    class="mt-1 text-sm {{ $supply->expiry_date->isPast() ? 'text-red-600 font-semibold' : 'text-gray-900' }}">
                                    {{ $supply->expiry_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            @if ($supply->supplier || $supply->notes)
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    @if ($supply->supplier)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-truck mr-2 text-orange-600"></i>Supplier Information</h3>
                            <dl class="space-y-4">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Supplier</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ $supply->supplier }}</dd>
                                </div>
                            </dl>
                        </div>
                    @endif

                    @if ($supply->notes)
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                    class="fas fa-sticky-note mr-2 text-blue-600"></i>Notes</h3>
                            <p class="text-sm text-gray-700 whitespace-pre-line">{{ $supply->notes }}</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
