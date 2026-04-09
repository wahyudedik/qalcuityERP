<x-app-layout>
    <x-slot name="header">Claim Asuransi Baru</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim Asuransi', 'url' => route('healthcare.insurance-claims.index')],
        ['label' => 'Claim Baru'],
    ]" />

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.insurance-claims.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="patient_id" class="block text-sm font-medium text-gray-700">Patient *</label>
                            <select name="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Patient</option>
                                @foreach ($patients as $patient)
                                    <option value="{{ $patient->id }}"
                                        {{ old('patient_id') == $patient->id ? 'selected' : '' }}>
                                        {{ $patient->name }} - {{ $patient->medical_record_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="insurance_provider" class="block text-sm font-medium text-gray-700">Insurance
                                Provider *</label>
                            <input type="text" name="insurance_provider" required
                                value="{{ old('insurance_provider') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., BPJS, Prudential">
                        </div>

                        <div>
                            <label for="policy_number" class="block text-sm font-medium text-gray-700">Policy Number
                                *</label>
                            <input type="text" name="policy_number" required value="{{ old('policy_number') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="claim_amount" class="block text-sm font-medium text-gray-700">Claim Amount (Rp)
                                *</label>
                            <input type="number" name="claim_amount" required value="{{ old('claim_amount') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="0">
                        </div>

                        <div>
                            <label for="diagnosis_code" class="block text-sm font-medium text-gray-700">Diagnosis Code
                                (ICD-10)</label>
                            <input type="text" name="diagnosis_code" value="{{ old('diagnosis_code') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., J06.9">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Claim
                                Description</label>
                            <textarea name="description" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Description of treatment...">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="submitted_at" class="block text-sm font-medium text-gray-700">Submission
                                Date</label>
                            <input type="date" name="submitted_at"
                                value="{{ old('submitted_at', now()->format('Y-m-d')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.insurance-claims.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Submit Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
