<x-app-layout>
    <x-slot name="header">{{ __('Lab Order Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order Number</p>
                        <p class="font-medium">{{ $order->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $order->status)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $order->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Test</p>
                        <p class="font-medium">{{ $order->labTest?->test_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Doctor</p>
                        <p class="font-medium">{{ $order->doctor?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Priority</p>
                        <p class="font-medium">{{ ucfirst($order->priority) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Clinical Notes</p>
                        <p class="font-medium">{{ $order->clinical_notes ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.laboratory.orders') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
