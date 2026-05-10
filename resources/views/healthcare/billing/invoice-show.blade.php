<x-app-layout>
    <x-slot name="header">{{ __('Invoice Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Bill Number</p>
                        <p class="font-medium">{{ $invoice->bill_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $invoice->payment_status)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $invoice->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Bill Date</p>
                        <p class="font-medium">{{ $invoice->bill_date?->format('d M Y') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Total Amount</p>
                        <p class="font-medium">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Amount Paid</p>
                        <p class="font-medium">Rp {{ number_format($invoice->amount_paid, 0, ',', '.') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Balance Due</p>
                        <p class="font-medium text-red-600">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.billing.invoices') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
