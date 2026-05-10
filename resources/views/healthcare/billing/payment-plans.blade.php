<x-app-layout>
    <x-slot name="header">{{ __('Payment Plans') }}</x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Patient</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Installments</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @forelse($paymentPlans ?? [] as $plan)
                            <tr>
                                <td class="px-6 py-4 text-sm">{{ $plan->patient?->name ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">Rp
                                    {{ number_format($plan->total_amount ?? 0, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-sm">{{ $plan->total_installments ?? '-' }}</td>
                                <td class="px-6 py-4 text-sm">{{ ucfirst($plan->status ?? '-') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-4 text-center text-gray-500">No payment plans found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                @if (method_exists($paymentPlans, 'links'))
                    <div class="mt-4">{{ $paymentPlans->links() }}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
