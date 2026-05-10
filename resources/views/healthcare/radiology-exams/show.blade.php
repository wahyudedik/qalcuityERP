<x-app-layout>
    <x-slot name="header">{{ __('Radiology Exam Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order Number</p>
                        <p class="font-medium">{{ $radiology_exam->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $radiology_exam->status)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $radiology_exam->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Exam Type</p>
                        <p class="font-medium">{{ $radiology_exam->exam?->exam_name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Scheduled Date</p>
                        <p class="font-medium">{{ $radiology_exam->scheduled_date?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Priority</p>
                        <p class="font-medium">{{ ucfirst($radiology_exam->priority) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Clinical Indication</p>
                        <p class="font-medium">{{ $radiology_exam->clinical_indication ?? '-' }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Notes</p>
                        <p class="font-medium">{{ $radiology_exam->notes ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.radiology-exams.index') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
