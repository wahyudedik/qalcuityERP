<x-app-layout>
    <x-slot name="header">{{ __('Claim Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Claim Number</p>
                        <p class="font-medium">{{ $claim->claim_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst($claim->status ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $claim->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Amount</p>
                        <p class="font-medium">Rp {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
                <div class="mt-6">
                    <a href="{{ route('healthcare.billing.insurance-claims') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
