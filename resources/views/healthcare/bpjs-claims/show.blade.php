<x-app-layout>
    <x-slot name="header">Detail Claim BPJS - {{ $bpjsClaim->claim_number }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS', 'url' => route('healthcare.bpjs-claims.index')],
        ['label' => 'Detail Claim'],
    ]" />

    <div class="py-4">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-file-invoice-dollar mr-2 text-blue-600"></i>Claim Information</h3>
                    <dl class="space-y-4">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Claim Number</dt>
                            <dd class="mt-1 text-lg font-semibold text-gray-900">{{ $bpjsClaim->claim_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Patient</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bpjsClaim->patient->name ?? 'N/A' }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">BPJS Number</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $bpjsClaim->bpjs_number }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Status</dt>
                            <dd class="mt-1">
                                <span
                                    class="px-2 py-1 text-xs font-semibold rounded-full {{ $bpjsClaim->status === 'approved' ? 'bg-green-100 text-green-800' : ($bpjsClaim->status === 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">{{ ucfirst($bpjsClaim->status) }}</span>
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
                                {{ number_format($bpjsClaim->claim_amount, 0, ',', '.') }}</dd>
                        </div>
                        @if ($bpjsClaim->approved_amount)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Approved Amount</dt>
                                <dd class="mt-1 text-xl font-semibold text-green-600">Rp
                                    {{ number_format($bpjsClaim->approved_amount, 0, ',', '.') }}</dd>
                            </div>
                        @endif
                        @if ($bpjsClaim->submission_date)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Submission Date</dt>
                                <dd class="mt-1 text-sm text-gray-900">
                                    {{ $bpjsClaim->submission_date->format('d/m/Y') }}</dd>
                            </div>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-notes-medical mr-2 text-red-600"></i>Treatment Information</h3>
                    <dl class="space-y-4">
                        @if ($bpjsClaim->diagnosis_code)
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Diagnosis Code</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">{{ $bpjsClaim->diagnosis_code }}
                                </dd>
                            </div>
                        @endif
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Treatment Description</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">
                                {{ $bpjsClaim->treatment_description }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4"><i
                            class="fas fa-info-circle mr-2 text-purple-600"></i>Additional Information</h3>
                    @if ($bpjsClaim->rejection_reason)
                        <dl class="space-y-4">
                            <div>
                                <dt class="text-sm font-medium text-gray-500">Rejection Reason</dt>
                                <dd class="mt-1 text-sm text-red-600 font-semibold whitespace-pre-line">
                                    {{ $bpjsClaim->rejection_reason }}</dd>
                            </div>
                        </dl>
                    @endif
                    @if ($bpjsClaim->notes)
                        <div class="mt-4 pt-4 border-t">
                            <dt class="text-sm font-medium text-gray-500">Notes</dt>
                            <dd class="mt-1 text-sm text-gray-700 whitespace-pre-line">{{ $bpjsClaim->notes }}</dd>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
