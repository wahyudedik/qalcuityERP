<x-app-layout>
    <x-slot name="header">Edit Claim Asuransi - {{ $insuranceClaim->claim_number }}</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim Asuransi', 'url' => route('healthcare.insurance-claims.index')],
        ['label' => 'Edit Claim'],
    ]" />

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.insurance-claims.update', $insuranceClaim) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-6">
                        <div>
                            <label for="insurance_provider" class="block text-sm font-medium text-gray-700">Insurance
                                Provider *</label>
                            <input type="text" name="insurance_provider" required
                                value="{{ old('insurance_provider', $insuranceClaim->insurance_provider) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="policy_number" class="block text-sm font-medium text-gray-700">Policy Number
                                *</label>
                            <input type="text" name="policy_number" required
                                value="{{ old('policy_number', $insuranceClaim->policy_number) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="claim_amount" class="block text-sm font-medium text-gray-700">Claim Amount (Rp)
                                *</label>
                            <input type="number" name="claim_amount" required
                                value="{{ old('claim_amount', $insuranceClaim->claim_amount) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="approved_amount" class="block text-sm font-medium text-gray-700">Approved Amount
                                (Rp)</label>
                            <input type="number" name="approved_amount"
                                value="{{ old('approved_amount', $insuranceClaim->approved_amount) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                            <select name="status"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="pending"
                                    {{ old('status', $insuranceClaim->status) === 'pending' ? 'selected' : '' }}>Pending
                                </option>
                                <option value="processing"
                                    {{ old('status', $insuranceClaim->status) === 'processing' ? 'selected' : '' }}>
                                    Processing</option>
                                <option value="approved"
                                    {{ old('status', $insuranceClaim->status) === 'approved' ? 'selected' : '' }}>
                                    Approved</option>
                                <option value="rejected"
                                    {{ old('status', $insuranceClaim->status) === 'rejected' ? 'selected' : '' }}>
                                    Rejected</option>
                                <option value="paid"
                                    {{ old('status', $insuranceClaim->status) === 'paid' ? 'selected' : '' }}>Paid
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="diagnosis_code" class="block text-sm font-medium text-gray-700">Diagnosis Code
                                (ICD-10)</label>
                            <input type="text" name="diagnosis_code"
                                value="{{ old('diagnosis_code', $insuranceClaim->diagnosis_code) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Claim
                                Description</label>
                            <textarea name="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('description', $insuranceClaim->description) }}</textarea>
                        </div>

                        <div>
                            <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Rejection
                                Reason (if applicable)</label>
                            <textarea name="rejection_reason" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">{{ old('rejection_reason', $insuranceClaim->rejection_reason) }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.insurance-claims.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Update Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
