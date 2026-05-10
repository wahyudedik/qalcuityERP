<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ __('New Radiology Order') }}</h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.radiology.exams.store') }}">
                    @csrf
                    <div class="space-y-6">
                        <div>
                            <label for="patient_visit_id" class="block text-sm font-medium text-gray-700">Patient Visit
                                *</label>
                            <select name="patient_visit_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Visit</option>
                                @foreach ($visits as $visit)
                                    <option value="{{ $visit->id }}"
                                        {{ old('patient_visit_id') == $visit->id ? 'selected' : '' }}>
                                        {{ $visit->patient?->name }} - {{ $visit->visit_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="exam_type" class="block text-sm font-medium text-gray-700">Exam Type *</label>
                            <select name="exam_type" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Select Exam</option>
                                <option value="xray" {{ old('exam_type') === 'xray' ? 'selected' : '' }}>X-Ray
                                </option>
                                <option value="ct_scan" {{ old('exam_type') === 'ct_scan' ? 'selected' : '' }}>CT Scan
                                </option>
                                <option value="mri" {{ old('exam_type') === 'mri' ? 'selected' : '' }}>MRI</option>
                                <option value="ultrasound" {{ old('exam_type') === 'ultrasound' ? 'selected' : '' }}>
                                    Ultrasound</option>
                                <option value="mammography" {{ old('exam_type') === 'mammography' ? 'selected' : '' }}>
                                    Mammography</option>
                            </select>
                        </div>

                        <div>
                            <label for="body_part" class="block text-sm font-medium text-gray-700">Body Part *</label>
                            <input type="text" name="body_part" required value="{{ old('body_part') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="e.g., Chest, Left Arm">
                        </div>

                        <div>
                            <label for="clinical_indication" class="block text-sm font-medium text-gray-700">Clinical
                                Indication</label>
                            <textarea name="clinical_indication" rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Reason for exam...">{{ old('clinical_indication') }}</textarea>
                        </div>

                        <div>
                            <label for="scheduled_at" class="block text-sm font-medium text-gray-700">Scheduled
                                Date/Time</label>
                            <input type="datetime-local" name="scheduled_at"
                                value="{{ old('scheduled_at', now()->format('Y-m-d\TH:i')) }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>

                        <div>
                            <label for="priority" class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="routine" {{ old('priority') === 'routine' ? 'selected' : '' }}>Routine
                                </option>
                                <option value="urgent" {{ old('priority') === 'urgent' ? 'selected' : '' }}>Urgent
                                </option>
                                <option value="stat" {{ old('priority') === 'stat' ? 'selected' : '' }}>STAT
                                    (Emergency)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="{{ route('healthcare.radiology.index') }}"
                            class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">Cancel</a>
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700"><i
                                class="fas fa-save mr-2"></i>Create Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
