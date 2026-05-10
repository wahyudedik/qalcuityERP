<x-app-layout>
    <x-slot name="header">{{ __('Radiology Exam Detail') }}</x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-500">Order Number</p>
                        <p class="font-medium">{{ $exam->order_number }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Status</p>
                        <p class="font-medium">{{ ucfirst(str_replace('_', ' ', $exam->status)) }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Patient</p>
                        <p class="font-medium">{{ $exam->patient?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Doctor</p>
                        <p class="font-medium">{{ $exam->doctor?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Scheduled Date</p>
                        <p class="font-medium">{{ $exam->scheduled_date?->format('d M Y H:i') ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Priority</p>
                        <p class="font-medium">{{ ucfirst($exam->priority) }}</p>
                    </div>
                    <div class="col-span-2">
                        <p class="text-sm text-gray-500">Clinical Indication</p>
                        <p class="font-medium">{{ $exam->clinical_indication ?? '-' }}</p>
                    </div>
                </div>

                <div class="mt-6 flex gap-2">
                    <a href="{{ route('healthcare.radiology.exams') }}"
                        class="px-4 py-2 bg-gray-200 rounded-md text-sm">Back to Exams</a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
