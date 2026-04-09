<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('Sterilization Details') }} -
                {{ $sterilization->record_id }}</h2>
            <a href="{{ route('healthcare.sterilization.index') }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 text-white rounded-md hover:bg-gray-700"><i
                    class="fas fa-arrow-left mr-2"></i>Back</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-shield-virus mr-2 text-blue-600"></i>Sterilization Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Record ID</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $sterilization->record_id }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Method</dt>
                            <dd class="mt-1"><span
                                    class="px-2 py-1 text-sm font-semibold rounded-full bg-blue-100 text-blue-800">{{ ucfirst(str_replace('_', ' ', $sterilization->method)) }}</span>
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $sterilization->status === 'completed' ? 'bg-green-100 text-green-800' : ($sterilization->status === 'in_progress' ? 'bg-yellow-100 text-yellow-800' : 'bg-red-100 text-red-800') }}">{{ ucfirst(str_replace('_', ' ', $sterilization->status)) }}</span>
                            </dd>
                        </div>
                        @if ($sterilization->operator_name)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Operator</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sterilization->operator_name }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-thermometer-half mr-2 text-red-600"></i>Process Parameters</h3>
                    <dl class="space-y-4">
                        @if ($sterilization->sterilized_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Sterilized At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $sterilization->sterilized_at->format('d/m/Y H:i') }}</dd>
                            </div>
                        @endif
                        @if ($sterilization->temperature)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Temperature</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sterilization->temperature }}°C</dd>
                            </div>
                        @endif
                        @if ($sterilization->duration_minutes)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Duration</dt>
                                <dd class="mt-1 text-sm text-gray-900">{{ $sterilization->duration_minutes }} minutes
                                </dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                        class="fas fa-list mr-2 text-purple-600"></i>Items Description</h3>
                <p class="text-sm text-gray-700 whitespace-pre-line">{{ $sterilization->items_description }}</p>
            </div>

            @if ($sterilization->notes)
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-sticky-note mr-2 text-orange-600"></i>Notes</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $sterilization->notes }}</p>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
