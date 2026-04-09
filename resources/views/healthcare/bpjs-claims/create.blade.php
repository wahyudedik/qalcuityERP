<x-app-layout>
    <x-slot name="header">Claim BPJS Baru</x-slot>

    {{-- Breadcrumbs --}}
    <x-breadcrumbs :items="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Healthcare', 'url' => route('healthcare.dashboard')],
        ['label' => 'Claim BPJS', 'url' => route('healthcare.bpjs-claims.index')],
        ['label' => 'Claim Baru'],
    ]" />

    <div class="py-4">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.bpjs-claims.store') }}">
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

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="bpjs_number" class="block text-sm font-medium text-gray-700">BPJS Number
                                    *</label>
                                <input type="text" name="bpjs_number" required value="{{ old('bpjs_number') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., 0001234567890">
                            </div>
                            <div>
                                <label for="claim_amount" class="block text-sm font-medium text-gray-700">Claim Amount
                                    (Rp) *</label>
                                <input type="number" name="claim_amount" required value="{{ old('claim_amount') }}"
                                    min="0"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Rp">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="diagnosis_code" class="block text-sm font-medium text-gray-700">Diagnosis
                                    Code (ICD-10)</label>
                                <input type="text" name="diagnosis_code" value="{{ old('diagnosis_code') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="e.g., J06.9">
                            </div>
                            <div>
                                <label for="submission_date" class="block text-sm font-medium text-gray-700">Submission
                                    Date *</label>
                                <input type="date" name="submission_date" required
                                    value="{{ old('submission_date', now()->format('Y-m-d')) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="treatment_description" class="block text-sm font-medium text-gray-700">Treatment
                                Description *</label>
                            <textarea name="treatment_description" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe the treatment...">{{ old('treatment_description') }}</textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Any additional information...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.bpjs-claims.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Submit Claim</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
