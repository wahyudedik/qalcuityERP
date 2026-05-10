<x-app-layout>
    <x-slot name="header">{{ __('Schedule Radiology Exam') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.radiology-exams.store') }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Patient</label>
                            <select name="patient_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Patient</option>
                                @if (isset($order) && $order)
                                    <option value="{{ $order->patient_id }}" selected>{{ $order->patient?->name }}
                                    </option>
                                @endif
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Exam Type</label>
                            <select name="exam_id" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="">Select Exam</option>
                                @foreach ($examCatalog ?? [] as $exam)
                                    <option value="{{ $exam->id }}">{{ $exam->exam_name }} ({{ $exam->modality }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                            <input type="datetime-local" name="scheduled_date" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" required
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="routine">Routine</option>
                                <option value="urgent">Urgent</option>
                                <option value="stat">STAT</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Clinical Indication</label>
                            <textarea name="clinical_indication" required rows="3"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm"></textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Schedule
                                Exam</button>
                            <a href="{{ route('healthcare.radiology-exams.index') }}"
                                class="px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
