<x-app-layout>
    <x-slot name="header">

    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold mb-0">
                <i class="fas fa-file-invoice-dollar text-blue-600"></i> Insurance Claims
            </h1>
            <p class="text-gray-500">Manage and track insurance claim submissions</p>
        </div>
    </div>
    </x-slot>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-amber-300">
                <div class="p-5 text-center">
                    <h3 class="text-amber-600">{{ $claims->where('status', 'pending')->count() }}</h3>
                    <small class="text-gray-500">Pending</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-blue-300">
                <div class="p-5 text-center">
                    <h3 class="text-sky-600">{{ $claims->where('status', 'submitted')->count() }}</h3>
                    <small class="text-gray-500">Submitted</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-emerald-300">
                <div class="p-5 text-center">
                    <h3 class="text-emerald-600">{{ $claims->where('status', 'approved')->count() }}</h3>
                    <small class="text-gray-500">Approved</small>
                </div>
            </div>
        </div>
        <div class="w-full md:w-1/4">
            <div class="bg-white rounded-2xl border border-red-300">
                <div class="p-5 text-center">
                    <h3 class="text-red-600">{{ $claims->where('status', 'rejected')->count() }}</h3>
                    <small class="text-gray-500">Rejected</small>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="w-full">
            <div class="bg-white rounded-2xl border border-gray-200">
                <div class="p-5">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead>
                                <tr>
                                    <th>Claim #</th>
                                    <th>Date</th>
                                    <th>Patient</th>
                                    <th>Insurance</th>
                                    <th>Amount</th>
                                    <th>Approved</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($claims as $claim)
                                    <tr>
                                        <td><code>{{ $claim->claim_number }}</code></td>
                                        <td>{{ $claim->claim_date?->format('d/m/Y') ?? '-' }}</td>
                                        <td>
                                            <a href="{{ route('healthcare.patients.show', $claim->patient) }}">
                                                {{ $claim->patient?->name ?? '-' }}
                                            </a>
                                        </td>
                                        <td>{{ $claim->insurance_provider ?? '-' }}</td>
                                        <td><strong>Rp {{ number_format($claim->claim_amount ?? 0, 0, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @if ($claim->approved_amount)
                                                <strong class="text-emerald-600">Rp
                                                    {{ number_format($claim->approved_amount, 0, ',', '.') }}</strong>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $statusColors = [
                                                    'pending' => 'warning',
                                                    'submitted' => 'info',
                                                    'approved' => 'success',
                                                    'rejected' => 'danger',
                                                    'partial' => 'secondary',
                                                ];
                                            @endphp
                                            <span class="badge bg-{{ $statusColors[$claim->status] ?? 'secondary'  }}">
                                                {{ ucfirst($claim->status) }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="flex gap-1">
                                                <button class="px-3 py-1.5 border border-blue-500 text-blue-600 hover:bg-blue-50 rounded-lg text-xs transition">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                @if ($claim->status == 'pending')
                                                    <button class="px-3 py-1.5 border border-emerald-500 text-emerald-600 hover:bg-emerald-50 rounded-lg text-xs transition">
                                                        <i class="fas fa-paper-plane"></i> Submit
                                                    </button>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-6 text-gray-400">No insurance claims found
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $claims->links() }}
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
