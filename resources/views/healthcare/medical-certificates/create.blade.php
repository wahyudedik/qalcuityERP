<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Medical Certificate') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.medical-certificates.store') }}">
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
                            <label for="certificate_type" class="block text-sm font-medium text-gray-700">Certificate
                                Type *</label>
                            <select name="certificate_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Type</option>
                                <option value="sick_leave"
                                    {{ old('certificate_type') === 'sick_leave' ? 'selected' : '' }}>Sick Leave
                                    Certificate</option>
                                <option value="fitness_to_work"
                                    {{ old('certificate_type') === 'fitness_to_work' ? 'selected' : '' }}>Fitness to
                                    Work</option>
                                <option value="medical_report"
                                    {{ old('certificate_type') === 'medical_report' ? 'selected' : '' }}>Medical Report
                                </option>
                                <option value="referral" {{ old('certificate_type') === 'referral' ? 'selected' : '' }}>
                                    Referral Letter</option>
                                <option value="vaccination"
                                    {{ old('certificate_type') === 'vaccination' ? 'selected' : '' }}>Vaccination
                                    Certificate</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="issue_date" class="block text-sm font-medium text-gray-700">Issue Date
                                    *</label>
                                <input type="date" name="issue_date" required
                                    value="{{ old('issue_date', now()->format('Y-m-d')) }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                            <div>
                                <label for="valid_until" class="block text-sm font-medium text-gray-700">Valid
                                    Until</label>
                                <input type="date" name="valid_until" value="{{ old('valid_until') }}"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            </div>
                        </div>

                        <div>
                            <label for="diagnosis" class="block text-sm font-medium text-gray-700">Diagnosis</label>
                            <input type="text" name="diagnosis" value="{{ old('diagnosis') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Medical diagnosis...">
                        </div>

                        <div>
                            <label for="description" class="block text-sm font-medium text-gray-700">Description/Notes
                                *</label>
                            <textarea name="description" required rows="4"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Certificate details...">{{ old('description') }}</textarea>
                        </div>

                        <div>
                            <label for="doctor_name" class="block text-sm font-medium text-gray-700">Issuing Doctor
                                *</label>
                            <input type="text" name="doctor_name" required value="{{ old('doctor_name') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Dr. ">
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.medical-certificates.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Issue Certificate</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
