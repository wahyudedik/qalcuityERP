<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-calendar-alt text-blue-600"></i> Payment Plans
            </h1>
            <p class="text-gray-500">Patient payment installment plans</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Plan #</th>
                                    <th>Patient</th>
                                    <th>Total Amount</th>
                                    <th>Paid</th>
                                    <th>Balance</th>
                                    <th>Installments</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($paymentPlans as $plan)
                                    <tr>
                                        <td><code>{{ $plan->plan_number }}</code></td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $plan->patient) }}">
                                                {{ $plan->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td><strong>Rp {{ number_format($plan->total_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td class="text-emerald-600">Rp
                                            {{ number_format($plan->paid_amount ?? 0, 0, ',', '.') }}</td>
                                        <td class="text-red-600">Rp {{ number_format($plan->balance ?? 0, 0, ',', '.') }}
                                        </td>
                                        <td>
                                            {{ $plan->paid_installments ?? 0 }}/{{ $plan->total_installments ?? 0 }}
                                            <div class="w-full bg-gray-200 rounded-full overflow-hidden mt-1" style="height: 6px;">
                                                <div class="bg-emerald-500 h-full rounded-full"
                                                    style="width: {{ $plan->progress_percentage ?? 0 }}%"></div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'active' => 'success',
                                                    'completed' => 'secondary',
                                                    'defaulted' => 'danger',
                                                    'cancelled' => 'warning',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$plan->status] ?? 'secondary'  }}">
                                                {{ ucfirst($plan->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($plan->status == 'active')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-money-bill"></i> Payment
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No payment plans found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $paymentPlans->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
