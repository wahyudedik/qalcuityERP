<x-app-layout>
    <x-slot name="header">{{ __('Billing') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-6">
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
                    <p class="text-sm text-gray-500">Overdue</p>
                    <p class="text-2xl font-semibold text-red-600">{{ $statistics['overdue'] ?? 0 }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Revenue</p>
                    <p class="text-xl font-semibold">Rp
                        {{ number_format($statistics['total_revenue'] ?? 0, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-4">
                    <p class="text-sm text-gray-500">Pending Claims</p>
                    <p class="text-2xl font-semibold text-blue-600">{{ $statistics['pending_claims'] ?? 0 }}</p>
                </div>
            </div>

            <div class="flex gap-4">
                <a href="{{ route('healthcare.billing.invoices') }}"
                    class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm">Invoices</a>
                <a href="{{ route('healthcare.billing.insurance-claims') }}"
                    class="px-4 py-2 bg-purple-600 text-white rounded-md text-sm">Insurance Claims</a>
                <a href="{{ route('healthcare.billing.reports') }}"
                    class="px-4 py-2 bg-gray-600 text-white rounded-md text-sm">Reports</a>
            </div>
        </div>
    </div>
</x-app-layout>
