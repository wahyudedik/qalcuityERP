<x-app-layout>
    <x-slot name="header">{{ __('Lab Result Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Result Number</p>
                        <p class="font-medium">{{ $result->result_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst($result->status ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $result->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Order</p>
                        <p class="font-medium">{{ $result->order?->order_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Result Value</p>
                        <p class="font-medium">{{ $result->result_value ?? '-' }} {{ $result->result_unit ?? '' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Reference Range</p>
                        <p class="font-medium">{{ $result->reference_range ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Critical</p>
                        <p class="font-medium {{ $result->is_critical ? 'text-red-600' : '' }}">
                            {{ $result->is_critical ? 'Yes' : 'No' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Verified</p>
                        <p class="font-medium">
                            {{ $result->verified_at ? 'Yes - ' . $result->verified_at->format('d M Y H:i') : 'Pending' }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.laboratory.results') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
