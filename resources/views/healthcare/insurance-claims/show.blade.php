<x-app-layout>
    <x-slot name="header">Detail Claim Asuransi - {{ $insuranceClaim->claim_number }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim Asuransi', 'url' => route('healthcare.insurance-claims.index')],
        ['label' => 'Detail Claim'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-blue-600"></i>Claim Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Claim Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $insuranceClaim->claim_number }}
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $insuranceClaim->patient?->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Insurance Provider</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $insuranceClaim->insurance_provider }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Policy Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $insuranceClaim->policy_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Diagnosis Code</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $insuranceClaim->diagnosis_code ?? '-' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $insuranceClaim->status === 'approved' ? 'bg-green-100 text-green-800' : ($insuranceClaim->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($insuranceClaim->status) }}</span>
                            </dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-money-bill-wave mr-2 text-green-600"></i>Financial Details</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Claim Amount</dt>
                            <dd class="mt-1 text-2xl font-bold text-gray-900">Rp
                                {{ number_format($insuranceClaim->claim_amount, 0, ',', '.') }}</dd>
                        </div>
                        @if ($insuranceClaim->approved_amount)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved Amount</dt>
                                <dd class="mt-1 text-2xl font-bold text-green-600">Rp
                                    {{ number_format($insuranceClaim->approved_amount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Submitted At</dt>
                            <dd class="mt-1 text-sm text-gray-900">
                                {{ $insuranceClaim->submitted_at ? $insuranceClaim->submitted_at->format('d/m/Y') : '-' }}
                            </dd>
                        </div>
                        @if ($insuranceClaim->processed_at)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Processed At</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $insuranceClaim->processed_at->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                    @if ($insuranceClaim->description)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Description</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                {{ $insuranceClaim->description }}</dd>
                        </div>
                    @endif
                    @if ($insuranceClaim->rejection_reason)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                            <dd class="mt-1 text-sm text-red-600 whitespace-pre-line">
                                {{ $insuranceClaim->rejection_reason }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
