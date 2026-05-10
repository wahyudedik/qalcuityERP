<x-app-layout>
    <x-slot name="header">{{ __('Prescription Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Prescription Number</p>
                        <p class="font-medium">{{ $prescription->prescription_number ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst($prescription->status ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">
                            {{ $prescription->patient?->full_name ?? ($prescription->patient?->name ?? '-') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Doctor</p>
                        <p class="font-medium">{{ $prescription->doctor?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Date</p>
                        <p class="font-medium">{{ $prescription->created_at?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.pharmacy.prescriptions') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                    @if ($prescription->status === 'pending')
                        <form method="POST"
                            action="{{ route('healthcare.pharmacy.prescriptions.verify', $prescription) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Verify</button>
                        </form>
                    @endif
                    @if ($prescription->status === 'verified')
                        <form method="POST"
                            action="{{ route('healthcare.pharmacy.prescriptions.dispense', $prescription) }}">
                            @csrf
                            <button type="submit"
                                class="px-4 py-2 bg-green-600 text-white rounded-md text-sm">Dispense</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
