<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Edit Triage Assessment') }} - {{ $assessment->triage_code }}
            </h2>
            <a href="{{ route('healthcare.triage.show', $assessment) }}"
                class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700">
                <i class="fas fa-arrow-left mr-2"></i>Back to Details
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <form method="POST" action="{{ route('healthcare.triage.update', $assessment) }}">
                        @csrf
                        @method('PUT')

                        <div class="space-y-6">
                            <!-- Patient Selection -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Patient Information</h3>
                                <div>
                                    <label for="patient_visit_id"
                                        class="block text-sm font-medium text-gray-700">Patient Visit</label>
                                    <select name="patient_visit_id" id="patient_visit_id" required
                                        class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                        <option value="">Select Patient Visit</option>
                                        @foreach ($visits as $visit)
                                            <option value="{{ $visit->id }}"
                                                {{ old('patient_visit_id', $assessment->patient_visit_id) == $visit->id ? 'selected' : '' }}>
                                                {{ $visit->patient->name }} - {{ $visit->visit_number }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Chief Complaint -->
                            <div>
                                <label for="chief_complaint" class="block text-sm font-medium text-gray-700">Chief
                                    Complaint</label>
                                <textarea name="chief_complaint" id="chief_complaint" required rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Describe the main reason for visit...">{{ old('chief_complaint', $assessment->chief_complaint) }}</textarea>
                            </div>

                            <!-- Priority Level -->
                            <div>
                                <label for="priority_level" class="block text-sm font-medium text-gray-700">Priority
                                    Level</label>
                                <select name="priority_level" id="priority_level" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="">Select Priority</option>
                                    <option value="critical"
                                        {{ old('priority_level', $assessment->priority_level) === 'critical' ? 'selected' : '' }}>
                                        T1 - RED (Critical - Immediate)</option>
                                    <option value="emergency"
                                        {{ old('priority_level', $assessment->priority_level) === 'emergency' ? 'selected' : '' }}>
                                        T2 - ORANGE (Emergency - < 10 min)</option>
                                    <option value="urgent"
                                        {{ old('priority_level', $assessment->priority_level) === 'urgent' ? 'selected' : '' }}>
                                        T3 - YELLOW (Urgent - < 60 min)</option>
                                    <option value="semi_urgent"
                                        {{ old('priority_level', $assessment->priority_level) === 'semi_urgent' ? 'selected' : '' }}>
                                        T4 - GREEN (Semi-Urgent - < 120 min)</option>
                                    <option value="non_urgent"
                                        {{ old('priority_level', $assessment->priority_level) === 'non_urgent' ? 'selected' : '' }}>
                                        T5 - BLUE (Non-Urgent - < 240 min)</option>
                                </select>
                            </div>

                            <!-- Vital Signs -->
                            <div class="border-b pb-6">
                                <h3 class="text-lg font-semibold text-gray-900 mb-4">Vital Signs</h3>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                                    <div>
                                        <label for="blood_pressure_systolic"
                                            class="block text-sm font-medium text-gray-700">BP Systolic</label>
                                        <input type="number" name="blood_pressure_systolic"
                                            id="blood_pressure_systolic"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('blood_pressure_systolic', $assessment->blood_pressure_systolic) }}"
                                            placeholder="120">
                                    </div>
                                    <div>
                                        <label for="blood_pressure_diastolic"
                                            class="block text-sm font-medium text-gray-700">BP Diastolic</label>
                                        <input type="number" name="blood_pressure_diastolic"
                                            id="blood_pressure_diastolic"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('blood_pressure_diastolic', $assessment->blood_pressure_diastolic) }}"
                                            placeholder="80">
                                    </div>
                                    <div>
                                        <label for="heart_rate" class="block text-sm font-medium text-gray-700">Heart
                                            Rate</label>
                                        <input type="number" name="heart_rate" id="heart_rate"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('heart_rate', $assessment->heart_rate) }}" placeholder="72">
                                    </div>
                                    <div>
                                        <label for="temperature"
                                            class="block text-sm font-medium text-gray-700">Temperature (°C)</label>
                                        <input type="number" name="temperature" id="temperature" step="0.1"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('temperature', $assessment->temperature) }}"
                                            placeholder="36.5">
                                    </div>
                                    <div>
                                        <label for="respiratory_rate"
                                            class="block text-sm font-medium text-gray-700">Respiratory Rate</label>
                                        <input type="number" name="respiratory_rate" id="respiratory_rate"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('respiratory_rate', $assessment->respiratory_rate) }}"
                                            placeholder="16">
                                    </div>
                                    <div>
                                        <label for="oxygen_saturation"
                                            class="block text-sm font-medium text-gray-700">O2 Saturation (%)</label>
                                        <input type="number" name="oxygen_saturation" id="oxygen_saturation"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('oxygen_saturation', $assessment->oxygen_saturation) }}"
                                            placeholder="98">
                                    </div>
                                    <div>
                                        <label for="pain_score" class="block text-sm font-medium text-gray-700">Pain
                                            Score (0-10)</label>
                                        <input type="number" name="pain_score" id="pain_score" min="0"
                                            max="10"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('pain_score', $assessment->pain_score) }}" placeholder="0">
                                    </div>
                                    <div>
                                        <label for="gcs" class="block text-sm font-medium text-gray-700">GCS
                                            Score</label>
                                        <input type="number" name="gcs" id="gcs" min="3"
                                            max="15"
                                            class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                            value="{{ old('gcs', $assessment->gcs) }}" placeholder="15">
                                    </div>
                                </div>
                            </div>

                            <!-- Status -->
                            <div>
                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                <select name="status" id="status" required
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                    <option value="pending"
                                        {{ old('status', $assessment->status) === 'pending' ? 'selected' : '' }}>
                                        Pending</option>
                                    <option value="in_progress"
                                        {{ old('status', $assessment->status) === 'in_progress' ? 'selected' : '' }}>In
                                        Progress</option>
                                    <option value="completed"
                                        {{ old('status', $assessment->status) === 'completed' ? 'selected' : '' }}>
                                        Completed</option>
                                </select>
                            </div>

                            <!-- Additional Notes -->
                            <div>
                                <label for="notes" class="block text-sm font-medium text-gray-700">Additional
                                    Notes</label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    placeholder="Any additional observations...">{{ old('notes', $assessment->notes) }}</textarea>
                            </div>

                            <!-- Assessment Time -->
                            <div>
                                <label for="assessment_time"
                                    class="block text-sm font-medium text-gray-700">Assessment Time</label>
                                <input type="datetime-local" name="assessment_time" id="assessment_time"
                                    class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                    value="{{ old('assessment_time', $assessment->assessment_time ? $assessment->assessment_time->format('Y-m-d\TH:i') : '') }}">
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <a href="{{ route('healthcare.triage.show', $assessment) }}"
                                class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                                Cancel
                            </a>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                                <i class="fas fa-save mr-2"></i>Update Assessment
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
