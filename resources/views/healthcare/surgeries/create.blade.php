<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Surgery Schedule') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.surgeries.store') }}">
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
                            <label for="surgeon_id" class="block text-sm font-medium text-gray-700">Surgeon *</label>
                            <select name="surgeon_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Surgeon</option>
                                @foreach ($surgeons as $surgeon)
                                    <option value="{{ $surgeon->id }}"
                                        {{ old('surgeon_id') == $surgeon->id ? 'selected' : '' }}>
                                        {{ $surgeon->name }} - {{ $surgeon->specialization }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="procedure_name" class="block text-sm font-medium text-gray-700">Procedure Name
                                *</label>
                            <input type="text" name="procedure_name" required value="{{ old('procedure_name') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Appendectomy">
                        </div>

                        <div>
                            <label for="operating_room" class="block text-sm font-medium text-gray-700">Operating Room
                                *</label>
                            <input type="text" name="operating_room" required value="{{ old('operating_room') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., OR-1">
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Scheduled
                                Date/Time *</label>
                            <input type="datetime-local" name="scheduled_at" required
                                value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="estimated_duration" class="block text-sm font-medium text-gray-700">Estimated
                                Duration (minutes)</label>
                            <input type="number" name="estimated_duration" value="{{ old('estimated_duration') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., 120">
                        </div>

                        <div>
                            <label for="pre_op_diagnosis" class="block text-sm font-medium text-gray-700">Pre-Operative
                                Diagnosis</label>
                            <textarea name="pre_op_diagnosis" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Pre-operative diagnosis...">{{ old('pre_op_diagnosis') }}</textarea>
                        </div>

                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                Notes</label>
                            <textarea name="notes" rows="2"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Special requirements...">{{ old('notes') }}</textarea>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.surgeries.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Schedule Surgery</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
