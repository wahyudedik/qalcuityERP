<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Equipment Details') }} -
                {{ $equipment->equipment_code }}</h2>
            <a href="{{ route('healthcare.medical-equipment.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Equipment Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Equipment Code</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $equipment->equipment_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Name</dt>
                            <dd class="mt-1 text-lg text-gray-900">{{ $equipment->name }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Type</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst($equipment->equipment_type) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $equipment->status === 'available' ? 'bg-green-100 text-green-800' : ($equipment->status === 'in_use' ? 'bg-blue-100 text-blue-800' : ($equipment->status === 'maintenance' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800')) }}">{{ ucfirst(str_replace('_', ' ', $equipment->status)) }}</span>
                            </dd>
                        </div>
                        @if ($equipment->location)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Location</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->location }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-cogs mr-2 text-purple-600"></i>Manufacturer Details</h3>
                    <dl class="space-y-4">
                        @if ($equipment->manufacturer)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Manufacturer</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->manufacturer }}</dd>
                            </div>
                        @endif
                        @if ($equipment->model)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Model</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->model }}</dd>
                            </div>
                        @endif
                        @if ($equipment->serial_number)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Serial Number</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->serial_number }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-calendar mr-2 text-green-600"></i>Warranty & Maintenance</h3>
                    <dl class="space-y-4">
                        @if ($equipment->purchase_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Purchase Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $equipment->purchase_date->format('d/m/Y') }}
                                </dd>
                            </div>
                        @endif
                        @if ($equipment->warranty_expiry)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Warranty Expiry</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $equipment->warranty_expiry->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                        @if ($equipment->last_maintenance_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Last Maintenance</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $equipment->last_maintenance_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                @if ($equipment->notes)
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                                class="fas fa-sticky-note mr-2 text-orange-600"></i>Notes</h3>
                        <p class="text-sm text-gray-700 whitespace-pre-line">{{ $equipment->notes }}</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
