<x-app-layout>
    <x-slot name="header">{{ __('Edit Radiology Exam') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('healthcare.radiology-exams.update', $radiology_exam) }}">
                    @csrf
                    @method('PUT')
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Scheduled Date</label>
                            <input type="datetime-local" name="scheduled_date"
                                value="{{ $radiology_exam->scheduled_date?->format('Y-m-d\TH:i') }}"
                                class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Priority</label>
                            <select name="priority" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">
                                <option value="routine" {{ $radiology_exam->priority === 'routine' ? 'selected' : '' }}>
                                    Routine</option>
                                <option value="urgent" {{ $radiology_exam->priority === 'urgent' ? 'selected' : '' }}>
                                    Urgent</option>
                                <option value="stat" {{ $radiology_exam->priority === 'stat' ? 'selected' : '' }}>STAT
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Clinical Indication</label>
                            <textarea name="clinical_indication" rows="3" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $radiology_exam->clinical_indication }}</textarea>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">Notes</label>
                            <textarea name="notes" rows="2" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm">{{ $radiology_exam->notes }}</textarea>
                        </div>

                        <div class="flex gap-2">
                            <button type="submit"
                                class="px-4 py-2 bg-blue-600 text-white rounded-md text-sm hover:bg-blue-700">Update</button>
                            <a href="{{ route('healthcare.radiology-exams.show', $radiology_exam) }}"
                                class="px-4 py-2 bg-gray-200 rounded-md text-sm">Cancel</a>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
