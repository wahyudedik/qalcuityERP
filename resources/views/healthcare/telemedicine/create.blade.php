<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Telemedicine Consultation') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.telemedicine.store') }}">
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
                            <label for="doctor_id" class="block text-sm font-medium text-gray-700">Doctor *</label>
                            <select name="doctor_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Doctor</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->id }}"
                                        {{ old('doctor_id') == $doctor->id ? 'selected' : '' }}>
                                        {{ $doctor->name }} - {{ $doctor->specialization }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="consultation_type" class="block text-sm font-medium text-gray-700">Consultation
                                Type *</label>
                            <select name="consultation_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="video" {{ old('consultation_type') === 'video' ? 'selected' : '' }}>
                                    Video Call</option>
                                <option value="voice" {{ old('consultation_type') === 'voice' ? 'selected' : '' }}>
                                    Voice Call</option>
                                <option value="chat" {{ old('consultation_type') === 'chat' ? 'selected' : '' }}>Chat
                                </option>
                            </select>
                        </div>

                        <div>
                            <label for="chief_complaint" class="block text-sm font-medium text-gray-700">Chief Complaint
                                *</label>
                            <textarea name="chief_complaint" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Describe the main complaint...">{{ old('chief_complaint') }}</textarea>
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Scheduled
                                Date/Time *</label>
                            <input type="datetime-local" name="scheduled_at" required
                                value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
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
                        <a href="{{ route('healthcare.telemedicine.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Schedule Consultation</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
