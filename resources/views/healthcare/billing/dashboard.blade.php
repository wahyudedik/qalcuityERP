<x-app-layout>
    <x-slot name="header">{{ __('Billing Dashboard') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Total Invoices</p>
                    <p class="text-2xl font-semibold">{{ $statistics['total_invoices'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending</p>
                    <p class="text-2xl font-semibold text-yellow-600">{{ $statistics['pending_payment'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Paid</p>
                    <p class="text-2xl font-semibold text-green-600">{{ $statistics['paid'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Revenue</p>
                    <p class="text-xl font-semibold">Rp
                        {{ number_format($statistics['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Recent Invoices -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium mb-4">Recent Invoices</h3>
                    @forelse($recentInvoices ?? [] as $invoice)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <p class="text-sm font-medium">{{ $invoice->bill_number }}</p>
                                <p class="text-xs text-gray-500">{{ $invoice->patient?->name ?? '-' }}</p>
                            </div>
                            <p class="text-sm">Rp {{ number_format($invoice->total_amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No recent invoices.</p>
                    @endforelse
                </div>

                <!-- Overdue -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-medium text-red-600 mb-4">Overdue Invoices</h3>
                    @forelse($overdueInvoices ?? [] as $invoice)
                        <div class="flex justify-between items-center py-2 border-b">
                            <div>
                                <p class="text-sm font-medium">{{ $invoice->bill_number }}</p>
                                <p class="text-xs text-gray-500">{{ $invoice->patient?->name ?? '-' }}</p>
                            </div>
                            <p class="text-sm text-red-600">Rp {{ number_format($invoice->balance_due, 0, ',', '.') }}
                            </p>
                        </div>
                    @empty
                        <p class="text-gray-500 text-center">No overdue invoices.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
